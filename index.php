<?php

/**
 * Kirby 3 "Commentions" - Comments and Mentions Plugin
 *
 * @version   1.0.4
 * @author    Sebastian Greger <msg@sebastiangreger.net>
 * @copyright Sebastian Greger <msg@sebastiangreger.net>
 * @link      https://github.com/sebastiangreger/kirby3-commentions
 * @license   MIT
 */

use sgkirby\Commentions\Commentions;
use sgkirby\Commentions\Structure;
use Kirby\Cms\App as Kirby;
use Kirby\Toolkit\F;

if (F::exists(__DIR__ . '/vendor/autoload.php') === false) {
    // Fallback to manual autoloading via class map, if composer
    // autoloader is not present
    load([
        'sgkirby\\commentions\\Commention' => 'lib/Commention.php',
        'sgkirby\\commentions\\Commentions' => 'lib/Commentions.php',
        'sgkirby\\commentions\\Cron' => 'lib/Cron.php',
        'sgkirby\\commentions\\Endpoint' => 'lib/Endpoint.php',
        'sgkirby\\commentions\\Frontend' => 'lib/Frontend.php',
        'sgkirby\\commentions\\Migration' => 'lib/Migration.php',
        'sgkirby\\commentions\\Formatter' => 'lib/Formatter.php',
        'sgkirby\\commentions\\Formatter\\CacheAdapter' => 'lib/Formatter/CacheAdapter.php',
        'sgkirby\\commentions\\Formatter\\CodeClassAttrDef' => 'lib/Formatter/CodeClassAttrDef.php',
        'sgkirby\\commentions\\Formatter\\LinkTransformer' => 'lib/Formatter/LinkTransformer.php',
        'sgkirby\\commentions\\Formatter\\RemoveEmptyLinksInjector' => 'lib/Formatter/RemoveEmptyLinksInjector.php',
        'sgkirby\\commentions\\Storage' => 'lib/Storage.php',
        'sgkirby\\commentions\\Structure' => 'lib/Structure.php',
    ], __DIR__);
}

@require_once __DIR__ . '/helpers.php';
@require_once __DIR__ . '/includes/mf2-parser.php';
@require_once __DIR__ . '/includes/php-comments.php';
@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('sgkirby/commentions', [

    'options' => [
        'cache'                     => [
            'sanitized-text'       => true,
            'purifier-definitions' => true,
        ],
        'templatesWithComments'     => null,
        'templatesWithWebmentions'  => null,
        'secret'                    => '',
        'keepfailed'                => true,
        'defaultstatus'             => 'pending',
        'endpoint'                  => 'webmention-endpoint',
        'spamprotection'            => [ 'honeypot', 'timemin', 'timemax' ],
        'spamtimemin'               => 5,
        'spamtimemax'               => 86400,
        'avatarurls'                => false,
        'hideforms'                 => false,
        'expand'                    => false,
        'allowlinks'                => true,
        'autolinks'                 => true,
        'commentfields'             => ['name'],
        'webmentionfields'          => ['text', 'name', 'website'],
        'grouped'                   => [
            'read'            => 'Read by',
            'like'            => 'Likes',
            'repost'          => 'Reposts',
            'bookmark'        => 'Bookmarks',
            'rsvp:yes'        => 'RSVP: yes',
            'rsvp:maybe'      => 'RSVP: maybe',
            'rsvp:interested' => 'RSVP: interested',
            'rsvp:no'         => 'RSVP: no',
        ],
    ],

    'api' => require __DIR__ . '/config/api.php',

    'blueprints' => [
        // DEPRECATED as of 1.0.0: replaced with section 'commentions'
        'fields/commentions' => __DIR__ . '/blueprints/fields/commentions.yml'
    ],

    'hooks' => require __DIR__ . '/config/hooks.php',

    'sections' => [
        'commentions' => require __DIR__ . '/sections/commentions.php',
    ],

    'routes' => require __DIR__ . '/config/routes.php',

    'snippets' => [
        'commentions/list'     => __DIR__ . '/snippets/commentions-list.php',
        'commentions/form'     => __DIR__ . '/snippets/commentions-form.php',
        'commentions/help'     => __DIR__ . '/snippets/commentions-help.php',
        'commentions/feedback' => __DIR__ . '/snippets/commentions-feedback.php',
        /* DEPRECATED: keeping old snippet names below for compatibility while on 1.x */
        'commentions-list'     => __DIR__ . '/snippets/commentions-list.php',
        'commentions-form'     => __DIR__ . '/snippets/commentions-form.php',
        'commentions-help'     => __DIR__ . '/snippets/commentions-help.php',
        'commentions-feedback' => __DIR__ . '/snippets/commentions-feedback.php',
    ],

    'pageMethods' => [
        'commentions' => function(string $status = 'approved', string $language = null) {
            return Commentions::get($this, $status, $language);
        },

        'addCommention' => function(array $data) {
            return Commentions::add($this, $data, 'commentions');
        },

        'deleteCommention' => function(string $uid) {
            return Commentions::update($this, $uid, 'delete');
        },

        'updateCommention' => function(string $uid, array $data) {
            return Commentions::update($this, $uid, $data);
        },
    ],

    'pagesMethods' => [
        'commentions' => function(string $status = 'approved', string $language = null): Structure {
            $commentions = new Structure();

            foreach ($this as $page) {
                $commentions = $commentions->add($page->commentions($status, $language));
            }

            return $commentions->sortBy('timestamp');
        },
    ],

    'translations' => [
        'de' => require __DIR__ . '/languages/de.php',
        'en' => require __DIR__ . '/languages/en.php',
        'fr' => require __DIR__ . '/languages/fr.php',
    ],

]);
