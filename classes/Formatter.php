<?php

namespace sgkirby\Commentions;

use Parsedown;
use HTMLPurifier;
use HTMLPurifier_AttrDef;
use HTMLPurifier_AttrValidator;
use HTMLPurifier_Config;
use HTMLPurifier_DefinitionCacheFactory;
use HTMLPurifier_Injector;
use HTMLPurifier_TagTransform;
use HTMLPurifier_TagTransform_Simple;
use HTMLPurifier_Token_End;
use HTMLPurifier_Token_Start;
use HTMLPurifier_Token_Text;
use sgkirby\Commentions\Formatter\CacheAdapter;
use sgkirby\Commentions\Formatter\CodeClassAttrDef;
use sgkirby\Commentions\Formatter\FixBreaksPosition;
use sgkirby\Commentions\Formatter\LinkTransformer;
use sgkirby\Commentions\Formatter\RemoveEmptyLinksInjector;

use function PHPSTORM_META\map;

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


    protected static $inline = [
        'a',
        'abbr',
        'b',
        'br',
        'cite',
        'code',
        'del',
        'em',
        'i',
        'ins',
        'kbd',
        'mark',
        'q',
        'strong',
        'sub',
        'sup',
    ];

    protected static $blocks = [
        'blockquote',
        'li',
        'ol',
        'p',
        'pre',
        'ul',
    ];

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
     * @param string|null $direction Text direction, 'ltr' or 'rtl'
     * @return string The cleaned-up/"purified" text.
     */
    public static function filter(string $text, ?string $direction = null): string
    {
        if (static::advancedFormattingAvailable() === false) {
            return static::escapeAndFormat($text);
        }

        $text = static::markdown($text);

        if (option('smartypants') === true) {
            // Only apply smartypants filter, if enabled in Kirby
            $text = smartypants($text);
        }

        return static::purifiy($text, $direction);
    }

    /**
     * Escapes HTML characters, normalizes to UNIX-style line breaks (\n),
     * automatically splits the text into paragraphs and converts
     * single line breaks into `<br>` tags.
     *
     * @param string $text Unsafe test input, possibly containing HTML tags
     * @return string Escaped and formatted HTML string
     */
    protected static function escapeAndFormat(string $text): string
    {

        // Normalize line breaks and replace 3 or more consecutive
        // break with just 2 breaks
        $text = str_replace(["\r\n", "\r", "\n"], "\n", $text);
        $text = preg_replace('/<\/p>[\s]+/', "</p>\n\n", $text);
        $text = preg_replace('/(\n{3,})/', "\n\n", $text);

        $text = strip_tags($text);
        $text = html($text);

        // Convert to paragraphs and convert single line breaks to
        // `<br>` elements
        $text = explode("\n\n", $text);
        $text = array_map(function($item) {
            $item = trim($item);
            return '<p>' . nl2br($item, false) . '</p>';
        }, $text);
        $text = implode("\n", $text);

        return $text;
    }

    /**
     * Checks whether advanced comment formatting is available. HTML and
     * Markdown formatting are only enabled in comments, if the
     * HTML Purifier Library has been installed via composer or
     * otherwise before.
     *
     * @return boolean
     */
    public static function advancedFormattingAvailable(): bool
    {
        return class_exists('HTMLPurifier');
    }

    /**
     * Cleans up a string of dirty HTML from invalid syntax, malicious
     * code and strips all tags and attributes, except for a few from
     * a given whitelist.
     *
     * @param string $text Untrusted string of HTML
     * @param string|null $direction Text direction, 'ltr' or 'rtl'
     * @return string Sanitized HTML string
     */
    protected static function purifiy(string $text, ?string $direction = null): string
    {
        if (static::$purifier === null) {

            $purifierCache = HTMLPurifier_DefinitionCacheFactory::instance();
            // Workaround for force-loading the class, because HTML Purifier
            // only checks for classes, that have already been loaded
            // beforehand.
            CacheAdapter::triggerAutoload();
            $purifierCache->register('Kirby', CacheAdapter::class);

            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.DefinitionImpl', 'Kirby');

            // Setting a doctype is required to get HTML5-style self-closing
            // tags (<img>) instead of XHTML syntax (<img />)
            $config->set('HTML.Doctype','HTML 4.01 Transitional');

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
            $def->addAttribute('code', 'class', new CodeClassAttrDef());

            // Add rel="noreferrer noopener" to all external links, while
            // "nofollow" has been added by the purifier itself already.
            // "norefferer" and "noopener" are for safety, if another filter or
            // JavaScript code adds target="_blank" to external links.
            $def->info_tag_transform['a'] = new LinkTransformer();

            // Remove links without `href` attribute.
            $def->info_injector[] = new RemoveEmptyLinksInjector();

            // Transform headlines into regular paragraphs
            $def->info_tag_transform['h1'] = new HTMLPurifier_TagTransform_Simple('p');
            $def->info_tag_transform['h2'] = new HTMLPurifier_TagTransform_Simple('p');
            $def->info_tag_transform['h3'] = new HTMLPurifier_TagTransform_Simple('p');
            $def->info_tag_transform['h4'] = new HTMLPurifier_TagTransform_Simple('p');
            $def->info_tag_transform['h5'] = new HTMLPurifier_TagTransform_Simple('p');
            $def->info_tag_transform['h6'] = new HTMLPurifier_TagTransform_Simple('p');

            static::$purifier = new HTMLPurifier($config);
        }

        // Apply purifier filter
        $text = static::$purifier->purify($text);

        // Remove links, which got their attribute stripped during sanitation
        $text = preg_replace('/<a(?:\s+rel="[^"]+")?>(.*)<\/a>/uU', '$1', $text);

        return $text;
    }

    /**
     * Converts Markdown formatting on given string into HTML using the
     * Parsedown library
     *
     * @return string The resulting HTML of the conversion
     */
    protected static function markdown(string $text): string
    {
        if (static::$parsedown === null) {
            // Using the Parsedown library directly instead of Kirby’s
            // Markdown component to have full control over the settings.
            static::$parsedown = new Parsedown();
            static::$parsedown->setBreaksEnabled(true);
            static::$parsedown->setUrlsLinked(option('sgkirby.commentions.allowlinks') && option('sgkirby.commentions.autolinks'));
        }

        return static::$parsedown->text($text);
    }
}
