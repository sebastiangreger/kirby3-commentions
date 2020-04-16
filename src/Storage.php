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

		// name conventions
		$path = '';
		$name = '_commentions';

		// create a path for virtual pages
		while ( ! is_dir( $page->root() ) ):

			// prepend the slug of the current virtual page to the path variable
			$path = DS . $page->slug() . $path;

			// move up one level and repeat
			$page = $page->parent();

		endwhile;

		// commentions are stored in _commentions.txt file (in a subfolder, if virtual page)
		return $page->root() . ( $path != '' ? DS . $name . $path : '' ) . DS . $name . '.txt';

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
		$file = static::file( $page );
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

		// get the file path
		$file = static::file( $page );

		if ( F::exists( $file ) ) :

			// read all the fields stored in the text file (e.g. 'comments' and 'queue')
			$fields = Data::read( $file );

			// if the targeted field already exists, replace its data with the new array
			foreach ( $fields as $key => $value ) :
				if ( $key == $field )
					$fields[$key] = Data::encode( $data, 'yaml' );
			endforeach;

			// if the targeted field does not yet exist, add it to the $fields array
			if ( !isset( $fields[$field] ) ) :
				$fields[$field] = Data::encode( $data, 'yaml' );
			endif;

		else :

			// create a fields array from scratch
			$fields[$field] = Data::encode( $data, 'yaml' );

		endif;

		// write the fields array to the text file
		try {
			Data::write( $file, $fields );
			return true;
		} catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}


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
		$data = static::read( $page, $field );
		ksort( $entry );
		$data[] = $entry;

		// replace the old comments in the yaml data and write it back to the file
		static::write( $page, $data, $field );

		return $entry;

	}


    /**
     * Updates or deletes a single entry in the commentions file; by default in the comments field
     *
     * @param \Kirby\Cms\Page $page
     * @param string $commentid
     * @param string|array $data
     * @return array
     */
     
    public static function update( $page, $uid, $data = [], $field = 'comments' ) {

		// clean up the data if it is an array (skip for string, which would be a command like 'delete')
		if ( $field == 'comments' && is_array( $data ) && !empty( $data ) ) :

			// sanitize data array, but keep the uid
			$data = Commentions::sanitize( $data, true );

		endif;

		// loop through array of all comments
		$output = [];
		foreach( static::read( $page, $field ) as $entry ) :

			// find the entry with matching ID
			if ( $entry['uid'] == $uid ) :

				// if the data variable is an array, update the fields contained within (default is an empty array, hence no updates)
				if ( is_array( $data ) ) :
					// loop through all new data submitted in array and update accordingly
					foreach( $data as $key => $value ) :
						$entry[ $key ] = $value;
					endforeach;
				endif;

				if ( $data == 'delete' ) :

					// on deletion, simply return true on success
					$return = true;

				else:

					// sort fields alphabetically for consistency
					ksort( $entry );

					// add this field to the array to be written to the file
					$output[] = $entry;

					// keep the values of the changed entry for returning
					$return = $entry;

				endif;

			else :

				// add the unchanged comment to the output array
				$output[] = $entry;

			endif;

		endforeach;

		// replace the old comments in the yaml data and write it back to the file
		static::write( $page, $output, $field );

		return $return ?? false;

	}


}
