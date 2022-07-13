<?php

namespace sgkirby\Commentions;

use Kirby\Cms\Field;
use Kirby\Cms\StructureObject;
use Kirby\Toolkit\Str;
use Parsedown;

/**
 * The Commention reprents each item
 * in a Structure collection.
 */
class Commention extends StructureObject
{
    /**
     * Compares the current object with the given commention object
     *
     * @param mixed $structure
     * @return bool
     */
    public function is($structure): bool
    {
        if (is_a($structure, static::class) === false) {
            return false;
        }

        return $this === $structure;
    }

    /**
     * Converts all fields in the object to a
     * plain associative array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = $this->content()->toArray();
        $array['name_formatted'] = $this->nameFormatted();
        $array['source_formatted'] = $this->sourceFormatted();
        ksort($array);
        return $array;
    }

    /**
     * Returns the name as field object if given or the translated
     * version of "Anonymous" as fallback.
     *
     * @return string
     */
    public function nameFormatted(?string $anonymous = null): string
    {
        $anonymous = $anonymous ?? t('commentions.name.anonymous');
        return $this->name()->or($anonymous)->html()->toString();
    }

    /**
     * Returns `true`, if the comment has been authenticated, otherwise `false`.
     *
     * @return boolean
     */
    public function isAuthenticated(): bool
    {
        return $this->authenticated()->toBool();
    }

    /**
     * Returns the formatted source of a commention if given, otherwise
     * null.
     *
     * @return string
     */
    public function sourceFormatted(string $anonymous = null): ?string
    {
        // Format author name
        $author = $this->nameFormatted($anonymous);

        if ($this->website()->isNotEmpty()) {
            $author = '<a href="' . $this->website() . '" rel="noopener noreferrer nofollow">' . $author . '</a>';
        } else {
            $author = "<strong>{$author}</strong>";
        }

        // Format domain of source URL

        $domain = $this->source()->isNotEmpty()
            ? preg_replace('/^www\./i', '', parse_url($this->source(), PHP_URL_HOST))
            : null;

        $translation = '';

        switch ($this->type()->toString()) {
            case 'like':
                $translation = t('commentions.snippet.list.liked');
                break;
            case 'bookmark':
                $translation = t('commentions.snippet.list.bookmarked');
                break;
            case 'repost':
                $translation = t('commentions.snippet.list.reposted');
                break;
            case 'reply':
                $translation = t('commentions.snippet.list.replies');
                break;
            case 'comment':
                $translation = t('commentions.snippet.list.comment');
                break;
            default:
                if (empty($domain) === false) {
                    $translation = t('commentions.snippet.list.mentionedAt');
                } else {
                    $translation = t('commentions.snippet.list.mentioned');
                }
        }

        $replace = [
            'author' => $author,
            'link' => $this->source()->isNotEmpty() ? '<a href="' . $this->source() . '" rel="noopener noreferrer nofollow">' . $domain . '</a>' : '',
            'source' => $this->source()->toString(),
            'domain' => $domain,
        ];

        // Use single brackets (instead of double) to match the behavior
        // of Kirby’s `tt()` helper function.
        if (version_compare(\Kirby\Cms\App::version(), '3.6') >= 0) {
            return Str::template($translation, $replace, ['fallback' => null, 'start' => '{', 'end' => '}']);
        }
        // keep backward-compatible with core <3.6
        else {
            return Str::template($translation, $replace, null, '{', '}');
        }

    }

    /**
     * Returns the a tidy version of the commention’s text, with malicious
     * HTML code stripped-out. If you rather want to access the raw version,
     * of the user input, use `$commention->content()->get('text')` instead.
     *
     * @return Field
     */
    public function text(): Field
    {
        $text = $this->textUnsafe();
        $type = $this->type()->toString();

        if ($text->isEmpty() === true) {
            // Return text field if empty, hence empty fields cannot
            // contain malicious HTML.
            return $text;
        }

        if (in_array($type, ['reply', 'comment']) === false) {
            // Always return empty field if not a reply or comment.
            return new Field($this, 'text', '');
        }

        if ($this->text_sanitized()->isNotEmpty()) {
            // Sanitized text present, return that field instead
            // or the unsafe raw text field.
            return $this->text_sanitized();
        }

        // If somehow the cache failed to load, which should never
        // happen by default, purify on-the fly to prevent unfiltered
        // HTML from ever appearing in the comments list.
        $sanitized = Formatter::sanitize($text, [
            'markdown' => $type === 'comment',
        ]);
        return new Field($this, 'text', $sanitized);
    }

    /**
     * Returns the raw, unescaped text value. Always use `$commention->text()`,
     * if you want to include untrusted user input into an HTML page to
     * mitigae the risk XSS attacks.
     *
     * @return Field The raw text as Field.
     */
    public function textUnsafe(): Field
    {
        return $this->content()->get('text');
    }

    /**
     * Returns the formatted date of the commention, based on the
     * current locale.
     *
     * @return string
     */
    public function dateFormatted(): string
    {
        // Get the right date format from translations, based on the
        // date handler set for Kirby.
        $format = t('commentions.snippet.list.dateFormat.' . option('date.handler', 'date'));
        return $this->timestamp()->toDate($format);
    }
}
