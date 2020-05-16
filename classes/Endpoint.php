<?php

namespace sgkirby\Commentions;

use Exception;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

class Endpoint
{
    /**
     * Called by the router, triggers the storage of the request on POST submission or deals out the default form for GET requests
     *
     * @return \Kirby\Http\Response
     */
    public static function route()
    {
        // bounce any submissions if no template accepts webmentions
        if (is_array(option('sgkirby.commentions.templatesWithWebmentions')) && sizeof(option('sgkirby.commentions.templatesWithWebmentions')) === 0) {
            return new Response('<p>Error: This website does not accept webmentions.</p>', 'text/html', 400);
        }

        if (kirby()->request()->is('POST')) {
            // for POST requests, queue the incoming webmention
            try {
                Endpoint::queueWebmention();

                // if submitted from the pretty frontend form, redirect to the page with according feedback message
                if (get('manualmention')) {
                    go(get('target') . '?thx=accepted');
                // other submissions (incl. from the default form shown under the endpoint URL) simply return a string
                } else {
                    return new Response('<p>Accepted.</p>', 'text/html', 202);
                }
            } catch (Exception $e) {
                return new Response('<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400);
            }
        } else {
            // for GET requests, provide a submission form as error fallback
            return new Response('
                <html><body>
                    <form action="' . site()->url('/') . '/' . option('sgkirby.commentions.endpoint') . '" method="post">
                        <div>
                            <label for="target">The URL on ' . site()->url('/') . ' you linked to</label>
                            <input type="url" name="target" value="' . get('target') . '" pattern=".*' . str_replace('.', '\.', site()->url('/')) . '.*" required>
                        </div>
                        <div>
                            <label for="source">The URL of your response (full URL incl https://)</label>
                            <input type="url" name="source" value="' . get('source') . '" pattern=".*http.*" required>
                        </div>
                        <input type="submit" name="submit" value="Submit">
                    </form>
                </body></html>
            ', 'text/html', 404);
        }
    }

    /**
     * Validates the submitted URLs and stores them into the queue file
     *
     * @return array The complete comment data (incl. UID) as returned by the Storage class after saving
     */
    public static function queueWebmention()
    {
        // source is the external site sending the webmention;
        $source = get('source');

        // target is the local URL, claimed to be mentioned in the source
        $target = get('target');

        if (!V::url($source)) {
            throw new Exception('Invalid source URL.');
        }

        if (!V::url($target)) {
            throw new Exception('Invalid target URL.');
        }

        if ($source == $target) {
            throw new Exception('Target and source are identical.');
        }

        if (!Str::contains($target, str_replace([ 'http:', 'https:' ], '', site()->url('/')))) {
            throw new Exception('Target URL not on this site.');
        }

        // find the Kirby page the target URL refers to
        $path = Url::path($target);
        if ($path == '') {
            $page = page(site()->homePageId());
        } elseif ( !$page = page($path) ){
            $page = page(kirby()->router()->call($path));
        }

        // if url does not resolve to valid page, attach to homepage instead
        if ($page === null) {
            $page = page(site()->homePageId());
        }

        // check for allowlist status
        if (!Commentions::accepted($page, 'webmentions')) {
            throw new Exception('This content does not accept webmentions.');
        }

        $data = [
            'target' => $target,
            'source' => $source,
            'timestamp' => time(),
            'uid' => Commentions::uid()
        ];

        // add to the queue in the according commention file
        return Storage::add($page, $data, 'webmentionqueue');
    }
}
