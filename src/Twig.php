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
use think\contract\TemplateHandlerInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use yunwuxin\twig\Extension;

class Twig implements TemplateHandlerInterface
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

    /** @var FilesystemLoader */
    protected $loader;

    /** @var Environment */
    protected $twig;

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

        $this->createTwig();
    }

    protected function createTwig()
    {
        $this->loader = new FilesystemLoader($this->config['view_path']);

        $twig = new Environment($this->loader, $this->getTwigConfig());

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

        $this->twig = $twig;
    }

    /**
     * 模板引擎配置项
     * @param array $config
     */
    public function config(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    protected function getTwigConfig()
    {
        return [
            'debug'               => $this->app->isDebug(),
            'auto_reload'         => $this->app->isDebug(),
            'cache'               => $this->config['cache_path'],
            'strict_variables'    => $this->config['strict_variables'],
            'base_template_class' => $this->config['base_template_class'],
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

    public function fetch(string $template, array $data = []): void
    {
        $this->twig->setLoader($this->loader);
        $this->twig->display($template . '.' . $this->config['view_suffix'], $data);
    }

    public function display(string $content, array $data = []): void
    {
        $name = md5($content);

        $this->twig->setLoader(new ArrayLoader([$name => $content]));
        $this->twig->display($name, $data);
    }

    /**
     * 检测是否存在模板文件
     * @access public
     * @param string $template 模板文件或者模板规则
     * @return bool
     */
    public function exists(string $template): bool
    {
        return $this->loader->exists($template);
    }

    /**
     * 获取模板引擎配置
     * @access public
     * @param string $name 参数名
     * @return void
     */
    public function getConfig(string $name)
    {
        return $this->config[$name] ?? null;
    }

    /**
     * @return FilesystemLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @return Environment
     */
    public function getTwig()
    {
        return $this->twig;
    }
}
