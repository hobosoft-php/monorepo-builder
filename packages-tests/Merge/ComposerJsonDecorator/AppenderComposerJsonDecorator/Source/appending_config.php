<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\Config\MBConfig;

return static function (MBConfig $mbConfig): void {
    $mbConfig->dataToAppend([
        ComposerJsonSection::REQUIRE_DEV => [
            'phpstan/phpstan' => '^0.9',
            'tracy/tracy' => '^2.4',
            'slam/php-cs-fixer-extensions' => '^1.15',
        ],
        ComposerJsonSection::AUTOLOAD => [
            'psr-4' => [
                'Symplify\Tests\\' => 'tests'
            ],
        ],
    ]);
};
