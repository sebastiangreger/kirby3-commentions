<?php

namespace sgkirby\Commentions;

use Parsedown;
use HTMLPurifier;
use HTMLPurifier_Config;

class Sanitizer
{
    const PURIFIER_ALLOWED_HTML = [
        '*[lang|dir]',
        'a[rel|href]',
        'abbr[title]',
        'bdo',
        'bdi',
        'b',
        'blockquote', // [cite]
        'br',
        'cite',
        'code[class]',
        'del', // [cite|datetime]
        'kbd',
        'mark',
        'em',
        'i',
        'ins',// [cite|datetime]
        'li', // [value]
        'ol', // [reversed|start|type] attributes disabled, as they could easily collide with page styles
        'p',
        'pre[class]',
        'q', // [cite]
        // 'ruby',
        // 'rb',
        // 'rp',
        // 'rt',
        // 'rtc',
        'u',
        's',
        'strong',
        'ul',
    ];

    const PURIFIER_ALLOWED_REL_ATTR = [
        'noopener',
        'noreferrer',
        'nofollow',
    ];

    protected static $purifier;
    protected static $parsedown;

    public static function markdown(string $text, string $dir = 'ltr'): ?string
    {
        if (static::$parsedown === null) {
            static::$parsedown = new Parsedown();
            static::$parsedown->setBreaksEnabled(true);
        }

        if (static::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Attr.AllowedRel', static::PURIFIER_ALLOWED_REL_ATTR);
            $config->set('Attr.DefaultTextDir', $dir);
            $config->set('AutoFormat.Linkify', true);
            $config->set('HTML.Allowed', static::PURIFIER_ALLOWED_HTML);
            $config->set('URI.DisableExternalResources', true);
            $config->set('URI.DisableResources', true);
            $config->set('Output.Newline', "\n"); // Use unix line breaks only 🤘
            $config->set('Cache.SerializerPath', kirby()->root('cache'));
            static::$purifier = new HTMLPurifier($config);
        }

        // Parse Markdown first
        $text = static::$parsedown->text($text);

        if (option('smartypants') === true) {
            // Only apply smartypants filter, if enabled in Kirby
            $text = smartypants($text);
        }

        $text = static::$purifier->purify($text);

        return $text;
    }
}
