<?php

namespace sgkirby\Commentions;

use Exception;
use Kirby\Data\Data;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

class Cron
{
    public static function route()
    {
        $secret = option('sgkirby.commentions.secret');

        // validation with actionable error messages
        if (!get('token')) {
            return new Response('<p>Error: Token attribute missing from URL or empty.</p>', 'text/html', 403);
        }
        if ($secret == '') {
            return new Response('<p>Error: No token configured in config file.</p>', 'text/html', 500);
        }
        if (strlen($secret) < 10) {
            return new Response('<p>Error: Token set in config is too short.</p>', 'text/html', 500);
        }
        if (preg_match('/[&%#+]/i', $secret)) {
            return new Response('<p>Error: Token set in config contains invalid characters.</p>', 'text/html', 500);
        }
        if (get('token') != $secret) {
            return new Response('<p>Error: Incorrect token in URL attribute.</p>', 'text/html', 403);
        }

        try {
            Cron::processQueue();
            return new Response('<p>Success.</p>', 'text/html', 200);
        } catch (Exception $e) {
            return new Response('<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400);
        }
    }


    /**
     * Handles the asynchronous processing of webmentions from the queue
     *
     * @return bool
     */
    public static function processQueue()
    {

        // loop through all pages in the index
        foreach (site()->index() as $page) {

            // loop through every webmention request in the queue of every page
            foreach (Storage::read($page, 'webmentionqueue') as $queueitem) {

                // skip requests already marked as failed
                if (! isset($queueitem['failed'])) {
                    if ($result = static::processWebmention($queueitem)) {

                        // if parsing was successful, $result is the array with the saved data
                        if (is_array($result)) {

                            // delete webmention from queue after successful parsing
                            Storage::update($page, $queueitem['uid'], 'delete', 'webmentionqueue');

                            // trigger a hook that allows further processing of the data
                            kirby()->trigger('commentions.webmention:after', $page, $result);

                            return true;

                        // if parsing led to an error, $result is a string with the error message
                        } else {

							// default is to keep failed queue items for later review
							if ( option('sgkirby.commentions.keepfailed') == true ) {
								// mark failed request as failed
								Storage::update($page, $queueitem['uid'], [ 'failed' => $result ], 'webmentionqueue');
							} else {
								// delete failed request
								Storage::update($page, $queueitem['uid'], 'delete', 'webmentionqueue');
							}

                        }
                    } else {
                        throw new Exception('Problem processing queue file.');
                    }
                }
            }
        }
    }


    /**
     * Parses webmention from the queue, based on given source and target
     *
     * @param string $source
     * @param string $target
     * @return $array
     */
    public static function processWebmention($request)
    {
        $source = $request['source'];
        $target = $request['target'];

        // retrieve the source HTML
        $sourcecontent = F::read($source);

        // parse for microformats
        require_once dirname(__DIR__) . DS . 'vendor' . DS . 'Mf2/Parser.php';
        require_once dirname(__DIR__) . DS . 'vendor' . DS . 'IndieWeb/comments.php';
        $mf2   = \Mf2\parse($sourcecontent, $source);

        // process microformat data
        if (isset($mf2['items'][0])) {

            // parse the Mf2 array to a comment array
            $result = \IndieWeb\comments\parse($mf2['items'][0], $target, 1000, 20);

            // php-comments does not do rel=author
            if ($result['author']['url'] === false && array_key_exists('rels', $mf2) && array_key_exists('author', $mf2['rels']) && array_key_exists(0, $mf2['rels']['author']) && is_string($mf2['rels']['author'][0])) {
                $result['author']['url'] = $mf2['rels']['author'][0];
            }

            // if h-card is not embedded in h-entry, php-comments returns no author; check for h-card in mf2 output and fill in missing
            // TODO: align with algorithm outlined in https://indieweb.org/authorship
            foreach ($mf2['items'] as $mf2item) {
                if ($mf2item['type'][0] == 'h-card') {
                    if ($result['author']['name'] == ''  && isset($mf2item['properties']['name'][0])) {
                        $result['author']['name'] = $mf2item['properties']['name'][0];
                    }
                    if ($result['author']['photo'] == '' && isset($mf2item['properties']['photo'][0])) {
                        $result['author']['photo'] = $mf2item['properties']['photo'][0];
                    }
                    if ($result['author']['url'] == ''  && isset($mf2item['properties']['url'][0])) {
                        $result['author']['url'] = $mf2item['properties']['url'][0];
                    }
                }
            }

            // do not keep author avatar URL unless activated in config option
            if (isset($result['author']['photo']) && (bool)option('sgkirby.commentions.avatarurls')) {
                unset($result['author']['photo']);
            }

            // timestamp the webmention
            if (!empty($result['published'])) {
                // use date of source, if available
                if (is_numeric($result['published'])) {
                    $result['timestamp'] = $result['published'];
                } else {
                    $result['timestamp'] = strtotime($result['published']);
                }
            } else {
                // otherwise use date the request received
                $result['timestamp'] = $request['timestamp'];
            }

            // neither microformats nor backlink = no processing possible
        } elseif (! Str::contains($sourcecontent, $target)) {
            return 'Could not verify link to target.';

        // case: no microformats, but links back to target URL
        } else {
            $result['timestamp'] = time();
        }

        // find the Kirby page the target URL refers to
        $path = Url::path($target);
        if ($path == '') {
            $page = page('home');
        } else {
            $page = page(kirby()->call(trim($path, '/')));
        }

        if (!empty($page) && !$page->isErrorPage()) {

            // if there is no link to this site in the source...
            if (! Str::contains($sourcecontent, $target)) {
                $found = false;

                if (isset($mf2['items'][0])) {

                        // ...maybe they instead linked to a syndicated copy?
                    if ($page->syndication()->isNotEmpty()) {
                        foreach ($page->syndication()->split() as $syndication) {
                            if (Str::contains($sourcecontent, $syndication)) {
                                $result = \IndieWeb\comments\parse($data['items'][0], $syndication);
                                $found = true;
                                break;
                            }
                        }
                    }
                }

                // if no backlink can be found, just give up
                if (!$found) {
                    return 'Could not verify link to target.';
                }
            }

            // set comment type, if not given or deprecated 'mention' given
            if (!isset($result['type']) || $result['type'] == '' || $result['type'] == 'mention') {
                $result['type'] = 'webmention';
            }

            // create the commention data
            $finaldata = [
                'status' => Commentions::defaultstatus($result['type']),
                'name' => $result['author']['name'],
                'website' => $result['author']['url'],
                'avatar' => $result['author']['photo'],
                'text' => $result['text'],
                'timestamp' => date(date('Y-m-d H:i'), $result['timestamp']),
                'source' => $source,
                'type' => $result['type'],
                'language' => Commentions::determineLanguage($path, $page),
            ];

            // save webmention to the according txt file
            return Commentions::add($page, $finaldata);
        } else {
            return 'Could not resolve target URL to Kirby page';
        }
    }
}
