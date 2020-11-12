<?php

namespace sgkirby\Commentions;

use Exception;

return [

    'route:after' => function ($route, $path, $method, $result) {
        // create the feedback message
        if (get('thx')) {
            if (Commentions::defaultstatus('comment') != 'approved') {
                Commentions::$feedback = ['success' => t('commentions.feedback.comment.queued')];
            } else {
                Commentions::$feedback = ['success' => t('commentions.feedback.comment.thankyou')];
            }

            Commentions::$feedback['accepted'] = t('commentions.feedback.webmention.queued');
        }

        // process form submission
        if (get('commentions') && get('submit')) {
            // fail if page does not exist
            if ($result === null) {
                throw new Exception('Target page does not exist.');
            }

            $return = Frontend::processCommentform($result, $path);
            if (isset($return['uid'])) {
                // return to the post page and display success message
                go($result->url() . '?thx=queued#commentions-feedback');
            } else {
                throw new Exception('Could not process comment.');
            }
        }
    }

];
