<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'String_AllTests::main');
}

require_once 'String_Buffertest.php';
    
class String_AllTests {
    
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite() {
        $suite = new PHPUnit_Framework_TestSuite('Strings');

        $suite->addTestSuite('String_Buffertest');
            
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'String_AllTests::main') {
    String_AllTests::main();
}
?>