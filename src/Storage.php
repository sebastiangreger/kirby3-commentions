<?php

namespace sgkirby\Commentions;

use Kirby\Data\Data;
use Kirby\Data\Yaml;
use Kirby\Toolkit\F;
use Exception;

class Storage {


    /**
     * Returns the path to the commentions file for a page
     *
     * @param \Kirby\Cms\Page $page
     * @return string
     */
     
    public static function file( $page ) {

		// name convention
		$filename = '_commentions';

		// for real pages, commentions are stored in _commentions.txt in the same folder
		if ( is_dir( $page->root() ) )
			$filepath = $page->root() . DS . $filename . '.txt';

		// for virtual pages, commentions are stored in _commentions-<slug>.txt in the parent folder
		else
			$filepath = $page->parent()->root() . DS . $filename . '-' . $page->slug() . '.txt';

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
		$file = Storage::file( $page );
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

		$file = Storage::file( $page );
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
     * Adds new entry to the commentions file; by default to the comments field
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data
     * @param string $field
     * @return array
     */
     
    public static function add( $page, $entry = [], $field = 'comments' ) {

		// attach new data set to array of existing comments
		$data = Storage::read( $page, $field );
		$data[] = $entry;

		// replace the old comments in the yaml data and write it back to the file
		Storage::write( $page, $data, $field );

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
		foreach( Storage::read( $page, $field ) as $entry ) :

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
		Storage::write( $page, $output, $field );

		return ['ok'];

	}


}
