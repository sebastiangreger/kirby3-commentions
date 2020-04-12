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
     * Returns the path to the commentions file for a page
     *
     * @param \Kirby\Cms\Page $page
     * @return string
     */
     
    public static function file( $page ) {

		// for real pages, commentions are stored in .commentions.txt in the same folder
		if ( is_dir( $page->root() ) )
			$filepath = $page->root() . DS . '.commentions.txt';

		// for virtual pages, commentions are stored in .commentions-<slug>.txt in the parent folder
		else
			$filepath = $page->parent()->root() . DS . '.commentions-' . $page->slug() . '.txt';

		return $filepath;

	}


    /**
     * Reads data from the commentions file; by default from the comments field
     *
     * @param \Kirby\Cms\Page $page
     * @param string $field
     * @return array
     */
     
    public static function read( $page, string $field = 'comments' ) {

		// read the data and return decoded yaml
		$file = Commentions::file( $page );
		if ( F::exists( $file ) ) :
			$data = Data::read( $file );
			if ( isset( $data[$field] ) )
				return Data::decode( $data[$field], 'yaml' );
			else
				return [];
		else :
			return [];
		endif;

	}


    /**
     * Writes data to the commentions file; by default to the comments field
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data
     * @param string $field
     * @return array
     */
     
    public static function write( $page, array $data, string $field = 'comments' ) {

		$file = Commentions::file( $page );
		if ( F::exists( $file ) ) :
			$fields = Data::read( $file );
			foreach ( $fields as $key => $value ) :
				if ( $key == $field )
					$fields[$key] = Data::encode( $data, 'yaml' );
			endforeach;
			if ( !isset( $fields[$field] ) ) :
				$fields[$field] = Data::encode( $data, 'yaml' );
			endif;
		else :
			$fields[$field] = Data::encode( $data, 'yaml' );
			//F::write( $file, '' )
		endif;

		Data::write( $file, $fields );

	}


    /**
     * Retrieves an array of comments for a given page
     *
     * @param \Kirby\Cms\Page $page
     * @param string $status
     * @return array
     */
     
    public static function retrieve( $page, string $status = 'approved' ) {

		$output = [];
		foreach( Commentions::read( $page ) as $comment ) :
			if ( ( $status == 'approved' && $comment['approved'] == 'true' ) || ( $status == 'pending' && $comment['approved'] == 'false' ) || $status == 'all' ) :
				$comment['pageid'] = $page->id();
				$output[] = $comment;
			endif;
		endforeach;

		return $output;

	}


    /**
     * Adds new entry to the commentions file; by default to the comments field
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data
     * @param string $field
     * @return array
     */
     
    public static function add( $page, $entry = [], $field = 'comments' ) {

		// attach new data set to array of existing comments
		$data = Commentions::read( $page, $field );
		$data[] = $entry;

		// replace the old comments in the yaml data and write it back to the file
		Commentions::write( $page, $data, $field );

	}


    /**
     * Updates or deletes a single entry in the commentions file; by default in the comments field
     *
     * @param \Kirby\Cms\Page $page
     * @param string $commentid
     * @param string|array $data
     * @return array
     */
     
    public static function update( $page, $entryid, $data = [], $field = 'comments' ) {

		// loop through array of all comments
		foreach( Commentions::read( $page, $field ) as $entry ) :

			// find the entry with matching ID
			if ( $entry['timestamp'] == $entryid || strtotime( $entry['timestamp'] ) == $entryid ) :

				// if the data variable is an array, update the fields contained within (default is an empty array, hence no updates)
				if ( is_array( $data ) ) :
					// loop through all new data submitted in array and update accordingly
					foreach( $data as $key => $value ) :
						$entry[ $key ] = $value;
					endforeach;
				endif;

				// if the data variable is not an array but string 'delete', omit this comment from the output array
				if ( $data != 'delete' ) :
					$output[] = $entry;
				endif;

			else :

				// add the unchanged comment to the output array
				$output[] = $entry;

			endif;

		endforeach;

		// replace the old comments in the yaml data and write it back to the file
		Commentions::write( $page, $output, $field );

		return ['ok'];

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
            'timestamp' => date( date('Y-m-d H:i'), time() ),
            'language' => static::determineLanguage( $path, $page ),
            'type' => 'comment',
            'approved' => 'false',
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
	
				// save commention to the according txt file
				Commentions::add( page( $page->id() ), $data );

				// return to the post page and display success message
				go( $page->url() . "?thx=queued" );

			} catch (Exception $e) {

				echo $e->getMessage();

			}

        }
        
    }


    /**
     * Handles the asynchronous processing of webmentions from the queue
     *
     * @return bool
     */

	public static function processQueue() {

		// loop through all pages in the index
		foreach ( site()->index() as $page ) :

			// loop through every webmention request in the queue of every page
			foreach( Commentions::read( $page, 'queue' ) as $queueitem ) :

				// skip requests already marked as failed
				if ( ! isset( $queueitem['failed'] ) ) :
								
					if ( $result = static::parseRequest( $queueitem['source'], $queueitem['target'] ) ) :

						// delete webmention from queue after successful parsing
						if ( is_bool( $result ) ) :
							Commentions::update( $page, $queueitem['timestamp'], 'delete', 'queue' );
							return true;

						else :
							// mark failed requests as failed
							Commentions::update( $page, $queueitem['timestamp'], [ 'failed' => $result ], 'queue' );

						endif;

					else :
				
						throw new Exception( 'Problem processing queue file.' );

					endif;

				endif;

			endforeach;
        endforeach;
        
	}


    /**
     * Parses webmention requests based on given source and target
     *
     * @param string $source
     * @param string $target
     * @return bool
     */

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

			// set comment type
			if ( !isset( $result['type'] ) || $result['type'] == '' || $result['type'] == 'mention' )
				$result['type'] = 'webmention';

			// save as new webmention
			$finaldata = [
				'approved' => false,
				'name' => $result['author']['name'],
				'website' => $result['author']['url'],
				'avatar' => $result['author']['photo'],
				'message' => $result['text'],
				'timestamp' => date( date('Y-m-d H:i'), $result['timestamp'] ),
				'source' => $source,
				'type' => $result['type'],
				'language' => static::determineLanguage( $path, $page ),
			];
			Commentions::add( page( $page->id() ), $finaldata );

			return true;

		} else {
		
			throw new Exception('Invalid page');
		
		}

	}

	public static function queueWebmention() {

		// source is the external site sending the webmention;
		$source = get('source');
		
		// target is the local URL, claimed to be mentioned in the source
		$target = get('target');

		if( !V::url( $source ) )
			throw new Exception( 'Invalid source URL.' );

		if( !V::url( $target ) )
			throw new Exception( 'Invalid target URL.' );

		if( $source == $target )
			throw new Exception( 'Target and source are identical.' );

		if( !Str::contains( $target, str_replace( array( 'http:', 'https:' ), '', site()->url() ) ) )
			throw new Exception( 'Target URL not on this site.' );

		// find the Kirby page the target URL refers to
		$path = Url::path( $target );
		if ( $path == '' )
			$page = page('home');
		else
			$page = page( kirby()->call( trim( $path, '/' ) ) );

		// if the target resolves to an existing Kirby page, add to the queue in the according commention file
		if( $page != null )
			Commentions::add( $page, [ 'target' => $target, 'source' => $source, 'timestamp' => time() ], 'queue' );
		// all other requests are enqueued in the home page commention file
		else
			Commentions::add( page('home'), [ 'target' => $target, 'source' => $source, 'timestamp' => time() ], 'queue' );

	}

}
