<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

return [

	'props' => [
	
		'headline' => function ( $message = "Pending comments" ) {
			return $message;
		}
		
	],
	
	'computed' => [
	
		'approve' => function () {
			
			return 'boo';
			
		},
	
		'commentions' => function () {
			
			$i = 0;
			$array = [];
			$files = kirby()->root() . '/content/.commentions/inbox/*.json';
			foreach ( glob( $files ) as $inboxfile ) {
				
				$data = Data::read( $inboxfile, 'json' );

				if ( isset( $data['message'] ) )
					$text = htmlspecialchars( $data['message'] );
				else
					$text = htmlspecialchars( $data['text'] );

				if ( $data['type'] == 'comment' ) :
					$name = htmlspecialchars( $data['name'] );
					$meta = $data['type'] . ' on ' . $data['target'];
				else :
					$name = htmlspecialchars( $data['author']['name'] );
					$meta = $data['type'] . ' on ' . $data['source'] . ' from ' . $data['target'];
				endif;

				$array[ F::filename( $inboxfile ) ] =
					$name
					. ', ' . date( 'Y-m-d H:i', $data['timestamp'] )
					. ' (' . $meta
					. '): ' . $text;

				$i++;
				
			}

			if ( $i >= 0 )
				return $array;
			
		}
		
	],

];
