<?php

namespace sgkirby\Commentions;

use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Data\Data;
use Kirby\Data\Yaml;
use Kirby\Http\Header;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Tpl;
use Kirby\Toolkit\V;
use Exception;

class Commentions {

    public static function approve( $filename ) {
		
		$inboxfile = kirby()->root() . '/content/.commentions/inbox' . DS . $filename;
		$data = Data::read( $inboxfile, 'json' );
		$targetpage = kirby()->page( $data['target'] );
		
		// translate unix timestamp to format required by Kirby
		$data['timestamp'] = date( date('Y-m-d H:i'), $data['timestamp'] );

		// load array of existing comments, if any
		if ( $targetpage->comments() )
			$comments = $targetpage->comments()->yaml();
		else
			$comments = [];
		
		if ( $data['type'] == 'comment' ) :
		
			// the comment array already exists in the required form
			$data['approved'] = 'true';
			$comments[] = $data;
		
		else :

			// for webmentions, some translations are required
			$comments[] = [
				'name' => $data['author']['name'],
				'message' => $data['text'],
				'timestamp' => $data['timestamp'],
				'source' => $data['source'],
				'avatar' => $data['author']['photo'],
				'website' => $data['author']['url'],
				'type' => $data['type'],
				'approved' => 'true',
			];
		
		endif;

		// save the updated comment array to the text file
		$targetpage->update(array(
			'comments' => yaml::encode($comments),
		));

		// delete the processed inbox file
		F::remove( $inboxfile );
		
		return ['ok'];
		
	}

    public static function delete( $filename ) {
		
		// delete the inbox file
		$inboxfile = kirby()->root() . '/content/.commentions/inbox' . DS . $filename;
		F::remove( $inboxfile );

		return ['ok'];
		
	}

    public static function endpointRoute( $kirby ) {

		return [
			[
				'pattern' => option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' ),
				'method'  => 'GET|POST',
				'action'  => function () {

					require_once( 'Endpoint.php' );

					if ( kirby()->request()->is('POST') ) :
					
						// for POST requests, queue the incoming webmention
						try {
							Endpoint::queueWebmention();
							return new Response( '<p>Accepted.</p>', 'text/html', 202 );
						} catch(Exception $e) {
							return new Response( '<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400 );
						}

					else :

						// for GET requests, provide a submission form instead
						return new Response( '
							<html><body>
								<form action="' . site()->url() . '/' . option( 'sgkirby.commentions.endpoint', 'webmention-endpoint' ) .  '" method="post">
									<div>
										<label for="target">The URL on ' . site()->url() . ' you linked to</label>
										<input type="url" name="target" value="' . get('target') . '" pattern=".*' . str_replace( '.', '\.', site()->url() ) . '.*" required>
									</div>
									<div>
										<label for="source">The URL of your response (full URL incl https://)</label>
										<input type="url" name="source" value="' . get('source') . '" pattern=".*http.*" required>
									</div>
									<input type="submit" name="submit" value="Submit">
								</form>
							</body></html>
						', 'text/html', 404);

					endif;

				}
			],
			[
				'pattern' => 'commentions-processqueue-' . option( 'sgkirby.commentions.secret', '' ),
				'action'  => function () {
	
					// a secret has to be set in config, otherwise this endpoint does not work
					if ( strlen( option( 'sgkirby.commentions.secret', '' ) ) < 10 )
						return new Response( '<p>Unauthorised request.</p>', 'text/html', 403 );

					try {
						\sgkirby\Commentions\Commentions::processQueue();
						return new Response( '<p>Processed.</p>', 'text/html', 200 );
					} catch(Exception $e) {
						return new Response( '<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400 );
					}

				}
			],
		];

	}

    public static function successMessage() {
		
		if ( option( 'sgkirby.commentions.autoapprovecomments', 'false' ) != 'true' )
			return [ 'success' => 'Thank you! Please be patient, your comment has has to be approved by the editor.' ];
		else
			return [ 'success' => 'Thank you for your comment!' ];
		
	}
	
    public static function queueComment( $page, $kirby, $pages ) {

        $data = array(
            'name' => get('name'),
            'message' => get('message'),
            'timestamp' => time(),
            'target' => $page->id(),
            'type' => 'comment',
        );
        $rules = array(
            'message' => array('required', 'min' => 4, 'max' => 4096),
        );
        $messages = array(
            'message' => 'Please enter a text between 4 and 4096 characters'
        );

		if ( $data['name'] == '' )
			$data['name'] = 'Anonymous';

        // some of the data is invalid
        if ( $invalid = invalid( $data, $rules, $messages ) ) {

			return [
				'alert' => $invalid,
			];

        } else {

			try {
	
				$inboxfile = kirby()->root() . '/content/.commentions/inbox' . DS . time() . '.json';
				$json = json_encode( $data );
				F::write( $inboxfile, $json );
				
				go( $page->url() . "?thx=queued" );

			} catch (Exception $e) {

				echo $e->getMessage();

			}

        }
        
    }

	public static function processQueue() {

        $files = kirby()->root() . '/content/.commentions/queue/webmention-*.json';
        foreach ( glob( $files ) as $queuefile ) {
            $data = Data::read( $queuefile, 'json' );
			if ( static::parseRequest( $data['source'], $data['target'] ) ) {
				F::remove( $queuefile );
			}
        }
        
        // TODO: if autoapprove is true, also publish immediately (incl. comments, not just webmentions); this may cause file conflicts when editing

	}

	public static function parseRequest( $source, $target ) {

		// retrieve the source HTML
		$sourcecontent = F::read( $source );

		// parse for microformats
		require_once( dirname(__DIR__) . DS . 'vendor' . DS . 'Mf2/Parser.php' );
		require_once( dirname(__DIR__) . DS . 'vendor' . DS . 'IndieWeb/comments.php' );
		$mf2   = \Mf2\parse( $sourcecontent, $source );

		// no microformats found = no processing possible
		if(!isset($mf2['items'][0]))
			throw new Exception('No Microformats h-* found');

		// parse the Mf2 array to a comment array
		$result = \IndieWeb\comments\parse( $mf2['items'][0], $target, 1000, 20 );

		// php-comments does not do rel=author
		if ($result['author']['url'] === false && array_key_exists('rels', $mf2) && array_key_exists('author', $mf2['rels']) && array_key_exists(0, $mf2['rels']['author']) && is_string($mf2['rels']['author'][0]))
			$result['author']['url'] = $mf2['rels']['author'][0];

		// if h-card is not embedded in h-entry, php-comments returns no author; check for h-card in mf2 output and fill in missing
		// TODO: align with algorithm outlined in https://indieweb.org/authorship
		foreach ( $mf2['items'] as $mf2item ) {
			if ( $mf2item['type'][0] == 'h-card' ) {
				if ( $result['author']['name'] == '' )
					$result['author']['name'] = $mf2item['properties']['name'][0];
				if ( $result['author']['photo'] == '' )
					$result['author']['photo'] = $mf2item['properties']['photo'][0];
				if ( $result['author']['url'] == '' )
					$result['author']['url'] = $mf2item['properties']['url'][0];
			}
		}

		// find the Kirby page the target URL refers to
		$path = Url::path( $target );
		if ( $path == '' )
			$page = page('home');
		else
			$page = page( $path );

		if( !$page->isErrorPage() ) {

			// if there is no link to this site in the source...
			if( !Str::contains( $sourcecontent, $target ) ) {

				$found = false;

				// ...maybe they instead linked to a syndicated copy?
				if ( $page->syndication()->isNotEmpty() ) {
					foreach ( $page->syndication()->split() as $syndication ) {
						if ( Str::contains( $sourcecontent, $syndication ) ) {
							$result = \IndieWeb\comments\parse( $data['items'][0], $syndication );
							$found = true;
							break;
						}
					}
				}

				// if no backlink can be found, just give up
				if ( !$found )
					throw new Exception('Probably spam');

			}

			// store source and target URL in the result array
			$result['source'] = $source;
			$result['target'] = $page->id();

			// timestamp the webmention
			if( !empty( $result['published'] ) )
				$result['timestamp'] = strtotime($result['published']);
			else
				$result['timestamp'] = time();

			$result['type'] = 'webmention';

			// TODO: instead of writing into JSON, write this into the "comments inbox"
			$json = json_encode( $result );
			$hash = sha1( $source );
			$inboxfile = kirby()->root() . '/content/.commentions/inbox' . DS . time() /* . '-' . $hash */ . '.json';
			F::write( $inboxfile, $json );

			return true;

		} else {
		
			throw new Exception('Invalid page');
		
		}

	}

}
