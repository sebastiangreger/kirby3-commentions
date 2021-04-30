<?php

namespace sgkirby\Commentions;

use Kirby\Toolkit\F;

return [

    'props' => [

        'show' => function ($show = 'page') {
            if (! in_array($show, [ 'page', 'pending', 'all' ])) {
                $show = 'page';
            }
            return $show;
        },

        'flip' => function ($flip = false) {
            return $flip;
        },

        'limit' => function ($limit = 20) {
            return $limit;
        },

        'empty' => function ($empty = null) {
            if ($empty === null) {
                if ($this->show() == 'pending') {
                    $empty = t('commentions.section.empty.pending');
                } else {
                    $empty = t('commentions.section.empty.default');
                }
            }
            return $empty;
        },

        'headline' => function ($headline = null) {
            if ($headline === null) {
                if ($this->show() == 'pending') {
                    $headline = t('commentions.section.headline.pending');
                } elseif ($this->show() == 'all') {
                    $headline = t('commentions.section.headline.all');
                } else {
                    $headline = t('commentions.section.headline.default');
                }
            }
            return $headline;
        },

    ],

    'computed' => [

        'commentionsSystemErrors' => function () {

            $errors = [];

            if (Commentions::accepted($this->model(), 'webmentions')) {
                $logfile = kirby()->root('site') . DS . 'logs' . DS . 'commentions' . DS . 'lastcron.log';
                if (!F::exists($logfile) || F::modified($logfile) < (time() - 86400)) {
                    $errors[] = [
                        'id'      => 'cronjob-alert',
                        'message' => t('commentions.section.error.cronjob-alert'),
                        'theme'   => 'negative',
                    ];
                }
            }

            if (is_dir(kirby()->root() . DS . 'content' . DS . '.commentions') === true) {
                $errors[] = [
                    'id'      => 'storage-version',
                    'message' => t('commentions.section.error.storage-version'),
                    'theme'   => 'negative',
                ];
            }

            if (class_exists('Masterminds\\HTML5') === false || Formatter::available() === false) {
                $errors[] = [
                    'id' => 'missing-dependencies',
                    'message' => t('commentions.section.error.missing-dependencies'),
                    'theme' => 'info',
                ];
            }

            if (option('sgkirby.commentions.templatesWithComments') === null || option('sgkirby.commentions.templatesWithWebmentions') === null) {
                $errors[] = [
                    'id' => 'no-templates-defined',
                    'message' => t('commentions.section.error.no-templates-defined'),
                    'theme' => 'negative',
                ];
            }

            return $errors;
        },

        'pageId' => function () {
            return $this->model()->id();
        },

        'customFields' => function () {
            // retrieve list of fields for this page minus the standard fields
            $customfields = array_diff_key(Commentions::fields($this->model()), array_flip(['name','email','website','text','honeypot','commentions']));
            // reduce to an array only containing id and type
            foreach($customfields as $customfield) {
                $return[] = [
                    'id' => $customfield['id'],
                    'type' => $customfield['type'],
                ];
            }
            return $return;
        },

        'commentions' => function (): array {
            // retrieve the show property
            switch ($this->show()) {
                case 'all':
                case 'pending':
                    $commentions = site()->index()->commentions($this->show());
                    break;
                default:
                    $commentions = $this->model()->commentions('all');
                    break;
            }

            if (!$this->flip()) {
                // display commentions newest first, unless flip option is true
                $commentions = $commentions->flip();
            }

            // JavaScript needs a zero-based index for native array,
            // while `$commentions->toArray()` uses UIDs as keys.
            return array_values($commentions->toArray());
        },

        'pageSettings' => function () {
            // page settings only apply on page-specific listings
            if($this->show() !== 'page') {
                return [];
            }

            // retrieve saved values (if exist) and loop to create the array for vue
            $stored = Commentions::pageSettings($this->model());
            $array = [
                'acceptComments' => [
                    'comments',
                    !in_array($this->model()->intendedTemplate(), (option('sgkirby.commentions.templatesWithComments') ?? [])),
                ],
                'acceptWebmentions' => [
                    'webmentions',
                    !in_array($this->model()->intendedTemplate(), (option('sgkirby.commentions.templatesWithWebmentions') ?? [])),
                ],
                'display' => [
                    'display',
                    false,
                ],
            ];
            foreach ($array as $k => $v) {
                $disabled = $v[1];
                $settings[$k] = [
                    'id' => $k,
                    'text' => [
                        $disabled ? t('commentions.section.setting.disabledInConfig') : t('commentions.section.setting.' . $v[0] . '.true'),
                        $disabled ? t('commentions.section.setting.disabledInConfig') : t('commentions.section.setting.' . $v[0] . '.false'),
                    ],
                    'value' => $disabled ? false : $stored[$k],
                    'disabled' => $disabled,
                ];
            }
            return $settings;
        },

    ],

];
