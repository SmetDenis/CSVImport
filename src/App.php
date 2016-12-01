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

namespace SmetDenis\CSVImport;

use JBZoo\Utils\FS;
use Symfony\Component\Console\Application;

/**
 * Class App
 * @package SmetDenis\CSVImport
 */
class App extends Application
{
    /**
     * @var array
     */
    private $_logo = array(
        "   _____  _______      __  _____                            _    ",
        "  / ____|/ ____\ \    / / |_   _|                          | |   ",
        " | |    | (___  \ \  / /    | |  _ __ ___  _ __   ___  _ __| |_  ",
        " | |     \___ \  \ \/ /     | | | '_ ` _ \| '_ \ / _ \| '__| __| ",
        " | |____ ____) |  \  /     _| |_| | | | | | |_) | (_) | |  | |_  ",
        "  \_____|_____/    \/     |_____|_| |_| |_| .__/ \___/|_|   \__| ",
        "                                          | |                    ",
        "                                          |_|                    ",
    );

    /**
     * Register commads by directory path
     *
     * @param string $commandsDir The commands class directory
     * @throws \Exception
     */
    public function registerCommands($commandsDir)
    {
        if (!is_dir($commandsDir)) {
            throw new \Exception('First argument is not directory!');
        }

        $this->_registerCommands($commandsDir);
    }

    /**
     * Register commands
     *
     * @param $commandsDir
     * @return bool
     */
    protected function _registerCommands($commandsDir)
    {
        $files = FS::ls($commandsDir);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $file) {

            require_once $file;

            $reflection = new \ReflectionClass(__NAMESPACE__ . '\\Command\\' . FS::filename($file));

            if ($reflection->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') &&
                !$reflection->isAbstract()
            ) {
                $this->add($reflection->newInstance());
            }
        }

        return true;
    }

    /**
     * Returns the long version of the application.
     * @return string The long application version
     */
    public function getLongVersion()
    {
        $logo   = '<info>' . implode('</info>' . PHP_EOL . '<info>', $this->_logo) . '</info>';
        $author = '<comment>by SmetDenis</comment>';

        return $logo . ' ' . $author;
    }
}
