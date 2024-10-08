<?php

declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Tests\Merge\ComposerJsonDecorator\RootRemoveComposerJsonDecorator;

use Symplify\MonorepoBuilder\Kernel\MonorepoBuilderKernel;
use Symplify\MonorepoBuilder\Merge\ComposerJsonMerger;
use Symplify\MonorepoBuilder\Tests\Merge\ComposerJsonDecorator\AbstractComposerJsonDecorator;

/**
 * @see \Symplify\MonorepoBuilder\Merge\ComposerJsonDecorator\RootRemoveComposerJsonDecorator
 */
final class RootRemoveComposerJsonDecorator extends AbstractComposerJsonDecorator
{
    private ComposerJsonMerger $composerJsonMerger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootKernel(MonorepoBuilderKernel::class);
        $this->composerJsonMerger = $this->getService(ComposerJsonMerger::class);
    }

    /**
     * Only packages collected from /packages directory should be removed
     */
    public function test(): void
    {
        $composerJson = $this->composerJsonFactory->createFromFilePath(__DIR__ . '/Source/composer.json');
        $extraComposerJson = $this->composerJsonFactory->createFromFilePath(__DIR__ . '/Source/composer.json');

        $this->composerJsonMerger->mergeJsonToRoot($composerJson, $extraComposerJson);

        $expectedComposerJson = $this->composerJsonFactory->createFromFilePath(
            __DIR__ . '/Source/expected-composer.json'
        );

        $this->assertComposerJsonEquals($expectedComposerJson, $composerJson);
    }
}
