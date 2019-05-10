# Kirby 3 Commentions

⚠️ This plugin is currently a conceptual exploration at a very early Beta stage; feel free to play around with it for experimentation, but think twice before using in production!

The plugin provides a minimalistic comment system. Comments can be sent through a form on the page or as a [Webmention](https://indieweb.org/webmention). Incoming webmentions are stored in a queue and processed asynchronously. All incoming comments and webmentions are stored in an inbox, pending admin approval. Approved comments are stored in the page's content file as a YAML structure and can be edited from within the Panel.

For details about the approach and philosophy of this plugin, visit https://sebastiangreger.net/2019/05/sendmention-commention-webmentions-for-kirby-3

*NB. The plugin only covers incoming webmentions (notifications from other websites who link to a page). Sending webmentions to URLs linked in content requires a separate solution, such as [Kirby 3 Sendmentions](https://github.com/sebastiangreger/kirby3-sendmentions).*

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby3-commentions`.

## Setup

NB. This plugin does not work without the following steps.

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

To show comments on pages and display a form to leave new comments, add the following helpers to the according templates in `site/templates`:

```
comments( $page, $kirby, $pages );
```

In order to receive webmentions, you have to announce your webmention endpoint in the HTML head. The easiest way is by adding the following helper in your `header.php` or similar (depending on your template setup):

```
webmentionEndpoint();
```

Incoming webmentions are placed in a queue for asynchronous processing. In order to receive these webmentions into your comments inbox, this queue needs to be processed regularly:

First, set a secret key with at least 10 characters in your `site/config/config.php`:

```php
'sgkirby.commentions.secret' => '<YOUR-SECRET>',
```

Second, set up a cronjob to call the following URL at regular intervals:

`https://domain.tld/commentions-processqueue-<YOUR-SECRET>`

Every time this URL is called, the backlog of incoming webmentions is processed; valid webmentions are moved to the comment inbox, while invalid ones are silently deleted.

## Options

The plugin can be configured with optional settings in your `site/config/config.php`.

To change the URL of the webmention endpoint (default is `https://domain.tld/webmention-endpoint`), add the following setting and change the string as desired:

```php
'sgkirby.commentions.endpoint' => 'webmention-endpoint',
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

### Roadmap/Ideas

- [ ] Extend HTML markup of form elements, to allow for easy theming
- [ ] Create separate helper for UI feedback, to integrate further up in the reloaded page
- [ ] Enhance presentation of comments in the frontend
- [ ] Implement spam protection
- [ ] Improve design of comment inbox by customizing Panel component
- [ ] Use [Kirby Queue]() plugin for queue processing instead of own queue and cronjob
- [ ] Add additional field options to comments (e.g. e-mail, website) and make configurable
- [ ] Display different types of webmentions accordingly (reply, bookmark, RSVP...)
- [ ] Investigate alternative means of storing comments (e.g. a Sqlite database)

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
