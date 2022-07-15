<?php

$translations = [


    // general

    'name.anonymous'                    => 'Anonyme',


    // snippets

    // - form
    'snippet.form.headline'            => 'Commentaires et Webmentions',
    'snippet.form.ctacomment'          => 'Ajouter un commentaire',
    'snippet.form.ctawebmention'       => 'Répondez sur votre propre site, envoyez une Webmention!',
    'snippet.form.responseurl'         => 'URL de la réponse sur votre site (assurez-vous qu’elle contient un lien vers cette page)',
    'snippet.form.name'                => 'Nom',
    'snippet.form.name.optional'       => 'Nom (optionnel)',
    'snippet.form.email'               => 'E-mail',
    'snippet.form.email.optional'      => 'E-mail (optionnel ; n’est pas affiché)',
    'snippet.form.honeypot'            => 'Laissez ce champ vide !',
    'snippet.form.website'             => 'Site web (public si saisi)',
    'snippet.form.website.optional'    => 'Site web (optionel; public si saisi)',
    'snippet.form.comment'             => 'Commentaire',
    'snippet.form.required'            => 'requis',
    'snippet.form.help.noformatting'   => 'La mise en forme HTML est supprimée.',
    'snippet.form.help.title'          => 'Commandes de formatage disponibles',
    'snippet.form.help.intro'          =>
        'Utilisez les commandes Markdown ou leurs équivalents HTML pour ajouter un formatage simple à votre commentaire :',
    'snippet.form.help.common'         =>
        '<dt>Balisage du texte</dt>' .
        '<dd><em>*italique*</em>, <strong>**gras**</strong>, <del>~~barré~~</del>, <code>`code`</code> et <mark>&lt;mark&gt;texte surligné&lt;/mark&gt;</mark></code>.</dd>' .
        '<dt>Listes</dt>' .
        '<dd><pre class="code"><code class="language-markdown">- Élément non ordonné 1<br>- Élément non ordonné 2</code></pre>' .
        '<dd><pre class="code"><code class="language-markdown">1. Élément ordonné 1<br>2. Élément ordonné 2</code></pre></dd>' .
        '<dt>Citations</dt>' .
        '<dd><pre class="code"><code class="language-markdown">&gt; Texte cité</code></pre></dd>' .
        '<dt>Blocs de code</dt>' .
        '<dd><pre><code>```<br>// Un simple bloc de code<br>```</code></pre>' .
        '<dd><pre class="code"><code class="language-php">```php<br>// Du code PHP<br>phpinfo();<br>```</code></pre></dd>',
    'snippet.form.help.links'          =>
        '<dt>Liens</dt>' .
        '<dd><code>[Texte du lien](https://example.com)</code></dd>',
    'snippet.form.help.autolinks'      =>
        '<dd>Les URLs complètes sont automatiquement converties en liens.</dd>',
    'snippet.form.submitcomment'       => 'Valider',
    'snippet.form.submitwebmention'    => 'Valider la webmention',

    // - list
    'snippet.list.comments'            => 'Commentaires',
    'snippet.list.comment'             => '{ author }',
    'snippet.list.mentioned'           => '{ author } a mentionné ceci',
    'snippet.list.mentionedAt'         => '{ author } a mentionné ceci sur { link }',
    'snippet.list.liked'               => '{ author } a aimé ceci sur { link }',
    'snippet.list.reposted'            => '{ author } a republié ceci sur { link }',
    'snippet.list.bookmarked'          => '{ author } a enregistré ceci sur { link }',
    'snippet.list.replies'             => '{ author } a répondu sur { link }',
    'snippet.list.dateFormat.date'     => 'd-m-Y H:i',
    'snippet.list.dateFormat.strftime' => '%d-%m-%Y %H:%M',
    'snippet.list.dateFormat.intl'     => 'yyyy-MM-dd HH:mm',


    // feedback

    'feedback.comment.queued'           => 'Merci ! Soyez patient, votre commentaire doit être approuvé par l’éditeur.',
    'feedback.comment.thankyou'         => 'Merci de votre commentaire!',
    'feedback.webmention.queued'        => 'Merci, votre webmention est en attente de traitement. Soyez patient, votre commentaire doit être approuvé par l’éditeur.',


    // panel sections

    // - headlines
    'section.headline.default'          => 'Commentaires et Webmentions',
    'section.headline.pending'          => 'Inbox: Commentaires et Webmentions',
    'section.headline.all'              => 'Tous les commentaires et Webmentions',

    // - empty messages
    'section.empty.pending'             => 'Pas de commentaire en attente',
    'section.empty.default'             => 'Pas encore de commentaire',

    // - options buttons
    'section.option.unapprove'          => 'Désapprouver',
    'section.option.approve'            => 'Approuver',
    'section.option.delete'             => 'Supprimer',
    'section.option.edit'               => 'Modifier',
    'section.option.openwebsite'        => 'Ouvrir le site',
    'section.option.sendemail'          => 'Envoyer un e-mail',

    // - dialogs
    'section.edit.name'                 => 'Nom',
    'section.edit.website'              => 'URL du site',
    'section.edit.email'                => 'Adresse e-mail',
    'section.edit.avatar'               => 'URL de l’avatar ',
    'section.edit.timestamp'            => 'Timestamp',
    'section.edit.type'                 => 'Type',
    'section.edit.text'                 => 'Texte',
    'section.edit.source'               => 'URL source (seulement pour les Webmentions)',
    'section.delete.webmention.confirm' => 'Voulez-vous vraiment supprimer cette <strong>webmention</strong>?',
    'section.delete.mention.confirm'    => 'Voulez-vous vraiment supprimer cette <strong>mention</strong>?',
    'section.delete.trackback.confirm'  => 'Voulez-vous vraiment supprimer ce <strong>trackback</strong>?',
    'section.delete.pingback.confirm'   => 'Voulez-vous vraiment supprimer ce <strong>pingback</strong>?',
    'section.delete.like.confirm'       => 'Voulez-vous vraiment supprimer ce <strong>like</strong>?',
    'section.delete.bookmark.confirm'   => 'Voulez-vous vraiment supprimer ce <strong>bookmark</strong>?',
    'section.delete.reply.confirm'      => 'Voulez-vous vraiment supprimer cette <strong>réponse</strong>?',
    'section.delete.comment.confirm'    => 'Voulez-vous vraiment supprimer ce <strong>commentaire</strong>?',
    'section.delete.unknown.confirm'    => 'Voulez-vous vraiment supprimer ceci ?',

    // - buttons
    'section.button.viewsource'         => 'Afficher la source',
    'section.button.refresh'            => 'Rafraîchir',

    // - page setting toggles
    'section.setting.comments.true'     => 'Commentaires ouverts',
    'section.setting.comments.false'    => 'Commentaires fermés',
    'section.setting.webmentions.true'  => 'Webmentions ouvertes',
    'section.setting.webmentions.false' => 'Webmentions fermées',
    'section.setting.display.true'      => 'Affichés sur le site',
    'section.setting.display.false'     => 'Masqués sur le site',
    'section.setting.disabledInConfig'  => 'Désactivé dans la configuration',

    // - misc
    'section.datetime.format'           => 'YYYY-MM-DD HH:mm',

    // - errors
    'section.error.storage-version'      => '<strong>Action requise !</strong> Vous avez mis à jour le plugin <em>Kirby3-Commentions</em> vers sa  version 1.x, mais votre configuration est toujours dans le format 0.x (désormais incompatible) ! Pas d’inquiétude : aucune donnée n’a été perdue, mais vous allez devoir utiliser l’<a href="/commentions-migrationassistant" rel="noopener noreferrer" target="_blank">assistant de migration</a> pour faire fonctionner le système à nouveau !',
    'section.error.missing-dependencies' => '<strong>Librairies manquantes :</strong> Le plugin Commentions utilise <strong>html5-php</strong> et <strong>HTML Purifier</strong> pour filtrer, analyser et formater les saisies HTML. Pour des raisons de sécurité, le HTML n’est pas affiché dans les commentaires ouy les Webmentions et aucun formatage markdown ne sera appliqué tant que ces librairies seront manquantes. Pour vous aider à les installer manuellement, voir le fichier <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#installation" rel="noopener noreferrer" target="_blank">README.md</a>.',
    'section.error.no-templates-defined' => '<strong>Erreur d’installation :</strong> les paramètres de configuration templatesWithComments et templatesWithWebmentions sont manquants. Pour plus de détails, voir le fichier <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#step-3-adding-frontend-uis-to-your-templates" rel="noopener noreferrer" target="_blank">README.md</a>.',
    'section.error.cronjob-alert'        => '<strong>Erreur d’installation :</strong> La *tâche cron* (cron job) requise pour traiter les webmentions entrantes n’a pas été lancée dans les dernières 24h. Pour supprimer cet avertissement, vous pouvez <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#step-4-setting-up-webmentions-optional" rel="noopener noreferrer" target="_blank">installer le cronjob</a> ou <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/README.md#activate-by-template" rel="noopener noreferrer" target="_blank">désactiver les webmentions</a>.',
];

foreach ($translations as $key => $value) {
    $return['commentions.' . $key] = option('sgkirby.commentions.t.fr.' . $key, $value);
}
return $return;
