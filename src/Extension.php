<?php

namespace yunwuxin\twig;

use Twig\Extension\AbstractExtension;
use yunwuxin\twig\nodevisitors\GetAttrAdjuster;

class Extension extends AbstractExtension
{
    public function getNodeVisitors()
    {
        return [
            new GetAttrAdjuster(),
        ];
    }
}
