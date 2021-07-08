<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Compiler;

use MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Argument\ArgumentInterface;
use MonorepoBuilder20210708\Symfony\Component\DependencyInjection\ContainerBuilder;
use MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Definition;
use MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Reference;
/**
 * Inline service definitions where this is possible.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class InlineServiceDefinitionsPass extends \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    private $analyzingPass;
    private $cloningIds = [];
    private $connectedIds = [];
    private $notInlinedIds = [];
    private $inlinedIds = [];
    private $notInlinableIds = [];
    private $graph;
    public function __construct(\MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass $analyzingPass = null)
    {
        $this->analyzingPass = $analyzingPass;
    }
    public function process(\MonorepoBuilder20210708\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $this->container = $container;
        if ($this->analyzingPass) {
            $analyzedContainer = new \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\ContainerBuilder();
            $analyzedContainer->setAliases($container->getAliases());
            $analyzedContainer->setDefinitions($container->getDefinitions());
            foreach ($container->getExpressionLanguageProviders() as $provider) {
                $analyzedContainer->addExpressionLanguageProvider($provider);
            }
        } else {
            $analyzedContainer = $container;
        }
        try {
            $remainingInlinedIds = [];
            $this->connectedIds = $this->notInlinedIds = $container->getDefinitions();
            do {
                if ($this->analyzingPass) {
                    $analyzedContainer->setDefinitions(\array_intersect_key($analyzedContainer->getDefinitions(), $this->connectedIds));
                    $this->analyzingPass->process($analyzedContainer);
                }
                $this->graph = $analyzedContainer->getCompiler()->getServiceReferenceGraph();
                $notInlinedIds = $this->notInlinedIds;
                $this->connectedIds = $this->notInlinedIds = $this->inlinedIds = [];
                foreach ($analyzedContainer->getDefinitions() as $id => $definition) {
                    if (!$this->graph->hasNode($id)) {
                        continue;
                    }
                    foreach ($this->graph->getNode($id)->getOutEdges() as $edge) {
                        if (isset($notInlinedIds[$edge->getSourceNode()->getId()])) {
                            $this->currentId = $id;
                            $this->processValue($definition, \true);
                            break;
                        }
                    }
                }
                foreach ($this->inlinedIds as $id => $isPublicOrNotShared) {
                    if ($isPublicOrNotShared) {
                        $remainingInlinedIds[$id] = $id;
                    } else {
                        $container->removeDefinition($id);
                        $analyzedContainer->removeDefinition($id);
                    }
                }
            } while ($this->inlinedIds && $this->analyzingPass);
            foreach ($remainingInlinedIds as $id) {
                if (isset($this->notInlinableIds[$id])) {
                    continue;
                }
                $definition = $container->getDefinition($id);
                if (!$definition->isShared() && !$definition->isPublic()) {
                    $container->removeDefinition($id);
                }
            }
        } finally {
            $this->container = null;
            $this->connectedIds = $this->notInlinedIds = $this->inlinedIds = [];
            $this->notInlinableIds = [];
            $this->graph = null;
        }
    }
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, bool $isRoot = \false)
    {
        if ($value instanceof \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Argument\ArgumentInterface) {
            // Reference found in ArgumentInterface::getValues() are not inlineable
            return $value;
        }
        if ($value instanceof \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Definition && $this->cloningIds) {
            if ($value->isShared()) {
                return $value;
            }
            $value = clone $value;
        }
        if (!$value instanceof \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Reference) {
            return parent::processValue($value, $isRoot);
        } elseif (!$this->container->hasDefinition($id = (string) $value)) {
            return $value;
        }
        $definition = $this->container->getDefinition($id);
        if (!$this->isInlineableDefinition($id, $definition)) {
            $this->notInlinableIds[$id] = \true;
            return $value;
        }
        $this->container->log($this, \sprintf('Inlined service "%s" to "%s".', $id, $this->currentId));
        $this->inlinedIds[$id] = $definition->isPublic() || !$definition->isShared();
        $this->notInlinedIds[$this->currentId] = \true;
        if ($definition->isShared()) {
            return $definition;
        }
        if (isset($this->cloningIds[$id])) {
            $ids = \array_keys($this->cloningIds);
            $ids[] = $id;
            throw new \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException($id, \array_slice($ids, \array_search($id, $ids)));
        }
        $this->cloningIds[$id] = \true;
        try {
            return $this->processValue($definition);
        } finally {
            unset($this->cloningIds[$id]);
        }
    }
    /**
     * Checks if the definition is inlineable.
     */
    private function isInlineableDefinition(string $id, \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Definition $definition) : bool
    {
        if ($definition->hasErrors() || $definition->isDeprecated() || $definition->isLazy() || $definition->isSynthetic()) {
            return \false;
        }
        if (!$definition->isShared()) {
            if (!$this->graph->hasNode($id)) {
                return \true;
            }
            foreach ($this->graph->getNode($id)->getInEdges() as $edge) {
                $srcId = $edge->getSourceNode()->getId();
                $this->connectedIds[$srcId] = \true;
                if ($edge->isWeak() || $edge->isLazy()) {
                    return \false;
                }
            }
            return \true;
        }
        if ($definition->isPublic()) {
            return \false;
        }
        if (!$this->graph->hasNode($id)) {
            return \true;
        }
        if ($this->currentId == $id) {
            return \false;
        }
        $this->connectedIds[$id] = \true;
        $srcIds = [];
        $srcCount = 0;
        $isReferencedByConstructor = \false;
        foreach ($this->graph->getNode($id)->getInEdges() as $edge) {
            $isReferencedByConstructor = $isReferencedByConstructor || $edge->isReferencedByConstructor();
            $srcId = $edge->getSourceNode()->getId();
            $this->connectedIds[$srcId] = \true;
            if ($edge->isWeak() || $edge->isLazy()) {
                return \false;
            }
            $srcIds[$srcId] = \true;
            ++$srcCount;
        }
        if (1 !== \count($srcIds)) {
            $this->notInlinedIds[$id] = \true;
            return \false;
        }
        if ($srcCount > 1 && \is_array($factory = $definition->getFactory()) && ($factory[0] instanceof \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Reference || $factory[0] instanceof \MonorepoBuilder20210708\Symfony\Component\DependencyInjection\Definition)) {
            return \false;
        }
        return $this->container->getDefinition($srcId)->isShared();
    }
}
