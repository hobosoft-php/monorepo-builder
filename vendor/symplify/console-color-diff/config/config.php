<?php

declare (strict_types=1);
namespace MonorepoBuilder20211203;

use MonorepoBuilder20211203\SebastianBergmann\Diff\Differ;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use MonorepoBuilder20211203\Symplify\PackageBuilder\Reflection\PrivatesAccessor;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->defaults()->public()->autowire()->autoconfigure();
    $services->load('MonorepoBuilder20211203\Symplify\ConsoleColorDiff\\', __DIR__ . '/../src');
    $services->set(\MonorepoBuilder20211203\SebastianBergmann\Diff\Differ::class);
    $services->set(\MonorepoBuilder20211203\Symplify\PackageBuilder\Reflection\PrivatesAccessor::class);
};
