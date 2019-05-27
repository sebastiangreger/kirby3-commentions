<?php

namespace sgkirby\Commentions;

/**
 * Kirby 3 "Commentions" - Comments and Mentions Plugin
 *
 * @version   0.2.2
 * @author    Sebastian Greger <msg@sebastiangreger.net>
 * @copyright Sebastian Greger <msg@sebastiangreger.net>
 * @link      https://github.com/sebastiangreger/kirby3-sendmentions
 * @license   MIT
 */

load([
    'sgkirby\\Commentions\\Commentions' => 'src/Commentions.php'
], __DIR__);

require( __DIR__ . DS . 'helpers.php' );

\Kirby::plugin('sgkirby/commentions', [

    'api'     		=> require __DIR__ . '/config/api.php',
    
    'blueprints' 	=> [
        'fields/commentions' => __DIR__ . '/blueprints/fields/commentions.yml'
    ],
        
    'hooks' 		=> require __DIR__ . '/config/hooks.php',

    'sections' 		=> [
        'commentions' => require __DIR__ . '/sections/commentions.php',
    ],

    'routes'   		=> function ( $kirby ) {
        return Commentions::endpointRoute( $kirby );
    },
    
    'snippets' 		=> [
        'commentions-list' => __DIR__ . '/snippets/commentions-list.php',
        'commentions-form' => __DIR__ . '/snippets/commentions-form.php',
        'commentions-feedback' => __DIR__ . '/snippets/commentions-feedback.php',
    ],

]);
