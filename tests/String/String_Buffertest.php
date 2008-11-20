<?php
/**
 * PHPUnit test case for the String Buffer class
 *
 * @package String
 * @subpackage Buffer
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @copyright (c) 2006, 2007, 2008 Peter Halasz. All rights reserved.
 * @filesource
 */

/**
 * PHPUnit test case for the String Buffer class
 *
 * @package String
 * @subpackage Buffer
 * @author Peter Halasz <skinn3r@gmail.com>
 */
class String_Buffertest extends PHPUnit_Framework_TestCase {
    
    
    private $buffer;
    
    private $test_str = "test_string";
    
    
    public function setUp() {
        $this->buffer = new String_Buffer($this->test_str);
    }
    
    public function tearDown() {
        
    }
    
    /**
     * Dummy function to make exception testing easier
     * 
     * @access private
     * @return void
     */
    private function pass() {}
    
    /**
     * Test to see if creating a buffer with no args works
     * 
     * @access public
     * @return void
     */
    public function testCreateWithNoArgs() {
        $buffer = new String_Buffer();
        $this->assertEquals(0, $buffer->length());
    }
    
    /**
     * Test to see if correctly sized buffers are created
     * 
     * @access public
     * @return void
     */
    public function testCreateWithLength() {
        $buffer = new String_Buffer(10);
        $this->assertEquals(10, $buffer->length());
        
        try {
            /* Length cannot be less than 0 */
            $buffer = new String_Buffer(-1);
        } catch(OutOfBoundsException $e) {
            $this->pass();
        } catch(Exception $e) {
            $this->fail("Expected OutOfBoundsException");
        }
    }
    
    /**
     * Test to see if correctly sized buffer is created.
     * 
     * @access public
     * @return void
     */
    public function testCreateWithString() {
        $test_str = "test";
        $buffer = new String_Buffer($test_str);
        
        $this->assertEquals(4, $buffer->length());
    }
    
    /**
     * Test for setting the length of the buffer
     * 
     * @access public
     * @return void
     */
    public function testSetLenght() {
        $test_str = "test";
        $buffer = new String_Buffer($test_str);
        
        $buffer->set_length(2);
        $this->assertEquals(2, $buffer->length());
        
        $buffer->set_length(10);
        $this->assertEquals(10, $buffer->length());
    }
    
    /**
     * Test for returning a character from the buffer
     * 
     * @access public
     * @return void
     */
    public function testCharAt() {
        $test_str = "test string";
        $buffer = new String_Buffer($test_str);
        
        $this->assertEquals("s", $buffer->char_at(2));
        
    }
    
    /**
     * Test for get chars functionality.
     * 
     * @access public
     * @return void
     */
    public function testGetChars() {
        
        $test = array();
        
        $this->buffer->get_chars(1, 6, &$test, 2);
        
        $this->assertEquals(5, count($test));
        $this->assertEquals("s", $test[3]);
    }
    
    /**
     * Test setting a character at a specified index.
     * 
     * @access public
     * @return void
     */
    public function testSetCharAt() {
                
        $this->buffer->set_char_at(4,"!");
        $this->assertEquals("!", $this->buffer->char_at(4));
    }
    
    /**
     * Test appending a string to the end of the buffer
     * 
     * @access public
     * @return void
     */
    public function testAppendString() {
        $str = "_appended";
        $test = & $this->buffer->append($str);
       
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("test_string_appended", $test->__toString());
    }
    
    /**
     * Test appending an object to the end of the buffer
     * 
     * @access public
     * @return void
     */
    public function testAppendObject() {
        $object = new testobject();
               
        $test = & $this->buffer->append($object);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("test_stringObject appended", $test->__toString());
    }
    
    /**
     * Test appending a String buffer to this buffer
     * 
     * @access public
     * @return void
     */
    public function testAppendStringBuffer() {
        $sb = new String_Buffer("Test buffer");
        
        $test = &$this->buffer->append($sb);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("test_stringTest buffer", $test->__toString());
    }
    
    /**
     * Test appending an array of characters to this buffer.
     * 
     * @access public
     * @return void
     */
    public function testAppendCharArray() {
        $chars = array("_","a","p","p","e","n","d","e","d");
        
        $test = &$this->buffer->append($chars);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("test_string_appended", $test->__toString());
    }
    
    /**
     * Test appending an integer to this buffer.
     * 
     * @access public
     * @return void
     */
    public function testAppendInt() {
        $int = 15;
        
        $test = &$this->buffer->append($int);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("test_string15", $test->__toString());
    }
    
    /**
     * Test appending a float to this buffer
     * 
     * @access public
     * @return void
     */
    public function testAppendFloat() {
        $float = 6.3;
        
        $test = &$this->buffer->append($float);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("test_string6.3", $test->__toString());
    }
    
    /**
     * Test deleting a subarray of a buffer.
     * 
     * @access public
     * @return void
     */
    public function testDelete() {
        $test = &$this->buffer->delete(2,5);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("testring", $test->__toString());
    }
    
    /**
     * Test deleting a character from the buffer.
     * 
     * @access public
     * @return void
     */
    public function testDeleteCharAt() {
        $test = &$this->buffer->delete_char_at(2);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals(10, $test->length());
        $this->assertEquals("tet_string", $test->__toString());
    }
    
    /**
     * Test replacing a substring in the buffer.
     * 
     * @access public
     * @return void
     */
    public function testReplace() {
        $test = &$this->buffer->replace(2,5,"ble");
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals(11, $test->length());
        $this->assertEquals("teblestring", $test->__toString());
        
        $test = &$this->buffer->replace(2,15,"replaced!");
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals(11, $test->length());
        $this->assertEquals("tereplaced!", $test->__toString());
    }
    
    /**
     * Test retrieving a substring of the buffer.
     * 
     * @access public
     * @return void
     */
    public function testSubString() {
        $str = $this->buffer->sub_string(5);
        
        $this->assertTrue(is_string($str));
        $this->assertEquals("string", $str);
    }
    
    /**
     * Test inserting a string into the buffer.
     * 
     * @access public
     * @return void
     */
    public function testInsertStr() {
        $str = "_inserted";
        
        $test = & $this->buffer->insert(5, $str);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals(20, $test->length());
        $this->assertEquals("test__insertedstring", $test->__toString());
    }
    
    /**
     * Test inserting an object into the buffer.
     * 
     * @access public
     * @return void
     */
    public function testInsertObject() {
        $object = new testobject();
               
        $test = & $this->buffer->insert(5, $object);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("test_Object appendedstring", $test->__toString());
    }
    
    /**
     * Test inserting an array into the buffer.
     * 
     * @access public
     * @return void
     */
    public function testInsertArray() {
        $array = array("a","r","r","a","y");
        
        $test = & $this->buffer->insert(5, $array);
        
        $this->assertTrue($test instanceof String_Buffer);
        $this->assertEquals("test_arraystring", $test->__toString());
    }
    
    /**
     * Test correct functionality of index_of(string).
     * 
     * @access public
     * @return void
     */
    public function testIndexOf() {
        $str = "_str";
        
        $this->assertEquals(4, $this->buffer->index_of($str));
    }
    
    /**
     * Test reversing the string in the buffer.
     * 
     * @access public
     * @return void
     */
    public function restReverse() {
        $this->buffer->reverse();
        
        $this->assertEquals("gnirts_tset", $this->buffer->__toString());
    }
    
    /**
     * Test last_index_of(string,offset)
     * 
     * @access public
     * @return void
     */
    public function testLastIndexOf() {
        $str = "st";
        
        $this->assertEquals(5, $this->buffer->last_index_of($str));
    }
}
?>