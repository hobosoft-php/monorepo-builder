<?php

declare (strict_types=1);
namespace MonorepoBuilder20210708\Symplify\ConsolePackageBuilder\Bundle;

use MonorepoBuilder20210708\Symfony\Component\DependencyInjection\ContainerBuilder;
use MonorepoBuilder20210708\Symfony\Component\HttpKernel\Bundle\Bundle;
use MonorepoBuilder20210708\Symplify\ConsolePackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass;
final class NamelessConsoleCommandBundle extends \MonorepoBuilder20210708\Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function build(\MonorepoBuilder20210708\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder) : void
    {
        $containerBuilder->addCompilerPass(new \MonorepoBuilder20210708\Symplify\ConsolePackageBuilder\DependencyInjection\CompilerPass\NamelessConsoleCommandCompilerPass());
    }
}
