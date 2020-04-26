<?php

namespace sgkirby\Commentions;

use Parsedown;
use HTMLPurifier;
use HTMLPurifier_AttrDef;
use HTMLPurifier_Config;
use HTMLPurifier_DefinitionCacheFactory;
use HTMLPurifier_TagTransform;
use Kirby\Cms\Dir;
use sgkirby\Commentions\Formatter\HTMLPurifierCacheAdapter;

class Formatter
{
    /**
     * Cached instance of HTML Purifier instance used for processing
     *
     * @var \HTMLPurifier
     */
    protected static $purifier;

    /**
     * Cached instance of the Parsedown Markdown parser
     *
     * @var \Parsedown
     */
    protected static $parsedown;

    /**
     * Generates the config string for HTML Purifiers list of allowed
     * elements, based on the plugin configutation
     *
     * @return string The configuration string
     */

    protected static function getAllowedElements(): string
    {
        $allowed = [
            '*[lang|dir]',
            'abbr[title]',
            'b',
            'blockquote',
            'br',
            'cite',
            'code[class]',
            'del',
            'em',
            'i',
            'ins',
            'kbd',
            'li',
            'mark',
            'ol',
            'p',
            'pre[class]',
            'q',
            'strong',
            'sub',
            'sup',
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
    public static function filter(string $text, ?bool $smartypants = null, ?string $direction = null): string
    {
        if (class_exists('HTMLPurifier') === false) {
            return static::stripHtml($text);
        }

        $text = static::markdown($text);

        if ($smartypants ?? option('smartypants')) {
            // Only apply smartypants filter, if enabled in Kirby
            $text = smartypants($text);
        }

        return static::purifiy($text);
    }

    protected static function stripHtml(string $text): string
    {
        $text = html($text);
        $text = str_replace(["\r\n", "\r", "\n"], "\n", $text);
        $text = preg_replace('/(\n{3,})/', "\n\n", $text);
        $text = explode("\n\n", $text);
        $text = array_map(function($item) {
            return '<p>' . nl2br($item, false) . '</p>'; }, $text);
        $text = implode("\n", $text);
        return $text;
    }

    protected static function purifiy(string $text, ?string $direction = null): string
    {
        if (static::$purifier === null) {

            $purifierCache = HTMLPurifier_DefinitionCacheFactory::instance();
            // Workaround for force-loading the class, because HTML Purifier
            // only checks for classes, that have already been loaded
            // beforehand.
            HTMLPurifierCacheAdapter::triggerAutoload();
            $purifierCache->register('Kirby', HTMLPurifierCacheAdapter::class);

            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.DefinitionImpl', 'Kirby');

            // Set default text direction
            if ($direction !== null) {
                $config->set('Attr.DefaultTextDir', $direction);
            } else if ($language = kirby()->language()) {
                $config->set('Attr.DefaultTextDir', $language->direction());
            }

            $config->set('Attr.AllowedRel', ['noopener', 'noreferrer', 'nofollow']);
            $config->set('HTML.Allowed', static::getAllowedElements());

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
            $config->set('URI.AllowedSchemes', [
                'http' => true,
                'https' => true,
                'mailto' => true,
                'xmpp' => true,
                'irc' => true,
                'ircs' => true,
            ]);

            $config->set('Output.Newline', "\n"); // Use unix line breaks only 🤘

            // Remove empty paragraphs
            $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
            $config->set('AutoFormat.RemoveEmpty', true);

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

        return static::$purifier->purify($text);
    }

    protected static function markdown(string $text): string
    {
        if (static::$parsedown === null) {
            // Using the Parsedown library directly instead of Kirby’s
            // Markdown component to have full control over the settings.
            static::$parsedown = new Parsedown();
            static::$parsedown->setBreaksEnabled(true);
        }

        return static::$parsedown->text($text);
    }
}
