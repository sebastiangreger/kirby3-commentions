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
     * Checks the settings to assign the correct status to new comment submissions
     *
     * @param string $type
     * @return string
     */

    public static function defaultstatus( $type ) {

		// array of valid status strings, for validation
		$valid = [ 'approved', 'unapproved', 'pending' ];

		// fetch the setting string/array from options
		$setting = option( 'sgkirby.commentions.defaultstatus' );

		// array: retrieve the applicable setting for this type
		if ( is_array( $setting ) && isset( $setting[ $type ] ) && in_array( $setting[ $type ], $valid ) )
			return $setting[ $type ];

		// string: use the preset value
		elseif ( is_string( $setting ) && in_array( $setting, $valid ) )
			return $setting;

		// fallback is always 'pending'
		else
			return 'pending';

	}


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

		// a regular comment has to at least feature a text
		if ( ( empty( $data['type'] ) || $data['type'] == 'comment' ) && empty( $data['text'] ) )
			return false;

		// a webmention has to at least feature a source that is a valid URL
		if ( ( ! empty( $data['type'] ) && $data['type'] != 'comment' ) && ( empty( $data['source'] ) || ! Str::isURL( $data['source'] ) ) )
			return false;

		// clean up the data; incl. removal of any user-provided uid
		$data = Commentions::sanitize( $data, false );

		// add a uid field
		$data['uid'] = Commentions::uid();

		// save commention to the according txt file
		$saved = Storage::add( $page, $data );

		// trigger a hook that allows further processing of the data
		kirby()->trigger( "commentions.add:after", $page, $saved );

		return $saved;

	}


    /**
     * Updates a comment on the page, incl. some cleanup
     *
     * @param \Kirby\Cms\Page $page
     * @param string $uid
     * @param array $data
     */

    public static function update( $page, $uid, $data ) {

		// UID cannot be updated externally
		if ( !empty( $data['uid'] ) )
			unset( $data['uid'] );

		$saved = Storage::update( $page, $uid, $data, 'comments' );

		// trigger a hook that allows further processing of the data
		kirby()->trigger( "commentions.update:after", $page, $saved );
		
		return $saved;

	}


    /**
     * Verifies and cleans up commentions data for saving
     *
     * @param array $data
     * @param book $keepuid
     * @return array
     */

    public static function sanitize( $data, $update = false ) {

		// validations on missing required fields only apply when creating new entries
		if ( ! $update ) :

			// users may not send 'uid' as part of the data payload
			if ( ! empty( $data['uid'] ) )
				unset( $data['uid'] );

			// timestamp is required; use current time if missing
			if ( empty( $data['timestamp'] ) )
				$data['timestamp'] = date( date('Y-m-d H:i'), time() );

			// status is required; set to 'pending' by default if missing
			if ( empty( $data['status'] ) )
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

		endif;

		// timestamp is required; use current time if missing or not a unix epoch
		if ( ! empty( $data['timestamp'] ) && ! is_numeric( $data['timestamp'] ) )
			$data['timestamp'] = date( date('Y-m-d H:i'), time() );

		// status is required; set to 'pending' by default if missing or invalid value
		if ( ! empty( $data['status'] ) && !in_array( $data['status'], [ 'approved', 'unapproved', 'pending' ] ) )
			$data['status'] = 'pending';

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
     * @param string $query
     * @param string $sort
     * @return array
     */

    public static function retrieve( $page, string $query = 'approved', string $sort = 'asc' ) {

		// if the query is a comment UID, return only that comment
		if ( !in_array( $query, ['approved','unapproved','pending'] ) && strlen( $query ) == 10 ) :

			foreach( Storage::read( $page ) as $comment ) :
				if ( $comment['uid'] == $query )
					return $comment;
			endforeach;

		else:

			$output = [];
			foreach( Storage::read( $page ) as $comment ) :
				if ( ( $query == $comment['status'] ) || $query == 'all' ) :
					$comment['pageid'] = $page->id();
					$output[] = $comment;
				endif;
			endforeach;

			// default sorting is chronological
			if ( $sort == 'desc' )
				return array_reverse( $output );
			else
				return $output;

		endif;

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
     * Processes the comment form data and stores the comment
     *
     * @param string $path
     */

    public static function processCommentform( $page, $path ) {

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
            'status' => static::defaultstatus( 'comment' ),
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

		// save comment to the according txt file
		return Commentions::add( $page, $data );

    }


    /**
     * Parses webmention from the queue, based on given source and target
     *
     * @param string $source
     * @param string $target
     * @return $array
     */

	public static function processWebmention( $request ) {

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
				'status' => static::defaultstatus( $result['type'] ),
				'name' => $result['author']['name'],
				'website' => $result['author']['url'],
				'avatar' => $result['author']['photo'],
				'text' => $result['text'],
				'timestamp' => date( date('Y-m-d H:i'), $result['timestamp'] ),
				'source' => $source,
				'type' => $result['type'],
				'language' => Commentions::determineLanguage( $path, $page ),
			];

			// save webmention to the according txt file
			return Commentions::add( $page, $finaldata );

		} else {
		
			throw new Exception('Invalid page');
		
		}

	}


}
