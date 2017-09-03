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
use RuntimeException;
use think\App;
use think\Config;
use think\Loader;
use think\Request;
use Twig_Environment;
use Twig_FactoryRuntimeLoader;
use Twig_Loader_Array;
use Twig_Loader_Filesystem;
use Twig_LoaderInterface;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class Twig
{
    // 模板引擎参数
    protected $config = [
        'view_base'         => '',
        // 模板起始路径
        'view_path'         => '',
        // 模板文件后缀
        'view_suffix'       => '.twig',
        // 模板文件名分隔符
        'view_depr'         => '/',
        'cache_path'        => TEMP_PATH,
        'strict_variables'  => true,
        'auto_add_function' => false,
        'functions'         => [],
        'filters'           => [],
        'globals'           => [],
        'runtime'           => []
    ];

    public function __construct($config = [])
    {
        $this->config($config);

        if (!is_dir($this->config['cache_path'])) {
            if (!mkdir($this->config['cache_path'], 0755, true)) {
                throw new RuntimeException('Can not make the cache dir!');
            }
        }

        if (empty($this->config['view_path'])) {
            $this->config['view_path'] = App::$modulePath . 'view' . DS;
        }
    }

    /**
     * 模板引擎配置项
     * @access public
     * @param array|string $name
     * @param mixed        $value
     */
    public function config($name, $value = null)
    {
        if (is_array($name)) {
            $this->config = array_merge($this->config, $name);
        } else {
            $this->config[$name] = $value;
        }
    }

    protected function getTwigConfig()
    {
        return [
            'debug'            => App::$debug,
            'auto_reload'      => App::$debug,
            'cache'            => $this->config['cache_path'],
            'strict_variables' => $this->config['strict_variables']
        ];
    }

    protected function addFunctions(Twig_Environment $twig)
    {
        $twig->registerUndefinedFunctionCallback(function ($name) {
            if (function_exists($name)) {
                return new Twig_SimpleFunction($name, $name);
            }

            return false;
        });
    }

    protected function getTwig(Twig_LoaderInterface $loader)
    {
        $twig = new Twig_Environment($loader, $this->getTwigConfig());

        if ($this->config['auto_add_function']) {
            $this->addFunctions($twig);
        }

        if (!empty($this->config['globals'])) {
            foreach ($this->config['globals'] as $name => $global) {
                $twig->addGlobal($name, $global);
            }
        }

        if (!empty($this->config['functions'])) {
            foreach ($this->config['functions'] as $name => $function) {
                if (is_integer($name)) {
                    $twig->addFunction(new Twig_SimpleFunction($function, $function));
                } else {
                    $twig->addFunction(new Twig_SimpleFunction($name, $function));
                }
            }
        }

        if (!empty($this->config['filters'])) {
            foreach ($this->config['filters'] as $name => $filter) {
                if (is_integer($name)) {
                    $twig->addFilter(new Twig_SimpleFilter($filter, $filter));
                } else {
                    $twig->addFilter(new Twig_SimpleFilter($name, $filter));
                }
            }
        }

        if (!empty($this->config['runtime'])) {
            $twig->addRuntimeLoader(new Twig_FactoryRuntimeLoader($this->config['runtime']));
        }

        return $twig;
    }

    public function fetch($template, $data = [], $config = [])
    {
        if ($config) {
            $this->config($config);
        }

        $loader = new Twig_Loader_Filesystem( $this->config['view_path']);

        if (Config::get('app_multi_module')) {
            $modules = $this->getModules();
            foreach ($modules as $module) {
                if ($this->config['view_base']) {
                    $view_dir = $this->config['view_base'] . $module;
                } else {
                    $view_dir = APP_PATH . $module . DS . 'view';
                }
                if (is_dir($view_dir)) {
                    $loader->addPath($view_dir, $module);
                }
            }
        }

        $twig = $this->getTwig($loader);

        $template = $this->parseTemplate($template);

        $twig->display($template, $data);
    }

    public function display($template, $data = [], $config = [])
    {
        if ($config) {
            $this->config($config);
        }
        $key    = md5($template);
        $loader = new Twig_Loader_Array([$key => $template]);

        $twig = $this->getTwig($loader);

        $twig->display($key, $data);
    }

    private function parseTemplate($template)
    {
        $request = Request::instance();

        $depr = $this->config['view_depr'];

        $controller = Loader::parseName($request->controller());

        if ($controller && 0 !== strpos($template, '/')) {
            if ('' == $template) {
                // 如果模板文件名为空 按照默认规则定位
                $template = str_replace('.', DS, $controller) . $depr . $request->action();
            } elseif (false === strpos($template, '/')) {
                $template = str_replace('.', DS, $controller) . $depr . $template;
            }
        }

        return str_replace('/', $depr, $template) . '.'.$this->config['view_suffix'];
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