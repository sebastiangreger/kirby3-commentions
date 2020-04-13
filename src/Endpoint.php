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

class Endpoint {


	public static function route() {

		if ( kirby()->request()->is('POST') ) :
		
			// for POST requests, queue the incoming webmention
			try {
				Endpoint::queueWebmention();
				if ( get('manualmention') )
					go( get('target') . "?thx=accepted" );
				else
					return new Response( '<p>Accepted.</p>', 'text/html', 202 );
			} catch(Exception $e) {
				return new Response( '<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400 );
			}

		else :

			// for GET requests, provide a submission form instead
			return new Response( '
				<html><body>
					<form action="' . site()->url() . '/' . option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' ) .  '" method="post">
						<div>
							<label for="target">The URL on ' . site()->url() . ' you linked to</label>
							<input type="url" name="target" value="' . get('target') . '" pattern=".*' . str_replace( '.', '\.', site()->url() ) . '.*" required>
						</div>
						<div>
							<label for="source">The URL of your response (full URL incl https://)</label>
							<input type="url" name="source" value="' . get('source') . '" pattern=".*http.*" required>
						</div>
						<input type="submit" name="submit" value="Submit">
					</form>
				</body></html>
			', 'text/html', 404);

		endif;

	}

	public static function queueWebmention() {

		// source is the external site sending the webmention;
		$source = get('source');
		
		// target is the local URL, claimed to be mentioned in the source
		$target = get('target');

		if( !V::url( $source ) )
			throw new Exception( 'Invalid source URL.' );

		if( !V::url( $target ) )
			throw new Exception( 'Invalid target URL.' );

		if( $source == $target )
			throw new Exception( 'Target and source are identical.' );

		if( !Str::contains( $target, str_replace( array( 'http:', 'https:' ), '', site()->url() ) ) )
			throw new Exception( 'Target URL not on this site.' );

		// find the Kirby page the target URL refers to
		$path = Url::path( $target );
		if ( $path == '' )
			$page = page('home');
		else
			$page = page( kirby()->call( trim( $path, '/' ) ) );

		// if the target resolves to an existing Kirby page, add to the queue in the according commention file
		if( $page != null )
			Storage::add( $page, [ 'target' => $target, 'source' => $source, 'timestamp' => time() ], 'queue' );
		// all other requests are enqueued in the home page commention file
		else
			Storage::add( page('home'), [ 'target' => $target, 'source' => $source, 'timestamp' => time() ], 'queue' );

	}

}
