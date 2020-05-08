<?php

namespace sgkirby\Commentions\Sanitizer;

use HTMLPurifier_TagTransform;

class LinkTransformer extends HTMLPurifier_TagTransform {
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
