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

/**
 * Class CodestyleTest
 * @package JBZoo\PHPUnit
 */
class CodestyleTest extends Codestyle
{
    protected $_packageName = "CSVImport";
    protected $_packageVendor = 'SmetDenis';
    protected $_packageLink = 'https://github.com/SmetDenis/_PACKAGE_';
    protected $_packageCopyright = 'Copyright (C) Denis Smetannikov, All rights reserved.';

    protected $_packageDesc = array(
        'This file is part of the SmetDenis/CSVImport Package.',
        'For the full copyright and license information, please view the LICENSE',
        'file that was distributed with this source code.',
    );
}
