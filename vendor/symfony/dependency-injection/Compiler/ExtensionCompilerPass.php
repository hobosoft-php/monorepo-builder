<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MonorepoBuilder20211114\Symfony\Component\DependencyInjection\Compiler;

use MonorepoBuilder20211114\Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * A pass to automatically process extensions if they implement
 * CompilerPassInterface.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ExtensionCompilerPass implements \MonorepoBuilder20211114\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process($container)
    {
        foreach ($container->getExtensions() as $extension) {
            if (!$extension instanceof \MonorepoBuilder20211114\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface) {
                continue;
            }
            $extension->process($container);
        }
    }
}
