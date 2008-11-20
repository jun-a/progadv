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
class CSV_Readertest extends PHPUnit_Framework_TestCase {
    
    private $filename = "../tmp/CSVReaderTest.tmp.csv";
    
    private $file;
    
    private $reader;
    
    private function pass() {    
    }
    
    private function send_char_events(CSV_Reader &$reader, $str) {
        for($i = 0; $i < strlen($str); $i++) {
            $reader->char_event($str[$i]);
        }
    }
    
    private function get_reader_close_writer() {
        if(is_resource($this->file)) {
            fclose($this->file);
        }
        
        $dialect = new CSV_Dialect_Test();
        return new CSV_Reader($this->filename, &$dialect);
    }
    
    private function writeln($str) {
        fwrite($this->file, $str."\r\n");
    }
    
    public function setUp() {
        $this->file = fopen($this->filename, 'w');
    }
    
    
    public function tearDown() {
        if(!is_null($this->reader)) {
            $this->reader->__destruct();
        }
        
        if(is_resource($this->file)) {
            fclose($this->file);
        }
        unlink($this->filename);   
    }

    /**
     * Test for no file
     * 
     * @access public
     * @return void
     */
    public function testCreateNoFile() {
        $bogus_file = "../tmp/bogus_file";
        
        if(file_exists($bogus_file)) {
            unlink($bogus_file);
        }
        
        try {
            $dialect = new CSV_Dialect_Test();
            $reader = new CSV_Reader($bogus_file, $dialect);
        } catch (RuntimeException $e) {
            $this->pass();
            return;
        }
        
        $this->fail("Expected RuntimeException.");
    }
    
    /**
     * Test for an empty file
     * 
     * @access public
     * @return void
     */
    public function testCreateWithEmptyFile() {
        
        $reader = $this->get_reader_close_writer();
        $this->assertTrue(!$reader->has_next());
    }
    
    /**
     * Test for reading a single record.
     * 
     * @access public
     * @return void
     */
    public function testReadSingleRecord() {
        $this->writeln("single record");
        
        $this->reader = $this->get_reader_close_writer();
        
        $this->assertTrue($this->reader->has_next());
        $columns = $this->reader->next();
        $this->assertEquals(1, count($columns));
        $this->assertEquals("single record", $columns[0]);
        $this->assertTrue(!$this->reader->has_next());
    }
    
    /**
     * Test for reading 2 lines
     * 
     * @access public
     * @return void
     */
    public function testReadTwoRecords() {
        $this->writeln("record 1");
        $this->writeln("record 2");
        
        $this->reader = $this->get_reader_close_writer();
        $this->reader->next();
        
        $columns = $this->reader->next();
        
        $this->assertEquals("record 2", $columns[0]);
    }
    
    /**
     * Test for 2 columns on a line
     * 
     * @access public
     * @return void
     */
    public function testTwoColumns() {
        $this->writeln("column 1,column 2");
        
        $this->reader = $this->get_reader_close_writer();
        $columns = $this->reader->next();
        
        $this->assertEquals(2, count($columns));
        $this->assertEquals("column 1", $columns[0]);
        $this->assertEquals("column 2", $columns[1]);
    }
    
    /**
     * Test for multiple columns on a line
     * 
     * @access public
     * @return void
     */
    public function testMultipleColumns() {
        $this->writeln("column 1,column 2,column 3");
        
        $this->reader = $this->get_reader_close_writer();
        
        $columns = $this->reader->next();
        
        $this->assertEquals(3, count($columns));
        $this->assertEquals("column 1", $columns[0]);
        $this->assertEquals("column 2", $columns[1]);
        $this->assertEquals("column 3", $columns[2]);
    }
    
    /**
     * Test for state word
     * 
     * @access public
     * @return void
     */
    public function testStateOneWord() {
        $this->reader = $this->get_reader_close_writer();
        
        $this->assertEquals(CSV_Reader::STATEDELIM, $this->reader->get_state());
        $this->assertEquals("", $this->reader->get_current_word());
        
        $this->reader->char_event('t');
        $this->assertEquals(CSV_Reader::STATEINWORD, $this->reader->get_state());
        
        $dialect = new CSV_Dialect_Test();
        
        $this->reader = new CSV_Reader($this->filename, $dialect);
        
        $test_word = "test";
        $this->send_char_events($this->reader, $test_word);
        $this->reader->end_of_string_event();
        
        $this->assertEquals("test", $this->reader->get_current_column());
    }
    
    /**
     * Testing states for two columns
     * 
     * @access public
     * @return void
     */
    public function testStateTwoColumns() {
        $test_input = "word1,word2";
        
        $this->reader = $this->get_reader_close_writer();
        
        $comma_idx = strpos($test_input, ",");
        
        for($i = 0; $i < $comma_idx; $i++) {
            $this->reader->char_event($test_input[$i]);
        }
        
        $this->assertEquals("word1", $this->reader->get_current_column());
        
        $this->reader->char_event(",");
        
        $this->assertEquals(CSV_Reader::STATEDELIM, $this->reader->get_state());
        
        $columns = $this->reader->get_current_line_columns();
        $this->assertEquals(1, count($columns));
        
        $this->reader->char_event($test_input[$comma_idx + 1]);
        $this->assertEquals(CSV_Reader::STATEINWORD, $this->reader->get_state());
        
        $this->assertEquals("w", $this->reader->get_current_column());
        
        for($i = $comma_idx + 2; $i < strlen($test_input); $i++) {
            $this->reader->char_event($test_input[$i]);
        }
        
        $this->reader->end_of_string_event();
        
        $this->assertEquals("word2", $this->reader->get_current_column());
        
        $columns = $this->reader->get_current_line_columns();
        $this->assertEquals(2, count($columns));
        $this->assertEquals("word1", $columns[0]);
        $this->assertEquals("word2", $columns[1]);
    }
    
    /**
     * Test state with reading from file
     * 
     * @access public
     * @return void
     */
    public function testStateWithRead() {
        $this->writeln("record 1,x");
        
        $this->reader = $this->get_reader_close_writer();
        $this->reader->next();
        
        $columns = $this->reader->get_current_line_columns();
        
        $this->assertEquals(2, count($columns));
    }
    
    public function testDoubleQuotes() {
        //example "A,a",b
        $this->reader = $this->get_reader_close_writer();
        
        $this->reader->char_event('"');
        $this->assertEquals(CSV_Reader::STATEINQUOTEWORD, $this->reader->get_state());
        
        $this->reader->char_event('A');
        $this->reader->char_event(',');
        $this->assertEquals(CSV_Reader::STATEINQUOTEWORD, $this->reader->get_state());
        
        $this->reader->char_event('a');
        $this->reader->char_event('"');
        $this->assertEquals(CSV_Reader::STATEQUOTEINQUOTEWORD, $this->reader->get_state());
        $this->reader->char_event(',');
        $columns = $this->reader->get_current_line_columns();
        $this->assertEquals("A,a", $columns[0]);
        
        $this->assertEquals(CSV_Reader::STATEDELIM, $this->reader->get_state());
        
        $this->reader->char_event('b');
        $this->reader->end_of_string_event();
        $columns = $this->reader->get_current_line_columns();
        $this->assertEquals("b", $columns[1]);
    }
    
    /**
     * Test to ensure that commas in double quotes get read correctly.
     * 
     * @access public
     * @return void
     */
    public function testCommaInDoubleQuotes() {
        $this->writeln("\"column with a , (comma)\",column 2");
        
        $this->reader = $this->get_reader_close_writer();
        
        $columns = $this->reader->next();
        
        $this->assertEquals(2, count($columns));
        $this->assertEquals("column with a , (comma)", $columns[0]);
        $this->assertEquals("column 2", $columns[1]);
    }
    
    /**
     * Test to ensure comments are handled correctly.
     * 
     * @access public
     * @return void
     */
    public function testComment() {
        $this->writeln("line 1");
        $this->writeln("# comment line");
        $this->writeln("line 2");
        $this->reader = $this->get_reader_close_writer();
        $this->reader->next();
        
        $columns = $this->reader->next();
        $this->assertEquals("line 2", $columns[0]);
    }
    
    /**
     * Test for empty lines read.
     * 
     * @access public
     * @return void
     */
    public function testEmptyLine() {
        try {
        	$this->writeln("");
        	$this->reader = $this->get_reader_close_writer();
        	$this->reader->next();
        	$this->pass();
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }
    
    /**
     * Test for end of file.
     * 
     * @access public
     * @return void
     */
    public function testEOF() {
        $this->writeln("x");
        $this->reader = $this->get_reader_close_writer();
        
        $this->reader->next();
        
        try {
            $this->reader->next();
            $this->fail("Should have gotten exception.");
        } catch(RuntimeException $e) {
            $this->pass();
        }
    }
    
    /**
     * Test for whitespace handling
     * 
     * @access public
     * @return void
     */
    public function testStateWhitespace() {
        $test_input = " a ,\tb\t";
        
        $this->reader = $this->get_reader_close_writer();
        
        $this->reader->char_event(' ');
        
        $this->assertEquals(CSV_Reader::STATEDELIM, $this->reader->get_state());
        
        $this->reader->char_event('a');
        $this->reader->char_event(' ');
        $this->reader->char_event(',');
        
        $columns = $this->reader->get_current_line_columns();
        $this->assertEquals("a", $columns[0]);
        
        $this->reader->char_event("\t");
        $this->reader->char_event('b');
        $this->reader->char_event("\t");
        $this->reader->end_of_string_event();
        
        $columns = $this->reader->get_current_line_columns();
        $this->assertEquals("b", $columns[1]);
    }
    
    /**
     * Test double quotes
     * 
     * @access public
     * @return void
     */
    public function testStateQuotes() {
        $this->reader = $this->get_reader_close_writer();
        $this->send_char_events($this->reader, ' " x, "');
        
        $this->assertEquals(CSV_Reader::STATEQUOTEINQUOTEWORD, $this->reader->get_state());
        
        $this->reader->char_event(',');
        
        $columns = $this->reader->get_current_line_columns();
        $this->assertEquals(" x, ", $columns[0]);
        
        $this->send_char_events($this->reader, " \"y\" ");
        $this->reader->end_of_string_event();
        
        $columns = $this->reader->get_current_line_columns();
        $this->assertEquals("y", $columns[1]);
    }
    
    /**
     * Test for empty fields
     * 
     * @access public
     * @return void
     */
    public function testEmptyFields() {
        $this->writeln("");
        $this->writeln(",");
        $this->writeln(",a,,,");
        $this->reader = $this->get_reader_close_writer();
        
        $columns = $this->reader->next();
        $this->assertEquals(1, count($columns));
        $this->assertEquals("", $columns[0]);
        
        $columns = $this->reader->next();
        $this->assertEquals(2, count($columns));
        
        $columns = $this->reader->next();
        $this->assertEquals(5, count($columns));
    }
    
    /**
     * Test for unmatched double quotes.
     * 
     * @access public
     * @return void
     */
    public function testUnmatchedDoubleQuoteIsAnError() {
        $this->writeln("\"jkl");
        $this->reader = $this->get_reader_close_writer();
        
        try {
            $this->reader->next();
            $this->fail("Should have thrown a Runtime Exception");
        } catch(RuntimeException $e) {
            $this->pass();
        }
    }
}
?>