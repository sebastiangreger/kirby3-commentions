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

    ]

];
