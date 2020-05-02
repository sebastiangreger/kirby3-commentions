<?php

namespace sgkirby\Commentions;

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

        'errors' => function () {

            $errors = [];

            if (is_dir(kirby()->root() . DS . 'content' . DS . '.commentions') === true) {
                $errors[] = [
                    'id'      => 'storage-version',
                    'message' => t('commentions.section.error.storage-version'),
                    'theme'   => 'negative',
                ];
            }

            if (class_exists('Masterminds\\HTML5') === false || Formatter::advancedFormattingAvailable() === false) {
                $errors[] = [
                    'id' => 'missing-dependencies',
                    'message' => t('commentions.section.error.missing-dependencies'),
                    'theme' => 'info',
                ];
            }

            return $errors;
        },

        'dependenciesError' => function () {
            return ;
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

            if ($this->flip()) {
                // display commentions newest first, unless flip option is true
                $commentions = $commentions->flip();
            }

            // JavaScript needs a zero-based index for native array,
            // while `$commentions->toArray()` uses UIDs as keys.
            return array_values($commentions->toArray());
        }

    ],

];
