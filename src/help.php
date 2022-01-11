<?php

namespace yunwuxin\twig {

    use think\Model;
    use Twig\Environment;
    use Twig\Source;
    use Twig\Template;

    function twig_get_attribute(Environment $env, Source $source, $object, $item, array $arguments = [], $type = /* Template::ANY_CALL */
    'any', $isDefinedTest = false, $ignoreStrictCheck = false, $sandboxed = false)
    {

        if ($object instanceof Model && $type != Template::METHOD_CALL) {

            if ($isDefinedTest) {
                return isset($object[$item]);
            }

            return $object->getAttr($item);
        }


        return \twig_get_attribute($env, $source, $object, $item, $arguments, $type, $isDefinedTest, $ignoreStrictCheck, $sandboxed);
    }
}
