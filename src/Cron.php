<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Data\Yaml;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use Exception;

class Cron {


	public static function route() {

		$secret = option( 'sgkirby.commentions.secret' );

		// validation with actionable error messages
		if ( !get('token') )
			return new Response( '<p>Error: Token attribute missing from URL or empty.</p>', 'text/html', 403 );
		if ( $secret == '' )
			return new Response( '<p>Error: No token configured in config file.</p>', 'text/html', 500 );
		if ( strlen( $secret ) < 10 )
			return new Response( '<p>Error: Token set in config is too short.</p>', 'text/html', 500 );
		if ( preg_match( "/[&%#+]/i", $secret ) )
			return new Response( '<p>Error: Token set in config contains invalid characters.</p>', 'text/html', 500 );
		if ( get('token') != $secret )
			return new Response( '<p>Error: Incorrect token in URL attribute.</p>', 'text/html', 403 );

		try {
			Cron::processQueue();
			return new Response( '<p>Success.</p>', 'text/html', 200 );
		} catch(Exception $e) {
			return new Response( '<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400 );
		}

	}


    /**
     * Handles the asynchronous processing of webmentions from the queue
     *
     * @return bool
     */

	public static function processQueue() {

		// loop through all pages in the index
		foreach ( site()->index() as $page ) :

			// loop through every webmention request in the queue of every page
			foreach( Storage::read( $page, 'queue' ) as $queueitem ) :

				// skip requests already marked as failed
				if ( ! isset( $queueitem['failed'] ) ) :

					if ( $result = Commentions::processWebmention( $queueitem ) ) :

						// if parsing was successful, $result is the array with the saved data
						if ( is_array( $result ) ) :

							// delete webmention from queue after successful parsing
							Storage::update( $page, $queueitem['uid'], 'delete', 'queue' );

							// trigger a hook that allows further processing of the data
							kirby()->trigger( "commentions.webmention:after", $page, $result );

							return true;

						// if parsing led to an error, $result is a string with the error message
						else :

							// mark failed requests as failed
							Storage::update( $page, $queueitem['uid'], [ 'failed' => $result ], 'queue' );

						endif;

					else :
				
						throw new Exception( 'Problem processing queue file.' );

					endif;

				endif;

			endforeach;
        endforeach;
        
	}


}
