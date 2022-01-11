<?php

namespace yunwuxin\twig\visitor;

use Twig\Environment;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;
use yunwuxin\twig\node\GetAttrNode;

class GetAttrAdjuster implements NodeVisitorInterface
{
    /**
     * @inheritdoc
     */
    public function enterNode(Node $node, Environment $env): Node
    {
        // Is it a GetAttrExpression (and not a subclass)?
        if (get_class($node) === GetAttrExpression::class) {
            // "Clone" it into a GetAttrNode
            $nodes = [
                'node'      => $node->getNode('node'),
                'attribute' => $node->getNode('attribute'),
            ];

            if ($node->hasNode('arguments')) {
                $nodes['arguments'] = $node->getNode('arguments');
            }

            $attributes = [
                'type'                => $node->getAttribute('type'),
                'is_defined_test'     => $node->getAttribute('is_defined_test'),
                'ignore_strict_check' => $node->getAttribute('ignore_strict_check'),
                'optimizable'         => $node->getAttribute('optimizable'),
            ];

            $node = new GetAttrNode($nodes, $attributes, $node->getTemplateLine(), $node->getNodeTag());
        }

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 0;
    }
}
