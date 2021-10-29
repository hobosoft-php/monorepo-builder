<?php

namespace MonorepoBuilder20211029;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use MonorepoBuilder20211029\Symfony\Component\VarDumper\VarDumper;
if (!\function_exists('MonorepoBuilder20211029\\dump')) {
    /**
     * @author Nicolas Grekas <p@tchwork.com>
     */
    function dump($var, ...$moreVars)
    {
        \MonorepoBuilder20211029\Symfony\Component\VarDumper\VarDumper::dump($var);
        foreach ($moreVars as $v) {
            \MonorepoBuilder20211029\Symfony\Component\VarDumper\VarDumper::dump($v);
        }
        if (1 < \func_num_args()) {
            return \func_get_args();
        }
        return $var;
    }
}
if (!\function_exists('MonorepoBuilder20211029\\dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $v) {
            \MonorepoBuilder20211029\Symfony\Component\VarDumper\VarDumper::dump($v);
        }
        exit(1);
    }
}
