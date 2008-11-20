<?php
session_start();
/**
 * PHPUnit Test Suite
 *
 * @package Tests 
 * @author Peter Halasz <skinn3r@gmail.com>
 * @filesource 
 */

require_once 'lib/TestInit.php';
    
// Suites
require_once 'CSV/CSV_AllTests.php';
require_once 'String/String_AllTests.php';



if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

class AllTests  {
    
    public static function main() {
            PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Research Tests');
    
        $suite->addTest(CSV_AllTests::suite());
        $suite->addTest(String_AllTests::suite());
        
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}

?>