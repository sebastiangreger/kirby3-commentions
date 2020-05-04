<?php

namespace sgkirby\Commentions;

use Exception;
use Kirby\Http\Remote;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\Dir;
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
        $logfolder = kirby()->root('site') . DS . 'logs' . DS . 'commentions';
        $lockfile = $logfolder . DS . 'queuelock.log';
        if (F::exists($lockfile) && F::modified($lockfile) > (time() - 120)) {
            throw new Exception('A queue process is already running.');
        } elseif (F::exists($lockfile)) {
            F::remove($lockfile);
        }

        // an array keeps track of what domains have been pinged for throttling
        $pingeddomains = [];

        // an array keeps track of pinged source-target pairs to skip duplicate requests in queue
        $processedpairs = [];

        // loop through all pages in the index
        foreach (site()->index() as $page) {

            // loop through every webmention request in the queue of every page
            foreach (Storage::read($page, 'webmentionqueue') as $queueitem) {

                // skip requests already marked as failed or source-target pairs pinged during this cron run
                if (!isset($queueitem['failed']) && !in_array($queueitem['source'] . $queueitem['target'], $processedpairs)) {

                    // create/update the lockfile
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

                    // add the source-target pair to eliminate duplicates during this run
                    $processedpairs[] = $queueitem['source'] . $queueitem['target'];
                }
            }
        }

        // remove the lockfile, if exists
        if (F::exists($lockfile)) {
            F::remove($lockfile);
        }

        // create/update the timestamped log file
        $logfile = $logfolder . DS . 'lastcron.log';
        F::write($logfile, time());

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
        $target = $request['target'];

        // find the Kirby page the target URL refers to
        $path = Url::path($target);
        if ($path == '') {
            // empty path means home page
            $page = page('home');
        } else {
            // run the path through the router to determine real page
            $page = page(kirby()->call(trim($path, '/')));
        }
        if (empty($page)) {
            return 'Could not resolve target URL to Kirby page';
        }

        // retrieve the source and use the final URL (after possible redirects) for processing
        $remote = Remote::get($request['source']);
        $source = $remote->info()['url'];

        // HTTP 410 = deletion
        if ($remote->info()['http_code'] === 410 && $page->commentions('all')->filterBy('source', $source)->count() != 0) {
            $updateid = $page->commentions('all')->filterBy('source', $source)->first()->uid()->toString();
            return Commentions::update($page, $updateid, 'delete');
        }

        // HTTP 200 = valid source
        elseif ($remote->info()['http_code'] === 200) {
            $sourcecontent = $remote->content();
            if (empty($sourcecontent)) {
                return 'Source content empty.';
            }

            // parse for microformats
            $mf2 = \Mf2\parse($sourcecontent, $source);

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
                $hcardfound = false;
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
                if (!$hcardfound) {
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

                // if there is no apparent link to this site in the source...
                if (Str::contains($sourcecontent, $target)) {
                    $linkfound = true;
                } else {
                    $linkfound = false;

                    if (isset($mf2['items'][0])) {
                        // ...maybe they instead linked to a syndicated copy?
                        if ($page->syndication()->isNotEmpty()) {
                            foreach ($page->syndication()->split() as $syndication) {
                                if (Str::contains($sourcecontent, $syndication)) {
                                    $result = \IndieWeb\comments\parse($mf2['items'][0], $syndication);
                                    $linkfound = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            // case: no microformats, but links back to target URL
            elseif (Str::contains($sourcecontent, $target)) {
                $result['timestamp'] = time();
                $linkfound = true;
            }

            // neither microformats nor backlink, but could still be a deletion
            else {
                $linkfound = false;
            }

            // if source does not contain link to target, it's either deletion or invalid request
            if (!$linkfound) {
                if ($page->commentions('all')->filterBy('source', $source)->count() != 0) {
                    $updateid = $page->commentions('all')->filterBy('source', $source)->first()->uid()->toString();
                    return Commentions::update($page, $updateid, 'delete');
                } else {
                    return 'Source does not contain link to target.';
                }
            }

            // set comment type, if not given or deprecated 'mention' given
            if (empty($result['type']) || $result['type'] == 'mention') {
                $result['type'] = 'webmention';
            }

            // create the commention data
            $finaldata = [
                'name'      => $result['author']['name'] ?? false,
                'website'   => $result['author']['url'] ?? false,
                'avatar'    => $result['author']['photo'] ?? false,
                'text'      => $result['text'],
                'source'    => $source,
                'type'      => $result['type'],
                'language'  => Commentions::determineLanguage($page, $path),
                'timestamp' => date('Y-m-d H:i', $result['timestamp']),
                'status'    => Commentions::defaultstatus($result['type']),
            ];

            // add as new webmention
            return Commentions::add($page, $finaldata);
        }

        // return error for any other HTTP codes
        else {
            return 'HTTP return code is neither 200 nor 410.';
        }
    }
}
