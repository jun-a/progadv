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
	
	const QUOTE_NONE = 0;
	
	const QUOTE_MINIMAL = 1;
	
	const QUOTE_NONNUMERIC = 2;
	
	const QUOTE_ALL = 3;
	
	public $delimiter = ',';
	
	public $line_end = "\r\n";
	
	public $quote_char = '"';
	
	public $escape_char = '\\';
	
	public $double_quote = '';
	
	public $skip_initial_space = true;
	
	public $skip_blank_lines = true;
	
	public $quoting =  CSV_Dialect_Base::QUOTE_NONE;
	
	public $has_header = true;
	
	public $header = array();
}
?>
