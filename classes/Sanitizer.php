<?php

namespace sgkirby\Commentions;

use Parsedown;
use HTMLPurifier;
use HTMLPurifier_AttrDef;
use HTMLPurifier_Config;
use HTMLPurifier_TagTransform;
use HTMLPurifier_TagTransform_Simple;
use Kirby\Cms\Dir;

class Sanitizer
{
    /**
     * Cached instance of HTML Purifier instance used for processing
     *
     * @var HTMLPurifier
     */
    protected static $purifier;

    /**
     * Cached instance of the Parsedown Markdown parser
     *
     * @var Parsedown
     */
    protected static $parsedown;

    /**
     * Generates the config string for HTML Purifiers list of allowed
     * elements, based on the plugin configutation
     *
     * @return string The configuration string.
     */

    protected static function getPurifierAllowedElements(): string
    {
        $allowed = [
            '*[lang|dir]',
            'abbr[title]',
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
            // 'u',
            // 's',
            // 'strike',
            'strong',
            'ul',
        ];

        if (option('sgkirby.commentions.allowlinks') === true) {
            $allowed[] = 'a[rel|href]';
        }

        return implode(',', $allowed);
    }

    /**
     * Converts untrusted HTML/Markdown input into sanitized, safe HTML code.
     *
     * @param string $text The input text, expecting "dirty" HTML code and/or Markdown
     * @return string The cleaned-up/"purified" text.
     */
    public static function markdown(string $text, ?bool $smartypants = null, ?string $direction = null): ?string
    {
        if (static::$parsedown === null) {
            // Using the raw Parsedown library directly instead of
            // Kirby’s Markdown component to have full control over the settings.
            static::$parsedown = new Parsedown();
            static::$parsedown->setBreaksEnabled(true);
        }

        if (static::$purifier === null) {
            // Create a cache directory, that HTMLPurifier uses
            // for storing serizalized definitions.
            $cacheRoot = kirby()->root('cache') . '/commentions/htmlpurifier';
            Dir::make($cacheRoot);

            $config = HTMLPurifier_Config::createDefault();

            // Set default text direction
            if ($direction !== null) {
                $config->set('Attr.DefaultTextDir', $direction);
            } else if ($language = kirby()->language()) {
                $config->set('Attr.DefaultTextDir', $language->direction());
            }

            $config->set('Attr.AllowedRel', ['noopener', 'noreferrer', 'nofollow']);
            $config->set('HTML.Allowed', static::getPurifierAllowedElements());

            if (option('sgkirby.commentions.allowlinks') === true) {
                // Enable link processing only, if enabled in site config

                if (option('sgkirby.commentions.autolinks') === true) {
                    // Recognize URLs in text and turn them into links
                    // automatically.
                    $config->set('AutoFormat.Linkify', true);
                }

                // Add rel="nofollow" to external links to signalize,
                // that these have not been endorsed by the author of
                // the page.
                $config->set('HTML.Nofollow', true);
            }

            $config->set('URI.Host', parse_url(url(), PHP_URL_HOST));
            $config->set('URI.DisableExternalResources', true);
            $config->set('URI.DisableResources', true);
            $config->set('Output.Newline', "\n"); // Use unix line breaks only 🤘
            $config->set('Cache.SerializerPath', $cacheRoot);

            // Add HTMl5-only elements to the HTML definition, otherwise
            // the purifier would refuse to accept them.
            $def = $config->getHTMLDefinition(true);
            $def->addElement('mark', 'Inline', 'Inline', 'Common');

            // The sanitized code should not contain any classes in the end,
            // with the exception of codeblocks, as generated by a markdown
            // formatting tool, such as Parsedown.

            // The class attribute is allowed on the pre element, but
            // its value can only be `code`.
            $def->addAttribute('pre', 'class', 'Enum#code');

            // The code element only accepts a class name in the format
            // `language-*`, that is used by JavaScript-based syntax
            // highlighters for determing a code block’s language.
            $def->addAttribute('code', 'class', new class extends HTMLPurifier_AttrDef {
                public function validate($string, $config, $context) {
                    return preg_match('/^language-[a-z0-9]+$/', $string) === 1;
                }
            });

            // Add rel="noreferrer noopener" to all external links, while
            // "nofollow" has been added by the purifier itself already.
            // "norefferer" and "noopener" are for safety, if another filter or
            // JavaScript code adds target="_blank" to external links.
            $def->info_tag_transform['a'] = new class extends HTMLPurifier_TagTransform {
                public function transform($tag, $config, $context) {
                    $tag = clone $tag;
                    $siteHost = parse_url(url(), PHP_URL_HOST);
                    $hrefHost = parse_url($tag->attr['href'] ?? '', PHP_URL_HOST);

                    if ($siteHost !== $hrefHost) {
                        // Hosts don’t match, this is an external link
                        // Add 'noreferrer' and 'noopener' to the attribute.
                        $rel = array_filter(explode(' ', $tag->attr['rel'] ?? ''));
                        $rel = array_merge($rel, ['noreferrer', 'noopener']);
                        $rel = array_unique($rel);
                        $tag->attr['rel'] = implode(' ', $rel);
                    }

                    return $tag;
                }
            };

            static::$purifier = new HTMLPurifier($config);
        }

        // Parse Markdown first
        $text = static::$parsedown->text($text);

        if ($smartypants ?? option('smartypants')) {
            // Only apply smartypants filter, if enabled in Kirby
            $text = smartypants($text);
        }

        return static::$purifier->purify($text);
    }
}