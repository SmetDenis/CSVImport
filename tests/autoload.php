<?php
/**
 * SmetDenis CSVImport
 *
 * This file is part of the SmetDenis/CSVImport Package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package   CSVImport
 * @license   MIT
 * @copyright Copyright (C) Denis Smetannikov, All rights reserved.
 * @link      https://github.com/SmetDenis/CSVImport
 */


if (!defined('ROOT_PATH')) { // for PHPUnit process isolation
    define('ROOT_PATH', realpath('.'));
}

// main autoload
if ($autoload = realpath('./vendor/autoload.php')) {
    require_once $autoload;
} else {
    echo 'Please execute "composer update" !' . PHP_EOL;
    exit(1);
}
