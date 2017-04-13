<?php

return [
    'name'   => 'posts',
    'label'  => 'Posts',
    'fields' => [
        [
            'name'     => 'published',
            'label'    => 'Published',
            'type'        => 'boolean',
            'default'     => '',
            'info'        => '',
            'localize'    => false,
            'options'     => [
                'default' => false,
                'label'   => false
            ],
            'width'       => '1-1',
            'lst'         => true
        ],

        [
            'name'     => 'title',
            'label'    => 'Title',
            'type'     => 'text',
            'default'  => '',
            'info'     => '',
            'localize' => false,
            'options'  => [
                'slug' => true
            ],
            'width'    => '1-1',
            'lst'      => true,
            'required' => true
        ],

        [
            'name'     => 'image',
            'label'    => 'Featured Image',
            'type'     => 'asset',
            'default'  => '',
            'info'     => '',
            'localize' => false,
            'options'  => [],
            'width'    => '1-1',
            'lst'      => true
        ],

        [
            'name'     => 'excerpt',
            'label'    => 'Excerpt',
            'type'     => 'markdown',
            'default'  => '',
            'info'     => '',
            'localize' => false,
            'options'  => [],
            'width'    => '1-1',
            'lst'      => true
        ],

        [
            'name'     => 'content',
            'label'    => 'Content',
            'type'     => 'markdown',
            'default'  => '',
            'info'     => '',
            'localize' => false,
            'options'  => [],
            'width'    => '1-1',
            'lst'      => true
        ],
        
        [
            'name'     => 'tags',
            'label'    => 'Tags',
            'type'     => 'tags',
            'default'  => '',
            'info'     => '',
            'localize' => false,
            'options'  => [],
            'width'    => '1-1',
            'lst'      => true
        ]
    ]
];