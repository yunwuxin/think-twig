<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>　
// +----------------------------------------------------------------------

namespace think\view\driver;


class Twig
{
    public function fetch($template, $data = [], $cache = [])
    {
        if (is_file($template)) {
            $loader   = new \Twig_Loader_Filesystem(THEME_PATH);
            $template = substr($template, strlen(THEME_PATH));
        } else {
            $key      = md5($template);
            $loader   = new \Twig_Loader_Array([$key => $template]);
            $template = $key;
        }
        $twig = new \Twig_Environment($loader, [
            'cache' => RUNTIME_PATH . 'template',
        ]);

        echo $twig->render($template, $data);
    }
}