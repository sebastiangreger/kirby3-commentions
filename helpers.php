<?php

use sgkirby\Commentions\Commentions;

function commentions() {

	commentionsFeedback();
	commentionsForm();
	commentionsList();

}

function commentionsFeedback() {

	if ( isset( Commentions::$feedback ) ) :
		snippet( 'commentions-feedback', Commentions::$feedback );
	endif;

}

function commentionsForm() {

	if ( !get('thx') ) :
		snippet( 'commentions-form', [
			'fields' => (array) option( 'sgkirby.commentions.formfields', ['name'] ),
		]);
	endif;

}

function commentionsList( string $format = 'list' ) {

	// retrieve all approved comments for this page
	$comments = page()->comments()->toStructure()->filterBy('approved','true')->toArray();

	if ( $format == 'raw' ) :

		// return an array with all comments for this page
		return $comments;

	elseif ( sizeof( $comments ) > 0 ) :

		// restructure the data if grouped view
		if ( $format == 'grouped' ) :

			// array of all groups to be pulled out from content list, in presentation order
			$groups = option( 'sgkirby.commentions.grouped', [
				'like' => 'Likes',
				'repost' => 'Reposts',
				'bookmark' => 'Bookmarks',
				'rsvp:yes' => 'RSVP: yes',
				'rsvp:maybe' => 'RSVP: maybe',
				'rsvp:interested' => 'RSVP: interested',
				'rsvp:no' => 'RSVP: no',
			] );

			foreach ( $comments as $comment )

				// group only those types included in the $groups variable
				if ( isset( $groups[ $comment['type'] ] ) )
					$reactions[ $groups[ $comment['type'] ] ][] = $comment;
				else
					$comments[] = $comment;

			// sort reactions by order given in $groups array
			$reactions = array_merge( array_flip( $groups ), $reactions );

		endif;

		// return selected markup
		snippet( 'commentions-list', [
			'comments' => $comments,
			'reactions' => ( isset( $reactions ) ? $reactions : [] ),
		]);

	endif;

}

function commentionsEndpoints() {

	$endpoint = site()->url() . '/' . option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' );
	
	return '
		<link rel="webmention" href="' . $endpoint . '" />
		<link rel="http://webmention.org/" href="' . $endpoint . '" />
	';

}

function commentionsCss() {

	return css( 'media/plugins/sgkirby/commentions/styles.css' );

}
