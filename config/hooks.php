<?php

namespace sgkirby\Commentions;

use Exception;

return [

    'route:after' => function ($route, $path, $method, $page) {
        // create the feedback message
        if (get('thx')) {
            if (Commentions::defaultstatus('comment') != 'approved') {
                Commentions::$feedback = [ 'success' => 'Thank you! Please be patient, your comment has has to be approved by the editor.' ];
            } else {
                Commentions::$feedback = [ 'success' => 'Thank you for your comment!' ];
            }

            Commentions::$feedback['accepted'] = 'Thank you, your webmention has been queued for processing. Please be patient, your comment has has to be approved by the editor.';
        }

        // process form submission
        if (get('commentions') && get('submit')) {
            // fail if page does not exist
            if ($page === null) {
                throw new Exception('Target page does not exist.');
            }

            $return = Frontend::processCommentform($page, $path);
            if (isset($return['uid'])) {
                // return to the post page and display success message
                go($page->url() . '?thx=queued');
            } else {
                throw new Exception('Could not process comment.');
            }
        }
    }

];
