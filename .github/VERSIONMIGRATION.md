# Version migration instructions

Following the Indieweb's [selfdogfood principle](https://indieweb.org/selfdogfood), the original version of this plugin was a rather limited, experimental tool for personal use. With the further development into version 1.0, some of the basics had to be reviewed and adjusted to make it more universally useful.

This leads to some update requirement on your website, when upgrading.

_NB. These new specifications are rather final, but as long as version 1.x is not in a stable state (i.e. versions still carry "beta" designation), further changes could be required._

## From 0.x to 1.x

Various APIs and practices have changed with the introduction of version 1.0. In order to ensure full functionality, and avoid data loss, after upgrading, the following changes have to be carried out:

## In your templates

### Rendering prefabricated HTML snippets

The names of most of the "helpers" used for rendering the plugin's HTML output have changed as well:

| If your templates/snippets use...      | ...replace with                    |
|----------------------------------------|-------------------------------------|
| `<?php commentionsCss() ?>`            | `<?php commentions('css') ?>`       |
| `<?php commentionsList() ?>`           | `<?php commentions('list') ?>`      |
| `<?php commentionsList('grouped') ?>`  | `<?php commentions('grouped') ?>`   |
| `<?php commentionsForm() ?>`           | `<?php commentions('form') ?>`      |
| `<?php commentionsEndpoints() ?>`      | `<?php commentions('endpoints') ?>` |
| `<?php commentionsFeedback() ?>`       | `<?php commentions('feedback') ?>`  |

### Retrieving comments as an array

In previous versions, you could retrieve all comment data in an array for building your own display template.

Instead of `<?php $array = commentions('raw'); ?>`, a similar (but not identical) array can now be retrieved using `<?php $array = $page->commentions(); ?>` where `$page` is the Kirby page object of the rendered page.

Please note that `$page->commentions()` returns slightly different fields than the old `commentionsList('raw')`:
* the 'message' field for comments is now 'text' (as it has always been for webmentions)
* the boolean value 'approved' has changed to a string field 'status' (values: approved, unapproved, pending)

## In your content folders

**All of the migrations described below should be possible to be carried out using the automated migration tool, to be found at `https://<YOUR-SITE-ADDRESS>/commentions-migrationassistant` after you installed the new version.**

The following details should only be relevant if the automated migration fails or leads to complications.

### Data of received comments and webmentions

While the experimental version stored comments within the text file of each page's content, they are now stored in separate files in a dedicated `_commentions` folder within each page's folder.

#### Old version 0.x

In content file, e.g. `content/2_notes/20181031_exploring-the-universe/note.txt`:

```
Title: Exploring the universe

----

Text: 

Lorem ipsum

----

Date: 2018-10-31 13:15

----

Comments:

- 
  language: de
  name: Testy McTesting
  text: >
    A rather simple comment with just a line
    of text. User entered their name.
  timestamp: 2020-04-15 11:20
  type: comment
  status: approved
  uid: a50vylqwl5
```

#### New version 1.x

For example in file `content/2_notes/20181031_exploring-the-universe/_commentions/commentions.yml`:

```
- 
  language: de
  name: Testy McTesting
  text: >
    A rather simple comment with just a line
    of text. User entered their name.
  timestamp: 2020-04-15 11:20
  type: comment
  status: approved
  uid: a50vylqwl5
```

### Queue of incoming webmentions

The experimental version stored a centralized queue of webmentions waiting for the asynchronous processing in JSON files in the folder `content/.commentions/queue`. From Version 1.0, these are stored in a separate YAML file in the `_commentions` subfolder for each page.

#### Old version 0.x

In JSON file `content/.commentions/queue/webmention-1587221857-d0be2dc421be4fcd0172e5afceea3970e2f3d940.json` (timestamp + sha1 hash of source URL):

```
{
	"target": "https://yourblog.com/notes/exploring-the-universe",
	"source": "https://example.com/the-blog-post-linking-to-yours"
}
```

#### New version 1.x

In the YAML file inside the page's folder, e.g. `content/2_notes/20181031_exploring-the-universe/_commentions/webmentionsqueue.yml`:

```
- 
  source: https://example.com/the-blog-post-linking-to-yours
  target: >
    https://yourblog.com/notes/exploring-the-universe
  timestamp: 1587221857
  uid: ty4lp1f1k6
```

### Inbox of unapproved comments

The experimental version stored unapproved comments (and webmentions after successful processing from the queue) centrally in the folder `content/.commentions/inbox`.

#### Old version 0.x

In JSON file `content/.commentions/inbox/1234456735.json`:

```
{
	"name": "Testy McTesting",
	"email":null,
	"website":null,
	"message":"Test",
	"timestamp":1584109391,
	"target":"notes/exploring-the-universe",
	"language":null,
	"type":"comment"
}
```

#### New version 1.x

In the YAML file inside the page's folder, e.g. `content/2_notes/20181031_exploring-the-universe/_commentions/commentions.yml` (the same folder where all approved comments are stored, only difference being `status: pending` rather than `status: approved`):

```
- 
  language: de
  name: Testy McTesting
  text: >
    A rather simple comment with just a line
    of text. User entered their name.
  timestamp: 2020-04-15 11:20
  type: comment
  status: pending
  uid: a50vylqwl5
```
