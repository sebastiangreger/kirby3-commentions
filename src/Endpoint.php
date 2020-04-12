<?php

namespace sgkirby\Commentions;

use Kirby\Http\Url;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use Exception;

class Endpoint {

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
			Commentions::add( $page, [ 'target' => $target, 'source' => $source, 'timestamp' => time() ], 'queue' );
		// all other requests are enqueued in the home page commention file
		else
			Commentions::add( page('home'), [ 'target' => $target, 'source' => $source, 'timestamp' => time() ], 'queue' );

	}

}
