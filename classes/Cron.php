<?php

namespace sgkirby\Commentions;

use Exception;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

class Cron
{
    /**
     * Called by the router, verifies the validity of the cron request and triggers the processing
     *
     * @return \Kirby\Http\Response
     */
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

        // trigger the processing
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
     * @return bool True if successful
     */
    public static function processQueue()
    {
        // limit to one process by only proceeding if no (or an expired left over) lockfile exists
        $lockfile = kirby()->root('content') . DS . '.commentions_queuelock';
        if (F::exists($lockfile) && F::modified($lockfile) > (time() - 120)) {
            throw new Exception('A queue process is already running.');
        } elseif (F::exists($lockfile)) {
            F::remove($lockfile);
        }

        // an array keeps track of what domains have been pinged for throttling
        $pingeddomains = [];

        // loop through all pages in the index
        foreach (site()->index() as $page) {

            // loop through every webmention request in the queue of every page
            foreach (Storage::read($page, 'webmentionqueue') as $queueitem) {

                // skip requests already marked as failed
                if (! isset($queueitem['failed'])) {

                    // create/update the lockfile, as this is where actual DoS harm can be done
                    F::write($lockfile, '');

                    // ensure that the same domain is pinged max. every n seconds
                    $pinglimit = 5;
                    $sourcedomain = parse_url($queueitem['source'], PHP_URL_HOST);
                    if (isset($pingeddomains[$sourcedomain]) && $pingeddomains[$sourcedomain] > (time() - $pinglimit)) {
                        sleep($pinglimit);
                    }
                    $pingeddomains[$sourcedomain] = time();

                    // parse the request
                    if ($result = static::parseWebmention($queueitem)) {

                        // if parsing was successful, $result is the array with the saved data
                        if (is_array($result)) {

                            // delete webmention from queue after successful parsing
                            Storage::update($page, $queueitem['uid'], 'delete', 'webmentionqueue');

                            // trigger a hook that allows further processing of the data
                            kirby()->trigger('commentions.webmention:after', $page, $result);

                        // if parsing led to an error, $result is a string with the error message
                        } else {

                            // default is to keep failed queue items for later review
                            if (option('sgkirby.commentions.keepfailed') == true) {
                                // mark failed request as failed
                                Storage::update($page, $queueitem['uid'], [ 'failed' => $result ], 'webmentionqueue');
                            } else {
                                // delete failed request
                                Storage::update($page, $queueitem['uid'], 'delete', 'webmentionqueue');
                            }
                        }
                    } else {
                        throw new Exception('Problem processing queue item.');
                    }
                }
            }
        }
        if (F::exists($lockfile)) {
            F::remove($lockfile);
        }
        return true;
    }

    /**
     * Parses webmention from the queue, based on given source and target
     *
     * @param string $source The URL of the website that claims to be linking to this site
     * @param string $target The URL of the page on this website that is claimed to be linked to
     * @return array|string - Array: The complete comment data (incl. UID) as returned by the Storage class after saving
     *                      - String: Human-readable error message in case of failure
     */
    public static function parseWebmention($request)
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

            // sometimes, the author name ends up in the url field
            if (!empty($result['author']['url']) && !Str::isUrl($result['author']['url'])) {
                if (empty($result['author']['name'])) {
                    $result['author']['name'] = $result['author']['url'];
                }
                $result['author']['url'] = false;
            }

            // php-comments does not do rel=author
            if (array_key_exists('url', $result['author']) && $result['author']['url'] === false && array_key_exists('rels', $mf2) && array_key_exists('author', $mf2['rels']) && array_key_exists(0, $mf2['rels']['author']) && is_string($mf2['rels']['author'][0])) {
                $result['author']['url'] = $mf2['rels']['author'][0];
            }

            // if h-card is not embedded in h-entry, php-comments returns no author; check for h-card in mf2 output and fill in missing
            foreach ($mf2['items'] as $mf2item) {
                if ($mf2item['type'][0] == 'h-card') {
                    $hcardfound = true;
                    if (empty($result['author']['name'])  && !empty($mf2item['properties']['name'][0])) {
                        $result['author']['name'] = $mf2item['properties']['name'][0];
                    }
                    if (empty($result['author']['photo']) && !empty($mf2item['properties']['photo'][0])) {
                        $result['author']['photo'] = $mf2item['properties']['photo'][0];
                    }
                    if (empty($result['author']['url']) && !empty($mf2item['properties']['url'][0])) {
                        $result['author']['url'] = $mf2item['properties']['url'][0];
                    }
                }
            }

            // if no h-card was found, try to use 'author' property of h-entry instead
            if (!$hcardfound ?? false) {
                foreach ($mf2['items'] as $mf2item) {
                    if ($mf2item['type'][0] == 'h-entry') {
                        if (empty($result['author']['name'])  && !empty($mf2item['properties']['author'][0])) {
                            $result['author']['name'] = $mf2item['properties']['author'][0];
                        }
                    }
                }
            }

            // TODO: potentially implement author discovery from rel-author or author-page URLs; https://indieweb.org/authorship-spec

            // do not keep author avatar URL unless activated in config option
            if (isset($result['author']['photo']) && (bool)option('sgkirby.commentions.avatarurls')) {
                $result['author']['photo'] = false;
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
        }

        // neither microformats nor backlink = no processing possible
        elseif (! Str::contains($sourcecontent, $target)) {
            return 'Could not verify link to target.';
        }

        // case: no microformats, but links back to target URL
        else {
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
                'name' => $result['author']['name'] ?? false,
                'website' => $result['author']['url'] ?? false,
                'avatar' => $result['author']['photo'] ?? false,
                'text' => $result['text'],
                'timestamp' => date(date('Y-m-d H:i'), $result['timestamp']),
                'source' => $source,
                'type' => $result['type'],
                'language' => Commentions::determineLanguage($page, $path),
            ];

            // save webmention to the according txt file
            return Commentions::add($page, $finaldata);
        } else {
            return 'Could not resolve target URL to Kirby page';
        }
    }
}
