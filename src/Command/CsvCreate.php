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

namespace SmetDenis\CSVImport\Command;

use JBZoo\Data\PHPArray;
use League\Csv\Writer;
use SmetDenis\CSVImport\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CsvCreate
 * @package SmetDenis\CSVImport\Command
 */
class CsvCreate extends Command
{
    /**
     * Configuration of command
     */
    protected function configure() // @codingStandardsIgnoreLine
    {
        $this
            ->setName('csv:create')
            ->setDescription('Create random CSV file')
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to config file',
                null
            )
            ->addOption(
                'lines',
                null,
                InputOption::VALUE_REQUIRED,
                'Count of lines',
                '1000'
            )
            ->addOption(
                'target-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to new CSV file. If it\'s empty dataset go to STDOUT',
                null
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        // Init command and extract config variables
        $this->_executePrepare($input, $output);

        $config      = new PHPArray($input->getOption('config'));
        $targetFile  = $input->getOption('target-file');
        $createLines = $input->getOption('lines');

        // Init and prepare CSV Writer
        if ($targetFile) {

            if (file_exists($targetFile)) {
                unlink($targetFile);
            }

            $writer = Writer::createFromPath($targetFile, 'w');
        } else {
            $writer = Writer::createFromString('');
        }

        $writer
            ->setDelimiter($config->get('delimiter', ','))
            ->setNewline($config->get('newline', "\n"))
            ->setEscape($config->get('escape', "\\"))
            ->setEnclosure($config->get('enclosure', '"'))
            ->setInputEncoding($config->get('input_ecoding', 'UTF-8'))
            ->insertOne($config->get('header'));

        $this->_showProfiler('Before generator');

        // Generate random content step-by-step (show progress bar for save2file-mode)
        $this->_progressBar(
            'Generate CSV file',
            $createLines,
            $config->find('create.step_size'),
            function ($start, $size) use ($writer, $createLines) {

                // TODO: Remove progress bar hack! :(
                $max = min($start + $size, $createLines);
                $min = $start + 1;

                $content = [];
                for ($id = $min; $id <= $max; $id++) { // TODO: Think about array_reduce for speedup, benchmark it!
                    $content[] = $this->_createRandomRow($id);
                }

                $writer->insertAll($content); // Flash pack to tmp file
            },
            !$targetFile // Show progress bar only for save2file mode
        );

        $this->_showProfiler('After generator');

        if (!$targetFile) {
            $this->_($writer->__toString());
        }

        $this->_showProfiler('Ok');
    }

    /**
     * @param int $newId
     * @return array
     */
    protected function _createRandomRow($newId)
    {
        $countries = ['ru', 'ua', 'by', 'uk', 'ch', 'jp']; // TODO: Fake list move to config
        $countryId = $newId % count($countries);

        return [
            $newId,
            date('Y-m-d', time() + 86400 * (int)mt_rand(-1000, 1000)),
            $countries[$countryId],
            mt_rand(1, 3),
            mt_rand(1, 3),
        ];
    }
}
