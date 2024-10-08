<?php

declare(strict_types=1);

use Hobosoft\Finders\FileFinder;
use Nette\Utils\Strings;

//use Isolated\Symfony\Component\Finder\Finder;

require __DIR__ . '/vendor/autoload.php';

$timestamp = (new DateTime('now'))->format('Ym');

// @see https://github.com/humbug/php-scoper/blob/master/docs/further-reading.md

$polyfillsBootstraps = array_map(
    static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
    iterator_to_array(
/*        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/symfony/polyfill-*')
            ->name('bootstrap*.php'),*/
        (new FileFinder())->wantFiles(true)->includePath([__DIR__ . '/vendor/symfony/polyfill-*']) ->acceptFileCallback(function (SplFileInfo $fileInfo) {
            $ext = pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION);
            $filename = pathinfo($fileInfo->getPathname(), PATHINFO_FILENAME);
            return ($ext === 'php' && str_starts_with($filename, 'bootstrap'));
        })->find(), false,
    ),
);

$polyfillsStubs = array_map(
    static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
    iterator_to_array(
        (new FileFinder())->wantFiles(true)->includePath([__DIR__ . '/vendor/symfony/polyfill-*']) ->acceptFileCallback(function (SplFileInfo $fileInfo) {
            $ext = pathinfo($fileInfo->getPathname(), PATHINFO_EXTENSION);
            $filename = pathinfo($fileInfo->getPathname(), PATHINFO_FILENAME);
            return ($ext === 'php');
        })->find(), false
    ),
);

// see https://github.com/humbug/php-scoper
return [
    'prefix' => 'MonorepoBuilderPrefix' . $timestamp,
    'exclude-files' => [
        // these paths are relative to this file location, so it should be in the root directory
        'vendor/symfony/deprecation-contracts/function.php',
        ...$polyfillsBootstraps,
        ...$polyfillsStubs,
    ],
    'exclude-namespaces' => [
        '#^Symplify\\\\MonorepoBuilder#',
        '#^Symfony\\\\Polyfill#',
        // part of public API in \Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface
        '#^PharIo\\\\Version#',
    ],
    'exclude-constants' => ['#^SYMFONY\_[\p{L}_]+$#'],
    'expose-classes' => [
        'Normalizer',
        // part of public interface of configs.php
        'Symplify\MonorepoBuilder\ComposerJsonManipulator\ValueObject\ComposerJsonSection',
        'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
    ],
    'patchers' => [
        // scope symfony configs
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::match($filePath, '#(packages|config|services)\.php$#')) {
                return $content;
            }

            // fix symfony config load scoping, except CodingStandard and EasyCodingStandard
            $content = Strings::replace(
                $content,
                '#load\(\'Symplify\\\\\\\\(?<package_name>[A-Za-z]+)#',
                function (array $match) use ($prefix) {
                    return 'load(\'' . $prefix . '\Symplify\\' . $match['package_name'];
                }
            );

            return $content;
        },

        // scope symfony configs
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::match($filePath, '#(packages|config|services)\.php$#')) {
                return $content;
            }

            // unprefix symfony config
            return Strings::replace(
                $content,
                '#load\(\'' . $prefix . '\\\\Symplify\\\\MonorepoBuilder#',
                'load(\'' . 'Symplify\\MonorepoBuilder',
            );
        },
    ],
];
