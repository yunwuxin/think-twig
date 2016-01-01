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
    private $template = null;

    public function __construct($config = [])
    {

        $loader = new \Twig_Loader_Filesystem(VIEW_PATH);

        $this->template = new \Twig_Environment($loader, [
            'cache' => RUNTIME_PATH . 'template',
        ]);
    }

    public function fetch($template, $data = [], $cache = [])
    {
        //TODO
    }
}