<?php

declare (strict_types=1);
namespace MonorepoBuilder20220604\Symplify\SymplifyKernel\ValueObject;

use MonorepoBuilder20220604\Symfony\Component\Console\Application;
use MonorepoBuilder20220604\Symfony\Component\Console\Command\Command;
use MonorepoBuilder20220604\Symfony\Component\HttpKernel\KernelInterface;
use MonorepoBuilder20220604\Symplify\PackageBuilder\Console\Input\StaticInputDetector;
use MonorepoBuilder20220604\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use MonorepoBuilder20220604\Symplify\SymplifyKernel\Contract\LightKernelInterface;
use MonorepoBuilder20220604\Symplify\SymplifyKernel\Exception\BootException;
use Throwable;
/**
 * @api
 */
final class KernelBootAndApplicationRun
{
    /**
     * @var class-string<(KernelInterface | LightKernelInterface)>
     */
    private $kernelClass;
    /**
     * @var string[]
     */
    private $extraConfigs = [];
    /**
     * @param class-string<KernelInterface|LightKernelInterface> $kernelClass
     * @param string[] $extraConfigs
     */
    public function __construct(string $kernelClass, array $extraConfigs = [])
    {
        $this->kernelClass = $kernelClass;
        $this->extraConfigs = $extraConfigs;
        $this->validateKernelClass($this->kernelClass);
    }
    public function run() : void
    {
        try {
            $this->booKernelAndRunApplication();
        } catch (\Throwable $throwable) {
            $symfonyStyleFactory = new \MonorepoBuilder20220604\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory();
            $symfonyStyle = $symfonyStyleFactory->create();
            $symfonyStyle->error($throwable->getMessage());
            exit(\MonorepoBuilder20220604\Symfony\Component\Console\Command\Command::FAILURE);
        }
    }
    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface|\Symplify\SymplifyKernel\Contract\LightKernelInterface
     */
    private function createKernel()
    {
        // random has is needed, so cache is invalidated and changes from config are loaded
        $kernelClass = $this->kernelClass;
        if (\is_a($kernelClass, \MonorepoBuilder20220604\Symplify\SymplifyKernel\Contract\LightKernelInterface::class, \true)) {
            return new $kernelClass();
        }
        $environment = 'prod' . \random_int(1, 100000);
        return new $kernelClass($environment, \MonorepoBuilder20220604\Symplify\PackageBuilder\Console\Input\StaticInputDetector::isDebug());
    }
    private function booKernelAndRunApplication() : void
    {
        $kernel = $this->createKernel();
        if ($kernel instanceof \MonorepoBuilder20220604\Symplify\SymplifyKernel\Contract\LightKernelInterface) {
            $container = $kernel->createFromConfigs($this->extraConfigs);
        } else {
            $kernel->boot();
            $container = $kernel->getContainer();
        }
        /** @var Application $application */
        $application = $container->get(\MonorepoBuilder20220604\Symfony\Component\Console\Application::class);
        exit($application->run());
    }
    /**
     * @param class-string $kernelClass
     */
    private function validateKernelClass(string $kernelClass) : void
    {
        if (\is_a($kernelClass, \MonorepoBuilder20220604\Symfony\Component\HttpKernel\KernelInterface::class, \true)) {
            return;
        }
        if (\is_a($kernelClass, \MonorepoBuilder20220604\Symplify\SymplifyKernel\Contract\LightKernelInterface::class, \true)) {
            return;
        }
        $currentValueType = \get_debug_type($kernelClass);
        $errorMessage = \sprintf('Class "%s" must by type of "%s" or "%s". "%s" given', $kernelClass, \MonorepoBuilder20220604\Symfony\Component\HttpKernel\KernelInterface::class, \MonorepoBuilder20220604\Symplify\SymplifyKernel\Contract\LightKernelInterface::class, $currentValueType);
        throw new \MonorepoBuilder20220604\Symplify\SymplifyKernel\Exception\BootException($errorMessage);
    }
}
