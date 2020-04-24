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

        'error' => function () {
            if (is_dir(kirby()->root() . DS . 'content' . DS . '.commentions')) {
                return 'version';
            } else {
                return false;
            }
        },

        'commentions' => function () {

            // display comments newest first, unless flip option is true
            $sort = $this->flip() ? 'asc' : 'desc';

            // retrieve the show property
            switch ($this->show()) {
                case 'all':
                case 'pending':
                    $comments = site()->index()->commentions($this->show());
                    break;
                default:
                    $comments = $this->model()->commentions('all');
                    break;
            }

            if ($this->flip()) {
                $comments = $comments->flip();
            }

            $comments = $comments->toArray();

            // transpose all comments into an array
            foreach ($comments as $data) {
                $text = isset($data['text']) ? htmlspecialchars($data['text']) : '';
                $name = isset($data['name']) ? htmlspecialchars($data['name']) : t('commentions.name.anonymous');
                $meta = $data['type'];

                // avoid returning empty array entries when no commentions exist
                if (!empty($meta)) {
                    $content =
                        strtoupper($meta)
                        . (!empty($data['language']) ? ' [' . $data['language'] . ']' : '')
                        . ': ' . $name . ' ('
                        . date($data['timestamp']) . ")\n"
                        . (!empty($data['source']) ? $data['source'] . "\n" : '')
                        . (empty($data['source']) && !empty($data['website']) ? $data['website'] . "\n" : '')
                        . ($text != '' ? "\n" . $text : '');

                    $options = [];

                    // appearance and dropdown options depend on comment status
                    if ($data['status'] == 'approved') {
                        $class = 'k-list-item-commention-approved';
                        $icon = [ 'type' => 'chat', 'back' => 'transparent' ];
                        $options[] = [
                            'icon' => 'remove',
                            'text' => t('commentions.section.option.unapprove'),
                            'click' => 'unapprove-' . $data['uid'] . '|' . $data['pageid']
                        ];
                    } elseif ($data['status'] == 'unapproved') {
                        $class = 'k-list-item-commention-unapproved';
                        $icon = [ 'type' => 'protected', 'back' => 'transparent' ];
                        $options[] = [
                            'icon' => 'check',
                            'text' => t('commentions.section.option.approve'),
                            'click' => 'approve-' . $data['uid'] . '|' . $data['pageid']
                        ];
                    } else {
                        $class = 'k-list-item-commention-pending';
                        $icon = [ 'type' => 'circle-outline', 'back' => 'transparent' ];
                        $options[] = [
                            'icon' => 'check',
                            'text' => t('commentions.section.option.approve'),
                            'click' => 'approve-' . $data['uid'] . '|' . $data['pageid']
                        ];
                        $options[] = [
                            'icon' => 'protected',
                            'text' => t('commentions.section.option.unapprove'),
                            'click' => 'unapprove-' . $data['uid'] . '|' . $data['pageid']
                        ];
                    }

                    // second option is always 'delete'
                    $options[] = [
                        'icon' => 'trash',
                        'text' => t('commentions.section.option.delete'),
                        'click' => 'delete-' . $data['uid'] . '|' . $data['pageid']
                    ];

                    // third option is link to source
                    if (! empty($data['source'])) {
                        $options[] = '-';
                        $options[] = [
                            'icon' => 'open',
                            'text' => t('commentions.section.option.viewsource'),
                            'click' => 'open-' . $data['uid'] . '|' . $data['source']
                        ];
                    } elseif (! empty($data['website'])) {
                        $options[] = '-';
                        $options[] = [
                            'icon' => 'open',
                            'text' => t('commentions.section.option.viewwebsite'),
                            'click' => 'open-' . $data['uid'] . '|' . $data['website']
                        ];
                    }
                    if (! empty($data['email'])) {
                        $options[] = '-';
                        $options[] = [
                            'icon' => 'open',
                            'text' => t('commentions.section.option.sendemail'),
                            'click' => 'open-' . $data['uid'] . '|mailto:' . $data['email']
                        ];
                    }

                    $return[] = [ $content, $options, $class, $icon ];
                }
            }

            // return the array to the vue component
            return  $return ?? [] ;
        }

    ],

];
