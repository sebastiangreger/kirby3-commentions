<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

return [

	'props' => [

		'show' => function ( $show = 'page' ) {
			if ( ! in_array( $show, [ 'page', 'pending', 'all' ] ) )
				$show = 'page';
			return $show;
		},

		'empty' => function ( $empty = null ) {
			if ( $empty === null ) :
				if ( $this->show() == 'pending' )
					$empty = 'No pending comments';
				else
					$empty = 'No comments yet';
			endif;
			return $empty;
		},

		'headline' => function ( $headline = null ) {
			if ( $headline === null ) :
				if ( $this->show() == 'pending' )
					$headline = 'Comments and Webmentions inbox';
				elseif ( $this->show() == 'all' )
					$headline = 'All comments and Webmentions';
				else
					$headline = 'Comments and Webmentions';
			endif;
			return $headline;
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

				$content =
					strtoupper( $meta ) . ": "
					. $name . " ("
					. date( $data['timestamp'] ) . ")\n"
					. ( !empty($data['source']) ? $data['source'] . "\n" : '' )
					. ( empty($data['source']) && !empty($data['website']) ? $data['website'] . "\n" : '' )
					. "\n"
					. $text;

				$options = [];

				// appearance and dropdown options depend on comment status
				if ( $data['approved'] == 'true' ) :
					$class = 'k-list-item-commention-approved';
					$icon = [ 'type' => 'chat', 'back' => 'transparent' ];
					$options[] = [
						'icon' => 'remove',
						'text' => 'Unapprove',
						'click' => 'unapprove-'.$commentid.'|'.$data['pageid']
					];
				else :
					$class = 'k-list-item-commention-pending';
					$icon = [ 'type' => 'protected', 'back' => 'transparent' ];
					$options[] = [
						'icon' => 'check',
						'text' => 'Approve',
						'click' => 'approve-'.$commentid.'|'.$data['pageid']
					];
				endif;

				// second option is always 'delete'
				$options[] = [
					'icon' => 'trash',
					'text' => 'Delete',
					'click' => 'delete-'.$commentid.'|'.$data['pageid']
				];

				// third option is link to source
				if ( ! empty($data['source']) ) :
					$options[] = '-';
					$options[] = [
						'icon' => 'open',
						'text' => 'View source',
						'click' => 'open-'.$commentid.'|'.$data['source']
					];
				elseif ( ! empty($data['website']) ) :
					$options[] = '-';
					$options[] = [
						'icon' => 'open',
						'text' => 'View website',
						'click' => 'open-'.$commentid.'|'.$data['website']
					];
				endif;
				if ( ! empty($data['email']) ) :
					$options[] = '-';
					$options[] = [
						'icon' => 'open',
						'text' => 'Send e-mail',
						'click' => 'open-'.$commentid.'|mailto:'.$data['email']
					];
				endif;

				$return[ $commentid ] = [ $content, $options, $class, $icon ];

			}

			// return the array to the vue component
			return ( isset( $return ) ? $return : [] );

		}

	],

];
