<?php

namespace sgkirby\Commentions;

use Exception;
use Kirby\Data\Data;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;

class Commentions
{
    public static $feedback = null;


    /**
     * Checks the settings to assign the correct status to new comment submissions
     *
     * @param string $type
     * @return string
     */
    public static function defaultstatus($type)
    {

        // array of valid status strings, for validation
        $valid = [ 'approved', 'unapproved', 'pending' ];

        // fetch the setting string/array from options
        $setting = option('sgkirby.commentions.defaultstatus');

        // array: retrieve the applicable setting for this type
        if (is_array($setting) && isset($setting[ $type ]) && in_array($setting[ $type ], $valid)) {
            return $setting[ $type ];
        }

        // string: use the preset value
        elseif (is_string($setting) && in_array($setting, $valid)) {
            return $setting;
        }

        // fallback is always 'pending'
        else {
            return 'pending';
        }
    }


    /**
     * Generates a random 10-character string to be used as comment UID
     *
     * @return string
     */
    public static function uid()
    {

        // generate uid
        $uidchars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $uid = '';
        for ($i = 0; $i < 10; $i++) {
            $uid .= $uidchars[ random_int(0, strlen($uidchars) - 1)];
        }

        return $uid;
    }


    /**
     * Adds a new comment to the page, incl. some cleanup and adding a UID
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data
     */
    public static function add($page, $data)
    {

        // a regular comment has to at least feature a text
        if ((empty($data['type']) || $data['type'] == 'comment') && empty($data['text'])) {
            return false;
        }

        // a webmention has to at least feature a source that is a valid URL
        if ((! empty($data['type']) && $data['type'] != 'comment') && (empty($data['source']) || ! Str::isURL($data['source']))) {
            return false;
        }

        // clean up the data; incl. removal of any user-provided uid
        $data = static::sanitize($data, false);

        // add a uid field
        $data['uid'] = static::uid();

        // trigger a hook that would allow to stop processing by throwing an exception
        kirby()->trigger('commentions.add:before', $page, $data);

        // save commention to the according txt file
        $saved = Storage::add($page, $data, 'commentions');

        // trigger a hook that allows further processing of the data
        kirby()->trigger('commentions.add:after', $page, $saved);

        return $saved;
    }


    /**
     * Updates a comment on the page, incl. some cleanup
     *
     * @param \Kirby\Cms\Page $page
     * @param string $uid
     * @param array $data
     */
    public static function update($page, $uid, $data)
    {

        // UID cannot be updated externally
        if (!empty($data['uid'])) {
            unset($data['uid']);
        }

        // trigger a hook that would allow to stop processing by throwing an exception
        kirby()->trigger('commentions.add:before', $page, $data);

        // update commention in the according txt file
        $saved = Storage::update($page, $uid, $data, 'commentions');

        // trigger a hook that allows further processing of the data
        kirby()->trigger('commentions.update:after', $page, $saved);
        
        return $saved;
    }


    /**
     * Verifies and cleans up commentions data for saving
     *
     * @param array $data
     * @param book $keepuid
     * @return array
     */
    public static function sanitize($data, $update = false)
    {

        // validations on missing required fields only apply when creating new entries
        if (! $update) {

            // users may not send 'uid' as part of the data payload
            if (! empty($data['uid'])) {
                unset($data['uid']);
            }

            // timestamp is required; use current time if missing
            if (empty($data['timestamp'])) {
                $data['timestamp'] = date('Y-m-d H:i');
            }

            // status is required; set to 'pending' by default if missing
            if (empty($data['status'])) {
                $data['status'] = 'pending';
            }

            // type is required; set to 'comment' default if missing
            if (empty($data['type'])) {
                $data['type'] = 'comment';
            }

            // validations based on type
            if ($data['type'] == 'comment') {

                // text is required for comments
                if (empty($data['text'])) {
                    $data['text'] = '';
                }

                // 'source' only used for webmentions
                if (!empty($data['source'])) {
                    unset($data['source']);
                }
            }
        }

        // timestamp is required; use current time if missing or not a unix epoch
        if (! empty($data['timestamp']) && ! is_numeric($data['timestamp'])) {
            $data['timestamp'] = date(date('Y-m-d H:i'), time());
        }

        // status is required; set to 'pending' by default if missing or invalid value
        if (! empty($data['status']) && !in_array($data['status'], [ 'approved', 'unapproved', 'pending' ])) {
            $data['status'] = 'pending';
        }

        foreach ($data as $key => $value) {

            // remove fields that are not allowed
            $allowlist = [ 'name', 'email', 'website', 'text', 'timestamp', 'language', 'type', 'status', 'source', 'avatar', 'uid' ];
            if (!in_array($key, $allowlist)) {
                unset($data[ $key ]);
            }

            // remove empty fields
            if ($value == null) {
                unset($data[ $key ]);
            }
        }

        return $data;
    }


    /**
     * Retrieves an array of comments for a given page
     *
     * @param \Kirby\Cms\Page $page
     * @param string $query
     * @param string $sort
     * @param string $language
     * @return array
     */
    public static function retrieve($page, string $query = 'approved', string $sort = 'asc', string $language = null)
    {

        // if the query is a comment UID, return only that comment
        if (!in_array($query, ['approved', 'unapproved', 'pending']) && strlen($query) == 10) {
            foreach (Storage::read($page) as $comment) {

                // comment with matching uid is returned, regardless of language
                if ($comment['uid'] == $query) {
                    return $comment;
                }
            }
        } else {

            // skip if no language has been specified in the call
            if ($language != null) {

                // try to get current language if auto is set
                if ($language == 'auto') {
                    $language = kirby()->language() ?? null;
                    if (!empty($language)) {
                        $language = $language->code() ?? null;
                    }

                    // invalid language code in call = show all
                } elseif (strlen($language) == 2) {
                    foreach (kirby()->languages() as $lang) {
                        $languages[] = $lang;
                    }
                    if (!in_array($language, $languages)) {
                        $language = null;
                    }

                    // fallback = show all
                } else {
                    $language = null;
                }
            }

            $output = [];
            foreach (Storage::read($page, 'commentions') as $comment) {

                // return comments with matching status...
                if ($query == 'all' || $query == $comment['status']) {
                    // ...where the language matches, no language is stored or no language is specified in the request
                    if (empty($language) || empty($comment['language']) || $comment['language'] == $language) {
                        $comment['pageid'] = $page->id();
                        $output[] = $comment;
                    }
                }
            }

            // default sorting is chronological
            if ($sort == 'desc') {
                return array_reverse($output);
            } else {
                return $output;
            }
        }
    }


    /**
     * Returns the two-letter language code for a given path and page
     *
     * @param string $path
     * @param \Kirby\Cms\Page $page
     * @return array
     */
    public static function determineLanguage($path, $page)
    {

        // find the language where the configured URI matches the given URI
        foreach (kirby()->languages() as $language) {
            $pathInLanguage = (!empty(kirby()->language($language->code())->path()) ? kirby()->language($language->code())->path() . '/' : '') . $page->uri($language->code());
            if ($pathInLanguage == $path) {
                // return (two-letter) language code
                return $language->code();
            }
        }

        // return null if no match (default on single-language sites)
        return null;
    }


    /**
     * Runs spam checks on submitted data
     *
     * @param string $data
     * @param array $get
     * @return bool
     */
    public static function spamcheck($data, $get = null)
    {
        $settings = option('sgkirby.commentions.spamprotection');

        // spam rules applicable when form input is provided
        if (!empty($get)) :

            // honeypot: if field has been filed, it is very likely a robot
            if (in_array('honeypot', $settings) && empty($get['website']) === false) {
                return false;
            }

        // time measuring spam filter only active if no cache active and values are not impossible
        if ((int)$get['commentions'] > 0 && (int)option('sgkirby.commentions.spamtimemin') < (int)option('sgkirby.commentions.spamtimemax')) :

                // spam timeout min: if less than n seconds between form creation and submission, it is most likely a bot
                if (in_array('timemin', $settings) && (int)$get['commentions'] > (time() - (int)option('sgkirby.commentions.spamtimemin'))) {
                    return false;
                }

        // spam timeout max: if more than n seconds between form creation and submission, it is most likely a bot
        if (in_array('timemax', $settings) && (int)$get['commentions'] < (time() - (int)option('sgkirby.commentions.spamtimemax'))) {
            return false;
        }

        endif;

        endif;

        // TODO: verifications based on the data array's values (below is just a placeholder)
        if (isset($data['name']) && $data['name'] == 'I am a spammer') {
            return false;
        }

        // not identified as spam
        return true;
    }
}
