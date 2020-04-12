<?php

namespace sgkirby\Commentions;

use Kirby\Http\Response;

return [

	[
		'pattern' => option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' ),
		'method'  => 'GET|POST',
		'action'  => function () {

			if ( kirby()->request()->is('POST') ) :
			
				// for POST requests, queue the incoming webmention
				try {
					Commentions::queueWebmention();
					if ( get('manualmention') )
						go( get('target') . "?thx=accepted" );
					else
						return new Response( '<p>Accepted.</p>', 'text/html', 202 );
				} catch(Exception $e) {
					return new Response( '<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400 );
				}

			else :

				// for GET requests, provide a submission form instead
				return new Response( '
					<html><body>
						<form action="' . site()->url() . '/' . option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' ) .  '" method="post">
							<div>
								<label for="target">The URL on ' . site()->url() . ' you linked to</label>
								<input type="url" name="target" value="' . get('target') . '" pattern=".*' . str_replace( '.', '\.', site()->url() ) . '.*" required>
							</div>
							<div>
								<label for="source">The URL of your response (full URL incl https://)</label>
								<input type="url" name="source" value="' . get('source') . '" pattern=".*http.*" required>
							</div>
							<input type="submit" name="submit" value="Submit">
						</form>
					</body></html>
				', 'text/html', 404);

			endif;

		}
	],
	[
		'pattern' => 'commentions-processqueue',
		'action'  => function () {

			$secret = option( 'sgkirby.commentions.secret', '1234567890' );

			// validation with actionable error messages
			if ( !get('token') )
				return new Response( '<p>Error: Token attribute missing from URL or empty.</p>', 'text/html', 403 );
			if ( $secret == '' )
				return new Response( '<p>Error: No token configured in config file.</p>', 'text/html', 500 );
			if ( strlen( $secret ) < 10 )
				return new Response( '<p>Error: Token set in config is too short.</p>', 'text/html', 500 );
			if ( preg_match( "/[&%#+]/i", $secret ) )
				return new Response( '<p>Error: Token set in config contains invalid characters.</p>', 'text/html', 500 );
			if ( get('token') != $secret )
				return new Response( '<p>Error: Incorrect token in URL attribute.</p>', 'text/html', 403 );

			try {
				\sgkirby\Commentions\Commentions::processQueue();
				return new Response( '<p>Success.</p>', 'text/html', 200 );
			} catch(Exception $e) {
				return new Response( '<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400 );
			}

		}
	]

];
