<?php

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
        'view_dir_name'       => 'view',
        // 模板文件后缀
        'view_suffix'         => 'twig',
        'cache_path'          => '',
        'base_template_class' => 'Twig_Template',
        'filters'             => [],
        'globals'             => [],
        'runtime'             => [],
        'extensions'          => [],
        'extra'               => null,
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
            $view = $this->config['view_dir_name'];
            if (is_dir($this->app->getAppPath() . $view)) {
                $path = $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;
            } else {
                $appName = $this->app->http->getName();
                $path    = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . ($appName ? $appName . DIRECTORY_SEPARATOR : '');
            }
            $this->config['view_path'] = $path;
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
        $this->loader = new FilesystemLoader();
        if (is_dir($this->config['view_path'])) {
            $this->loader->addPath($this->config['view_path']);
        }

        $twig = new Environment($this->loader, $this->getTwigConfig());

        $this->addFunctions($twig);

        if (!empty($this->config['globals'])) {
            foreach ($this->config['globals'] as $name => $global) {
                $twig->addGlobal($name, $global);
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

        if (!empty($this->config['extensions'])) {
            foreach ($this->config['extensions'] as $extension) {
                $twig->addExtension(new $extension);
            }
        }

        if (!empty($this->config['extra'])) {
            $this->config['extra']($twig);
        }

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
            'strict_variables'    => true,
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
