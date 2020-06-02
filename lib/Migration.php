<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Http\Response;
use Kirby\Toolkit\A;

class Migration
{
    /**
     * Called by the router, manages the entire migration flow
     *
     * @return mixed
     */
    public static function route()
    {
        // display error to non-admin users
        if (! kirby()->user() || ! kirby()->user()->isAdmin()) {
            return new Response('You first have to log in to the Kirby Panel as admin user to proceed.', 'text/html', 403);
        }

        // POST request is the filled in form
        if (kirby()->request()->is('POST') && get('backup') == 'yes' && get('disclaimer') == 'yes') {
            $log = '';

            // loop through all pages
            // TODO: index(true) would include drafts, but does this lead to issues further down?
            foreach (site()->index() as $page) {

                // extract the 'comments' field
                if (sizeof(kirby()->languages()) > 1) {
                    $comments = [];
                    foreach (kirby()->languages() as $language) {
                        if ($page->translation($language->code())->exists()) {
                            $data = $page->content($language->code())->comments()->toArray();
                            $tmp = Data::decode($data['comments'], 'yaml');
                            foreach ($tmp as $k => $v) {
                                $tmp[$k]['language'] = $language->code();
                            }
                            $comments = A::merge($comments, $tmp);
                        }
                    }
                } else {
                    $data = $page->comments()->toArray();
                    $comments = Data::decode($data['comments'], 'yaml');
                }

                $oldcount = sizeof($comments);
                $currentcount = Commentions::get($page, 'all')->count();

                if ($oldcount > 0 && $currentcount == 0) {

                    // make sure the comments are in chronological order
                    $comments = A::sort($comments, 'timestamp', 'asc');

                    // modify the data format where spec has changed
                    $newcount = 0;
                    foreach ($comments as $comment) {

                        // rename all 'message' fields to 'text'
                        if (isset($comment['message']) && (!isset($comment['text']) || $comment['text'] == '')) {
                            $comment['text'] = $comment['message'];
                            unset($comment['message']);
                        }

                        // replace bool 'approved' with string 'status'
                        if ($comment['approved'] == 'true') {
                            $comment['status'] = 'approved';
                        } else {
                            $comment['status'] = 'unapproved';
                        }
                        unset($comment['approved']);

                        // add it using the new API
                        Commentions::add($page, $comment);
                        $newcount++;
                    }

                    if ($newcount == $oldcount) {
                        // delete the 'comments' field from the page
                        $page->update([ 'comments' => null ]);
                        $log .= 'OK';
                    } else {
                        $log .= 'ERROR';
                    }
                    $log .= ' ' . $page->id() . ' (' . $newcount . '/' . $oldcount . " migrated)\n";
                } elseif ($currentcount > 0) {
                    $log .= 'ERROR ' . $page->id() . " (already has 1.x format comments)\n";
                } else {
                    $log .= 'OK ' . $page->id() . " (0)\n";
                }
            }

            // delete queue folder
            $folders = [
                kirby()->root('content') . '/.commentions/queue',
                kirby()->root('content') . '/.commentions/inbox',
                kirby()->root('content') . '/.commentions',
            ];
            foreach ($folders as $folder) {
                if (is_dir($folder) && is_readable($folder)) {
                    if (count(scandir($folder)) == 2) {
                        if (rmdir($folder)) {
                            $log .= 'OK deleted folder ' . $folder . "\n";
                        } else {
                            $log .= 'ERROR failed to delete folder ' . $folder . " (unspecified reason)\n";
                        }
                    } else {
                        $log .= 'ERROR failed to delete folder ' . $folder . " (not empty)\n";
                    }
                } else {
                    $log .= 'ERROR failed to delete folder ' . $folder . " (not a directory or not readable)\n";
                }
            }

            $html = '<h2>Migration concluded</h2><p>Log file:</p><textarea style="width:100%; height:200px;">' . $log . '</textarea>';
            return new Response(static::html($html), 'text/html');

        // GET request is the default view
        } else {
            $html = '
                <p>Version 1.x of the Commentions plugin uses a new way of storing its data (<a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/.github/VERSIONMIGRATION.md">detailed documentation on Github</a>).</p>
            ';
            $evidence = '';

            // check for presence of the old content/.commentions folder
            if (is_dir(kirby()->root('content') . DS . '.commentions')) {
                $evidence .= '<li>Found <strong>now obsolete folder</strong> <code>' . kirby()->root() . DS . 'content' . DS . '.commentions' . '</code></li>';
            }

            // check for presence of queue files
            $files = kirby()->root('content') . '/.commentions/queue/webmention-*.json';
            $queuefiles = 0;
            foreach (glob($files) as $queuefile) {
                $queuefiles++;
            }
            if ($queuefiles > 0) {
                $evidence .= '<li>' . $queuefiles . ' <strong>incoming webmentions in the queue folder</strong> have not been parsed yet</li>';
            }

            // check for presence of inbox files
            $files = kirby()->root('content') . '/.commentions/inbox/*.json';
            $inboxfiles = 0;
            foreach (glob($files) as $queuefile) {
                $inboxfiles++;
            }
            if ($inboxfiles > 0) {
                $evidence .= '<li>' . $inboxfiles . ' new comments/webmentions are waiting <strong>in your inbox</strong></li>';
            }

            // check for any content files that contain a comments field with a data pattern as used in Commentions
            $pageswithcomments = 0;
            foreach (site()->index()->pluck('comments') as $probe) {
                $probe = Data::decode($probe->comments(), 'yaml');
                // type, timestamp, and approved are the smallest common denominator of v0.x comments
                if (sizeof($probe) > 0 && isset($probe[0]['type'], $probe[0]['timestamp'], $probe[0]['approved'])) {
                    $pageswithcomments++;
                }
            }
            if ($pageswithcomments > 0) {
                $evidence .= '<li>' . $pageswithcomments . ' page(s) appear to <strong>contain comments stored in the old format</strong></li>';
            }

            // if any evidence has been found, output the list
            if ($evidence != '') {
                $html .= '
                    <h2>Evidence of old data formats found:</h2>
                    <p>The following signs indicate that your site contains Commentions data in the now outdated 0.x format:</p>
                    <ul>' . $evidence . '</ul>
                    <h2>A migration from version 0.x to 1.x is required:</h2>
                ';

                // give error message if unprocessed incoming stuff is present
                if ($inboxfiles > 0 || $queuefiles > 0) {
                    $html .= '<p style="color:red;">You cannot proceed with the automated migration while unprocessed webmentions are in the queue; please purge the queue first, by running the cronjob (or deleting the JSON files in folder <code>' . kirby()->root('content') . '/.commentions/queue/</code> if you prefer)!</p>';
                }
                if ($inboxfiles > 0 || $queuefiles > 0) {
                    $html .= '<p style="color:red;">You cannot proceed with the automated migration while unapproved/undeleted comments are in your inbox; please empty the queue first, by using the tools in your panel (or deleting the JSON files in folder <code>' . kirby()->root('content') . '/.commentions/inbox/</code> if you prefer)!</p>';
                }

                if ($inboxfiles == 0 && $queuefiles == 0) {
                    $html .= '
                        <p>This tool attempts to carry out the migration in an automated manner. It may not work in all circumstances, so it is <strong>absolutely required to create a backup before using this</strong>.</p>
                        <form action="' . kirby()->urls()->base() . '/commentions-migrationassistant" method="post">
                    ';
                    if (kirby()->request()->is('POST')) {
                        $html .= '<p style="color:red;">Please confirm the two safety checks to proceed!</p>';
                    }
                    $html .= '
                            <label><input type="checkbox" name="backup" value="yes">Yes, I created a complete, verified backup of all my data</label><br>
                            <label><input type="checkbox" name="disclaimer" value="yes">Yes, I understand that this tool comes with no warranty</label><br><br>
                            <input type="submit" name="submit" value="I accept the risks, try to migrate my Commentions data now">
                        </form>
                    ';
                }
            } else {
                $html .= '
                    <h2>No signs of old 0.x data found:</h2>
                    <p>Based on the automated checks, it does not appear that your site has any commentions data in the old (0.x) format. Sorry, this tool won\'t be able to assist you.</p>
                ';
            }

            return new Response(static::html($html), 'text/html');
        }
    }

    /**
     * Outputs the HTML "template" for the migration assistant
     *
     * @return string
     */
    public static function html($html)
    {
        return '
            <html><head>
                <title>Kirby3-Commentions migration assistant</title>
                <link nonce="' . kirby()->nonce() . '" rel="stylesheet" href="' . kirby()->url('media') . '/panel/' . kirby()->versionHash() . '/css/app.css">
                <style>
                    a { text-decoration:underline; }
                    .k-fields-section input[type="submit"] { display:block; }
                    .k-fields-section input[type="checkbox"] { margin-right:.5em; }
                    form { border:1px solid #ccc; padding:1em; }
                    h2, p,li { margin-bottom:1rem; }
                    h2 { margin-top:2rem; }
                    li { list-style:square; padding-left:.5em; margin-left:1em; }
                </style>
            </head><body><div class="k-panel"><main class="k-panel-view"><div class="k-view k-page-view"><header class="k-header" data-editable="true">
                <h1 data-size="huge" class="k-headline">
                    <span>Kirby3-Commentions migration assistant</span>
                </h1>
            </header><div data-gutter="large" class="k-grid k-sections"><div data-width="1/1" class="k-column"><section class="k-fields-section k-section k-section-name-main-fields">
        ' . $html . '
            <h2>Things to update manually:</h2>
            <!--
            <ul>
            <li>While the old plugin was "on for all templates" by default, the new version is "off for all templates". Make sure to add the sgkirby.commentions.templatesWithComments and sgkirby.commentions.templatesWithWebmentions arrays to your config.php</li>
            <li>Check your templates/snippets for use of the now deprecated helper commentionsList(\'raw\'). Change this to the page method $page->commentions(), which returns a similar array.</li>
            <li>commentionsList(\'raw\') and $page->commentions() return slightly different fields: the \'message\' field for comments is now \'text\' (as it has always been for webmentions) and the boolean value \'approved\' has changed to a string field \'status\' (values: approved, unapproved, pending).</li>
            </ul>
            //-->
            <p>Please follow the <a href="https://github.com/sebastiangreger/kirby3-commentions/blob/master/.github/VERSIONMIGRATION.md">version migration instructions on Github</a></p>
            </section></div></div></div></main></div></body></html>
        ';
    }
}
