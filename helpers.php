<?php

use sgkirby\Commentions\Commentions;

function commentions() {

	// process form submission
    if ( get('submit') )
		$feedback = Commentions::queueComment();

	if ( get('thx') )
		$feedback = Commentions::successMessage();

	// output the markup
	if ( isset( $feedback ) )
		snippet( 'commentions-feedback', $feedback );

	if ( !get('thx') )
		snippet( 'commentions-form', [
			'fields' => (array) option( 'sgkirby.commentions.formfields', ['name'] ),
		]);

	snippet( 'commentions-list' );
	
}

function webmentionEndpoint() {

	$endpoint = site()->url() . '/' . option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' );
	
	return '
		<link rel="webmention" href="' . $endpoint . '" />
		<link rel="http://webmention.org/" href="' . $endpoint . '" />
	';

}

function commentionsCss() {

	return css( 'media/plugins/sgkirby/commentions/styles.css' );

}
