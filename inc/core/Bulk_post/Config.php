<?php
return [
    'platform' => 1,
    'id' => 'bulk_post',
    'folder' => 'core',
    'name' => 'Bulk post',
    'author' => 'Stackcode',
    'author_uri' => 'https://stackposts.com',
    'desc' => 'Schedule hundreds of posts in just a few clicks',
    'icon' => 'fad fa-rocket',
    'color' => '#41a900',
    'menu' => [
        'tab' => 1,
        'type' => 'top',
        'position' => 1500,
        'name' => 'Bulk post'
    ],
    'cron' => [
        'name' => 'Bulk post',
        'uri' => 'bulk_post/cron',
        'style' => '* * * * *',
    ],
    'hidden' => false,
];