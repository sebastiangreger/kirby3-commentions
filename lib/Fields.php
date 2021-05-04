<?php

namespace sgkirby\Commentions;

class Fields
{
    /**
     * Returns the data field setup for a page
     *
     * @param \Kirby\Cms\Page $page The page object
     * @param string $type The type of comment ('webmention' or 'comment')
     * @return array Complete array of valid field names (keys) and their setup (value array)
     */
    public static function configuration($page, string $type = "comment")
    {
        // retrieve setup from config
        $fieldsetup = option('sgkirby.commentions.' . $type . 'fields');

        // the config variable might contain a callable function for advanced setup
        if (is_callable($fieldsetup)) {
            $fieldsetup = $fieldsetup($page);
        }

        // if the setup is not a valid array, fall back to empty array
        if (!is_array($fieldsetup)) {
            $fieldsetup = [];
        }

        if ($type == 'comment') {
            // fallback defaults for standard fields
            $fielddefaults = [
                'name' => [
                    'type'          => 'text',
                    'autocomplete'  => 'name',
                ],
                'email' => [
                    'type'          => 'email',
                    'autocomplete'  => 'email',
                    'validate'      => [
                        'rules'         => ['email'],
                        'message'       => t('commentions.snippet.form.email.error'),
                    ],
                ],
                'website' => [
                    'type'          => 'url',
                    'autocomplete'  => 'url',
                    'validate'      => [
                        'rules'         => ['url'],
                        'message'       => t('commentions.snippet.form.website.error'),
                    ],
                ],
                'text' => [
                    'type' => 'textarea',
                    'required'  => true,
                    'validate'      => [
                        'rules'         => ['required', 'min' => 2],
                        'message'       => t('commentions.snippet.form.comment.error'),
                    ],
                ],
            ];

            // loop through all fields
            foreach ($fieldsetup as $field => $dfn) {
                // if only an array of strings is given, the value is the key
                if (!is_string($field)) {
                    $field = $dfn;
                    $dfn = $fielddefaults[$field]['required'] ?? false;
                }

                // if no config array is given as value, it is implied with defaults
                if (!is_array($dfn)) {
                    $dfn = [
                        'type'      => $fielddefaults[$field]['type'] ?? 'text',
                        'required'  => $dfn === true ? $dfn : ($fielddefaults[$field]['required'] ?? false),
                    ];
                }

                // at this point we have normalized the dfn variables and can start interpreting
                $fields[$field]['id'] = $field;
                // alter id for website field for honeypot use
                if ($field === 'website') {
                    $fields[$field]['id'] = 'realwebsite';
                }

                // if array contains no field type, use defaults
                $allowedtypes = ['text','email','url','textarea','hidden','backend'];
                if (!isset($dfn['type']) || !in_array($dfn['type'], $allowedtypes)) {
                    $dfn['type'] = $fielddefaults[$field]['type'] ?? 'text';
                }
                $fields[$field]['type'] = $dfn['type'];

                // if array contains no validation rules, use defaults
                if (!isset($dfn['validate'])) {
                    $dfn['validate'] = $fielddefaults[$field]['validate'] ?? null;
                }
                $fields[$field]['validate'] = $dfn['validate'] ?? null;

                // the text field is always required, this must not be overridden
                if ($field == 'text') {
                    $dfn['required'] = true;
                }

                // add validation boolean if validation rule indicates required
                if (isset($dfn['validate']['rules']) && in_array('required', $dfn['validate']['rules'])) {
                    $dfn['required'] = true;
                }
                // add validation rule if required boolean present, but no validation rule
                elseif (isset($dfn['required']) && $dfn['required'] === true && (!isset($dfn['validate']['rules']) || !in_array('required', $dfn['validate']['rules']))) {
                    $fields[$field]['validate']['rules'] = array_merge(['required'], $dfn['validate']['rules'] ?? []);
                    if (empty($fields[$field]['validate']['message'])) {
                        $fields[$field]['validate']['message'] = 'This field is required';
                    }
                }

                // if array contains no label, use defaults; add required text if applicable
                if (!isset($dfn['label'])) {
                    $dfn['label'] = t('commentions.snippet.form.' . $field . (!$dfn ? '.optional' : ''), $field);
                }
                $fields[$field]['label'] = $dfn['label'] . (!empty($dfn['required']) ? ' <abbr title="' . t('commentions.snippet.form.required') . '">*</abbr>' : '');

                // render additional attributes
                $allowedattributes = [
                    'required'      => 'required',
                    'autocomplete'  => 'on',
                    'placeholder'   => null,
                    'value'         => null,
                ];
                foreach ($allowedattributes as $attr => $truestring) {
                    // get the default if no value is given in config
                    if (!isset($dfn[$attr])) {
                        $dfn[$attr] = $fielddefaults[$field][$attr] ?? null;
                    }
                    // true translates into specific default value for some attributes
                    if ($truestring && $dfn[$attr] === true) {
                        $dfn[$attr] = $truestring;
                    }
                    if (!empty($dfn[$attr])) {
                        $fields[$field][$attr] = $dfn[$attr];
                    }
                }
            }

            // add the honeypot field if active
            if (array_key_exists('website', $fields) && in_array('honeypot', option('sgkirby.commentions.spamprotection'))) {
                $fields['honeypot'] = [
                    'id'            => 'website',
                    'required'      => false,
                    'autocomplete'  => 'url',
                    'label'         => t('commentions.snippet.form.honeypot'),
                    'type'          => 'url',
                ];
            }

            // add the compulsory message field, if not already included in the earlier array setup
            if (!array_key_exists('text', $fields)) {
                $fields['text'] = [
                    'id'        => 'text',
                    'required'  => 'required',
                    'label'     => t('commentions.snippet.form.comment') . ' <abbr title="' . t('commentions.snippet.form.required') . '">*</abbr>',
                    'type'      => $fielddefaults['text']['type'],
                    'validate'  => $fielddefaults['text']['validate'],
                ];
            }

            // add the hidden timestamp field for spam control
            $fields['commentions'] = [
                'id'        => 'commentions',
                'required'  => false,
                'type'      => 'hidden',
                'value'     => !$page->isCacheable() ? time() : 0,
            ];
        }

        elseif ($type == 'webmention' && is_array($fieldsetup)) {
            // loop through all fields
            foreach ((array)$fieldsetup as $k => $v) {
                if (is_string($k)) {
                    $fields[$k] = [
                        'required' => $v,
                    ];
                } else {
                    $fields[$v] = [
                        'required' => false,
                    ];
                }
            }
        }

        else {
            return false;
        }

        // return fields array
        return $fields;
    }
}
