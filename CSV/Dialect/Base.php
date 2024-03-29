<?php
/**
 * Base Dialect file for the CSV Reader/Writer.
 * 
 * It holds information about the file to be processed. 
 *
 * @package CSV
 * @subpackage Dialect
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2008 Edit. All rights reserved.
 * @filesource
 */
 
/**
 * Base Dialect for the CSV Reader/Write.
 *
 * @package CSV
 * @subpackage Dialect
 * @author Peter Halasz <skinn3r@gmail.com>
 */
class CSV_Dialect_Base {
	
    /**
     * Instructs writer objets to never quote fields. When the current delimiter
     * occurs in output data it is preceded by the current $escape_char character.
     * If $escape_char is not set, the writer will raise a RuntimeException.
     * 
     * Instructs reader objects to perform no special processing of quote
     * characters.
     */
	const QUOTE_NONE = 0;
	
	/**
	 * Instructs writer objects to only quote those fields which contain special
	 * characters such as $delimiter, $quote_char or any of the characters in 
	 * $line_end.
	 */
	const QUOTE_MINIMAL = 1;
	
	/**
	 * Instructs writer objects to quote all non-numeric fields.
	 * 
	 * Instructs reader objects to convert all non-quoted fields to type float.
	 */
	const QUOTE_NONNUMERIC = 2;
	
	/**
	 * Instructs writer objects to quote all fields.
	 */
	const QUOTE_ALL = 3;
	
	/**
	 * A one-character string used to separate fields. It defaults to ','. 
	 * 
	 * @access public
	 * @var string
	 */
	public $delimiter = ',';
	
	/**
	 * The string used to terminate lines produced by the writer. 
	 * It defaults to '\r\n'. 
	 * 
	 * @access public
	 * @var string
	 */
	public $line_end = "\r\n";
	
	/**
	 * A one-character string used to quote fields containing special 
	 * characters, such as the delimiter or quotechar, or which contain 
	 * new-line characters. It defaults to '"'. 
	 * 
	 * @access public
	 * @var string
	 */
	public $quote_char = '"';
	
	/**
	 * A one-character string used by the writer to escape the delimiter if 
	 * quoting is set to QUOTE_NONE and the quotechar if doublequote is False. 
	 * On reading, the escapechar removes any special meaning from the following
	 *  character. It defaults to None, which disables escaping. 
	 * 
	 * @access public
	 * @var string
	 */
	public $escape_char = 'None';
	
	/**
	 * Controls how instances of $quote_char appearing inside a field should be 
	 * quoted. When true, the character is doubled. When false, the $escape_char
	 * is used as a prefix to the $quote_char. It defaults to true.
	 * 
	 * @access public
	 * @var boolean
	 */
	public $double_quote = true;
	
	/**
	 * When True, whitespace immediately following the delimiter is ignored. 
	 * The default is False.
	 * 
	 * @access public
	 * @var string 
	 */
	public $skip_initial_space = false;
	
	/**
	 * Controls when quotes should be generated by the writer and recognised by 
	 * the reader. It can take on any of the QUOTE_* constants and defaults 
	 * to QUOTE_MINIMAL. 
	 * 
	 * @access public
	 * @var integer
	 */
	public $quoting =  CSV_Dialect_Base::QUOTE_MINIMAL;
	
	/**
	 * When set to true, reader objects will treat the first line as a header row.
	 * Defaults to False.
	 * 
	 * @access public
	 * @var boolean
	 */
	public $has_header = false;
}
?>
