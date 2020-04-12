<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

return [

	'props' => [

		'headline' => function ( $headline = "Comments and Webmentions" ) {
			return $headline;
		},

		'show' => function ( $show = "page" ) {
			if ( ! in_array( $show, [ 'page', 'pending', 'all' ] ) )
				$show = 'page';
			return $show;
		},

	],

	'computed' => [

		'commentions' => function () {

			// retrieve the show property
			switch ( $this->show() ) {
				case 'all':
					$comments = site()->index()->commentions('all');
					break;
				case 'pending':
					$comments = site()->index()->commentions('pending');
					break;
				default:
					$page = $this->model();
					$comments = $page->commentions('all');
					break;
			}

			// transpose all comments into an array
			foreach ( $comments as $data ) {

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
					$options[0] = ['icon' => 'remove', 'text' => 'Unapprove', 'click' => 'unapprove-'.$commentid.'|'.$data['pageid']];
				else
					$options[0] = ['icon' => 'check', 'text' => 'Approve', 'click' => 'approve-'.$commentid.'|'.$data['pageid']];
				$options[1] = ['icon' => 'trash', 'text' => 'Delete', 'click' => 'delete-'.$commentid.'|'.$data['pageid']];

				$return[ $commentid ] = [ $content, $options ];

			}

			// return the array to the vue component
			return $return;

		}

	],

];
