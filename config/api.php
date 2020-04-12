<?php

namespace sgkirby\Commentions;

return [

    'routes' => [
    
		[
			'pattern' => 'commentions/approve/(:num)/(:all)',
			'method'  => 'GET',
			'action'  => function ( $commentid, $pageid ) {
				return Commentions::update( page( $pageid ), $commentid, [ 'approved' => 'true' ] );
            }
        ],

		[
			'pattern' => 'commentions/unapprove/(:num)/(:all)',
			'method'  => 'GET',
			'action'  => function ( $commentid, $pageid ) {
				return Commentions::update( page( $pageid ), $commentid, [ 'approved' => 'false' ] );
            }
        ],

		[
			'pattern' => 'commentions/delete/(:num)/(:all)',
			'method'  => 'GET',
			'action'  => function ( $commentid, $pageid ) {
				return Commentions::update( page( $pageid ), $commentid, 'delete' );
            }
        ],
        
	]

];
