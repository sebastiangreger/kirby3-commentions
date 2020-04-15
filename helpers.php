<?php

use sgkirby\Commentions\Frontend;

function commentions( $template = null, $var = null ) {
	Frontend::render( $template, $var );
}

// DEPRECATED as of 1.0.0: separate helpers replaced with commentions() helper + template variable
function commentionsFeedback() {
	commentions( 'feedback' );
}
function commentionsForm() {
	commentions( 'form' );
}
function commentionsList( string $format = 'list' ) {
	commentions( 'list', $format );
}
function commentionsEndpoints() {
	commentions( 'endpoints' );
}
function commentionsCss() {
	commentions( 'css' );
}
