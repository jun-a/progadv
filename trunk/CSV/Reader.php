<?php
/**
 * CSV reader class.
 * 
 * @package CSV
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @copyright (c) 2008 Peter Halasz. All rights reserved.
 * @filesource 
 */

//define("DEBUG", true);

/**
 * CSV reader class.
 * 
 * @package CSV
 * @author Peter Halasz <skinn3r@gmail.com>
 */
class CSV_Reader {
    
    /**
     * Parser State for delimiters
     * 
     * @access public
     * @static 
     * @var integer
     */
    const STATEDELIM = 0;
    
    /**
     * Parser state for in word.
     * 
     * @access public
     * @static 
     * @var integer
     */
    const STATEINWORD = 1;
    
    /**
     * Parser state for word in quotes
     * 
     * @access public
     * @static
     * @var integer
     */
    const STATEINQUOTEWORD = 2;
    
    /**
     * Parser state for reading a quote in a quoted word.
     * 
     * @access public
     * @static
     * @var integer
     */
    const STATEQUOTEINQUOTEWORD = 3;
    
    /**
     * Parser state for reading escape character in a word.
     */
    const STATEESCAPEINWORD = 4;
    
    /**
     * Parser state for reading escape character in a quoted word.
     */
    const STATEESCAPEINQUOTEWORD = 5;
    
    /**
     * The path to the file to be read.
     * 
     * @access private
     * @var string
     */
    private $csv;
    
    /**
     * The file contents.
     * 
     * @access private
     * @var resource
     */
    private $file;
    
    /**
     * The file pointer.
     * 
     * @access private
     * @var int
     */
    private $fp = 0;
    
    /**
     * The current line
     * 
     * @access private
     * @var mixed
     */
    private $_current_line;
    
    /**
     * The state the parser is in.
     * 
     * @access private
     * @var integer
     */
    private $_state = CSV_Reader::STATEDELIM;
    
    /**
     * The current column.
     * 
     * @access private
     * @var string
     */
    private $_current_column = "";
    
    /**
     * The columns we read.
     * 
     * @access private
     * @var string[]
     */
    private $_columns = array();
    
    private $_column_buffer = null;
    
    /**
     * The currently read word.
     * 
     * @access private
     * @var string
     */
    private $_current_word = "";
    
    /**
     * The dialect object.
     * 
     * @access private
     * @var object
     */
    private $dialect = null;
    
    /**
     * The input files encoding.
     * 
     * Defaults to UTF-8
     * 
     * @access private
     * @var string
     */
    private $input_enc = "UTF-8";
    
    /**
     * The header fields if any.
     * 
     * @access private
     * @var String[]
     */
    private $header = array();
    
    /**
     * Log object.
     * 
     * @access private
     * @var PEAR_Log
     */
    private $log;
    
    /**
     * Array of state names for debuging.
     * 
     * @access private
     * @var string[]
     */
    private $statedbg = array("STATEDELIM", "STATEINWORD", "STATEINQUOTEWORD", "STATEQUOTEINQUOTEWORD", "STATEESCAPEINWORD", "STATEESCAPEINQUOTEWORD");
    
    /**
     * microseconds when processing started.
     * 
     * @access private
     * @var float
     */
    private $start;
    
    /**
     * An array of parsed data.
     * 
     * @access private
     * @var array
     */
    private $parsed_data;
    
    /**
     * Object constructor
     * 
     * @access public
     * @param string $file The file we want to parse.
     * @param CSV_Dialect $dialect
     * @return void
     */
    public function __construct($file, &$dialect = null) {
        $this->log = &Log::singleton('file', BASE_PATH."/tmp/parse.log", '');
        
        list($usec, $sec) = explode(' ', microtime());
        $this->start = (float) $sec + (float) $usec;
        
        if(!file_exists($file)) {
            $this->log->log("Runtime Exception: File {$file} does not exist.", PEAR_LOG_CRIT);
            throw new RuntimeException();
        }
        
        /* Initialize our buffer */
        $this->_column_buffer = new String_Buffer();
        
        /* The file we will parse */
        $this->csv = $file;
        
        /* Get files encoding */
        $this->get_input_enc($file);
        
        /* Get the file contents */
        $contents = file_get_contents($this->csv);
        /* Convert the encoding to UTF-8 */
        $contents = iconv($this->input_enc, "UTF-8", $contents);
        
        if(is_null($dialect)) {
            $this->dialect = $this->detect_dialect($contents);    
        } else {
            $this->dialect = $dialect;
        }
        
        /* Log start of processing */
        $this->log->log("Parsing {$file} using ".get_class($dialect)." dialect.", PEAR_LOG_INFO);

        /* Check to see if we have line delimiters at the end of the string */
        $fix_last_record = false;
        $end_len = mb_strlen($this->dialect->line_end, "UTF-8");
        $offset = mb_strlen($contents, "UTF-8") - $end_len;
        $ending = mb_substr($contents, $offset, $end_len, "UTF-8");
        
        if($ending == $this->dialect->line_end) {
            $fix_last_record = true;
        }
        
        /* Split the file contents into lines */
        $this->file = mb_split($this->dialect->line_end, $contents);
        
        if($fix_last_record) {
            array_pop($this->file);
        }
        
        /* Read the first line */
        $this->read_next_line();
        
        /* If the csv has a header load it into the header array */
        if($this->dialect->has_header) {
            $this->log->log("--- HEADER ---", PEAR_LOG_INFO);
            $this->header = $this->next();
            $this->log->log("--- HEADER ---");
        }
    }
    
    /**
     * Destructor.
     * 
     * @access public
     * @return void
     */
    public function __destruct() {
        
        /* End of processing time in microseconds */
        list($usec, $sec) = explode(' ', microtime());
        $end = (float) $sec + (float) $usec;
        
        /* Calculate elapsed time in seconds. */
        $time = round($end - $this->start, 5);
        
        /* Log elapsed time */
        $this->log->log("Elapsed time: ". $time . " sec");
        
        /* Close log */
        if ($this->log instanceof Log) {
            $this->log->close();
        }
        
    }
    
    /**
     * Try to detect how to read the file.
     * 
     * @access private
     * @return CSV_Dialect_*
     */
    private function &detect_dialect($data) {
        return CSV_Dialect_Sniffer::detect($data);
    }
    
    /**
     * Guess the input files character encoding using 
     * the "file" command
     * 
     * <code>
     * file -bi path/to/file
     * </code>
     * 
     * @access private
     * @param string $file
     * @return void
     */
    private function get_input_enc($file) {
        if (substr(php_uname(), 0, 7) == "Windows") {
            $cmd = "file.exe -bi " . $file;
            $info = popen($cmd, "r");

            $data = fread($info, 2096);
            $data = explode(" ", $data);
            $this->input_enc = trim(strtoupper(substr($data[1], 8)));

            pclose($info);
        } else {
            $cmd = "file -bi " . BASE_PATH . "/test.csv";
            
            $info = popen($cmd, "r");

            $data = fread($info, 2096);
            $data = explode(" ", $data);
            $this->input_enc = strtoupper(substr($data[1], 8));

            pclose($info);
        }
    }
    
    /**
     * Return the array containing the header fields.
     * 
     * @access public
     * @return string[]
     */
    public function get_header() {
        return $this->header;
    }
    
    public function get_header_idx_columns() {
        return array_combine($this->get_header(), $this->get_current_line_columns());
    }
    
    /**
     * Get the input encoding.
     * 
     * @access public
     * @return string
     */
    public function get_input_enc_string() {
        return $this->input_enc;
    }
    
    /**
     * Tells if there are more lines to read.
     * 
     * @access public
     * @return boolean
     */
    public function has_next() {
        return $this->_current_line != false;
    }
    
    /**
     * Return the next line
     * 
     * @access public
     * @return string[]
     */
    public function next() {

		
        if(count($this->file) <= $this->fp && $this->_current_line == "") {
            $this->log->log("Runtime Exception: Tried to read past end of file.", PEAR_LOG_CRIT);
        	throw new RuntimeException("Read past end of file in next().");
        }
        
        $this->parse($this->_current_line);
        
        $this->read_next_line();
        
        return $this->get_current_line_columns();
    }
    
    /**
     * Parse the $line argument.
     * 
     * @access private
     * @param string $line
     * @return void
     */
    private function parse($line) {
        $this->_columns = array();
        $this->new_word();
        $this->log->log("--- New Line ---", PEAR_LOG_INFO);
        for($i = 0; $i < mb_strlen($this->_current_line, "UTF-8"); $i++) {
        	$char = mb_substr($this->_current_line, $i, 1, "UTF-8");
            $this->char_event($char);
        }
        
        $this->end_of_string_event();
    }
    
    /**
     * Return the array of parsed data.
     * 
     * @access public
     * @return array
     */
    public function parse_all() {
        $this->parsed_data = array();
        
        do {
            $this->next();
            $this->parsed_data[] = $this->get_header_idx_columns();
            
        } while($this->has_next());
        
        return $this->parsed_data;
    }
    
    /**
     * Read the next line.
     * 
     * @access private
     * @return void
     */
    private function read_next_line() {
    	
    	do {
    	    if($this->fp == count($this->file)) {
    	        $this->_current_line = false;
    	        break;
    	    }
    	    
    		$line = $this->file[$this->fp]; 
    		
    	    $this->_current_line = $line;
    		
    	    $this->fp++;
    	    
        	if(defined("DEBUG")) {
        	    $this->log->log("Line: " . $this->_current_line, PEAR_LOG_NOTICE);
        	    $this->log->log("", PEAR_LOG_NOTICE);
        	    var_dump($this->_current_line);        	    
        	}
        	
    	} while ( count($this->file) > $this->fp && $this->is_comment_line($this->_current_line) );
    }
    
    /**
     * Get the parser's state
     * 
     * @access public
     * @return integer
     */
    public function get_state() {
        return $this->_state;
    }
    
    /**
     * Returns current word
     * 
     * @access  public
     * @return string
     */
    public function get_current_word() {
        return $this->_current_column;
    }
    
    /**
     * Generate a character event.
     * 
     * @access public
     * @return void
     */
    public function char_event($char) {
        if(defined("DEBUG")) {
            echo "Char: ".$char." (State: ".$this->statedbg[$this->_state].")<br>";
            $this->log->log("Character : ".$char." [Code : ".ord($char) ."] (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
        
    	switch($char) {
    	    case $this->dialect->delimiter:
    	    	$this->delimiter_event();
    	    	break;
    	   	case $this->dialect->quote_char:
    	   		$this->quote_event();
    	   		break;
    	   	case $this->dialect->escape_char:
    	   	    $this->escape_event();
    	   	    break;
            case "\t":
    	    case ' ':
    	    	$this->whitespace_event($char);
    	    	break;
    	   	default:
    	   		$this->default_char_event($char);
    	   		break;
    	}
    }
    
    /**
     * Generate a whitespace event
     * 
     * @access public
     * @param char $char
     * @return vodi
     */
    public function whitespace_event($char) {
        if(defined("DEBUG")) {
            $this->log->log("Before Whitespace Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
        
        if($this->_state == CSV_Reader::STATEDELIM) {
            
        } else {
            $this->default_char_event($char);
        }
        
        if(defined("DEBUG")) {
            $this->log->log("After Whitespace Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
    }
    
    /**
     * Generate a delimiter event
     * 
     * @access public
     * @return void
     */
    public function delimiter_event() {
        if(defined("DEBUG")) {
            $this->log->log("Before Delimiter Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
        
        switch($this->_state) {
            case CSV_Reader::STATEINWORD:
                $this->write_end_trim_word();
                $this->new_word();
                $this->_state = CSV_Reader::STATEDELIM;
                break;
            case CSV_Reader::STATEDELIM:
            	$this->write_word();
            	break;
            case CSV_Reader::STATEINQUOTEWORD:
            	$this->append($this->dialect->delimiter);
            	break;
            case CSV_Reader::STATEQUOTEINQUOTEWORD:
                $this->write_word();
                $this->new_word();
            	$this->_state = CSV_Reader::STATEDELIM;
            	break;
            case CSV_Reader::STATEESCAPEINWORD:
                $this->append($this->dialect->delimiter);
                $this->_state = CSV_Reader::STATEINWORD;
                break;
        }
        
        if(defined("DEBUG")) {
            $this->log->log("After Delimiter Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
    }
    
    /**
     * Generate an escape character event
     * 
     * @access public
     * @param string $char
     * @return void
     */
    public function escape_event($char) {
        if(defined("DEBUG")) {
            $this->log->log("Before Escape Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
        
        switch($this->_state) {
            case CSV_Reader::STATEINWORD:
                $this->_state = CSV_Reader::STATEESCAPEINWORD;
                break;
            case CSV_Reader::STATEINQUOTEWORD:
                $this->_state = CSV_Reader::STATEESCAPEINQUOTEWORD;
                break;
            case CSV_Reader::STATEESCAPEINWORD:
                $this->append($this->dialect->escape_char);
                $this->_state = CSV_Reader::STATEINWORD;
                break;
            case CSV_Reader::STATEESCAPEINQUOTEWORD:
                $this->append($this->dialect->escape_char);
                $this->_state = CSV_Reader::STATEINQUOTEWORD;
                break;
            default:
                $this->_state = CSV_Reader::STATEINWORD;
        }
        
        if(defined("DEBUG")) {
            $this->log->log("After Escape Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
    }
    
    /**
     * Generate a quote event.
     * 
     * @access public
     * @return void
     */
    public function quote_event() {
        if(defined("DEBUG")) {
            $this->log->log("Before Quote Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
        
        switch($this->_state) {
            case CSV_Reader::STATEDELIM:
            	$this->_state = CSV_Reader::STATEINQUOTEWORD;
            	break;
            case CSV_Reader::STATEINWORD:
            	$this->append($this->dialect->quote_char);
            	break;
            case CSV_Reader::STATEINQUOTEWORD:
            	$this->_state = CSV_Reader::STATEQUOTEINQUOTEWORD;
            	/*$this->write_word();
            	$this->log->log("Found token: ". $this->_column_buffer->__toString(), PEAR_LOG_INFO);
            	$this->new_word();*/
            	break;
            case CSV_Reader::STATEQUOTEINQUOTEWORD:
                $this->append($this->dialect->quote_char);
                $this->_state = CSV_Reader::STATEINQUOTEWORD;
                break;
            case CSV_Reader::STATEESCAPEINWORD:
                $this->append($this->dialect->quote_char);
                $this->_state = CSV_Reader::STATEINWORD;
                break;
            case CSV_Reader::STATEESCAPEINQUOTEWORD:
                $this->append($this->dialect->quote_char);
                $this->_state = CSV_Reader::STATEINQUOTEWORD;
                break;
        }
        
        if(defined("DEBUG")) {
            $this->log->log("After Quote Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
    }
    
    /**
     * Generate a default character event.
     * 
     * @access public
     * @param char $char
     * @return void
     */
    public function default_char_event($char) {
        if(defined("DEBUG")) {
            $this->log->log("Before Default Char Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
        
        switch($this->_state) {
            case CSV_Reader::STATEDELIM:
            	$this->_state = CSV_Reader::STATEINWORD;
            	$this->append($char);
            	break;
            case CSV_Reader::STATEINWORD:
            	$this->append($char);
            	break;
            case CSV_Reader::STATEINQUOTEWORD:
            	$this->append($char);
            	break;
            case CSV_Reader::STATEESCAPEINWORD:
                $this->append($this->dialect->escape_char.$char);
                $this->_state = CSV_Reader::STATEINWORD;
                break;
            case CSV_Reader::STATEESCAPEINQUOTEWORD:
                $this->append($this->dialect->escape_char.$char);
                $this->_state = CSV_Reader::STATEINQUOTEWORD;
                break;
        }
        
        if(defined("DEBUG")) {
            $this->log->log("After Default Char Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
    }
    
    /**
     * Generate an end of string event.
     * 
     * @access public
     * @return void
     */
    public function end_of_string_event() {
        if(defined("DEBUG")) {
            $this->log->log("Before End of String Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
        
    	switch($this->_state) {
    	    case CSV_Reader::STATEINWORD:
    	    	$this->write_end_trim_word();
    	    	break;
    	    case CSV_Reader::STATEINQUOTEWORD:
    	        $this->log->log("Badly formed record: quoted string not terminated.", PEAR_LOG_ERR);
    	    	throw new RuntimeException("Badly formed record: quoted string not terminated.");
    	    	break;
    	    default:
    	    	$this->write_word();
    	    	break;
    	}
    	
    	$this->_state = CSV_Reader::STATEDELIM;
    	
        if(defined("DEBUG")) {
            $this->log->log("After End of String Event (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_DEBUG);
        }
    }
    
    /**
     * Return the current column from the buffer.
     * 
     * @access public
     * @return string
     */
    public function get_current_column() {
        return $this->_column_buffer->__toString();
    }
    
    /**
     * Returns an array of columns.
     * 
     * @access public
     * @return array
     */
    public function get_current_line_columns() {
        return $this->_columns;
    }
    
    /**
     * Add current word to column list
     */
    public function write_word() {
        $this->_columns[] = $this->get_current_column();
        $this->log->log("Found token: ". $this->_column_buffer->__toString(), PEAR_LOG_INFO);
    }
    
    /**
     * Add current word to column list
     * 
     * @access public
     * @return void
     */
    public function write_end_trim_word() {
        
        if($this->dialect->quoting == CSV_Dialect_Base::QUOTE_NONNUMERIC) {
            /*
             * We only get here if the read word is not quoted. This means
             * it is a number so convert it to float.
             */
            $col = (float) $this->end_trim($this->get_current_column());
        } else {
            $col = $this->end_trim($this->get_current_column());
        }
        
        $this->_columns[] = $col;
        $this->log->log("Found token: ". $this->_column_buffer->__toString(), PEAR_LOG_INFO);
    }
    
    /**
     * Trim whitespace
     * 
     * @access private
     * @param string $src
     * @return string
     */
    private function end_trim($src) {
        $i = mb_strlen($src, "UTF-8") - 1;
        
        while($i > -1) {
            if($this->is_whitespace($src[$i])) {
                $i--;
            } else {
            	break;
            }
        }
        
        return mb_substr($src, 0, $i + 1, "UTF-8");
    }
    
    /**
     * Check wether the $char argument is a whitespace character or not.
     * 
     * @access private
     * @param char $char
     * @return boolean
     */
    private function is_whitespace($char) {
        return $char == ' ' || $char == "\t";
    }
    
    /**
     * Remove characters from the buffer.
     * 
     * @access public
     * @return void
     */
    public function new_word() {
        
        if(defined("DEBUG")) {
            echo "New word<br>";
            $this->log->log("", PEAR_LOG_INFO);
            $this->log->log("Buffer: " . $this->_column_buffer->__toString(), PEAR_LOG_DEBUG);
            $this->log->log("New word (State: ".$this->statedbg[$this->_state].")", PEAR_LOG_NOTICE);
            $this->log->log("", PEAR_LOG_INFO);
        }
        
        $this->_column_buffer->delete(0, $this->_column_buffer->length());
    }
    
    /**
     * Append the read character to the buffer.
     * 
     * @access public
     * @param char $char the character we read.
     * @return void
     */
    public function append($char) {
        $this->_column_buffer->append($char);
    }
    
    /**
     * Check wether the $line argument is a comment or not, by looking at the 
     * first character.
     * 
     * @access public
     * @param string $line the line we want to check
     * @return boolean
     */
    private function is_comment_line($line) {
        
        if(strlen($line) == 0) {
            return false;
        }
        
        return $line[0] == iconv("ASCII","UTF-8","#");
    }
}
?>