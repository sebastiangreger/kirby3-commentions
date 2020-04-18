<?php

namespace sgkirby\Commentions;

use Exception;
use Kirby\Data\Data;
use Kirby\Data\Yaml;
use Kirby\Toolkit\F;

class Storage
{
    /**
     * Returns the path to the commentions file for a page
     *
     * @param \Kirby\Cms\Page $page
     * @return string
     */
    public static function file($page, string $filename)
    {

        // name conventions
        $path = '';
        $foldername = '_commentions';

        // create a path for virtual pages
        while (! is_dir($page->root())) {

            // prepend the slug of the current virtual page to the path variable
            $path = DS . $page->slug() . $path;

            // move up one level and repeat
            $page = $page->parent();
        }

        // commentions are stored in _commentions.txt file (in a subfolder, if virtual page)
        return $page->root() . DS . $foldername . $path . DS . $filename . '.yml';
    }


    /**
     * Reads data from the commentions folder
     *
     * @param \Kirby\Cms\Page $page
     * @param string $filename
     * @return array
     */
    public static function read($page, string $filename)
    {

        // read the data and return decoded yaml
        $file = static::file($page, $filename);
        if (F::exists($file)) {
            return Data::read($file);
        } else {
            return [];
        }
    }


    /**
     * Writes data to the commentions folder
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data
     * @param string $filename
     * @return array
     */
    public static function write($page, array $data, string $filename)
    {

        // get the file path
        $file = static::file($page, $filename);

        // write the fields array to the text file
        try {
            Data::write($file, $data);
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }


    /**
     * Adds new entry to the commentions file
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data
     * @param string $filename
     * @return array
     */
    public static function add($page, $entry = [], string $filename)
    {

        // attach new data set to array of existing comments
        $data = static::read($page, $filename);
        ksort($entry);
        $data[] = $entry;

        // replace the old comments in the yaml data and write it back to the file
        static::write($page, $data, $filename);

        return $entry;
    }


    /**
     * Updates or deletes a single entry in the commentions file; by default in the comments file
     *
     * @param \Kirby\Cms\Page $page
     * @param string $commentid
     * @param string|array $data
     * @return array
     */
    public static function update($page, $uid, $data = [], $filename = 'commentions')
    {

        // clean up the data if it is an array (skip for string, which would be a command like 'delete')
        if ($filename == 'comments' && is_array($data) && !empty($data)) {

            // sanitize data array, but keep the uid
            $data = Commentions::sanitize($data, true);
        }

        // loop through array of all comments
        $output = [];
        foreach (static::read($page, $filename) as $entry) {

            // find the entry with matching ID
            if ($entry['uid'] == $uid) {

                // if the data variable is an array, update the fields contained within (default is an empty array, hence no updates)
                if (is_array($data)) :
                    // loop through all new data submitted in array and update accordingly
                    foreach ($data as $key => $value) :
                        $entry[ $key ] = $value;
                endforeach;
                endif;

                if ($data == 'delete') {

                    // on deletion, simply return true on success
                    $return = true;
                } else {

                    // sort fields alphabetically for consistency
                    ksort($entry);

                    // add this field to the array to be written to the file
                    $output[] = $entry;

                    // keep the values of the changed entry for returning
                    $return = $entry;
                }
            } else {

                // add the unchanged comment to the output array
                $output[] = $entry;
            }
        }

        // replace the old comments in the yaml data and write it back to the file
        static::write($page, $output, $filename);

        return $return ?? false;
    }
}
