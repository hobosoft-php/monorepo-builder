<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Tests\Merge\Application;

use Symplify\MonorepoBuilder\Merge\Application\MergedAndDecoratedComposerJsonFactory;
use Symplify\MonorepoBuilder\Tests\Merge\ComposerJsonDecorator\AbstractComposerJsonDecorator;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MergedAndDecoratedComposerJsonFactory extends AbstractComposerJsonDecorator
{
    private MergedAndDecoratedComposerJsonFactory $mergedAndDecoratedComposerJsonFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mergedAndDecoratedComposerJsonFactory = $this->getService(
            MergedAndDecoratedComposerJsonFactory::class
        );
    }

    public function test(): void
    {
        if (! defined('SYMPLIFY_MONOREPO')) {
            $this->markTestSkipped('Already tested on monorepo');
        }

        $mainComposerJson = $this->createComposerJson(__DIR__ . '/Source/root_composer.json');

        $packagesFileInfos = [
            new SmartFileInfo(__DIR__ . '/Source/one_package.json'),
            new SmartFileInfo(__DIR__ . '/Source/two_package.json'),
        ];

        $this->mergedAndDecoratedComposerJsonFactory->createFromRootConfigAndPackageFileInfos(
            $mainComposerJson,
            $packagesFileInfos
        );

        $this->assertComposerJsonEquals(__DIR__ . '/Source/expected_composer.json', $mainComposerJson);
    }
}
