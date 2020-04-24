<?php

$translations = [


    // general

    'commentions.name.anonymous'                    => 'Anonym',


    // snippets

    // - form
    'commentions.snippet.form.headline'             => 'Kommentare und Webmentions',
    'commentions.snippet.form.ctacomment'           => 'Hinterlasse einen Kommentar',
    'commentions.snippet.form.ctawebmention'        => 'Auf der eigenen Website geantwortet? Sende eine Webmention!',
    'commentions.snippet.form.responseurl'          => 'URL der Antwort auf deiner Website (stelle sicher, dass die Seite einen Link auf diese URL enthält)',
    'commentions.snippet.form.name'                 => 'Name (optional)',
    'commentions.snippet.form.email'                => 'E-Mail (optional; falls du eine persönliche Antwort wünschst)',
    'commentions.snippet.form.honeypot'             => 'Bitte lasse dieses Feld leer!',
    'commentions.snippet.form.website'              => 'Website (optional; wird öffentlich verlinkt, falls angegeben)',
    'commentions.snippet.form.comment'              => 'Kommentar',

    // - list
    'commentions.snippet.list.comments'             => 'Kommentare',
    'commentions.snippet.list.comment'              => '{ author }',
    'commentions.snippet.list.mentioned'            => '{ author } erwähnte dies',
    'commentions.snippet.list.mentionedAt'          => '{ author } erwähnte dies auf { link }',
    'commentions.snippet.list.liked'                => '{ author } gab diesem Beitrag ein „Gefällt mir« auf { link }',
    'commentions.snippet.list.bookmarked'           => '{ author } fügte ein Lesezeichen hinzu auf { link }',
    'commentions.snippet.list.replies'              => '{ author } antwortete auf { link }',
    'commentsions.snippet.list.dateFormat.date'     => 'd.m.Y H:i Uhr',
    'commentsions.snippet.list.dateFormat.strftime' => '%d.%m.%Y %H:%M Uhr',


    // panel sections

    // - headlines
    'commentions.section.headline.default'          => 'Kommentare und Webmentions',
    'commentions.section.headline.pending'          => 'Eingang: Kommentare und Webmentions',
    'commentions.section.headline.all'              => 'Alle Kommentare und Webmentions',

    // - empty messages
    'commentions.section.empty.pending'             => 'Keine ungeprüften Kommentare',
    'commentions.section.empty.default'             => 'Keine Kommentare',

    //  -options buttons
    'commentions.section.option.unapprove'          => 'Ablehnen',
    'commentions.section.option.approve'            => 'Akzteptieren',
    'commentions.section.option.delete'             => 'Löschen',
    'commentions.section.option.viewsource'         => 'Quelle ansehen',
    'commentions.section.option.viewwebsite'        => 'Website ansehen',
    'commentions.section.option.sendemail'          => 'E-Mail senden',


];

foreach ($translations as $key => $value) {
    $return['commentions.' . $key] = option('sgkirby.commentions.t.de.' . $key, $value);
}
return $return;
