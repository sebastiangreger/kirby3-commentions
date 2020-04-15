# Kirby 3 Commentions

A minimalist comment system and Webmention endpoint.

* Comments can be submitted through a form on the page or as a [Webmention](https://indieweb.org/webmention).
* Incoming webmentions are stored in a queue and processed asynchronously.
* Comments can be approved/deleted in the Kirby Panel

Versions 1.x (April 2020 and later) are no longer compatible with the exploratory 0.x versions. After upgrading, you will have to follow the [version migration instructions](/.github/VERSIONMIGRATION.md); to go back to an old version, the last release of the old branch was [0.3.0](https://github.com/sebastiangreger/kirby3-commentions/releases/tag/v0.3.0).

_NB. The plugin only covers incoming webmentions (i.e. receiving notifications from other websites who link to a page). Sending outgoing webmentions to other websites requires a separate solution, such as [Kirby 3 Sendmentions](https://github.com/sebastiangreger/kirby3-sendmentions)._

## Table of contents

* [Installation](#installation)
* [Setup](#setup)
* [Frontend helper](#frontend-helper)
* [Panel sections](#panel-sections)
* [Page methods](#page-methods)
* [Pages methods](#pages-methods)
* [Data structure](#data-structure)
* [Config options](#config-options)
* [Requirements, credits, license](#requirements)

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby3-commentions`.

## Setup

### Step 1: Adding the Commentions UIs to the Panel blueprints

All comments are stored in small text files attached to each page in the Kirby CMS. In order to display and manage them, it is required to add them to your Panel blueprints.

#### Adding a "comments inbox" to a blueprint

To approve/delete incoming comments, add the following to a suitable blueprint; e.g. to `site/blueprints/site.yml` if you want the Commentions inbox to be displayed in the main Panel view of your site:

```yaml
sections:
  commentions:
    type: commentions
    show: pending
```

Alternatively, `pending` can be replaced with `all`, in which case all comments are displayed. This is useful in setups where new comments are set to be approved automatically.

_NB. If you leave out the `show` attribute, the comments of the page itself are displayed instead. If you are embedding the inbox on a page that also receives comments/webmentions itself, you would have to set up two sections of `type: commentions` (see immediately below for the other one)._

#### Page-specific list of comments

To display and manage the comments for each page, add the following to the according blueprint in `site/blueprints/pages` (i.e. to all page blueprints where you want to use comments):

```yaml
sections:
  commentions:
    type: commentions
```

By default, newest comments are shown on top; this is to ensure you immediately notice new, unapproved comments. If you prefer an ascending sorting by date, add the `flip` variable as follows:

```yaml
sections:
  commentions:
    type: commentions
    flip: true
```

### Step 2: Adding frontend UIs to your templates

The plugin comes with a set of default snippets. These are optimized to work with the Starterkit but might be of use in other themes as well; they can also serve as boilerplates for designing your own (you can find them in the `site/plugins/kirby3-commentions/snippets` folder).

To show comments on pages and display a form to leave new comments, there are three options:

#### Option A. Add everything at once

In order to add everything at once, add helper `<?php commentions(); ?>` to the according templates in `site/templates` - a shorthand for the three helpers described in alternative B:

This is your one-stop-shop, and it should sit rather nicely at the bottom of your content. But it might be too limited for your needs, hence there is Option B...

If you would like to use basic CSS styles for these prefabricated HTML snippets (a minimalistic design suitable for the Kirby 3 Starterkit), add `<?php commentionsCss(); ?>` to your HTML &lt;head&gt; area (e.g. in `snippets/header.php` in the Starterkit); you can place it rather flexibly, either with other CSS links or at the very end just before the &lt;</head>&gt; tag:

#### Option B. Add three template parts where you see them fit best

Alternatively, you can add the form feedback snippet (error or success message), the comment form and the list of comments separately, by adding the following helpers to the according templates in `site/templates` - this for example allows to integrate the feedback element at the top of a template, and changing the default order of form vs. comment list.

* The helper `<?php commentionsFeedback(); ?>` renders the user feedback UI (error/success message after a user submits the comment form; this might be beneficial to include "above the fold" on your page).
* To render the comment form, include `<?php commentionsForm(); ?>` in your template.
* Finally, `<?php commentionsList(); ?>` renders a list of comments. By default, this presents all comments and mentions in one list; to present certain reactions (e.g. bookmarks, likes, RSVPs) separately, use `commentionsList('grouped')` instead (check options further below for additional control).

As with option A, you may want to include the `<?php commentionsCss(); ?>` to your HTML &lt;head&gt; template.

#### Option C. Create your own frontend presentation

Since above snippets are mainly provided to enable a quick start, you may of course run your own frontend code entirely. If you'd like to build on the templates, you can find them in the `site/plugins/kirby3-commentions/snippets` folder.

While it may be advisable to use the `commentionsFeedback()` and `commentionsForm()` helpers, as their markup changes based on the plugin settings (and possibly in future versions, if new features are added), you may want to have more control over presenting your list of comments and webmentions.

The page method `$page->commentions()` on a page object returns an array with all approved comments for that page. This is the preferred API if you want to control how your comments and webmentions are displayed (the `commentionsList('raw')` from Commentions 0.x is deprecated and no longer recommended). Use that array to build your presentation logic to taste. To include unapproved comments in that array, use `$page->commentions('all')` (handle with care!).

_NB. The raw data array returned by this page method may contain e-mail addresses etc., so make sure to carefully limit what data is being displayed publicly._

### Step 3: Setting up Webmentions (optional)

#### Announcing your webmentions endpoint in your HTML &lt;head&gt;

In order to receive webmentions (this is optional, you may also use the plugin for traditional comments only), you have to announce your webmention endpoint in the HTML &lt;head&gt;. The easiest way is by placing `<?php commentionsEndpoints(); ?>` within the applicable template (often in `snippets/header.php`); if in doubt, place it at the very end just before the &lt;</head>&gt; tag.

#### Setting up a cronjob to process the inbox queue (only applicable if using Webmentions)

Per the specification ([ch 3.2](https://www.w3.org/TR/webmention/#receiving-webmentions)), incoming webmentions are always placed in a backlog queue for asynchronous processing (this is to mitigate the risk of DDoS attacks by flooding your site with webmentions). In order to have these webmentions processed, this queue needs to be run regularly.

First, set a secret key with at least 10 characters in your `site/config/config.php` (the key may NOT include any of the following: `&` `%` `#` `+` nor a space sign ` `):

```php
return [
	'sgkirby.commentions.secret' => '<YOUR-SECRET>'
];
```

_N.B. Any attempt to process the queue before you set this secret in your `config.php` will lead to an error._

Second, set up a cronjob to call the URL `https://<SITE-URL>/commentions-processqueue?token=<YOUR-SECRET>` at regular intervals (when testing this URL in a browser first, it responds with either "Success" or a descriptive error message).

Every time this URL is called, the queue of incoming webmentions is processed; valid webmentions are moved to the comment inbox, while invalid ones are silently deleted.

## Frontend helper

The frontend helper is a PHP function that can be called from within templates or snippets and renders HTML. The function can be used with various arguments (and without).

### commentions()

`<?php commentions(); ?>` with no arguments is a shorthand for displaying three helpers (described below) in the following order:

```php
<?php
	commentions('feedback');
	commentions('form');
	commentions('list');
?>
```

### commentions('feedback')

Renders the user feedback UI (error/success message after a user submits the comment form; beneficial to include "above the fold" on your page).

`<?php commentions('feedback'); ?>`

![feedback](https://user-images.githubusercontent.com/6355217/79339976-fc35ea80-7f29-11ea-8a62-f8b3d6d7382e.png)

### commentions('form')

Renders the comment form, based on the config settings, for direct use in the template.

`<?php commentions('form'); ?>`

![form](https://user-images.githubusercontent.com/6355217/79339978-fcce8100-7f29-11ea-9102-42f1070977d3.png)

### commentions('list')

Renders a list of comments for display in the frontend.

`<?php commentions('list'); ?>`

![list](https://user-images.githubusercontent.com/6355217/79339982-fd671780-7f29-11ea-967d-c0e507d570e4.png)

### commentions('grouped')

`'list'` presents all comments and mentions in one list; to present certain reactions (e.g. bookmarks, likes, RSVPs) separately, use `'grouped'` instead:

`<?php commentions('grouped'); ?>`

![grouped](https://user-images.githubusercontent.com/6355217/79339979-fcce8100-7f29-11ea-8464-e25d0cb98764.png)

The behaviour of the grouping can be adjusted via config settings (see further below).

### commentions('endpoints')

By default, the helper is nothing more than a shortcut to render the following HTML, which allows other websites to discover that your site accepts webmentions:

```HTML
<link rel="webmention" href="https://<SITE-URL>/webmention-endpoint" />
<link rel="http://webmention.org/" href="https://<SITE-URL>/webmention-endpoint" />
```

`<?php commentions('endpoints'); ?>`

### commentions('css')

Renders elementary CSS styles for the HTML snippets rendered by the frontend helper; add this to your HTML &lt;head&gt; area (e.g. in `snippets/header.php` in the Starterkit); you can place it rather flexibly, either with other CSS links or at the very end just before the &lt;</head>&gt; tag.

`<?php commentions('css'); ?>`

Unless your site is running on the Starterkit, you likely want to write your own CSS for the pre-rendered markup. To build on the prefabricated styles, they can be found in `site/plugins/kirby3-commentions/assets/styles.css`.

## Panel sections

### commentions

This universal panel section displays either
* all comments for the page it is on (no `show` property, or `show: page`), or
* an "inbox" of all or all pending comments for all pages (`show: all`, `show: pending`)

| Property | Type    | Default | Description                                                                                                                                                                                           |
|----------|---------|---------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| empty    | string  | –       | Sets the text for the empty state box                                                                                                                                                                 |
| flip     | boolean | false   | Default presentation order (`false`) is latest first; `true` shows comments chronologically                                                                                                           |
| headline | string  | –       | The headline for the section.                                                                                                                                                                         |
| show     | string  | 'page'  | Defines what comments are shown; 'page' lists comments for current page, 'pending' lists all pending comments for the entire site (aka. the "Inbox"), and 'all' lists all comments of the entire site |

#### Default

By default, the `commentions` section displays all comments for the page the blueprint applies to.

```yaml
sections:
  commentions:
    type: commentions
```

![page](https://user-images.githubusercontent.com/6355217/79339984-fdffae00-7f29-11ea-9bf1-8b938ac89019.png)

#### Pending

For creating an "inbox" of comments, the property `show: pending` renders a list of all pending comments and webmentions instead.

```yaml
sections:
  commentions:
    type: commentions
    show: pending
```

![pending](https://user-images.githubusercontent.com/6355217/79339985-fdffae00-7f29-11ea-9f1a-4457f5e02fc3.png)

## Page methods

### $page->commentions()

Returns an array with comments for the page object.

`$page->commentions( $status, $sort )`

#### Parameters

| Name     | Type   | Default    | Description                                                                                     |
|----------|--------|------------|-------------------------------------------------------------------------------------------------|
| $status  | string | 'approved' | Selects the comments to be included in the array; possible values: 'approved', 'pending', 'all' |
| $sort    | string | 'asc'      | Comments are ordered by timestamp; 'desc' lists latest first, 'asc' lists chronologically       |

#### Return

Array with all comments for the requested object (see "Data structure" chapter below for details).

The following example shows the most minimal comment and webmentions possible (displayed fields are compulsory):

```
Array (
    [0] => Array
        (
            [timestamp] => 2020-04-01 11:30
            [text] => Most minimal comment possible
            [type] => comment
            [status] => approved
            [uid] => 1m6los473p
            [pageid] => notes/exploring-the-universe
        )
    [1] => Array
        (
            [timestamp] => 2020-04-01 11:32
            [type] => like
            [status] => approved
            [source] => https://example.com
            [uid] => 126los473p
            [pageid] => notes/exploring-the-universe
        )
)
```

### $page->addCommention()

Adds a comment entry to the page.

`$page->addCommentions( $data )`

#### Parameters

| Name  | Type  | Description                                                                                    |
|-------|-------|------------------------------------------------------------------------------------------------|
| $data | array | All the data for the comment/webmention, according to the specifications of the Data structure |

#### Return

Array with the data as saved, including the assigned UID, or boolean `false` if failed.

### $page->updateCommention()

Updates a comment entry on the page.

`$page->updateCommentions( $uid, $data )`

#### Parameters

| Name  | Type   | Description                                                                                    |
|-------|--------|------------------------------------------------------------------------------------------------|
| $uid  | string | The unique ID of the comment; 10 alphanumeric characters (lower-case letters and numbers).     |
| $data | array  | All the data for the comment/webmention, according to the specifications of the Data structure |

#### Return

Array with the data as saved, or boolean `false` if failed.

### $page->deleteCommention()

Deletes a comment entry from the page.

`$page->deleteCommentions( $uid )`

#### Parameters

| Name | Type   | Description                                                                                |
|------|--------|--------------------------------------------------------------------------------------------|
| $uid | string | The unique ID of the comment; 10 alphanumeric characters (lower-case letters and numbers). |

#### Return

Boolean `true` on success, `false` if failed.

## Pages methods

### $pages->commentions()

Returns an array with comments for the page collection.

`$pages->commentions( string $status = 'approved', string $sort = 'asc' )`

#### Parameters

| Name     | Type   | Default    | Description                                                                                     |
|----------|--------|------------|-------------------------------------------------------------------------------------------------|
| $status  | string | 'approved' | Selects the comments to be included in the array; possible values: 'approved', 'pending', 'all' |
| $sort    | string | 'asc'      | Comments are ordered by timestamp; 'desc' lists latest first, 'asc' lists chronologically       |

#### Return

Same as for page method `$page->commentions()`.

## Data structure

The commentions are stored in a `_commentions.txt` file in the according page's folder. For virtual pages, the storage location is `_commentions-<PAGE-SLUG>.txt` in the parent page's folder.

### Comments

These are the fields that can be used, including information on which are compulsory for what type of comment:

| Field     | Comment  | Webment. | Description                                                                                                                                        | Example                               |
|-----------|----------|----------|----------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------------------|
| timestamp | required | required | Time of the comment; for webmentions, either the date of the source page (where available) or the time the webmention was submitted is used.       | 2020-04-01 12:00                      |
| type      | required | required | The type of comment. Possible values: 'comment' (regular comment), 'webmention' (unspecified webmention), 'like', 'bookmark', etc.                 | comment                               |
| status    | required | required | Status of the comment; possible values: 'approved', 'pending', 'all'                                                                               | approved                              |
| uid       | required | required | Randomly generated unique ID, used internally for commands to update/delete comments. 10 alphanumeric characters (lower-case letters and numbers). | 1m6los473p                            |
| text      | required | optional | The body of the comment; in case of webmentions, this is the content of the source page.                                                           | Lorem ipsum dolor sit amet.           |
| source    |          | required | The URL where this page was mentioned, as submitted by the webmention request.                                                                     | https://example.com/a-webmention-post |
| email     | optional |          | The author's e-mail address (if entered in the comment form).                                                                                      | example@example.com                   |
| avatar    |          | optional | The URL of the author's avatar image, as submitted in the webmention source metadata.                                                              | https://example.com/portrait.jpg      |
| website   | optional | optional | The author's website URL (entered in the comment form or from webmention metadata).                                                                | https://example.com                   |
| language  | optional | optional | Only on multi-language sites: the two-letter language code of the page version this comment/webmention was submitted to.                           | en                                    |
| pageid    | default  | default  | The page ID of the Kirby page is added to the output of the page(s) method by default. It is not stored in the text file.                          | notes/exploring-the-universe          |

## Config options

The plugin can be configured with optional settings in your `site/config/config.php`.

### Webmention endpoint

To change the URL of the webmention endpoint (default is `https://<SITE-URL>/webmention-endpoint`), add the following setting and change the URI string as desired:

```php
'sgkirby.commentions.endpoint' => 'webmention-endpoint',
```

### Cronjob secret

A cronjob is required for the asynchronous processing of incoming webmentions. The HTTP request for that job requires a `token` attribute, which is set in the config file.

```php
'sgkirby.commentions.secret' => '<YOUR-SECRET>',
```

A valid secret key must be at least 10 characters long and may NOT include any of the following: `&` `%` `#` `+` nor a space sign ` `.

_NB. Without this setting, the cronjob will always fail._

### Comment form fields

By default, only an optional name field and a textarea for the comment are shown in the form rendered with the `commentions('form')` helper. To modify, add this array to `site/config/config.php` and remove the undesired field names (the form is rendered to only include the fields present in this array):

```php
'sgkirby.commentions.formfields' => [
    'name',
    'email',
    'url'
],
```

### Collapsible forms (show/hide)

If desired, the following setting triggers additional markup in the included form markup (when using the `commentions('form')` helper) that can be used to hide the forms by default, allowing for an accessible open/close functionality:

```php
'sgkirby.commentions.hideforms' => true,
```

_NB. This setting only triggers the inclusion of the required HTML markup. In order to create a working open/close toggle, additional JavaScript code is required._

### Privacy settings

The plugin is designed with data minimalism in mind; storing more than the absolutely necessary data is possible, but please consider the ethical and possibly legal implications of processing such data.

Since the default presentation does not make use of avatar images, these are not stored. To write avatar URLs from incoming webmention metadata to the comment file, add this setting:

```php
'sgkirby.commentions.avatarurls' => true,
```

_NB. This setting only ensures that valid avatar URLs from incoming webmentions are stored. Downloading, storing, and displaying theme has to be implemented separately, using the `$page->commentions()` page method described above._

### Spam protection

The plugin provides several means to block comment spam; all active by default, these can be deactivated by adding the following setting and then remove any undesired methods:

```php
'sgkirby.commentions.spamprotection' => [
    'honeypot',    /* filter comments where a hidden field contains data */
    'timemin',     /* filter comments submitted too fast */
    'timemax',     /* filter comments submitted after very long time */
],
```

_NB. The timemin/timemax spam protections are disabled if Kirby's built-in page cache is in use._

When timeout protections are active, comments are rejected if submitted too soon or too long after the form has been created; the defaults can be adjusted by adding either or both of the following settings:

```php
'sgkirby.commentions.spamtimemin' => 5    ,    /* seconds after which a submission is valid; default 5s */
'sgkirby.commentions.spamtimemax' => 86400,    /* seconds after which a submission is no longer valid; default 24h */
```

_NB. These time settings do not have an effect if Kirby's built-in page cache is used._

### Grouping reactions

When comments are displayed using the `commentions('grouped')` helper, adding the following settings array gives control over what reaction types are displayed as separate groups, in what order, and what title is used - remove any comment types to include them in the main comment list instead of displaying them as a separate group:

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

_NB. Sometimes webmentions of these types may contain a text body regardless. By grouping them like this, their content is not shown._

## Requirements

[Kirby 3.3.0+](https://getkirby.com)

## Credits

Inspiration and code snippets from:

- https://github.com/bastianallgeier/kirby-webmentions
- https://github.com/sebsel/seblog-kirby-webmentions
- https://github.com/fabianmichael/kirby-pluginstorage
- https://github.com/bnomei/kirby3-autoid

Included vendor libraries:

- https://github.com/indieweb/php-comments
- https://github.com/microformats/php-mf2

## License

Kirby 3 Commentions is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Copyright © 2020 [Sebastian Greger](https://sebastiangreger.net)

It is discouraged to use this plugin in any project that promotes the destruction of our planet, racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
