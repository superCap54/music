<?php
return [
    'platform' => 1,
    'id' => 'account_manager',
    'folder' => 'core',
    'name' => 'Account manager',
    'author' => 'Stackcode',
    'author_uri' => 'https://stackposts.com',
    'desc' => 'Customize system interface',
    'icon' => 'fad fa-share-alt',
    'color' => '#002bff',
    'menu' => [
        'tab' => 3,
        'type' => 'top',
        'position' => 2000,
        'name' => 'Account manager'
    ],
    'css' => [
        "Assets/css/account_manager.css"
    ],
    'js' => [
        "Assets/js/account_manager.js"
    ],
    //google的clientId 和clientSecret 用在登录网盘这里
    'clientId' => '228167396591-2i0fm9fp7rfcocimgqns35l6mf606dav.apps.googleusercontent.com',
    'clientSecret' =>'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',
];