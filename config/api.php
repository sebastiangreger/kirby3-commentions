<?php

namespace sgkirby\Commentions;

return [

    'routes' => [
    
		[
			'pattern' => 'commentions/approve/(:any)',
			'method'  => 'GET',
			'action'  => function ( $id ) {
				return Commentions::approve( $id );
            }
        ],
        
		[
			'pattern' => 'commentions/delete/(:any)',
			'method'  => 'GET',
			'action'  => function ( $id ) {
				return Commentions::delete( $id );
            }
        ],
        
	]

];
