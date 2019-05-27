# Kirby 3 Commentions

⚠ _This plugin is currently a conceptual exploration at a very early Beta stage; feel free to play around with it for experimentation, but think twice before using in production!_

A minimalistic comment system. Comments can be sent through a form on the page or as a [Webmention](https://indieweb.org/webmention). Incoming webmentions are stored in a queue and processed asynchronously. All incoming comments and webmentions are stored in an inbox, pending admin approval. Approved comments are stored in the page's content file as a YAML structure and can be edited from within the Panel.

Read more about the [approach and philosophy](https://sebastiangreger.net/2019/05/sendmention-commention-webmentions-for-kirby-3) of this plugin

**NB. The plugin only covers incoming webmentions (notifications from other websites who link to a page). Sending webmentions to URLs linked in content requires a separate solution, such as [Kirby 3 Sendmentions](https://github.com/sebastiangreger/kirby3-sendmentions).**

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby3-commentions`.

## Setup

_NB. This plugin does not work without the following steps._

### 1. Prepare your blueprints

To approve/delete incoming comments and webmentions, place the comment inbox in your panel by adding the following to a suitable blueprint; e.g. to `site/blueprints/site.yml` (the headline is optional):

```yaml
sections:
  commentions:
    type: commentions
    headline: Pending comments
```

To display, edit and manage comments in the Panel, add the following to the according blueprint in `site/blueprints/pages` (i.e. to all page blueprints where you want to use comments):

```yaml
fields:
  comments: fields/commentions
```

### 2. Add the UIs to your templates

To show comments on pages and display a form to leave new comments, there are two options:

1. In order to add everything at once, add the following helper to the according templates in `site/templates` - a shorthand for the three helpers described next:

```php
commentions();    /* adds user feedback, comment form and comment list */
```

2. Alternatively, you can add the form feedback (error or success message), the comment form and the list of comments separately, by adding the following helpers to the according templates in `site/templates` - this for example allows to integrate the feedback element at the top of a template, and changing the default order of form vs. comment list:

```php
commentionsFeedback();    /* adds user feedback (error/success message) */
commentionsForm();        /* adds comment form */
commentionsList();        /* adds list of comments */
```

By default, `commentionsList()` presents all comments and mentions in one list. To present certain reactions (e.g. bookmarks, likes, RSVPs) separately, use the following instead (check options below for further control):

```php
commentionsList('grouped');        /* adds list of comments, with separate reactions */
```

To fully customize the presentation of the reactions, an array with all approved comments and mentions can be retrieved as follows:

```php
$comments = commentionsList('raw');        /* returns array of all comments */
```

If you would like to use the basic CSS styles (minimalistic design suitable for the Kirby 3 Starterkit), add the following to your HTML head area (e.g. in `snippets/header.php` in the Starterkit):

```php
commentionsCss();
```

In order to receive webmentions, you have to announce your webmention endpoint in the HTML head. The easiest way is by adding the following helper in your `header.php` or similar (depending on your template setup):

```php
commentionsEndpoints();
```

### 3. Set up a cronjob to process the inbox queue

Incoming webmentions are placed in a queue for asynchronous processing. In order to receive these webmentions into your comments inbox, this queue needs to be processed regularly:

First, set a secret key with at least 10 characters in your `site/config/config.php` (the key may NOT include any of the following: `&` `%` `#` `+` nor a space sign ` `):

```php
'sgkirby.commentions.secret' => '<YOUR-SECRET>',
```

Second, set up a cronjob to call the following URL at regular intervals (when testing this URL in a browser first, it responds with either "Success" or a descriptive error message):

`https://<SITE-URL>/commentions-processqueue?token=<YOUR-SECRET>`

Every time this URL is called, the backlog of incoming webmentions is processed; valid webmentions are moved to the comment inbox, while invalid ones are silently deleted.

## Options

The plugin can be configured with optional settings in your `site/config/config.php`.

### Webmention endpoint

To change the URL of the webmention endpoint (default is `https://domain.tld/webmention-endpoint`), add the following setting and change the string as desired:

```php
'sgkirby.commentions.endpoint' => 'webmention-endpoint',
```

### Comment form fields

By default, only an optional name field and a textarea for the comment are shown. To modify, add this array to `site/config/config.php` and remove only the undesired field names:

```php
'sgkirby.commentions.formfields' => [
    'name',
    'email',
    'url'
],
```

### Privacy settings

The plugin is designed with data minimalism in mind; storing more than the absolutely necessary data is possible, but please consider the ethical and possibly legal implications of processing such data.

Since the default presentation does not make use of avatar images, these are not stored. To write avatar URLs from webmention metadata to the comment file, change the default `false` value to `true`:

```php
'sgkirby.commentions.avatarurls' => false,
```

### Spam protection

The plugin provides several means to block comment spam; all active by default, these can be deactivated by using the following setting with undesired methods removed from the array:

```php
'sgkirby.commentions.spamprotection' => [
    'honeypot',    /* filter comments where a hidden field contains data */
    'timemin',     /* filter comments submitted too fast */
    'timemax',     /* filter comments submitted after very long time */
],
```

When timeout protections are active, comments are rejected if submitted too soon or too long after the form has been created (disabled when using Kirby's built-in page cache); the defaults can be adjusted as follows:

```php
'sgkirby.commentions.spamtimemin' => 5    ,    /* seconds after which a submission is valid; default 5s */
'sgkirby.commentions.spamtimemax' => 86400,    /* seconds after which a submission is no longer valid; default 24h */
```

### Reactions in "grouped" view

When comments are displayed using `commentionsList('grouped')`, the following settings array controls what reaction types are displayed as separate groups, in what order, and what title is used - remove any comment types to include them in the main comment list:

```php
'sgkirby.commentions.grouped', [
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
- [x] Places all incoming comments and webmentions in an inbox for approval via the Panel
- [x] Stores approved comments/webmentions as a structure field within the content's markup file
- [x] Published comments/webmentions can be managed in the Panel view for each page
- [x] Comments and the comments form can be included in a page template using a helper function
- [x] A helper adds the (configurable) URL of the webmention endpoint in the HTML head
- [x] UI feedback, form and comment list can be integrate flexibly across templates
- [x] Configurable meta fields (name, e-mail, website); default is name only

### Roadmap/Ideas

- [ ] Extend HTML markup of form elements, to allow for easy theming
- [ ] Enhance presentation of comments in the frontend
- [ ] Improve design of comment inbox by customizing Panel component
- [ ] Use [Kirby Queue](https://github.com/bvdputte/kirby-queue) plugin for queue processing instead of own queue and cronjob
- [ ] Additional configuration options (e.g. required/optional) for form meta fields
- [ ] Display different types of webmentions accordingly (reply, bookmark, RSVP...)
- [ ] Make UI texts translatable
- [ ] Check whether the editor lock function in Kirby 3.2 will enable auto-approving comments without conflict
- [ ] Investigate alternative means of storing comments (e.g. a Sqlite database or subpages)

## Requirements

Kirby 3.1.3+(https://getkirby.com)

## Credits

Inspiration and code snippets from:

- https://github.com/bastianallgeier/kirby-webmentions
- https://github.com/sebsel/seblog-kirby-webmentions

Included vendor libraries:

- https://github.com/indieweb/php-comments
- https://github.com/microformats/php-mf2

## License

Kirby 3 Commentions is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Copyright © 2019 [Sebastian Greger](https://sebastiangreger.net)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
