<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MonorepoBuilder20210727\Symfony\Component\VarDumper\Caster;

use MonorepoBuilder20210727\Symfony\Component\VarDumper\Cloner\Stub;
/**
 * @author Jan Schädlich <jan.schaedlich@sensiolabs.de>
 *
 * @final
 */
class MemcachedCaster
{
    private static $optionConstants;
    private static $defaultOptions;
    public static function castMemcached(\Memcached $c, array $a, \MonorepoBuilder20210727\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $a += [\MonorepoBuilder20210727\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL . 'servers' => $c->getServerList(), \MonorepoBuilder20210727\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL . 'options' => new \MonorepoBuilder20210727\Symfony\Component\VarDumper\Caster\EnumStub(self::getNonDefaultOptions($c))];
        return $a;
    }
    private static function getNonDefaultOptions(\Memcached $c) : array
    {
        self::$defaultOptions = self::$defaultOptions ?? self::discoverDefaultOptions();
        self::$optionConstants = self::$optionConstants ?? self::getOptionConstants();
        $nonDefaultOptions = [];
        foreach (self::$optionConstants as $constantKey => $value) {
            if (self::$defaultOptions[$constantKey] !== ($option = $c->getOption($value))) {
                $nonDefaultOptions[$constantKey] = $option;
            }
        }
        return $nonDefaultOptions;
    }
    private static function discoverDefaultOptions() : array
    {
        $defaultMemcached = new \Memcached();
        $defaultMemcached->addServer('127.0.0.1', 11211);
        $defaultOptions = [];
        self::$optionConstants = self::$optionConstants ?? self::getOptionConstants();
        foreach (self::$optionConstants as $constantKey => $value) {
            $defaultOptions[$constantKey] = $defaultMemcached->getOption($value);
        }
        return $defaultOptions;
    }
    private static function getOptionConstants() : array
    {
        $reflectedMemcached = new \ReflectionClass(\Memcached::class);
        $optionConstants = [];
        foreach ($reflectedMemcached->getConstants() as $constantKey => $value) {
            if (\strncmp($constantKey, 'OPT_', \strlen('OPT_')) === 0) {
                $optionConstants[$constantKey] = $value;
            }
        }
        return $optionConstants;
    }
}
