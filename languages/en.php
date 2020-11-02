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
    'snippet.form.name'                => 'Name',
    'snippet.form.name.optional'       => 'Name (optional)',
    'snippet.form.email'               => 'Email',
    'snippet.form.email.optional'      => 'Email (optional; not displayed)',
    'snippet.form.honeypot'            => 'Please leave this field empty!',
    'snippet.form.website'             => 'Website (public if provided)',
    'snippet.form.website.optional'    => 'Website (optional; public if provided)',
    'snippet.form.comment'             => 'Comment',
    'snippet.form.required'            => 'required',
    'snippet.form.help.noformatting'   => 'HTML-Formatierungen werden entfernt.',
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
    'snippet.form.submitcomment'       => 'Submit',
    'snippet.form.submitwebmention'    => 'Submit webmention',

    // - list
    'snippet.list.comments'            => 'Comments',
    'snippet.list.comment'             => '{ author }',
    'snippet.list.mentioned'           => '{ author } mentioned this',
    'snippet.list.mentionedAt'         => '{ author } mentioned this at { link }',
    'snippet.list.liked'               => '{ author } liked this at { link }',
    'snippet.list.reposted'            => '{ author } reposted this at { link }',
    'snippet.list.bookmarked'          => '{ author } bookmarked this at { link }',
    'snippet.list.replies'             => '{ author } replied at { link }',
    'snippet.list.dateFormat.date'     => 'Y-m-d H:i',
    'snippet.list.dateFormat.strftime' => '%Y-%m-%d %H:%M',


    // feedback

    'feedback.comment.queued'           => 'Thank you! Please be patient, your comment has to be approved by the editor.',
    'feedback.comment.thankyou'         => 'Thank you for your comment!',
    'feedback.webmention.queued'        => 'Thank you, your webmention has been queued for processing. Please be patient, your comment has to be approved by the editor.',


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
    'section.option.edit'               => 'Edit',
    'section.option.openwebsite'        => 'Open website',
    'section.option.sendemail'          => 'Send email',

    // - dialogs
    'section.edit.name'                 => 'Name',
    'section.edit.website'              => 'Website URL',
    'section.edit.email'                => 'Email address',
    'section.edit.avatar'               => 'Avatar URL',
    'section.edit.timestamp'            => 'Timestamp',
    'section.edit.type'                 => 'Type',
    'section.edit.text'                 => 'Text',
    'section.edit.source'               => 'Source URL (for Webmentions only)',
    'section.delete.webmention.confirm' => 'Do you really want to delete this <strong>Webmention</strong>?',
    'section.delete.mention.confirm'    => 'Do you really want to delete this <strong>Mention</strong>?',
    'section.delete.trackback.confirm'  => 'Do you really want to delete this <strong>Trackback</strong>?',
    'section.delete.pingback.confirm'   => 'Do you really want to delete this <strong>Pingback</strong>?',
    'section.delete.like.confirm'       => 'Do you really want to delete this <strong>Like</strong>?',
    'section.delete.bookmark.confirm'   => 'Do you really want to delete this <strong>Bookmark</strong>?',
    'section.delete.reply.confirm'      => 'Do you really want to delete this <strong>Reply</strong>?',
    'section.delete.comment.confirm'    => 'Do you really want to delete this <strong>Comment</strong>?',
    'section.delete.unknown.confirm'    => 'Do you really want to delete this?',

    // - buttons
    'section.button.viewsource'         => 'View source',
    'section.button.refresh'            => 'Refresh',

    // - page setting toggles
    'section.setting.comments.true'     => 'Comments open',
    'section.setting.comments.false'    => 'Comments closed',
    'section.setting.webmentions.true'  => 'Webmentions open',
    'section.setting.webmentions.false' => 'Webmentions closed',
    'section.setting.display.true'      => 'Shown on site',
    'section.setting.display.false'     => 'Hidden from site',
    'section.setting.disabledInConfig'  => 'Disabled in config',

    // - misc
    'section.datetime.format'           => 'YYYY-MM-DD HH:mm',

    // - errors
    'section.error.storage-version'      => '<strong>Action required!</strong> You updated the <em>Kirby3-Commentions</em> plugin to version 1.x, but your setup is still in the (now incompatible) 0.x format! Worry not: no data has been lost, but you will have to use the <a href="/commentions-migrationassistant" rel="noopener noreferrer" target="_blank">Migration assistant</a> to get things running again!',
    'section.error.missing-dependencies' => '<strong>Missing libraries:</strong> The Commentions plugin uses <strong>html5-php</strong> and <strong>HTML Purifier</strong> for filtering, analysing and formatting HTML input. For security reasons, no HTML is shown in comments or Webmention and no Markdown-formatting will be applied, as long as these Libraries are missing. For help with installing them manually, see the <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#installation" rel="noopener noreferrer" target="_blank">README.md</a>.',
    'section.error.no-templates-defined' => '<strong>Setup issue:</strong> The required config settings templatesWithComments and templatesWithWebmentions are missing. For details see the <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#step-3-adding-frontend-uis-to-your-templates" rel="noopener noreferrer" target="_blank">README.md</a>.',
    'section.error.cronjob-alert'        => '<strong>Setup issue:</strong> The cron job required to process incoming webmentions, has not been run in the last 24h. To get rid of this warning, you may <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#step-4-setting-up-webmentions-optional" rel="noopener noreferrer" target="_blank">set up the cronjob</a> or <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#activate-by-template" rel="noopener noreferrer" target="_blank">disable webmentions</a>.',
];

foreach ($translations as $key => $value) {
    $return['commentions.' . $key] = option('sgkirby.commentions.t.en.' . $key, $value);
}
return $return;
