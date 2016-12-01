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

use Symfony\Component\Console\Command\Command as CommandSymfony;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 * @package SmetDenis\CSVImport
 */
abstract class Command extends CommandSymfony
{
    /**
     * @var OutputInterface
     */
    protected $_out;
    /**
     * @var InputInterface
     */
    protected $_in;

    /**
     * @return int
     */
    protected function _isDebug()
    {
        return $this->_out->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function _executePrepare(InputInterface $input, OutputInterface $output)
    {
        $this->_out = $output;
        $this->_in  = $input;
    }

    /**
     * @param string|array $messages The message as an array of lines of a single string
     */
    protected function _($messages)
    {
        $this->_out->writeln($messages);
    }

    /**
     * @param string $label
     * @return bool
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function _showProfiler($label)
    {
        if (!$this->_isDebug()) {
            return false;
        }

        // memory
        $memoryCur  = round(memory_get_usage(false) / 1024 / 1024, 2);
        $memoryCur  = sprintf("%.02lf", round($memoryCur, 2));
        $memoryPeak = round(memory_get_peak_usage(false) / 1024 / 1024, 2);
        $memoryPeak = sprintf("%.02lf", round($memoryPeak, 2));

        // time
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        $time = sprintf("%.02lf", round($time, 2));

        // Show
        $message = array(
            'Memory: ' . $memoryCur . 'MB',
            'Mem.Peak: ' . $memoryPeak . 'MB',
            "Time: " . $time . 's',
            $label ? '(' . $label . ')' : ' ',
        );

        $this->_(implode("  |  ", $message), 'Info');
    }

    /**
     * Show progress bar and run the loop
     *
     * @param string   $name
     * @param int      $total
     * @param int      $stepSize
     * @param \Closure $callback
     * @param bool     $mute
     */
    protected function _progressBar($name, $total, $stepSize, $callback, $mute = false)
    {
        if (!$mute) {
            $this->_('Current progress of ' . $name . ' (Wait! or `Ctrl+C` to cancel):');
            $progressBar = new ProgressBar($this->_out, $total);
            $progressBar->display();
            $progressBar->setRedrawFrequency(1);
        }

        for ($currentStep = 0; $currentStep <= $total; $currentStep += $stepSize) {
            $callbackResult = $callback($currentStep, $stepSize);
            if ($callbackResult === false) {
                break;
            }

            if (isset($progressBar)) {
                $progressBar->setProgress($currentStep);
            }
        }

        if (isset($progressBar)) {
            $progressBar->finish();
            $this->_(''); // Progress bar hack for rendering
        }
    }
}
