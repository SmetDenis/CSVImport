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

namespace JBZoo\PHPUnit;

use JBZoo\Data\PHPArray;
use SmetDenis\CSVImport\Command\CsvCreate;
use SmetDenis\CSVImport\Command\CsvInsert;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class CsvImportTest
 * @package JBZoo\PHPUnit
 */
class CsvImportTest extends PHPUnit
{
    /**
     * @var PHPArray
     */
    protected $_config;

    /**
     * @var \mysqli
     */
    protected $_dbConect;

    /**
     * @var string
     */
    protected $_randomPath;

    protected function setUp()
    {
        parent::setUp();

        $this->_config     = new PHPArray('./tests/fixtures/config.php');
        $this->_randomPath = PROJECT_ROOT . '/tests/fixtures/random.csv';

        $this->_dbConect = mysqli_connect(
            $this->_config->find('db.host', 'localhost'),
            $this->_config->find('db.user', 'root'),
            $this->_config->find('db.pass', ''),
            $this->_config->find('db.db'),
            $this->_config->find('db.port', 3306)
        );

        $tableName = $this->_config->find('db.table');

        $this->_dbQuery("DROP TABLE IF EXISTS `{$tableName}`");
        $this->_dbQuery("CREATE TABLE `{$tableName}` (
            `created_on` DATE NOT NULL,
            `country_iso` CHAR(2) NOT NULL,
            `shows` INT(11) NOT NULL,
            `clicks` INT(11) NOT NULL,
            UNIQUE INDEX `summary_created_on_country_iso_uindex` (`created_on`, `country_iso`)
        ) COLLATE='utf8_general_ci' ENGINE=InnoDB;");

        @unlink($this->_randomPath);
    }

    /**
     * Simple query to DB
     *
     * @param $sql
     * @return array|null
     * @throws Exception
     */
    protected function _dbQuery($sql)
    {
        $result = mysqli_query($this->_dbConect, (string)$sql);

        if (!$result) {
            echo $sql;
            throw new Exception('SQL Query Error: ' . mysqli_error($this->_dbConect));
        }

        if (!is_bool($result)) {
            return mysqli_fetch_assoc($result);
        }
    }

    public function testCreate()
    {
        $command = new CommandTester(new CsvCreate());

        $lines = mt_rand(10, 100);

        $codeResult = $command->execute([
            '--lines'  => $lines,
            '--config' => PROJECT_ROOT . '/tests/fixtures/config.php'
        ]);

        $display = trim($command->getDisplay());

        isSame(0, $codeResult);
        isContain('id,created_on,country_iso,shows,clicks', $display);
        isSame($lines + 1, count(explode("\n", $display)));
    }

    public function testCreateFile()
    {
        $command = new CommandTester(new CsvCreate());

        $linesCount = mt_rand(10, 100);

        $codeResult = $command->execute([
            '--lines'       => $linesCount,
            '--config'      => PROJECT_ROOT . '/tests/fixtures/config.php',
            '--target-file' => $this->_randomPath,
        ]);

        isSame(0, $codeResult);
        isFile($this->_randomPath);
        isSame($linesCount + 1, count(file($this->_randomPath)));
    }

    public function testInsert()
    {
        $command = new CommandTester(new CsvInsert());

        $codeResult = $command->execute([
            '--config'      => PROJECT_ROOT . '/tests/fixtures/config.php',
            '--source-file' => PROJECT_ROOT . '/tests/fixtures/example.csv',
        ]);

        $display    = trim($command->getDisplay());
        $countLines = count(file(PROJECT_ROOT . '/tests/fixtures/example.csv'));

        isSame(0, $codeResult);
        isContain((string)($countLines - 1), $display);

        $result = $this->_dbQuery('SELECT count(*) AS count FROM ' . $this->_config->find('db.table'));
        is('96', $result['count']);
    }
}
