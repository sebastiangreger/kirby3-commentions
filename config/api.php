<?php

namespace sgkirby\Commentions;

return [

    'routes' => [
    
		[
			// approve pattern from the inbox
			'pattern' => 'commentions/approve/(:any)',
			'method'  => 'GET',
			'action'  => function ( $id ) {
				return Commentions::approve( $id );
            }
        ],

		[
			// approve pattern from page view
			'pattern' => 'commentions/approve/(:num)/(:all)',
			'method'  => 'GET',
			'action'  => function ( $commentid, $pageid ) {
				return Commentions::update( page( $pageid ), $commentid, [ 'approved' => true ] );
            }
        ],

		[
			// unapprove pattern from page view
			'pattern' => 'commentions/unapprove/(:num)/(:all)',
			'method'  => 'GET',
			'action'  => function ( $commentid, $pageid ) {
				return Commentions::update( page( $pageid ), $commentid, [ 'approved' => false ] );
            }
        ],

		[
			// delete pattern from the inbox
			'pattern' => 'commentions/delete/(:any)',
			'method'  => 'GET',
			'action'  => function ( $id ) {
				return Commentions::delete( $id );
            }
        ],

		[
			// delete pattern from page view
			'pattern' => 'commentions/delete/(:num)/(:all)',
			'method'  => 'GET',
			'action'  => function ( $commentid, $pageid ) {
				return Commentions::update( page( $pageid ), $commentid, 'delete' );
            }
        ],
        
	]

];
