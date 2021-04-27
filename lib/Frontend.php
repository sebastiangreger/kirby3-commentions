<?php

namespace sgkirby\Commentions;

use Kirby\Cms\Collection;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Obj;

class Frontend
{
    /**
     * Called by the frontend helper, echoes the HTML output
     */
    public static function render($template = null)
    {
        // checks if custom snippets exist in a subfolder and sets snippet accordingly
        if (Dir::exists(kirby()->root('snippets') . DS . 'commentions')) {
            $snippetprefix = 'commentions/';
        } else {
            // DEPRECATED: snippets were not in a separate folder until v1.0
            $snippetprefix = 'commentions-';
        }

        switch ($template) {

            // output html head tag for default style sheet
            case 'css':
                echo css('media/plugins/sgkirby/commentions/styles.css');
                break;

            // output html head tags for webmention endpoint discovery
            case 'endpoints':
                // display whenever any template is allowlisted for webmentions (regardless of current page; needed for discovery)
                if (is_array(option('sgkirby.commentions.templatesWithWebmentions')) && sizeof(option('sgkirby.commentions.templatesWithWebmentions')) > 0) {
                    $endpoint = kirby()->urls()->base() . '/' . option('sgkirby.commentions.endpoint');
                    echo '
                        <link rel="webmention" href="' . $endpoint . '" />
                        <link rel="http://webmention.org/" href="' . $endpoint . '" />
                    ';
                    break;
                }

            // display ui feedback after form submission
            case 'feedback':
                if (isset(Commentions::$feedback)) {
                    snippet($snippetprefix . 'feedback', Commentions::$feedback);
                }
                break;

            // display comment form
            case 'form':
                if (!get('thx')) {
                    // LEGACY: until v1.0.4, the `text` field was `message`; overriding this for compatibility if snippets present in old snippet location
                    $fields = Commentions::fields(page());
                    if (array_key_exists('text', $fields) && F::exists(kirby()->root('snippets') . DS . 'commentions-form.php')) {
                        $keys = array_keys($fields);
                        $keys[array_search('text', $keys)] = 'message';
                        $fields = array_combine($keys, $fields);
                        $fields['message']['id'] = 'message';
                    }

                    snippet('commentions-form', [
                        'fields' => $fields,
                    ]);
                }
                break;

            case 'help':
                snippet($snippetprefix . 'help', [
                    'formattingEnabled' => Formatter::available(),
                    'allowlinks' => option('sgkirby.commentions.allowlinks'),
                    'autolinks' => option('sgkirby.commentions.autolinks'),
                ]);
                break;

            // display comments
            case 'list':
            case 'grouped':
            case 'raw':

                // retrieve all approved comments for this page
                $commentions = page()->commentions('approved', 'auto');
                $comments  = $commentions->clone();
                $reactions = new Collection();

                // DEPRECATED as of 1.0.0: use $page->comments() instead
                if ($template == 'raw') {
                    // return an array with all comments for this page
                    return $commentions;
                }

                elseif ($commentions->count() > 0 && Commentions::pageSettings(page(), 'display')) {

                    // restructure the data if grouped view
                    if ($template == 'grouped') {

                    // array of all groups to be pulled out from content list,
                        // in presentation order
                        $groups = option('sgkirby.commentions.grouped');

                        foreach ($groups as $type => $label) {
                            $groupReactions = $commentions->filterBy('type', $type);
                            if ($groupReactions->count() === 0) {
                                // skip empty groups
                                continue;
                            }

                            $reactions->add(new Obj([
                                'id' => $type,
                                'label' => $label,
                                'items' => $groupReactions,
                            ]));
                        }

                        // replace the original comments array with a filtered one, that
                        // does only contain everything, that has been excluded from
                        // the grouped view
                        $comments = $commentions->filterBy('type', 'not in', array_keys($groups));
                    }

                    // return selected markup
                    snippet($snippetprefix . 'list', [
                        'comments' => $comments,
                        'reactions' => $reactions,
                    ]);
                }
                break;

            default:
                commentions('feedback');
                commentions('form');
                commentions('list');
        }
    }

    /**
     * Processes the comment form data and stores the comment
     *
     * @param \Kirby\Cms\Page $page The parent page object
     * @param string $path The path from the Kirby router (needed to determine language)
     * @return array Content depends on success
     *               - On success: The complete comment data (incl. UID) as returned by the Storage class after saving
     *               - On error: array with single 'alert' field contains the error message
     */
    public static function processCommentform($page, $path)
    {
        // bounce submissions to pages not allowlisted for comments
        if(!Commentions::accepted($page, 'comments')) {
            go($page->url());
            exit;
        }

        // retrieve the settings array of allowed fields
        $fieldsetup = Commentions::fields($page);

        // assemble the commention data
        $data = [
            'name' => (array_key_exists('name', $fieldsetup)) ? get('name') : null,
            'email' => (array_key_exists('email', $fieldsetup)) ? get('email') : null,
            'website' => (array_key_exists('website', $fieldsetup)) ? get('realwebsite') : null,
            // LEGACY: until v1.0.4, the `text` field was `message`; keeping this check for compatibility with older customized snippets
            'text' => !empty(get('message')) ? get('message') : get('text'),
            'timestamp' => date(date('Y-m-d H:i'), time()),
            'language' => Commentions::determineLanguage($page, $path),
            'type' => 'comment',
            'status' => Commentions::defaultstatus('comment'),
        ];

        // discover any custom fields submitted and store them in the custom fields array
        foreach($fieldsetup as $fieldname => $fieldvalue) {
            if(!in_array($fieldname, ['name','email','website','realwebsite','text','commentions','honeypot'])) {
                $data['custom'][$fieldname] = get($fieldname);
            }
        }

        // run a spam check
        $spam = Commentions::spamcheck($data, kirby()->request()->get());
        if ($spam === true) {
            go($page->url());
            exit;
        }

        // verify field rules
        $rules = [
            'text' => ['required', 'min' => 4, 'max' => 4096],
        ];
        $messages = [
            'text' => 'Please enter a text between 4 and 4096 characters'
        ];
        if ($invalid = invalid($data, $rules, $messages)) {
            Commentions::$feedback = $invalid;
            return [
                'alert' => $invalid,
            ];
        }

        // save comment to the according txt file
        return Commentions::add($page, $data);
    }
}
