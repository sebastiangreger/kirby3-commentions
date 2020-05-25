<?php

namespace sgkirby\Commentions;

return [

    'routes' => [
        [
            'pattern' => 'commentions/(:any)/(\w{10})',
            'method' => 'DELETE',
            'action' => function (string $pageid, string $uid) {
                return $this->page($pageid)->deleteCommention($uid);
            }
        ],
        [
            'pattern' => 'commentions/(:any)/(\w{10})',
            'method'  => 'PATCH',
            'action'  => function (string $pageid, string $uid) {
                return $this->page($pageid)->updateCommention($uid, $this->requestBody());
            }
        ],
        [
            'pattern' => 'commentions/pagesettings/(:any)',
            'method'  => 'PATCH',
            'action'  => function (string $pageid) {
                $data = $this->requestBody();
                $settings = Storage::read($this->page($pageid), 'pagesettings');
                $settings[$data['key']] = $data['value'];
                return Storage::write($this->page($pageid), $settings, 'pagesettings');
            }
        ],

    ]

];
