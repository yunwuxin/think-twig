<?php
/**
 * Created by PhpStorm.
 * User: yunwuxin
 * Date: 2019/3/14
 * Time: 15:12
 */

namespace yunwuxin\twig;

use ReflectionClass;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;
use yunwuxin\twig\nodevisitors\GetAttrAdjuster;

class Extension extends AbstractExtension
{
    public function getNodeVisitors()
    {
        return [
            new GetAttrAdjuster(),
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('instance of', function ($var, $instance) {
                $ref = new ReflectionClass($instance);
                return $ref->isInstance($var);
            }),
        ];
    }
}
