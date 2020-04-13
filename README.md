# Kirby 3 Commentions

## Overview

A minimalistic comment system with Webmention support.

* Comments can be submitted through a form on the page or as a [Webmention](https://indieweb.org/webmention).
* Incoming webmentions are stored in a queue and processed asynchronously.
* Comments can be approved/deleted in the Kirby Panel

**NB. The plugin only covers incoming webmentions (notifications from other websites who link to a page). Sending webmentions to URLs linked in content requires a separate solution, such as [Kirby 3 Sendmentions](https://github.com/sebastiangreger/kirby3-sendmentions).**

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby3-commentions`.

## Setup

### Announce your webmentions endpoint in your HTML head

In order to receive webmentions (this is optional, you may also use the plugin for traditional comments only), you have to announce your webmention endpoint in the HTML head. The easiest way is by adding the following helper in your `header.php` or similar (depending on your template setup, just make sure it's within the &lt;head&gt; tags):

```php
<?= commentionsEndpoints(); ?>
```

By default, the above helper is nothing more than a shortcut to render the following HTML, which allows other websites to discover that your site accepts webmentions:

```HTML
<link rel="webmention" href="https://<SITE-URL>/webmention-endpoint" />
<link rel="http://webmention.org/" href="https://<SITE-URL>/webmention-endpoint" />
```

### Adding the Commentions UIs to the Panel blueprints

All comments are stored in small text files attached to each page in the Kirby CMS. In order to display and manage these, it is required to add them to your Panel blueprints.

#### Inbox view

To approve/delete incoming comments, add the following to a suitable blueprint; e.g. to `site/blueprints/site.yml`:

```yaml
sections:
  commentions:
    type: commentions
    show: pending
```

Alternatively, `pending` can be replaced with `all`, in which case all comments are displayed. This is useful in setups where new comments are set to be approved automatically.

#### Page-specific list of comments

To display and manage the comments for each page, add the following to the according blueprint in `site/blueprints/pages` (i.e. to every page blueprints where you want to use comments):

```yaml
sections:
  commentions:
    type: commentions
```

### Adding frontend UIs to your templates

The plugin comes with a set of default snippets. These are optimized to work with the Kirby Starterkit but might be of use in other themes as well; they can also serve as boilerplates for designing your own.

To show comments on pages and display a form to leave new comments, there are two options:

#### A. Add everything at once

In order to add everything at once, add the following helper to the according templates in `site/templates` - a shorthand for the three helpers described in alternative B:

```php
<?= commentions(); ?>
```

#### B. Add three template parts where you see them fit best

Alternatively, you can add the form feedback snippet (error or success message), the comment form and the list of comments separately, by adding the following helpers to the according templates in `site/templates` - this for example allows to integrate the feedback element at the top of a template, and changing the default order of form vs. comment list.

To render the user feedback UI (error/success message after a user submits the comment form):

```php
<?= commentionsFeedback(); ?>
```

To render the comment form:

```php
<?= commentionsForm(); ?>
```

To render a list of comments:

```php
<?= commentionsList(); /* adds list of comments */ ?>
```

By default, `commentionsList()` presents all comments and mentions in one list. To present certain reactions (e.g. bookmarks, likes, RSVPs) separately, use `commentionsList('grouped')` instead (check options further below for additional control).

### Retrieving comments for display in the frontend

While it is advisable to use the `commentionsFeedback()` and `commentionsForm()` helpers as their markup changes based on the plugin settings, you may want to have more control over displaying your comments and webmentions.

The function `$page->commentions()` on a page object returns an array with the raw and complete comments data for that page (NB. this includes also comments pending approval, as indicated by the `approved` field!). This is the preferred API if you want to control how your comments and webmentions are displayed. Use that array to build your presentation logic.

### Adding CSS styles

If you would like to use basic CSS styles for these prefabricated HTML snippets (a minimalistic design suitable for the Kirby 3 Starterkit), add the following to your HTML &lt;head&gt; area (e.g. in `snippets/header.php` in the Starterkit):

```php
<?= commentionsCss(); ?>
```

Unless your site is running on the Starterkit, you would likely want to write your own CSS for the pre-rendered markup.

### Setting up a cronjob to process the inbox queue

Per the specification, incoming webmentions are always placed in a backlog queue for asynchronous processing (this is to mitigate the risk of DDoS attacks by flooding your site with webmentions). In order to have these webmentions delivered into your Commentions inbox, this queue needs to be processed regularly.

First, set a secret key with at least 10 characters in your `site/config/config.php` (the key may NOT include any of the following: `&` `%` `#` `+` nor a space sign ` `):

```php
return [
	'sgkirby.commentions.secret' => '<YOUR-SECRET>'
];
```

Second, set up a cronjob to call the URL `https://<SITE-URL>/commentions-processqueue?token=<YOUR-SECRET>` at regular intervals (when testing this URL in a browser first, it responds with either "Success" or a descriptive error message).

Every time this URL is called, the queue of incoming webmentions is processed; valid webmentions are moved to the comment inbox, while invalid ones are silently deleted.

## Options

The plugin can be configured with optional settings in your `site/config/config.php`.

### Webmention endpoint

To change the URL of the webmention endpoint (default is `https://<SITE-URL>/webmention-endpoint`), add the following setting and change the string as desired:

```php
'sgkirby.commentions.endpoint' => 'webmention-endpoint',
```

### Comment form fields

By default, only an optional name field and a textarea for the comment are shown. To modify, add this array to `site/config/config.php` and remove the undesired field names (the form is rendered to only include the fields present in this array):

```php
'sgkirby.commentions.formfields' => [
    'name',
    'email',
    'url'
],
```

### Hide forms behind button

If desired, the following setting triggers additional markup that can be used to hide the forms by default, allowing for an accessible open/close functionality:

```php
'sgkirby.commentions.hideforms' => true,
```

### Privacy settings

The plugin is designed with data minimalism in mind; storing more than the absolutely necessary data is possible, but please consider the ethical and possibly legal implications of processing such data.

Since the default presentation does not make use of avatar images, these are not stored. To write avatar URLs from incoming webmention metadata to the comment file, add this setting:

```php
'sgkirby.commentions.avatarurls' => true,
```

### Spam protection

The plugin provides several means to block comment spam; all active by default, these can be deactivated by adding the following setting and then remove any undesired methods:

```php
'sgkirby.commentions.spamprotection' => [
    'honeypot',    /* filter comments where a hidden field contains data */
    'timemin',     /* filter comments submitted too fast */
    'timemax',     /* filter comments submitted after very long time */
],
```

When timeout protections are active, comments are rejected if submitted too soon or too long after the form has been created (disabled when using Kirby's built-in page cache); the defaults can be adjusted by adding either or both of the following settings:

```php
'sgkirby.commentions.spamtimemin' => 5    ,    /* seconds after which a submission is valid; default 5s */
'sgkirby.commentions.spamtimemax' => 86400,    /* seconds after which a submission is no longer valid; default 24h */
```

### Reactions in "grouped" view

When comments are displayed using the `<?= commentionsList('grouped') ?>` helper, adding the following settings array gives control over what reaction types are displayed as separate groups, in what order, and what title is used - remove any comment types to include them in the main comment list instead of displaying them as a separate group:

```php
'sgkirby.commentions.grouped', [
	'read'            => 'Read by',
	'like'            => 'Likes',
	'repost'          => 'Reposts',
	'bookmark'        => 'Bookmarks',
	'rsvp:yes'        => 'RSVP: yes',
	'rsvp:maybe'      => 'RSVP: maybe',
	'rsvp:interested' => 'RSVP: interested',
	'rsvp:no'         => 'RSVP: no',
],
```

## Features

### Plugin features

- [x] Accepts comments on content via comment form or webmention
- [x] Processes incoming webmentions asynchronously (triggered by a cronjob)
- [x] Sets new comments to "waiting for approval" by default (unless changed via setting)
- [x] Stores approved comments/webmentions in a "_commentions.txt" file in the page folder
- [x] Comments/webmentions for each page can be viewed/managed in the Panel view
- [x] A inbox section can be added to a desired blueprint to give easy access to new/unapproved comments
- [x] A helper adds the (configurable) URL of the webmention endpoint in the HTML head
- [x] Comments and the comments form can be included in a page template using a helper function
- [x] UI feedback, form and comment list can be integrated flexibly across templates
- [x] Configurable meta fields (name, e-mail, website); default is name only

### Roadmap/Ideas

- [ ] Extend HTML markup of form elements, to allow for easy theming
- [ ] Research using [Kirby Queue](https://github.com/bvdputte/kirby-queue) plugin for queue processing instead of own queue and cronjob
- [ ] Additional configuration options (e.g. required/optional) for form meta fields

## Requirements

Kirby 3.3.0+(https://getkirby.com)

## Credits

Inspiration and code snippets from:

- https://github.com/bastianallgeier/kirby-webmentions
- https://github.com/sebsel/seblog-kirby-webmentions

Included vendor libraries:

- https://github.com/indieweb/php-comments
- https://github.com/microformats/php-mf2

## License

Kirby 3 Commentions is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Copyright © 2020 [Sebastian Greger](https://sebastiangreger.net)

It is discouraged to use this plugin in any project that promotes the destruction of our planet, racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
