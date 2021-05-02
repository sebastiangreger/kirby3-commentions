<?php

namespace sgkirby\Commentions;

use Kirby\Cms\Collection;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\V;

class Frontend
{
    /**
     * Called by the frontend helper, echoes the HTML output
     */
    public static function render($template = null, $attrs = null)
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
                    snippet($snippetprefix . 'feedback', array_merge(Commentions::$feedback, ['attrs' => $attrs]));
                }
                break;

            // display comment form
            case 'form':
                if (!get('thx') || (isset($attrs['keepvisible']) && $attrs['keepvisible'] === true)) {
                    $fields = Fields::configuration(page());

                    // LEGACY: until v1.0.4, the `text` field was `message`; overriding this for compatibility if snippets present in old snippet location
                    if (array_key_exists('text', $fields) && F::exists(kirby()->root('snippets') . DS . 'commentions-form.php')) {
                        $keys = array_keys($fields);
                        $keys[array_search('text', $keys)] = 'message';
                        $fields = array_combine($keys, $fields);
                        $fields['message']['id'] = 'message';
                    }

                    // loop through all configured fields to adjust frontend output
                    foreach ($fields as $fieldname => $dfn) {

                        // check if an error is present for this field
                        if (array_key_exists($dfn['id'], Commentions::$feedback['invalid'] ?? [])) {
                            // add the error message to field
                            $fields[$fieldname]['error'] = Commentions::$feedback['invalid'][$dfn['id']];
                            // make sure the form is displayed open regardless of collapse setting
                            $attrs['open'] = true;
                            // add autofocus attribute to the first field with an error
                            if (empty($errorcount)) {
                                $fields[$fieldname]['autofocus'] = 'autofocus';
                                $errorcount = $errorcount ?? 0 + 1;
                            }
                        }

                        // fill field with any sanitized values already entered by the user
                        if(get('submit') && !in_array($fieldname, ['commentions'])) {
                            $fields[$fieldname]['value'] = htmlspecialchars(get($dfn['id']));
                        }

                        // backend fields must not be displayed
                        if ($dfn['type'] == 'backend') {
                            unset($fields[$fieldname]);
                        }
                    }

                    // by default the form is always set to novalidate; only overriden by explicit novalidate=false in attrs
                    if ($attrs['novalidate'] ?? true !== false) {
                        $attrs['novalidate'] = true;
                    }

                    // if set in attrs, forward the jump anchors to the backend via hidden field
                    if (!empty($attrs['jump']) || !empty($attrs['jump-success']) ) {
                        $fields['jump'] = [
                            'id' => 'commentions-jump',
                            'type' => 'hidden',
                            'value' => ($attrs['jump-success'] ?? $attrs['jump']),
                        ];
                    }
                    $attrs['jump'] = $attrs['jump-error'] ?? $attrs['jump'] ?? null;

                    snippet('commentions-form', [
                        'fields' => $fields,
                        'attrs'  => $attrs,
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
                    if (!empty($attrs['grouped'])) {

                    // array of all groups to be pulled out from content list,
                        // in presentation order
                        $groups = $attrs['grouped'] ?? [
                            'read'            => 'Read by',
                            'like'            => 'Likes',
                            'repost'          => 'Reposts',
                            'bookmark'        => 'Bookmarks',
                            'rsvp:yes'        => 'RSVP: yes',
                            'rsvp:maybe'      => 'RSVP: maybe',
                            'rsvp:interested' => 'RSVP: interested',
                            'rsvp:no'         => 'RSVP: no',
                        ];

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
                // id and class attrs cannot be set with the shorthand helper
                if (array_key_exists('class', $attrs)) {
                    unset($attrs['class']);
                }
                if (array_key_exists('id', $attrs)) {
                    unset($attrs['id']);
                }

                // call each helper for this shorthand separately
                commentions('feedback', $attrs);
                commentions('form', $attrs);
                commentions('list', $attrs);
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
            Commentions::$feedback = [
                'alert'     => ['This page does not accept comments.'],
            ];
            return false;
        }

        // retrieve the settings array of allowed fields
        $fieldsetup = Fields::configuration($page);

        // merge validation arrays for use with invalid() helper
        foreach($fieldsetup as $field => $dfn) {
            if(!in_array($field, ['commentions','honeypot'])) {
                // process the correct field for website, not the honeypot
                if ($field == 'website') {
                    $field = 'realwebsite';
                }
                if (isset($dfn['validate']) && (isset($dfn['required']) || !empty(get($field)))) {
                    $rules[$field] = $dfn['validate']['rules'];
                    $messages[$field] = $dfn['validate']['message'];
                }
            }
        }

        // retrieve submitted data and do some cleanup
        $formdata = get();
        if(isset($formdata['realwebsite']) && !V::url($formdata['realwebsite']) && !strpos('://', $formdata['realwebsite'])) {
            $formdata['realwebsite'] = 'https://' . $formdata['realwebsite'];
        }

        // run validation and return error array if validation fails
        if (isset($rules) && $invalid = invalid($formdata, $rules, $messages)) {
            Commentions::$feedback = [
                'alert'     => ['There are errors in your form'],
                'invalid'   => $invalid,
            ];
            return false;
        }

        // assemble the commention data
        $data = [
            'name' => (array_key_exists('name', $fieldsetup)) ? $formdata['name'] : null,
            'email' => (array_key_exists('email', $fieldsetup)) ? $formdata['email'] : null,
            'website' => (array_key_exists('website', $fieldsetup)) ? $formdata['realwebsite'] : null,
            // LEGACY: until v1.0.4, the `text` field was `message`; keeping this check for compatibility with older customized snippets
            'text' => !empty($formdata['message']) ? $formdata['message'] : $formdata['text'],
            'timestamp' => date(date('Y-m-d H:i'), time()),
            'language' => Commentions::determineLanguage($page, $path),
            'type' => 'comment',
            'status' => Commentions::defaultstatus('comment'),
        ];

        // add custom fields submitted
        foreach($fieldsetup as $fieldname => $fieldvalue) {
            if(!in_array($fieldname, ['name', 'email', 'website', 'realwebsite', 'text', 'commentions', 'honeypot'])) {
                $data[$fieldname] = get($fieldname);
            }
        }

        // run a spam check
        $spam = Commentions::spamcheck($data, kirby()->request()->get());
        if ($spam === true) {
            go($page->url());
            exit;
        }

        // save comment to the according txt file
        return Commentions::add($page, $data);
    }
}
