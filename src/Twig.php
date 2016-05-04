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


use DirectoryIterator;
use think\Config;

class Twig
{
    // 模板引擎参数
    protected $config = [
        // 模板起始路径
        'view_path'   => '',
        // 模板文件后缀
        'view_suffix' => '.html',
        // 模板文件名分隔符
        'view_depr'   => '/',

        'cache_path' => ''
    ];

    public function __construct($config = [])
    {
        $this->config($config);
    }

    /**
     * 模板引擎配置项
     * @access public
     * @param array $config
     * @return void
     */
    public function config($config)
    {
        $this->config = array_merge($this->config, $config);
        if (empty($this->config['cache_path'])) {
            $this->config['cache_path'] = RUNTIME_PATH . 'temp';
        }
        if (!is_dir($this->config['cache_path'])) {
            if (!mkdir($this->config['cache_path'], 0755, true)) {
                throw new \RuntimeException('Can not make the cache dir!');
            }
        }
    }

    public function fetch($template, $data = [], $config = [])
    {
        if ($config) {
            $this->config($config);
        }
        $path = $this->config['view_path'] ?: (defined('VIEW_PATH') ? VIEW_PATH : '');

        $loader = new \Twig_Loader_Filesystem($path);

        if (APP_MULTI_MODULE) {
            $modules = $this->getModules();
            foreach ($modules as $module) {
                $view_dir = APP_PATH . $module . DIRECTORY_SEPARATOR . VIEW_LAYER;
                if (is_dir($view_dir)) {
                    $loader->addPath($view_dir, $module);
                }
            }
        }

        $twig = new \Twig_Environment($loader, [
            'debug'       => APP_DEBUG,
            'auto_reload' => true,
            'cache'       => $this->config['cache_path']
        ]);

        $template = $this->parseTemplate($template);

        $twig->display($template, $data);
    }

    public function display($template, $data = [], $config = [])
    {
        if ($config) {
            $this->config($config);
        }
        $key    = md5($template);
        $loader = new \Twig_Loader_Array([$key => $template]);

        $twig = new \Twig_Environment($loader, [
            'debug'       => APP_DEBUG,
            'auto_reload' => true,
            'cache'       => $this->config['cache_path']
        ]);

        $twig->display($key, $data);
    }

    private function parseTemplate($template)
    {
        $depr = $this->config['view_depr'];

        if (defined('CONTROLLER_NAME')) {
            if ('' == $template) {
                // 如果模板文件名为空 按照默认规则定位
                $template = CONTROLLER_NAME . '.' . ACTION_NAME;
            } elseif (false === strpos($template, '.')) {
                $template = CONTROLLER_NAME . '.' . $template;
            }
        }

        return str_replace('.', $depr, $template) . $this->config['view_suffix'];
    }

    private function getModules()
    {
        $modules      = [];
        $oDir         = new DirectoryIterator(APP_PATH);
        $deny_modules = Config::get('deny_module_list');
        foreach ($oDir as $file) {
            if ($file->isDir() && !$file->isDot() && !in_array($file->getFilename(), $deny_modules)) {
                $modules[] = $file->getFilename();
            }
        }
        return $modules;
    }
}