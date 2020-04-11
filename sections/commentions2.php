<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

return [

	'props' => [

		'headline' => function ( $headline = "Comments and Webmentions" ) {
			return $headline;
		}

	],

	'computed' => [

		'approve' => function () {

			return 'boo';

		},

		'commentions' => function () {

			// set the page currently open in the panel
			$page = $this->model();

			// read the commentions text file and decode the yaml
			$datafile = $page->root() . '/.commentions.txt';
			$rawdata = Data::read( $datafile );
			$dataarray = Data::decode( $rawdata['comments'], 'yaml' );

			//file_put_contents( __DIR__ . '/debug.txt', print_r( $dataarray, true ) );

			// transpose all comments into an array
			$i = 0;
			$array = [];
			foreach ( $dataarray as $data ) {

				$text = htmlspecialchars( $data['message'] );
				$name = htmlspecialchars( $data['name'] );
				$meta = $data['type'];

				$commentid = strtotime( $data['timestamp'] );

				$content = $name
					. ', ' . date( $data['timestamp'] )
					. ' (' . $meta
					. '): ' . $text;

				// create the dropdown options
				if ( $data['approved'] == 'true' )
					$options[0] = ['icon' => 'remove', 'text' => 'Unapprove', 'click' => 'unapprove-'.$commentid.'|'.$page->id()];
				else
					$options[0] = ['icon' => 'check', 'text' => 'Approve', 'click' => 'approve-'.$commentid.'|'.$page->id()];
				$options[1] = ['icon' => 'trash', 'text' => 'Delete', 'click' => 'delete-'.$commentid.'|'.$page->id()];

				$array[ $commentid ] = [ $content, $options ];
				$i++;

			}
			// file_put_contents( __DIR__ . '/debug.txt', print_r( $array, true ) );

			// return the array to the vue component
			if ( $i >= 0 )
				return $array;

		}

	],

];
