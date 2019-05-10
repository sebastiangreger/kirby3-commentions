<?php

namespace sgkirby\Commentions;

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

		$hash = sha1( $source );
		$file = kirby()->root() . '/content/.commentions/queue' . DS . 'webmention-' . time() . '-' . $hash . '.json';
		$json = json_encode( [ 'target' => $target, 'source' => $source ] );
		F::write( $file, $json );

	}

}
