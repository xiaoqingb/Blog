<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    'member'=>'index/index/member',
    'submit'=>'index/index/submit',
    'article'=>'index/index/article',
    'regist'=>'index/index/regist',
    'login'=>'index/index/login',
    'setting'=>'index/index/setting',
    'my_comment'=>'index/index/my_comment',
    'comment_to_me'=>'index/index/comment_to_me',
    'my-article'=>'index/index/my_article',
    'my_article'=>'index/index/my_article',
];
