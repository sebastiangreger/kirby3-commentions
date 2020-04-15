<?php

/**
 * Kirby 3 "Commentions" - Comments and Mentions Plugin
 *
 * @version   1.0.0-alpha1
 * @author    Sebastian Greger <msg@sebastiangreger.net>
 * @copyright Sebastian Greger <msg@sebastiangreger.net>
 * @link      https://github.com/sebastiangreger/kirby3-sendmentions
 * @license   MIT
 */

namespace sgkirby\Commentions;

load([

    'sgkirby\\Commentions\\Commentions'	=> 'src/Commentions.php',
    'sgkirby\\Commentions\\Storage'		=> 'src/Storage.php',

    'sgkirby\\Commentions\\Frontend'	=> 'src/Frontend.php',

    'sgkirby\\Commentions\\Endpoint'	=> 'src/Endpoint.php',
    'sgkirby\\Commentions\\Cron'		=> 'src/Cron.php',

    'sgkirby\\Commentions\\Migration' 	=> 'src/Migration.php'

], __DIR__);

require( __DIR__ . DS . 'helpers.php' );

\Kirby::plugin('sgkirby/commentions', [

	'options' 		=> array(

		'secret'				=> '',
		'autoapprovecomments' 	=> 'false',
		'endpoint' 				=> 'webmention-endpoint',
		'spamprotection' 		=> [ 'honeypot', 'timemin', 'timemax' ],
		'spamtimemin' 			=> 5,
		'spamtimemax'			=> 86400,
		'avatarurls'			=> false,
		'hideforms'				=> false,
		'expand'				=> false,
		'formfields'			=> ['name'],
		'grouped' 				=> [
									'read' 				=> 'Read by',
									'like' 				=> 'Likes',
									'repost' 			=> 'Reposts',
									'bookmark' 			=> 'Bookmarks',
									'rsvp:yes' 			=> 'RSVP: yes',
									'rsvp:maybe' 		=> 'RSVP: maybe',
									'rsvp:interested' 	=> 'RSVP: interested',
									'rsvp:no' 			=> 'RSVP: no',
								],

	),
	
    'api'     		=> require __DIR__ . '/config/api.php',
    
    'blueprints' 	=> [

		// DEPRECATED as of 1.0.0: replaced with section 'commentions'
		'fields/commentions' 	=> __DIR__ . '/blueprints/fields/commentions.yml'

    ],
        
    'hooks' 		=> require __DIR__ . '/config/hooks.php',

    'sections' 		=> [

        'commentions' 			=> require __DIR__ . '/sections/commentions.php',

    ],

    'routes'   		=> require __DIR__ . '/config/routes.php',
    
    'snippets' 		=> [

        'commentions-list'		=> __DIR__ . '/snippets/commentions-list.php',
        'commentions-form' 		=> __DIR__ . '/snippets/commentions-form.php',
        'commentions-feedback' 	=> __DIR__ . '/snippets/commentions-feedback.php',

    ],

    'pageMethods' => [

        'commentions' 			=> function ( string $status = 'approved', string $sort = 'asc' ) {
									return Commentions::retrieve( $this, $status, $sort );
								},

        'addCommention' 		=> function ( array $data ) {
									return Commentions::add( $this, $data );
								},

        'deleteCommention' 		=> function ( string $uid ) {
									return Commentions::update( $this, $uid, 'delete' );
								},

        'updateCommention' 		=> function ( string $uid, array $data ) {
									return Commentions::update( $this, $uid, $data );
								},

    ],

    'pagesMethods' => [

        'commentions'			=> function ( $status = 'approved', $sort = 'asc' ) {
									$return = [];
									foreach ( $this as $page ) :
										$return = \Kirby\Toolkit\A::merge( $return, Commentions::retrieve( $page, $status ) );
									endforeach;
									return \Kirby\Toolkit\A::sort( $return, 'timestamp', $sort );
								},

    ],

    'translations' => array(

        'en' 					=> require_once __DIR__ . '/languages/en.php',

    ),

]);
