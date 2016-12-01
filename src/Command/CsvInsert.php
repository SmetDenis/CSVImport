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

use JBZoo\Data\Data;
use JBZoo\Data\PHPArray;
use JBZoo\SqlBuilder\Query\Insert;
use JBZoo\SqlBuilder\SqlBuilder;
use League\Csv\Reader;
use SmetDenis\CSVImport\Command;
use SmetDenis\CSVImport\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CsvInsert
 * @package SmetDenis\CSVImport\Command
 */
class CsvInsert extends Command
{
    /**
     * @var \mysqli
     */
    protected $_dbConect;

    /**
     * Configuration of command
     */
    protected function configure() // @codingStandardsIgnoreLine
    {
        $this
            ->setName('csv:insert')
            ->setDescription('Insert CSV Dataset to MySQL database')
            ->addOption(
                'config',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to config file',
                null
            )
            ->addOption(
                'source-file',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path to source CSV file. If it\'s empty dataset reads from STDIN',
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

        $config = new PHPArray($input->getOption('config'));
        $this->_initDbConection($config);

        $sourceFile = $input->getOption('source-file');

        // Init and prepare CSV Writer
        if ($sourceFile) {
            if (!file_exists($sourceFile)) {
                throw new Exception('Source file not found: ' . $sourceFile);
            }

            $reader = Reader::createFromPath($sourceFile, 'r+');
        } else {
            $csvRaw = file_get_contents('php://stdin');
            $reader = Reader::createFromString($csvRaw);
        }

        $this->_showProfiler('Before init reader');

        $reader
            ->setDelimiter($config->get('delimiter', ','))
            ->setNewline($config->get('newline', "\n"))
            ->setEscape($config->get('escape', "\\"))
            ->setEnclosure($config->get('enclosure', '"'))
            ->setInputEncoding($config->get('input_ecoding', 'UTF-8'));

        $this->_showProfiler('After init reader');

        // TODO: Remove it for count of lines more then 2M
        $totalCount = $reader->each(function () {
            return true;
        });

        $this->_showProfiler('Total count');

        $tableName  = $config->find('db.table');
        $tableNameQ = SqlBuilder::get()->quoteName($config->find('db.table'));

        // Generate random content step-by-step (show progress bar for save2file-mode)
        $this->_progressBar(
            'Add new CSV dataset into database',
            $totalCount - 1, // Todo: Check CSV header
            $config->find('insert.step_size'),
            function ($currentStep, $stepSize) use ($reader, $tableName, $tableNameQ) {

                $rawRows = $reader
                    ->setOffset($currentStep)
                    ->setLimit($stepSize)
                    ->fetchAssoc(0, function ($row) {
                        unset($row['id']);
                        return $row;
                    });

                $rows = iterator_to_array($rawRows, true);

                if ($rows) {
                    $insert = new Insert($tableName);
                    $sql    = (string)$insert->multi($rows)
                        . ' ON DUPLICATE KEY UPDATE'
                        . ' `shows`=`shows` + ' . $tableNameQ . '.`shows`,'
                        . ' `clicks`=`clicks` + ' . $tableNameQ . '.`clicks`';

                    $this->_dbQuery($sql);
                }
            }
        );

        $this->_showProfiler('Ok');
    }

    /**
     * @param Data $config
     */
    protected function _initDbConection(Data $config)
    {
        $this->_dbConect = mysqli_connect(
            $config->find('db.host', 'localhost'),
            $config->find('db.user', 'root'),
            $config->find('db.pass', ''),
            $config->find('db.db'),
            $config->find('db.port', 3306)
        );

        SqlBuilder::set('Mysqli', $this->_dbConect, '');
    }

    /**
     * TODO: Use some package for mysqli queries
     * @param $sql
     * @throws Exception
     */
    protected function _dbQuery($sql)
    {
        $result = mysqli_query($this->_dbConect, (string)$sql);

        if (!$result) {
            $this->_($sql);
            throw new Exception('SQL Query Error: ' . mysqli_error($this->_dbConect));
        }
    }
}
