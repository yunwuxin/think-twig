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

use RuntimeException;
use think\App;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use yunwuxin\twig\Extension;

class Twig
{
    /** @var App */
    protected $app;

    // 模板引擎参数
    protected $config = [
        // 模板起始路径
        'view_path'           => '',
        // 模板文件后缀
        'view_suffix'         => 'twig',
        'cache_path'          => '',
        'strict_variables'    => true,
        'auto_add_function'   => false,
        'base_template_class' => 'Twig_Template',
        'functions'           => [],
        'filters'             => [],
        'globals'             => [],
        'runtime'             => [],
    ];

    public function __construct(App $app, $config = [])
    {
        $this->app = $app;
        $this->config($config);

        if (empty($this->config['view_path'])) {
            $this->config['view_path'] = $app->getAppPath() . 'view';
        }

        if (empty($this->config['cache_path'])) {
            $this->config['cache_path'] = $app->getRuntimePath() . 'temp';
        }

        if (!is_dir($this->config['cache_path'])) {
            if (!mkdir($this->config['cache_path'], 0755, true)) {
                throw new RuntimeException('Can not make the cache dir!');
            }
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
            'debug'               => $this->app->isDebug(),
            'auto_reload'         => $this->app->isDebug(),
            'cache'               => $this->config['cache_path'],
            'strict_variables'    => $this->config['strict_variables'],
            'base_template_class' => $this->config['base_template_class']
        ];
    }

    protected function addFunctions(Environment $twig)
    {
        $twig->registerUndefinedFunctionCallback(function ($name) {
            if (function_exists($name)) {
                return new TwigFunction($name, $name);
            }

            return false;
        });
    }

    protected function getTwig(LoaderInterface $loader)
    {
        $twig = new Environment($loader, $this->getTwigConfig());

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
                    $twig->addFunction(new TwigFunction($function, $function));
                } else {
                    $twig->addFunction(new TwigFunction($name, $function));
                }
            }
        }

        if (!empty($this->config['filters'])) {
            foreach ($this->config['filters'] as $name => $filter) {
                if (is_integer($name)) {
                    $twig->addFilter(new TwigFilter($filter, $filter));
                } else {
                    $twig->addFilter(new TwigFilter($name, $filter));
                }
            }
        }

        if (!empty($this->config['runtime'])) {
            $twig->addRuntimeLoader(new FactoryRuntimeLoader($this->config['runtime']));
        }

        $twig->addExtension(new Extension());

        return $twig;
    }

    public function fetch($template, $data = [], $config = [])
    {
        if ($config) {
            $this->config($config);
        }

        $loader = new FilesystemLoader($this->config['view_path']);

        $twig = $this->getTwig($loader);

        $twig->display($template . '.' . $this->config['view_suffix'], $data);
    }

    public function display($template, $data = [], $config = [])
    {
        if ($config) {
            $this->config($config);
        }
        $key    = md5($template);
        $loader = new ArrayLoader([$key => $template]);

        $twig = $this->getTwig($loader);

        $twig->display($key, $data);
    }

}