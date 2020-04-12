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
	
		'commentions' => function () {

			$array = [];
			foreach( site()->index()->commentions('pending') as $data ) :

				$text = htmlspecialchars( $data['message'] );
				$name = htmlspecialchars( $data['name'] );
				$meta = $data['type'];

				$commentid = strtotime( $data['timestamp'] );

				$content =
					$name
					. ', ' . date( 'Y-m-d H:i', strtotime($data['timestamp']) )
					. ' (' . $meta
					. '): ' . $text;

				// create the dropdown options
				if ( $data['approved'] == 'true' )
					$options[0] = ['icon' => 'remove', 'text' => 'Unapprove', 'click' => 'unapprove-'.$commentid.'|'.$data['pageid']];
				else
					$options[0] = ['icon' => 'check', 'text' => 'Approve', 'click' => 'approve-'.$commentid.'|'.$data['pageid']];
				$options[1] = ['icon' => 'trash', 'text' => 'Delete', 'click' => 'delete-'.$commentid.'|'.$data['pageid']];

				$return[ $commentid ] = [ $content, $options ];

			endforeach;
			
			return $return;
			
		}
		
	],

];
