<?php

namespace sgkirby\Commentions;

use Exception;
use Kirby\Data\Data;
use Kirby\Toolkit\F;

class Storage
{
    /**
     * Returns the path to the appropriate file for plugin data (incl. for virtual pages)
     *
     * @param \Kirby\Cms\Page $page
     * @param string $filename The file name, without the .yml ending (commonly either 'commentions' or 'webmentionqueue')
     * @return string The full path to the YAML file
     */
    public static function file($page, string $filename)
    {
        // name conventions
        $path = '';
        $foldername = '_commentions';

        // create a path for virtual pages
        while (! is_dir($page->root())) {

            // prepend the slug of the current virtual page to the path variable
            $path = '/' . $page->slug() . $path;

            // move up one level and repeat
            $page = $page->parent();
        }

        // commentions are stored in _commentions.txt file (in a subfolder, if virtual page)
        return $page->root() . '/' . $foldername . $path . '/' . $filename . '.yml';
    }

    /**
     * Reads data from the plugin data file
     *
     * @param \Kirby\Cms\Page $page
     * @param string $filename The file name, without the .yml ending (commonly either 'commentions' or 'webmentionqueue')
     * @return array Multidimensional array, containing all data entries with their fields from the file
     */
    public static function read($page, string $filename)
    {
        // read the data and return decoded yaml
        $file = static::file($page, $filename);

        if (F::exists($file) === true) {
            return Data::read($file);
        }

        return [];
    }

    /**
     * Get the modified date of the plugin data file
     *
     * @param \Kirby\Cms\Page $page
     * @param string $filename The file name, without the .yml ending (commonly either 'commentions' or 'webmentionqueue')
     * @return int|false Modified date as UNIX timestamp, if the data file exists or boolean `false`, if it does not
     */
    public static function modified($page, string $filename)
    {
        $file = static::file($page, $filename);

        if (F::exists($file) === true) {
            return F::modified($file);
        }

        return false;
    }

    /**
     * Writes data to the plugin data file (replacing the current data in the file)
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data The complete data array to be written into the file
     * @param string $filename The file name, without the .yml ending (commonly either 'commentions' or 'webmentionqueue')
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
     * Adds a new entry to the plugin data file
     *
     * @param \Kirby\Cms\Page $page
     * @param array $data The array with the fields for the new entry
     * @param string $filename The file name, without the .yml ending (commonly either 'commentions' or 'webmentionqueue')
     * @return array
     */
    public static function add($page, array $entry, string $filename)
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
     * @param string $commentid The UID for the entry to be replaced
     * @param array|string $data Depending on the action to be carried out:
     *                           - Array: The fields of the entry that are to be replaced
     *                           - String: A predefined action (currently only valid: 'delete', to delete the complete entry)
     * @param string $filename The file name, without the .yml ending (commonly either 'commentions' or 'webmentionqueue')
     * @return array|bool Array with updated data on successful update, true on successful delete, false on failure
     */
    public static function update($page, $uid, $data, $filename)
    {
        // loop through array of all comments
        $output = [];
        foreach (static::read($page, $filename) as $entry) {

            // find the entry with matching ID
            if ($entry['uid'] == $uid) {

                // if the data variable is an array, update the fields contained within (default is an empty array, hence no updates)
                if (is_array($data)) {
                    if ($filename === 'commentions') {
                        // depending on the entry type, certain fields can not be deleted
                        if ($entry['type'] === 'comment') {
                            $required = ['uid', 'timestamp', 'status', 'type', 'text'];
                        } else {
                            $required = ['uid', 'timestamp', 'status', 'type', 'source'];
                        }
                    } elseif ($filename === 'webmentionqueue') {
                        $required = ['uid', 'timestamp', 'target', 'source'];
                    }

                    // loop through all new data submitted in array and update accordingly
                    foreach ($data as $key => $value) {
                        // if $value is empty, this means a deletion; some fields cannot be deleted (partially depending on the entry type)
                        if (empty($value) && array_key_exists($key, $entry) && !in_array($key, $required)) {
                            unset($entry[$key]);
                        // update the existing value with the new value
                        } else {
                            $entry[$key] = $value;
                        }
                    }
                }

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
