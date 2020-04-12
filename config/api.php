<?php

namespace sgkirby\Commentions;

return [

    'routes' => [
    
		[
			'pattern' => 'commentions/(approve|unapprove|delete)/(:num)/(:all)',
			'method'  => 'GET',
			'action'  => function ( $action, $commentid, $pageid ) {

				switch ( $action ) {
					case 'approve':
						$array = [ 'approved' => 'true' ];
						break;
					case 'unapprove':
						$array = [ 'approved' => 'false' ];
						break;
					case 'approve':
						$array = 'delete';
						break;
					default:
						return false;
				}

				return Commentions::update( page( $pageid ), $commentid, $array );

            }
        ],
       
	]

];
