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
						page( $pageid )->updateCommention( $commentid, [ 'status' => 'approved' ] );
						return true;
						break;
					case 'unapprove':
						page( $pageid )->updateCommention( $commentid, [ 'status' => 'unapproved' ] );
						return true;
						break;
					case 'delete':
						page( $pageid )->deleteCommention( $commentid );
						return true;
						break;
					default:
						return false;
				}

            }
        ],
       
	]

];
