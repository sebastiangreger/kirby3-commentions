<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Http\Response;
use Kirby\Toolkit\A;

class Migration
{
    public static function route()
    {

        // display error to non-admin users
        if (! kirby()->user() || ! kirby()->user()->isAdmin()) {
            return new Response('You have to log in as admin user to proceed.', 'text/html', 403);
        }
        
        // POST request is the filled in form
        if (kirby()->request()->is('POST') && get('backup') == 'yes' && get('disclaimer') == 'yes') {
            $log = '';
        
            // loop through all pages
            foreach (site()->index() as $page) {
                
                // extract the 'comments' field
                $data = $page->comments()->toArray();
                $comments = Data::decode($data['comments'], 'yaml');

                $oldcount = sizeof($comments);
                $currentcount = sizeof(Commentions::retrieve($page, 'all'));

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
                        //$page->update([ 'comments' => null ]);
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
            
            return new Response('<p>Migration concluded. Log file:</p><textarea style="width:100%; height:200px;">' . $log . '</textarea>', 'text/html');
        
        // GET request is the default view
        } else {
            $html = '
				<h1>Kirby3-Commentions migration assistant</h1>
				<p>Version 1.x of the Commentions plugin uses a new way of storing its data; a migration is therefore necessary. This tool attempts to carry out the migration in an automated manner. It may not work in all circumstances, so it is absolutely required to create a backup before using this. For more details about the changes from version 0.x to 1.x, see the <a href="">GitHub page</a>.</p>
			';
            $evidence = '';

            // check for presence of the old content/.commentions folder
            if (is_dir(kirby()->root('content') . DS . '.commentions')) {
                $evidence .= '<li>The folder ' . kirby()->root() . DS . 'content' . DS . '.commentions' . ' (used for storing the comments inbox and Webmention queue) is no longer used in version 1.x</li>';
            }

            // check for any content files that contain a comments field with a data pattern as used in Commentions
            $pageswithcomments = 0;
            foreach (site()->index()->pluck('comments') as $probe) :
                    $probe = Data::decode($probe->comments(), 'yaml');
            // type, timestamp, and approved are the smallest common denominator of v0.x comments
            if (sizeof($probe) > 0 && isset($probe[0]['type'], $probe[0]['timestamp'], $probe[0]['approved'])) {
                $pageswithcomments++;
            }
            endforeach;
            if ($pageswithcomments > 0) {
                $evidence .= '<li>' . $pageswithcomments . ' page(s) appear to contain comments stored in the old 0.x version format</li>';
            }

            // if any evidence has been found, output the list
            if ($evidence != '') {
                $html .= '
					<h2>Evidence of old data formats found:</h2>
					<p>The following signs indicate that your site contains Commentions data in the now outdated 0.x format:</p>
					<ul>' . $evidence . '</ul>
					<h2>A migration from version 0.x to 1.x is required:</h2>
				';
                if (kirby()->request()->is('POST')) {
                    $html .= '<p style="color:red;">Please confirm the two safety checks to proceed!</p>';
                }
                $html .= '
					<form action="' . kirby()->site()->url() . DS . 'commentions-migrationassistant" method="post">
						<label><input type="checkbox" name="backup" value="yes">Yes, I created a complete, verified backup of all my data</label><br>
						<label><input type="checkbox" name="disclaimer" value="yes">Yes, I understand that this tool comes with no warranty</label><br><br>
						<input type="submit" name="submit" value="Try to migrate my Commentions data now">
					</form>
				';
            } else {
                $html .= '
					<h2>No signs of old 0.x data found:</h2>
					<p>Based on the automated checks, it does not appear that your site has any commentions data in the old (0.x) format. Sorry, this tool won\'t be able to assist you.</p>
				';
            }

            $html .= '
				<h2>Things to check manually:</h2>
				<ul>
				<li>Check your templates/snippets for use of the now deprecated helper commentionsList(\'raw\'). Change this to the page method $page->commentions(), which returns a similar array.</li>
				<li>commentionsList(\'raw\') and $page->commentions() return slightly different fields: the \'message\' field for comments is now \'text\' (as it has always been for webmentions) and the boolean value \'approved\' has changed to a string field \'status\' (values: approved, unapproved, pending).</li>
				</ul>
			';

            return new Response($html, 'text/html');
        }
    }
}
