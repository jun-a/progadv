<?php
/**
 * LangIsland Dialect file for the CSV Reader/Writer.
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
 * LangIsland Dialect for the CSV Reader/Write.
 *
 * @package CSV
 * @subpackage Dialect
 * @author Peter Halasz <skinn3r@gmail.com>
 */
class CSV_Dialect_LangIsland extends CSV_Dialect_Base {
    public $delimiter = ";";
    
    public $line_end = "\r\n";
    
    public $has_header = true;
}
?>
