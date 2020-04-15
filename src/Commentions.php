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


    /**
     * Generates a random 10-character string to be used as comment UID
     *
     * @return string
     */

    public static function uid() {

		// generate uid
		$uidchars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$uid = '';
		for ( $i = 0; $i < 10; $i++ )
			$uid .= $uidchars[ random_int( 0, strlen( $uidchars ) - 1)];

		return $uid;
		
	}


    /**
     * Adds a new comment to the page, incl. some cleanup and adding a UID
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data
     */

    public static function add( $page, $data ) {

		// clean up the data; incl. removal of any user-provided uid
		$data = Commentions::sanitize( $data, false );

		// add a uid field
		$data['uid'] = Commentions::uid();

		// save commention to the according txt file
		Storage::add( $page, $data );

		return $data;

	}


    /**
     * Updates a comment on the page, incl. some cleanup
     *
     * @param \Kirby\Cms\Page $page
     * @param string $uid
     * @param array $data
     */

    public static function update( $page, $uid, $data ) {

		return Storage::update( $page, $uid, $data, 'comments' );

	}


    /**
     * Verifies and cleans up commentions data for saving
     *
     * @param array $data
     * @param book $keepuid
     * @return array
     */

    public static function sanitize( $data, $keepuid = false ) {

		// users may not send 'uid' as part of the data payload
		if ( ! $keepuid && ! empty( $data['uid'] ) )
			unset( $data['uid'] );

		// timestamp is required; use current time if missing or not a unix epoch
		if ( empty( $data['timestamp'] ) || ! is_numeric( $data['timestamp'] ) )
			$data['timestamp'] = date( date('Y-m-d H:i'), time() );

		// status is required; set to 'pending' default if missing or invalid value
		if ( empty( $data['status'] ) || !in_array( $data['status'], [ 'approved', 'unapproved', 'pending' ] ) )
			$data['status'] = 'pending';

		// type is required; set to 'comment' default if missing
		if ( empty( $data['type'] ) )
			$data['type'] = 'comment';

		// validations based on type
		if ( $data['type'] == 'comment' ) :

			// text is required for comments
			if ( empty( $data['text'] ) )
				$data['text'] = '';

			// 'source' only used for webmentions
			if ( !empty( $data['source'] ) )
				unset( $data['source'] );

		endif;

		foreach ( $data as $key => $value ) :

			// remove fields that are not allowed
			$allowlist = [ 'name', 'email', 'website', 'text', 'timestamp', 'language', 'type', 'status', 'source', 'avatar', 'uid' ];
			if ( !in_array( $key, $allowlist ) )
				unset( $data[ $key ] );

			// remove empty fields
			if ( $value == null )
				unset( $data[ $key ] );

		endforeach;

		return $data;

	}


    /**
     * Retrieves an array of comments for a given page
     *
     * @param \Kirby\Cms\Page $page
     * @param string $status
     * @param string $sort
     * @return array
     */

    public static function retrieve( $page, string $status = 'approved', string $sort = 'asc' ) {

		$output = [];
		foreach( Storage::read( $page ) as $comment ) :
			if ( ( $status == $comment['status'] ) || $status == 'all' ) :
				$comment['pageid'] = $page->id();
				$output[] = $comment;
			endif;
		endforeach;

		// default sorting is chronological
		if ( $sort == 'desc' )
			return array_reverse( $output );
		else
			return $output;

	}


    /**
     * Returns the two-letter language code for a given path and page
     *
     * @param string $path
     * @param \Kirby\Cms\Page $page
     * @return array
     */

    public static function determineLanguage( $path, $page ) {

		// find the language where the configured URI matches the given URI
		foreach( kirby()->languages() as $language ) :
			if ( $page->uri( $language->code() ) == $path )
				// return (two-letter) language code
				return $language->code();
		endforeach;

		// return null if no match (default on single-language sites)
		return null;

	}


    /**
     * Adds new comment to the given page's commention file
     *
     * @param string $path
     */

    public static function queueComment( $path, $page ) {

		$spamfilters = option( 'sgkirby.commentions.spamprotection' );

		// honeypot: if field has been filed, it is very likely a robot
        if ( in_array( 'honeypot', $spamfilters ) && empty( get('website') ) === false ) {
            go( $page->url() );
            exit;
        }

		// time measuring spam filter only active if no cache active and values are not impossible
		if ( (int) get('commentions') > 0 && (int) option( 'sgkirby.commentions.spamtimemin' ) < (int) option( 'sgkirby.commentions.spamtimemax' ) ) :

			// spam timeout min: if less than n seconds between form creation and submission, it is most likely a bot
			if ( in_array( 'timemin', $spamfilters ) && (int) get('commentions') > ( time() - (int) option( 'sgkirby.commentions.spamtimemin' ) ) ) {
				go( $page->url() );
				exit;
			}

			// spam timeout max: if more than n seconds between form creation and submission, it is most likely a bot
			if ( in_array( 'timemax', $spamfilters ) && (int) get('commentions') < ( time() - (int) option( 'sgkirby.commentions.spamtimemax' ) ) ) {
				go( $page->url() );
				exit;
			}

		endif;

        $data = array(
            'name' => get('name'),
            'email' => get('email'),
            'website' => get('realwebsite'),
            'text' => get('message'),
            'timestamp' => date( date('Y-m-d H:i'), time() ),
            'language' => Commentions::determineLanguage( $path, $page ),
            'type' => 'comment',
            'status' => 'pending',
        );
        $rules = array(
            'text' => array('required', 'min' => 4, 'max' => 4096),
        );
        $messages = array(
            'text' => 'Please enter a text between 4 and 4096 characters'
        );

        // some of the data is invalid
        if ( $invalid = invalid( $data, $rules, $messages ) ) {

			Commentions::$feedback = $invalid;
			return [
				'alert' => $invalid,
			];

        }

		// save commention to the according txt file
		Commentions::add( $page, $data );

		// trigger a hook that allows further processing of the data
		kirby()->trigger( "commentions.queueComment:after", $page, $data );

		// return to the post page and display success message
		go( $page->url() . "?thx=queued" );

    }


    /**
     * Parses webmention from the queue, based on given source and target
     *
     * @param string $source
     * @param string $target
     * @return bool
     */

	public static function parseRequest( $request ) {

		$source = $request['source'];
		$target = $request['target'];

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
			if ( isset( $result['author']['photo'] ) && (bool) option( 'sgkirby.commentions.avatarurls' ) )
				unset( $result['author']['photo'] );

			// timestamp the webmention
			if( !empty( $result['published'] ) ) :
				// use date of source, if available
				if ( is_numeric( $result['published'] ) )
					$result['timestamp'] = $result['published'];
				else
					$result['timestamp'] = strtotime( $result['published'] );
			else :
				// otherwise use date the request received
				$result['timestamp'] = $request['timestamp'];
			endif;

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
			$page = page( kirby()->call( trim( $target, '/' ) ) );

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

			// set comment type, if not given or deprecated 'mention' given
			if ( !isset( $result['type'] ) || $result['type'] == '' || $result['type'] == 'mention' )
				$result['type'] = 'webmention';

			// save as new webmention
			$finaldata = [
				'status' => 'pending',
				'name' => $result['author']['name'],
				'website' => $result['author']['url'],
				'avatar' => $result['author']['photo'],
				'text' => $result['text'],
				'timestamp' => date( date('Y-m-d H:i'), $result['timestamp'] ),
				'source' => $source,
				'type' => $result['type'],
				'language' => Commentions::determineLanguage( $path, $page ),
			];

			Commentions::add( page( $page->id() ), $finaldata );

			return true;

		} else {
		
			throw new Exception('Invalid page');
		
		}

	}


}
