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

return [
    'db'            => [
        'table' => 'summary',
        'host'  => '127.0.0.1',
        'user'  => 'root',
        'pass'  => '',
        'db'    => 'csv_import',
        'port'  => 3306,
    ],
    'create'        => [
        'step_size' => 10000,
    ],
    'insert'        => [
        'step_size' => 10000,
    ],
    'delimiter'     => ',',
    'escape'        => "\\",
    'enclosure'     => "\"",
    'newline'       => "\n",
    'input_ecoding' => 'UTF-8',
    'header'        => ['id', 'created_on', 'country_iso', 'shows', 'clicks']
];
