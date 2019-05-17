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

function commentionsList() {

	snippet( 'commentions-list' );

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
