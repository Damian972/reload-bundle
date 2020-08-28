<?php

namespace Damian972\ReloadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('reload');
        /**
         * @var ArrayNodeDefinition
         */
        $rootNode = $treeBuilder->getRootNode();

        $this->addServerPort($rootNode);

        return $treeBuilder;
    }

    /**
     * $tree['reload']['server_port'].
     *
     * @param ArrayNodeDefinition
     */
    private function addServerPort(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->integerNode('server_port')
            ->defaultValue(8088)
            ->end()
        ;
    }
}
