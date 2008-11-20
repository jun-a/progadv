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
    private $writer;
    
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
        $this->dialect->quoting = CSV_Dialect_Base::QUOTE_NONE;
        $this->file_data = array("name" => BASE_PATH."/tmp/writer.tmp.csv",
                      "mode" => "w");
    }
    
    public function tearDown() {
        if(file_exists($this->file_data['name'])) {
            unlink($this->file_data['name']);
        }
    }
    
    private function &get_writer() {
        return new CSV_Writer($this->file_data, $this->dialect);
    }
    
    private function &get_reader() {
        return new CSV_Reader($this->file_data['name'], $this->dialect);
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
    
    public function testWritingRowToFile() {
        
        $writer = $this->get_writer();
        
        $data = array("Column 1", "Column 2");
        
        $writer->write_row($data);
        unset($writer);
        
        $reader = $this->get_reader();
        
        $columns = $reader->next();
        $this->assertEquals(2, count($columns));
        $this->assertEquals("Column 1", $columns[0]);
        $this->assertEquals("Column 2", $columns[1]);
    }
    
    public function testWriteMultipleRows() {
        $data = array(
                    array("Column 11", "Column 12"),
                    array("Column 21", "Column 22")
                    );
                    
        $writer = $this->get_writer();
        
        $writer->write_rows($data);
        unset($writer);
        
        $reader = $this->get_reader();
        
        $columns = $reader->next();
        $this->assertEquals(2, count($columns));
        $this->assertEquals("Column 11", $columns[0]);
        $this->assertEquals("Column 12", $columns[1]);
        
        $columns = $reader->next();
        $this->assertEquals(2, count($columns));
        $this->assertEquals("Column 21", $columns[0]);
        $this->assertEquals("Column 22", $columns[1]);
    }
    
    public function testWriteUnevenLengthRows() {
        $data = array(
                    array("Column 11", "Column 21"),
                    array("Column 21")
                    );
        
        $writer = $this->get_writer();
        
        try {
            $writer->write_rows($data);
            $this->fail("Expected RuntimeException.");
        } catch(RuntimeException $e) {
            $this->pass();
        }
        
    }
    
    public function testWriteNoQuotes() {
        $data = array("Column 1", "Column 2");
        
        $this->dialect->quoting = CSV_Dialect_Base::QUOTE_NONE;
        
        $writer = $this->get_writer();
        
        $writer->write_row($data);
        unset($writer);
        
        $contents = file_get_contents($this->file_data['name']);
        
        $this->assertEquals("Column 1,Column 2".$this->dialect->line_end, $contents);
    }
    
    public function testWriteQuoteMinimal() {
        $data = array('Column " , (comma) 1', "Column 2");
        
        $this->dialect->quoting = CSV_Dialect_Base::QUOTE_MINIMAL;
        $this->dialect->escape_char = "\\";
        
        $writer = $this->get_writer();
        
        $writer->write_row($data);
        unset($writer);
        
        $contents = file_get_contents($this->file_data['name']);
        
        $this->assertEquals('"Column "" , (comma) 1",Column 2'
                . $this->dialect->line_end, $contents);
    }
    
    public function testWriteQuoteNonNumeric() {
        $data = array('Column 1', 15, "1Something");
        
        $this->dialect->quoting = CSV_Dialect_Base::QUOTE_NONNUMERIC;
        
        $writer = $this->get_writer();
        
        $writer->write_row($data);
        unset($writer);
        
        $contents = file_get_contents($this->file_data['name']);
        
        $this->assertEquals('"Column 1",15,"1Something"'
                . $this->dialect->line_end,$contents);
    }
    
    public function testWriteQuoteAll() {
        $data = array("Column 1", 234, "3 Column");
        
        $this->dialect->quoting = CSV_Dialect_Base::QUOTE_ALL;
        
        $writer = $this->get_writer();
        
        $writer->write_row($data);
        unset($writer);
        
        $contents = file_get_contents($this->file_data['name']);
        
        $this->assertEquals('"Column 1","234","3 Column"'
                . $this->dialect->line_end,$contents);
    }
}

?>