<?php
return [
    'platform' => 0,
    'id' => 'file_manager',
    'folder' => 'core',
    'name' => 'File manager',
    'author' => 'Stackcode',
    'author_uri' => 'https://stackposts.com',
    'desc' => 'Customize system interface',
    'icon' => 'fad fa-folders',
    'color' => '#5156ff',
    'menu' => [
        'tab' => 3,
        'type' => 'top',
        'position' => 1500,
        'name' => 'File manager'
    ],
    'css' => [
        'Assets/css/file_manager.css'
    ],
    'js' => [
        'Assets/plugins/jquery.lazy/jquery.lazy.min.js',
        'Assets/js/file_manager.js'
    ],
    'hidden' => true,
    //google的clientId 和clientSecret 用在登录网盘这里
    'clientId' => '228167396591-2i0fm9fp7rfcocimgqns35l6mf606dav.apps.googleusercontent.com',
    'clientSecret' =>'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',
];