<?php

$translations = [


    // general

    'name.anonymous'                    => 'Anonymous',


    // snippets

    // - form
    'snippet.form.headline'            => 'Comments and Webmentions',
    'snippet.form.ctacomment'          => 'Leave a comment',
    'snippet.form.ctawebmention'       => 'Replied on your own website? Send a Webmention!',
    'snippet.form.responseurl'         => 'URL of the response on your site (make sure it has a hyperlink to this page)',
    'snippet.form.name'                => 'Name (optional)',
    'snippet.form.email'               => 'Email (optional; if you’d like a personal reply)',
    'snippet.form.honeypot'            => 'Please leave this field empty!',
    'snippet.form.website'             => 'Website (optional; publicly linked if provided)',
    'snippet.form.comment'             => 'Comment',
    'snippet.form.help.title'          => 'Available formatting commands',
    'snippet.form.help.intro'          =>
        'Use Markdown commands or their HTML equivalents to add simple formatting to your comment:',
    'snippet.form.help.common'         =>
        '<dt>Text markup</dt>' .
        '<dd><em>*italic*</em>, <strong>**bold**</strong>, <del>~~strikethrough~~</del>, <code>`code`</code> and <mark>&lt;mark&gt;marked text&lt;/mark&gt;</mark></code>.</dd>' .
        '<dt>Lists</dt>' .
        '<dd><pre class="code"><code class="language-markdown">- Unordered item 1<br>- Unordered list item 2</code></pre>' .
        '<dd><pre class="code"><code class="language-markdown">1. Ordered list item 1<br>2. Ordered list item 2</code></pre></dd>' .
        '<dt>Quotations</dt>' .
        '<dd><pre class="code"><code class="language-markdown">&gt; Quoted text</code></pre></dd>' .
        '<dt>Code blocks</dt>' .
        '<dd><pre><code>```<br>// A simple code block<br>```</code></pre>' .
        '<dd><pre class="code"><code class="language-php">```php<br>// Some PHP code<br>phpinfo();<br>```</code></pre></dd>',
    'snippet.form.help.links'          =>
        '<dt>Links</dt>' .
        '<dd><code>[Link text](https://example.com)</code></dd>',
    'snippet.form.help.autolinks'      =>
        '<dd>Full URLs are automatically converted into links.</dd>',

    // - list
    'snippet.list.comments'            => 'Comments',
    'snippet.list.comment'             => '{ author }',
    'snippet.list.mentioned'           => '{ author } mentioned this',
    'snippet.list.mentionedAt'         => '{ author } mentioned this at { link }',
    'snippet.list.liked'               => '{ author } liked this at { link }',
    'snippet.list.bookmarked'          => '{ author } bookmarked this at { link }',
    'snippet.list.replies'             => '{ author } replied at { link }',
    'snippet.list.dateFormat.date'     => 'Y-m-d H:i',
    'snippet.list.dateFormat.strftime' => '%Y-%m-%d %H:%M',


    // panel sections

    // - headlines
    'section.headline.default'          => 'Comments and Webmentions',
    'section.headline.pending'          => 'Inbox: Comments and Webmentions',
    'section.headline.all'              => 'All comments and Webmentions',

    // - empty messages
    'section.empty.pending'             => 'No pending comments',
    'section.empty.default'             => 'No comments yet',

    // - options buttons
    'section.option.unapprove'          => 'Unapprove',
    'section.option.approve'            => 'Approve',
    'section.option.delete'             => 'Delete',
    'section.option.viewsource'         => 'View source',
    'section.option.viewwebsite'        => 'View website',
    'section.option.sendemail'          => 'Send email',


];

foreach ($translations as $key => $value) {
    $return['commentions.' . $key] = option('sgkirby.commentions.t.en.' . $key, $value);
}
return $return;
