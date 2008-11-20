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
        
    }
    
    /**
     * Write multiple rows.
     * 
     * @access public
     * @param array $data
     * @return void
     */
    public function write_rows(Array $data) {
        
    }
}
?>