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
						$array = [ 'status' => 'approved' ];
						break;
					case 'unapprove':
						$array = [ 'status' => 'unapproved' ];
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
