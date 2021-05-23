<?php
return [
    'account' => [
        'fields' => [
            'app_mode' => [
                'label' => 'App Mode',
                'type' => 'select',
                'options' => [
                    'options' => 'LITE, PRO',
                    'default' => 'LITE',
                ],
            ],
        ],
    ],
    # define additional groups
    'groups' => [
        'publisher' => [
            '$admin' => false,
            '$vars' => [
                'finder.path' => '/storage/upload'
            ],
            'cockpit' => [
                'backend' => true,
                'finder' => true
            ],
            'collections' => [
                'manage' => true
            ]
        ] ,
        'user' => [
            '$admin' => false,
            'cockpit' => [
                'backend' => true,
            ]
        ]
    ],
    # use mongodb as main data storage
    'database' => [
        'server' =>  'mongodb://localhost:27017',
        'options' => [
            'db' => 'cockpitdb',
            'username' => 'root',
            'password' => 'password'
        ]
    ],
    'whitelabel' => [
        'logo'       => '#uploads:2021/05/23/nutricia_uid_60aa07c94db6f.png',
        'logoWidth'  => '8em',   # optional, default: 30px
        'logoHeight' => '3em',   # optional, default: 30px
        'hideName'   => true,    # hide app name
        'colors' => [
            '#ff0000',
            '#00ff00',
            '#0000ff',
        ],
    ]
];