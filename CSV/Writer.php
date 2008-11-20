<?php
/**
 * CSV writer class.
 * 
 * @package CSV
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @copyright (c) 2008 Peter Halasz. All rights reserved.
 * @filesource 
 */

/**
 * CSV writer class.
 * 
 * Provides and easy way for writing csv files.
 * 
 * @package CSV
 * @author Peter Halasz <skinn3r@gmail.com>
 */
class CSV_Writer {
    
    /**
     * The dialect object.
     * 
     * This object tells us how we want to format our CSV file.
     * 
     * @access private
     * @var CSV_Dialect_*
     */
    private $dialect;
    
    /**
     * The file pointer.
     * 
     * @access private
     * @var resource
     */
    private $fp;
    
    /**
     * The path to the file we are writing
     * 
     * @access private
     * @var string
     */
    private $filename;
    
    /**
     * The rows we want to write out to file.
     * 
     * @access private
     * @var string[]
     */
    private $rows = array();
    
    /**
     * Pear Log object.
     * 
     * @access private
     * @var PEAR_Log
     */
    private $log;
    
    /**
     * The object constructor.
     * 
     * @access public
     * @param CSV_Dialect_Base $dialect The Dialect object
     * @param string[] $file Array of info about our file.
     * @return void
     */
    public function __construct($file, &$dialect = null) {
        
        /* Set internal encoding to UTF-8 */
        mb_internal_encoding("UTF-8");
        
        if(is_null($file) || $file == "") {
            throw new RuntimeException("File cannot be null");
        }
        
        if(!is_array($file)) {
            throw new RuntimeException("Array expected, got string.");
        }
        
        /* If we didn't get a Dialect object use basic settings */
        if(is_null($dialect)) {
            $dialect = new CSV_Dialect_Base();
        }
        
        $this->dialect = $dialect;
        
        /* Open the file according to the dialect settings */
        if(is_null($file['mode'])) {
            $file['mode'] = "w";
        }
        
        $this->fp = fopen($file['name'], $file['mode']);
        $this->filename = $file['name'];
    }
    
    /**
     * Destructor function.
     * 
     * Closes the file and the log.
     */
    public function __destruct() {
        if(is_resource($this->fp)) {
            fclose($this->fp);
        }
        
        if($this->log instanceof Log) {
            $this->log->close();
        }
    }
    
    /**
     * Return the filename and path.
     * 
     * @access public
     * @return string
     */
    public function get_filename() {
        return $this->filename;
    }
    
    /**
     * Return the Dialect object's reference
     * 
     * @access public
     * @return CSV_Dialect_*
     */
    public function &get_dialect() {
        return $this->dialect;
    }
    
    /**
     * Write a single row
     * 
     * @access public
     * @param array $data
     * @return void
     */
    public function write_row(Array $data) {
        $this->rows[] = $data;
        
        $this->write_data();
    }
    
    /**
     * Write multiple rows.
     * 
     * @access public
     * @param array $data
     * @return void
     */
    public function write_rows(Array $data) {
        
        $this->rows = $data;
        
        /*
         * Check if all rows have equal number of columns
         * else throw RuntimeException
         */
        $first = array_shift($data);
        $len = count($first);
        
        foreach($data as $row) {
            if(count($row) != $len) {
                throw new RuntimeException("Input data does not have equal length rows.");
            }
        }        
        
        $this->write_data();
    }
    
    /**
     * Format and write out the data to file.
     * 
     * @access private
     * @return void
     */
    private function write_data() {
        
        foreach($this->rows as $idx => $row) {
            $this->rows[$idx] = $this->format_row($row);
        }
        
        $rows = implode($this->dialect->line_end, $this->rows) . $this->dialect->line_end;
        
        fwrite($this->fp, $rows);
    }
    
    /**
     * Formats a row of data according to the dialect.
     * 
     * @access private
     * @param string[] $row
     * @return string
     */
    private function format_row($row) {
        
        foreach($row as &$column) {
            /*
             * Quote columns if needed. Specified by dialect's quoting property.
             */
            switch($this->dialect->quoting) {
                case CSV_Dialect_Base::QUOTE_NONE:
                    /* Escape delimiter characters */
                    if($this->has_delimiter($column)) {
                        $column = $this->escape_delimiter($column);
                    }
                    break;
                case CSV_Dialect_Base::QUOTE_NONNUMERIC:
                    if($this->is_non_numeric($column)) {
                        $column = $this->quote($this->escape($column));
                    }
                    break;
                case CSV_Dialect_Base::QUOTE_ALL:
                    $column = $this->quote($column);
                    break;
                case CSV_Dialect_Base::QUOTE_MINIMAL:
                default:
                    if($this->has_special_chars($column)) {
                        $column = $this->quote($this->escape($column));
                    }
            }
        }
        
        return implode($this->dialect->delimiter, $row);
    }
    
    /**
     * Escape special charactes in the $column argument.
     * 
     * @access private
     * @param string $column
     * @return string
     */
    private function escape(&$column) {

        //$column = $this->escape_delimiter($column);
        
        if($this->dialect->double_quote) {
            $column = $this->double_quote($column);
        } else {
            $column = $this->escape_quote($column);
        }
        
        return $column;
    }
    
    /**
     * Escapes the quote character found in output.
     * If escape character is None throws RuntimeException.
     * 
     * @access private
     * @param string $column
     * @throws RuntimeException
     * @return string 
     */
    private function escape_quote(&$column) {
        if ($this->dialect->escape_char == 'None') {
            throw new RuntimeException("Couldn't escape quote character. "
                        . "No escape character set in ".get_class($this->dialect)
                        . " dialect.");
        }
        
        $column = mb_ereg_replace($this->dialect->quote_char
                    , $this->dialect->escape_char . $this->dialect->quote_char
                    , $column);
                    
        return $column;
    }
    
    /**
     * Doubles all quote characters in output.
     * Call before quoting the column.
     * 
     * @access private
     * @param string $column
     * @return string
     */
    private function double_quote(&$column) {
        $column = mb_ereg_replace($this->dialect->quote_char
                    , $this->dialect->quote_char . $this->dialect->quote_char
                    , $column);
                    
        return $column;
    }
    
    /**
     * Escape delimiters in output with escape character set in dialect.
     * If escape character is None throws RuntimeException.
     * 
     * @access private
     * @param string $column
     * @throws RuntimeException
     * @return string
     */
    private function escape_delimiter(&$column) {
        if ($this->dialect->escape_char == 'None') {
            throw new RuntimeException("Couldn't escape delimiter."
                        . "No escape character set in ".get_class($this->dialect)
                        . " dialect.");
        }
        
        $column = mb_ereg_replace($this->dialect->delimiter
                    , $this->dialect->escape_char . $this->dialect->delimiter
                    , $column);
                    
        return $column;
    }
    
    
    /**
     * Check wether the $column argument contains the $delimiter character set
     * in the dialect.
     * 
     * @access private
     * @param string $column
     * @return boolean
     */
    private function has_delimiter(&$column) {
        mb_ereg_search_init($column, $this->dialect->delimiter);
        return mb_ereg_search();
    }
    
    /**
     * Check for special characters such as delimiter, quote_char
     * 
     * @access private
     * @param string $column
     * @return boolean
     */
    private function has_special_chars(&$column) {
        $pattern = "[.*"
                .$this->dialect->delimiter."|.*"
                .$this->dialect->quote_char."]";
        mb_ereg_search_init($column, $pattern);
                
        $ret = mb_ereg_search();
        
        return $ret;
    }
    
    /**
     * Checks wether the column is numeric or not.
     * 
     * @access private
     * @param string $column
     * @return boolean
     */
    private function is_non_numeric($column) {
        $pattern = ".*^[0-9]*\.?[0-9]+$";
        
        $ret = !mb_ereg_match($pattern, $column);
        
        return $ret;
    }
    
    /**
     * Put quote chars defined by the dialect around the column.
     * 
     * @access private
     * @param string $column The column to quote.
     * @return string
     */
    private function quote($column) {
        return $this->dialect->quote_char . $column 
                . $this->dialect->quote_char;
    }
}
?>