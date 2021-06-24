<?php

/*
 * This file is part of the Predis package.
 *
 * (c) Daniele Alessandri <suppakilla@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (version_compare(phpversion(), '5.3.0', '>=')) {
    require __DIR__.'/src/Autoloader.php';
    call_user_func('Predis\Autoloader::register');
}

