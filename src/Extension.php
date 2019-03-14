<?php
/**
 * Created by PhpStorm.
 * User: yunwuxin
 * Date: 2019/3/14
 * Time: 15:12
 */

namespace yunwuxin\twig;


use Twig\Extension\AbstractExtension;
use yunwuxin\twig\nodevisitors\GetAttrAdjuster;

class Extension extends AbstractExtension
{
    public function getNodeVisitors()
    {
        return [
            new GetAttrAdjuster()
        ];
    }
}