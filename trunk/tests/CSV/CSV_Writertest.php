<?php
/**
 * PHPUnit test case for the CSV reader class
 *
 * @package CSV_Reader
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @copyright (c) 2008 Peter Halasz. All rights reserved.
 * @filesource
 */

/**
 * PHPUnit test case for the CSV reader class
 *
 * @package CSV_Reader
 * @author Peter Halasz <skinn3r@gmail.com>
 */
class CSV_Writertest extends PHPUnit_Framework_TestCase {

    /**
     * Dialect file used for testing
     * 
     * @access private
     * @var CSV_Dialect_Test
     */
    private $dialect;
    
    /**
     * The CSV_Reader we use for reading the written data
     * 
     * @access private
     * @var CSV_Reader
     */
    private $reader;
    
    /**
     * File information
     * 
     * @access private
     * @var string[]
     */
    private $file_data;
    
    /**
     * Dummy function for passing tests.
     * 
     * @access private
     * @return void
     */
    private function pass() {}
    
    public function setUp() {
        $this->dialect = new CSV_Dialect_Test();
        $this->file_data = array("name" => BASE_PATH."/tmp/writer.tmp.csv",
                      "mode" => "w");
    }
    
    public function tearDown() {
        
    }
    
    public function testWithNoFile() {
        
        try {
            $writer = new CSV_Writer(null);
            $this->fail("Expected RuntimeException.");
        } catch(RuntimeException $e) {
            $this->pass();
        }
        
    }
    
    public function testWithFileString() {
        
        try {
            $writer = new CSV_Writer("bogus");
            $this->fail("Expected RuntimeException");
        } catch(RuntimeException $e) {
            $this->pass();
        }
    }
    
    public function testWithFileArrayAndDefaults() {

        $writer = new CSV_Writer($this->file_data);
        
        $this->assertEquals(BASE_PATH."/tmp/writer.tmp.csv", $writer->get_filename());
        $this->assertTrue($writer->get_dialect() instanceof CSV_Dialect_Base);
    }
    
    public function testWithCustomDialect() {
        $writer = new CSV_Writer($this->file_data, $this->dialect);
        
        $this->assertEquals(BASE_PATH."/tmp/writer.tmp.csv", $writer->get_filename());
        $this->assertTrue($writer->get_dialect() instanceof CSV_Dialect_Test);
    }
}

?>