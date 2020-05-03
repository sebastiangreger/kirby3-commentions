<?php

$translations = [


    // general

    'name.anonymous'                    => 'Anonym',


    // snippets

    // - form
    'snippet.form.headline'             => 'Kommentare und Webmentions',
    'snippet.form.ctacomment'           => 'Hinterlasse einen Kommentar',
    'snippet.form.ctawebmention'        => 'Auf der eigenen Website geantwortet? Sende eine Webmention!',
    'snippet.form.responseurl'          => 'URL der Antwort auf deiner Website (stelle sicher, dass die Seite einen Link auf diese URL enthält)',
    'snippet.form.name'                 => 'Name (optional)',
    'snippet.form.email'                => 'E-Mail (optional; falls du eine persönliche Antwort wünschst)',
    'snippet.form.honeypot'             => 'Bitte lasse dieses Feld leer!',
    'snippet.form.website'              => 'Website (optional; wird öffentlich verlinkt, falls angegeben)',
    'snippet.form.comment'              => 'Kommentar',
    'snippet.form.help.noformatting'    => 'All HTML tags are removed.',
    'snippet.form.help.title'           => 'Verfügbare Formatierungen',
    'snippet.form.help.intro'           =>
        'Benutze Markdown-Befehle oder ihre HTML-Äquivalente, um deinen Kommentar zu formatieren:',
    'snippet.form.help.common'         =>
        '<dt>Textauszeichnungen</dt>' .
        '<dd><em>*kursiv*</em>, <strong>**fett**</strong>, <del>~~durchgestrichen~~</del>, <code>`Code`</code> und <mark>&lt;mark&gt;markierter Text&lt;/mark&gt;</mark></code>.</dd>' .
        '<dt>Listen</dt>' .
        '<dd><pre class="code"><code class="language-markdown">- Listenpunkt 1<br>- Listenpunkt 1</code></pre>' .
        '<dd><pre class="code"><code class="language-markdown">1. Nummerierte Liste 1<br>2. Nummerierte Liste 2</code></pre></dd>' .
        '<dt>Zitate</dt>' .
        '<dd><pre class="code"><code class="language-markdown">&gt; Zitierter Text</code></pre></dd>' .
        '<dt>Code-Blöcke</dt>' .
        '<dd><pre><code>```<br>// Ein einfacher Code-Block<br>```</code></pre>' .
        '<dd><pre class="code"><code class="language-php">```php<br>// Etwas PHP-Code<br>phpinfo();<br>```</code></pre></dd>',
    'snippet.form.help.links'          =>
        '<dt>Verlinkungen</dt>' .
        '<dd><code>[Link-Text](https://example.com)</code></dd>',
    'snippet.form.help.autolinks'      =>
        '<dd>Vollständige URLs werden automatisch in Links umgewandelt.</dd>',

    // - list
    'snippet.list.comments'             => 'Kommentare',
    'snippet.list.comment'              => '{ author }',
    'snippet.list.mentioned'            => '{ author } erwähnte dies',
    'snippet.list.mentionedAt'          => '{ author } erwähnte dies auf { link }',
    'snippet.list.liked'                => '{ author } gab diesem Beitrag ein „Gefällt mir“ auf { link }',
    'snippet.list.bookmarked'           => '{ author } fügte ein Lesezeichen hinzu auf { link }',
    'snippet.list.replies'              => '{ author } antwortete auf { link }',
    'snippet.list.dateFormat.date'      => 'd.m.Y H:i Uhr',
    'snippet.list.dateFormat.strftime'  => '%d.%m.%Y %H:%M Uhr',


    // panel sections

    // - headlines
    'section.headline.default'          => 'Kommentare und Webmentions',
    'section.headline.pending'          => 'Eingang: Kommentare und Webmentions',
    'section.headline.all'              => 'Alle Kommentare und Webmentions',

    // - empty messages
    'section.empty.pending'             => 'Keine ungeprüften Kommentare',
    'section.empty.default'             => 'Keine Kommentare',

    //  -options buttons
    'section.option.unapprove'          => 'Ablehnen',
    'section.option.approve'            => 'Akzteptieren',
    'section.option.delete'             => 'Löschen',
    'section.option.openwebsite'        => 'Website öffnen',
    'section.option.sendemail'          => 'E-Mail senden',

    // - dialogs
    'section.delete.webmention.confirm' => 'Willst du diese <strong>Webmention</strong> wirklich löschen?',
    'section.delete.mention.confirm'    => 'Willst du diese <strong>Erwähnung</strong> wirklich löschen?',
    'section.delete.trackback.confirm'  => 'Willst du diesen <strong>Trackback</strong> wirklich löschen?',
    'section.delete.pingback.confirm'   => 'Willst du diesen <strong>Pingback</strong> wirklich löschen?',
    'section.delete.like.confirm'       => 'Willst du dieses <strong>„Gefällt mir“</strong> wirklich löschen?',
    'section.delete.bookmark.confirm'   => 'Willst du dieses <strong>Lesezeichen</strong> wirklich löschen?',
    'section.delete.reply.confirm'      => 'Willst du diese <strong>Antwort</strong> wirklich löschen?',
    'section.delete.comment.confirm'    => 'Willst du diesen <strong>Kommentar</strong> wirklich löschen?',
    'section.delete.unknown.confirm'    => 'Willst du diesen <strong>Eintrag</strong> wirklich löschen?',

    // - buttons
    'section.button.viewsource'         => 'Quelltext anzeigen',
    'section.button.refresh'            => 'Aktualisieren',

    // - misc
    'section.datetime.format'           => 'DD.MM.YYYY · HH:mm [Uhr]',

    // - errors
    'section.error.storage-version'      => '<strong>Aktion notwendig!</strong> Du hast das <em>Kirby3-Commentions</em>-Plugin auf Version 1.x aktualisiert, doch deine Daten sind noch im (jetzt inkompatiblen) 0.x-Format gespeichert! Keine Sorge: Deine Daten gehen nicht verloren, aber du musst den  <a href="/commentions-migrationassistant" target="_blank">Migrations-Assistenten</a> ausführen, damit wieder alles funktioniert!',
    'section.error.missing-dependencies' => '<strong>Fehlende Bibliotheken:</strong> Das Commentions-Plugin verwendet <a href="http://masterminds.github.io/html5-php/" target="_blank">html5-php</a> und <a href="http://htmlpurifier.org/">HTML Purifier</a>, um HTML-Eingaben zu filtern, analysieren und formatieren. Aus Sicherheitsgründen wird kein HTML-Text in Kommentaren oder Webmentions angezeigt und keine Markdown-Formatierung angewendet, solange diese Bibliotheken fehlen. In <code>README.md</code> im Plugin-Ordner findest du Hinweise dazu, wie du diese Pakete manuell installieren kannst.',
    'section.error.cronjob-alert'        => '<strong>Fehler in der Konfiguration:</strong> Der zur Verarbeitung eingehender Webmentions nötige Cronjob wurde in den letzten 24 Stunden nicht ausgeführt. In <code>README.md</code> im Plugin-Ordner findest du Hinweise zur Einrichtung des Cronjobs.',
];

foreach ($translations as $key => $value) {
    $return['commentions.' . $key] = option('sgkirby.commentions.t.de.' . $key, $value);
}
return $return;
