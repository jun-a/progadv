<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'CSV_AllTests::main');
}

require_once 'CSV_Readertest.php';
require_once 'CSV_Writertest.php';
    
class CSV_AllTests {
    
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('CSV Reader');

        $suite->addTestSuite('CSV_Readertest');
        $suite->addTestSuite('CSV_Writertest');
            
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'CSV_AllTests::main') {
    CSV_AllTests::main();
}
?>