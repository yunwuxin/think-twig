<?php

namespace yunwuxin\twig;

use ReflectionClass;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;
use yunwuxin\twig\parser\SwitchTokenParser;
use yunwuxin\twig\visitor\GetAttrAdjuster;

class Extension extends AbstractExtension
{
    public function getNodeVisitors()
    {
        return [
            new GetAttrAdjuster(),
        ];
    }

    public function getTokenParsers(): array
    {
        return [
            new SwitchTokenParser(),
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('instance of', function ($var, $instance) {
                $ref = new ReflectionClass($instance);
                return is_object($var) && $ref->isInstance($var);
            }),
        ];
    }
}
