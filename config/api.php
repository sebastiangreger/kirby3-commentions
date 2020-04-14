<?php

namespace sgkirby\Commentions;

return [

    'routes' => [
    
		[
			'pattern' => 'commentions/(approve|unapprove|delete)/(\w{10})/(:all)',
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

				return Storage::update( page( $pageid ), $commentid, $array );

            }
        ],
       
	]

];
