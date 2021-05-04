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
    'snippet.form.name'                 => 'Name',
    'snippet.form.name.optional'        => 'Name (optional)',
    'snippet.form.email'                => 'E-Mail (nicht veröffentlicht)',
    'snippet.form.email.optional'       => 'E-Mail (optional; nicht veröffentlicht)',
    'snippet.form.email.error'          => 'E-Mail-Adresse ungültig',
    'snippet.form.honeypot'             => 'Bitte lasse dieses Feld leer!',
    'snippet.form.website'              => 'Website (öffentlich verlinkt)',
    'snippet.form.website.optional'     => 'Website (optional; öffentlich verlinkt)',
    'snippet.form.website.error'        => 'Keine gültige URL',
    'snippet.form.comment'              => 'Kommentar',
    'snippet.form.comment.error'        => 'Kommentar erforderlich',
    'snippet.form.required'             => 'erforderlich',
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
    'snippet.form.submitcomment'       => 'Absenden',
    'snippet.form.submitwebmention'    => 'Webmention senden',
    'snippet.form.error'               => 'Fehler',

    // - list
    'snippet.list.comments'             => 'Kommentare',
    'snippet.list.comment'              => '{ author }',
    'snippet.list.mentioned'            => '{ author } erwähnte dies',
    'snippet.list.mentionedAt'          => '{ author } erwähnte dies auf { link }',
    'snippet.list.liked'                => '{ author } gab diesem Beitrag ein „Gefällt mir“ auf { link }',
    'snippet.list.reposted'             => '{ author } hat diesen Beitrag auf { link } geteilt',
    'snippet.list.bookmarked'           => '{ author } fügte ein Lesezeichen hinzu auf { link }',
    'snippet.list.replies'              => '{ author } antwortete auf { link }',
    'snippet.list.dateFormat.date'      => 'd.m.Y H:i \U\h\r',
    'snippet.list.dateFormat.strftime'  => '%d.%m.%Y %H:%M Uhr',


    // feedback

    'feedback.comment.queued'           => 'Vielen Dank! Bitte etwas Geduld, Kommentare werden manuell freigeschaltet.',
    'feedback.comment.thankyou'         => 'Vielen Dank für deinen Kommentar!',
    'feedback.comment.fielderrors'      => 'Eingabefehler im Formular; bitte überprüfe die markierten Felder!',
    'feedback.comment.closed'           => 'Für diese Seite kann kein Kommentar abgegeben werden.',
    'feedback.comment.spam'             => 'Das Formular konnte nicht verarbeitet werden; bitte Eingaben überprüfen!',
    'feedback.webmention.queued'        => 'Vielen Dank, deine Webmention wurde registriert! Bitte etwas Geduld, Kommentare werden manuell freigeschaltet.',


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
    'section.option.approve'            => 'Akzeptieren',
    'section.option.delete'             => 'Löschen',
    'section.option.edit'               => 'Bearbeiten',
    'section.option.openwebsite'        => 'Website öffnen',
    'section.option.sendemail'          => 'E-Mail senden',

    // - dialogs
    'section.edit.name'                 => 'Name',
    'section.edit.website'              => 'URL Website',
    'section.edit.email'                => 'E-Mail',
    'section.edit.avatar'               => 'URL Avatarbild',
    'section.edit.timestamp'            => 'Zeitstempel',
    'section.edit.type'                 => 'Typ',
    'section.edit.text'                 => 'Text',
    'section.edit.source'               => 'URL der Quelle (nur bei Webmentions)',
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

    // - page setting toggles
    'section.setting.comments.true'     => 'Kommentare offen',
    'section.setting.comments.false'    => 'Kommentare aus',
    'section.setting.webmentions.true'  => 'Webmentions offen',
    'section.setting.webmentions.false' => 'Webmentions aus',
    'section.setting.display.true'      => 'Sichtbar',
    'section.setting.display.false'     => 'Nicht sichtbar',
    'section.setting.disabledInConfig'  => 'Inaktiv (config)',

    // - misc
    'section.datetime.format'           => 'DD.MM.YYYY · HH:mm [Uhr]',

    // - errors
    'section.error.storage-version'      => '<strong>Aktion notwendig!</strong> Du hast das <em>Kirby3-Commentions</em>-Plugin auf Version 1.x aktualisiert, doch deine Daten sind noch im (jetzt inkompatiblen) 0.x-Format gespeichert! Keine Sorge: Deine Daten gehen nicht verloren, aber du musst den <a href="/commentions-migrationassistant" rel="noopener noreferrer" target="_blank">Migrations-Assistenten</a> ausführen, damit wieder alles funktioniert!',
    'section.error.missing-dependencies' => '<strong>Fehlende Bibliotheken:</strong> Das Commentions-Plugin verwendet <strong>html5-php</strong> und <strong>HTML Purifier</strong>, um HTML-Eingaben zu filtern, analysieren und formatieren. Aus Sicherheitsgründen wird kein HTML-Text in Kommentaren oder Webmentions angezeigt und keine Markdown-Formatierung angewendet, solange diese Bibliotheken fehlen.  Hinweise zu ihrer manuellen Installation findest du in der <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#installation" rel="noopener noreferrer" target="_blank">README.md</a>.',
    'section.error.no-templates-defined' => '<strong>Konfiguration unvollständig:</strong> Die erforderlichen Konfigurationsvariablen templatesWithComments und templatesWithWebmentions sind nicht gesetzt. Für Hinweise zum Setup siehe <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#step-3-adding-frontend-uis-to-your-templates" rel="noopener noreferrer" target="_blank">README.md</a>.',
    'section.error.cronjob-alert'        => '<strong>Konfiguration unvollständig:</strong> Der zur Verarbeitung eingehender Webmentions nötige Cronjob wurde in den letzten 24 Stunden nicht ausgeführt. Um diese Fehlermeldung auszublenden, kannst du <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#step-3-setting-up-webmentions-optional" rel="noopener noreferrer" target="_blank">den Cronjob einrichten</a> oder <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#activate-by-template" rel="noopener noreferrer" target="_blank">Webmentions deaktivieren</a>.',
];

foreach ($translations as $key => $value) {
    $return['commentions.' . $key] = option('sgkirby.commentions.t.de.' . $key, $value);
}
return $return;
