<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Data\Yaml;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use Exception;

class Commentions {

	public static $feedback = null;

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

			// remove empty fields
			foreach ( $data as $key => $value )
				if ( $value == null ) unset( $data[ $key ] );

			// no need to keep target page info in comment meta
			unset( $data['target'] );

			$comments[] = $data;
		
		else :

			// no need to keep target page info in comment meta
			unset( $data['target'] );

			// for webmentions, some translations are required
			$mentiondata = [
				'name' => $data['author']['name'],
				'message' => $data['text'],
				'timestamp' => $data['timestamp'],
				'source' => $data['source'],
				'website' => $data['author']['url'],
				'type' => $data['type'],
				'approved' => 'true',
			];

			// only create non-essential fields if they contain data
			if ( isset( $data['author']['photo'] ) && $data['author']['photo'] != null )
				$mentiondata['avatar'] = $data['author']['photo'];

			$comments[] = $mentiondata;
		
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
							if ( get('manualmention') )
								go( get('target') . "?thx=accepted" );
							else
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
				'pattern' => 'commentions-processqueue',
				'action'  => function () {

					$secret = option( 'sgkirby.commentions.secret', '' );

					// validation with actionable error messages
					if ( !get('token') )
						return new Response( '<p>Error: Token attribute missing from URL or empty.</p>', 'text/html', 403 );
					if ( $secret == '' )
						return new Response( '<p>Error: No token configured in config file.</p>', 'text/html', 500 );
					if ( strlen( $secret ) < 10 )
						return new Response( '<p>Error: Token set in config is too short.</p>', 'text/html', 500 );
					if ( preg_match( "/[&%#+]/i", $secret ) )
						return new Response( '<p>Error: Token set in config contains invalid characters.</p>', 'text/html', 500 );
					if ( get('token') != $secret )
						return new Response( '<p>Error: Incorrect token in URL attribute.</p>', 'text/html', 403 );

					try {
						\sgkirby\Commentions\Commentions::processQueue();
						return new Response( '<p>Success.</p>', 'text/html', 200 );
					} catch(Exception $e) {
						return new Response( '<p>Error: ' . $e->getMessage() . '</p>', 'text/html', 400 );
					}

				}
			],
		];

	}
	
    public static function queueComment( $path ) {

		$page = page( $path );

		$spamfilters = option( 'sgkirby.commentions.spamprotection', [ 'honeypot', 'timemin', 'timemax' ] );

		// honeypot: if field has been filed, it is very likely a robot
        if ( in_array( 'honeypot', $spamfilters ) && empty( get('website') ) === false ) {
            go( $page->url() );
            exit;
        }

		// time measuring spam filter only active if no cache active and values are not impossible
		if ( (int) get('commentions') > 0 && (int) option( 'sgkirby.commentions.spamtimemin', 5 ) < (int) option( 'sgkirby.commentions.spamtimemax', 86400 ) ) :

			// spam timeout min: if less than n seconds between form creation and submission, it is most likely a bot
			if ( in_array( 'timemin', $spamfilters ) && (int) get('commentions') > ( time() - (int) option( 'sgkirby.commentions.spamtimemin', 5 ) ) ) {
				go( $page->url() );
				exit;
			}

			// spam timeout max: if more than n seconds between form creation and submission, it is most likely a bot
			if ( in_array( 'timemax', $spamfilters ) && (int) get('commentions') < ( time() - (int) option( 'sgkirby.commentions.spamtimemax', 86400 ) ) ) {
				go( $page->url() );
				exit;
			}

		endif;

        $data = array(
            'name' => get('name'),
            'email' => get('email'),
            'website' => get('realwebsite'),
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

        // some of the data is invalid
        if ( $invalid = invalid( $data, $rules, $messages ) ) {

			Commentions::$feedback = $invalid;
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
        foreach ( glob( $files ) as $queuefile ) :
        
            $data = Data::read( $queuefile, 'json' );

			if ( $result = static::parseRequest( $data['source'], $data['target'] ) ) :

				// delete webmention from queue after successful parsing
				if ( is_bool( $result ) ) :
				
					F::remove( $queuefile );
					return true;

				// rename failed webmention for debug
				// TODO: delete after some time
				else :
							
					F::rename( $queuefile, str_replace( 'webmention-', 'failed-', F::name( $queuefile ) ) );
					throw new Exception( $result );

				endif;

			else :
		
				throw new Exception( 'Problem processing queue file.' );

			endif;

        endforeach;
        
	}

	public static function parseRequest( $source, $target ) {

		// retrieve the source HTML
		$sourcecontent = F::read( $source );

		// parse for microformats
		require_once( dirname(__DIR__) . DS . 'vendor' . DS . 'Mf2/Parser.php' );
		require_once( dirname(__DIR__) . DS . 'vendor' . DS . 'IndieWeb/comments.php' );
		$mf2   = \Mf2\parse( $sourcecontent, $source );

		// process microformat data
		if( isset( $mf2['items'][0] ) ) :

			// parse the Mf2 array to a comment array
			$result = \IndieWeb\comments\parse( $mf2['items'][0], $target, 1000, 20 );

			// php-comments does not do rel=author
			if ( $result['author']['url'] === false && array_key_exists( 'rels', $mf2 ) && array_key_exists( 'author', $mf2['rels'] ) && array_key_exists( 0, $mf2['rels']['author'] ) && is_string( $mf2['rels']['author'][0] ) )
				$result['author']['url'] = $mf2['rels']['author'][0];

			// if h-card is not embedded in h-entry, php-comments returns no author; check for h-card in mf2 output and fill in missing
			// TODO: align with algorithm outlined in https://indieweb.org/authorship
			foreach ( $mf2['items'] as $mf2item ) {
				if ( $mf2item['type'][0] == 'h-card' ) {
					if ( $result['author']['name'] == ''  && isset( $mf2item['properties']['name'][0] ) )
						$result['author']['name'] = $mf2item['properties']['name'][0];
					if ( $result['author']['photo'] == '' && isset( $mf2item['properties']['photo'][0] ) )
						$result['author']['photo'] = $mf2item['properties']['photo'][0];
					if ( $result['author']['url'] == ''  && isset( $mf2item['properties']['url'][0] ) )
						$result['author']['url'] = $mf2item['properties']['url'][0];
				}
			}

			// do not keep author avatar URL unless activated in config option
			if ( isset( $result['author']['photo'] ) && (bool) option( 'sgkirby.commentions.avatarurls', false ) )
				unset( $result['author']['photo'] );

			// timestamp the webmention
			if( !empty( $result['published'] ) )
				$result['timestamp'] = strtotime($result['published']);
			else
				$result['timestamp'] = time();

		// neither microformats nor backlink = no processing possible
		elseif( ! Str::contains( $sourcecontent, $target ) ) :

			return 'Could not verify link to target.';

		// case: no microformats, but links back to target URL
		else :

			$result['timestamp'] = time();

		endif;

		// find the Kirby page the target URL refers to
		$path = Url::path( $target );
		if ( $path == '' )
			$page = page('home');
		else
			$page = page( $path );

		if( !$page->isErrorPage() ) {

			// if there is no link to this site in the source...
			if( ! Str::contains( $sourcecontent, $target ) ) :

				$found = false;

				if ( isset( $mf2['items'][0] ) ) :

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

				endif;

				// if no backlink can be found, just give up
				if ( !$found )
					return 'Could not verify link to target.';

			endif;

			// store source and target URL in the result array
			$result['source'] = $source;
			$result['target'] = $page->id();

			// set comment type
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
