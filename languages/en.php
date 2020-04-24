<?php

$translations = [


    // general

    'name.anonymous'                    => 'Anonymous',


    // snippets

    // - form
    'snippet.form.headline'             => 'Comments and Webmentions',
    'snippet.form.ctacomment'           => 'Leave a comment',
    'snippet.form.ctawebmention'        => 'Replied on your own website? Send a Webmention!',
    'snippet.form.responseurl'          => 'URL of the response on your site (make sure it has a hyperlink to this page)',
    'snippet.form.name'                 => 'Name (optional)',
    'snippet.form.email'                => 'Email (optional; if you’d like a personal reply)',
    'snippet.form.honeypot'             => 'Please leave this field empty!',
    'snippet.form.website'              => 'Website (optional; publicly linked if provided)',
    'snippet.form.comment'              => 'Comment',

    // - list
    'snippet.list.comments'             => 'Comments',
    'snippet.list.comment'              => '{ author }',
    'snippet.list.mentioned'            => '{ author } mentioned this',
    'snippet.list.mentionedAt'          => '{ author } mentioned this at { link }',
    'snippet.list.liked'                => '{ author } liked this at { link }',
    'snippet.list.bookmarked'           => '{ author } bookmarked this at { link }',
    'snippet.list.replies'              => '{ author } replied at { link }',
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
