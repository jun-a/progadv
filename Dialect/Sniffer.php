<?php
/**
 * Dialect detector
 * 
 * Tries to guess the dialect used. 
 *
 * @package CSV
 * @subpackage Dialect
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright (c) 2008 Edit. All rights reserved.
 * @filesource
 */
 
/**
 * Dialect detector
 * 
 * Tries to guess the dialect used. 
 *
 * @package CSV
 * @subpackage Dialect
 * @author Peter Halasz <skinn3r@gmail.com>
 */

class CSV_Dialect_Sniffer {
    
    /**
     * Detector function.
     * 
     * @access public
     * @static 
     * @param string $data file contents to examin
     * @return CSV_Dialect_Base
     */
    public static function &detect($data) {
        $line_end = self::guess_line_end($data);
        list($quote, $delimiter) = self::guess_quote_delim($data);
        
        if(!$quote) {
            $quote = '"';
        }
        
        if(is_null($delimiter)) {
            if(! $delimiter = self::quess_delimiter($data, $line_end, $quote)) {
                throw new RuntimeException("Couldn't detect the files dialect.");
            }
        }
        
        $dialect = new CSV_Dialect_Base();
        $dialect->line_end = $line_end;
        $dialect->double_quote = $quote;
        $dialect->delimiter = $delimiter;
        
        return $dialect;
    }
    
/**
     * Try to detect the line end character(s).
     * 
     * @access private
     * @param string $data File contents to examine.
     * @return string The line end.
     */
    private function guess_line_end($data) {
        $cr = "\r";
        $lf = "\n";
        
        $ret = "$cr$lf";
        
        $cr_count = mb_substr_count($data, $cr, "UTF-8");
        $lf_count = mb_substr_count($data, $lf, "UTF-8");
        
        if($cr_count == $lf_count) {
            $ret = "$cr$lf";
        }
        
        if(!$cr_count && $lf_count) {
            $ret = "$lf";
        }
        
        if($cr_count && !$lf_count) {
            $ret = "$cr";
        }
        
        return $ret;
    }
    
    /**
     * Try to guess the quote character and the delimiter used.
     * It looks for text enclosed by identical quote charactes which are 
     * surrounded by identical characters. If there are no quotes, then
     * the delimiter cannot be determined this way.
     * 
     * @access private
     * @param string $data The file contents to check.
     * @return array
     */
    private function guess_quote_delim($data) {
        
        $patterns[] = '/([^\w\n"\']) ?(["\']).*?(\2)(\1)/';     // ,".*?"
        $patterns[] = '/(?:^|\n)(["\']).*?(\1)([^\w\n"\']) ?/'; // ".*?",
        $patterns[] = '/([^\w\n"\']) ?(["\']).*?(\2)(?:$|\n)/'; // ,".*?"
        $patterns[] = '/(?:^|\n)(["\']).*?(\1)(?:$|\n)/';       // ".*?"(no delim, no space)
                        
        
        foreach ($patterns as $pattern) {
            if ($nummatches = preg_match_all($pattern, $data, $matches)) {
                if ($matches) break;
            }
        }
        
        if (!$matches) return array("", null); // couldn't guess quote or delim
        
        $quotes = array_count_values($matches[2]);
        arsort($quotes);
        if ($quote = array_shift(array_flip($quotes))) {
            $delims = array_count_values($matches[1]);
            arsort($delims);
            $delim = array_shift(array_flip($delims));
        } else {
            $quote = ""; $delim = null;
        }
                
        return array($quote, $delim);
    }
    
    /**
     * Try to guess the field delimiter.
     * 
     * The delimiter 'should' occur the same number of times on
     * each row. However, due to malformed data, it may not. 
     * 1. build a table of the frequency of each character on every line.
     * 2. build a table of frequencies of this frequency (meta-frequency).
     * 3. use the mode of the meta-frequency to determine the 'expected'
     *    frequency for that character
     * 4. find out how often the character actually meets that goal.
     * 5. the character that best meets its goal is the delimiter. 
     * 
     * @access private
     * @param string $data The file contents to examin
     * @param string $line_end The line ending character(s)
     * @param string $quote The quotes used
     * @return string The guessed delimiter character
     */
    private function guess_delimiter($data, $line_end, $quote) {
        $iteration = 0;
        $char_freq = array();
        $chunk_length = min(array(10, iconv_strlen($data, "UTF-8")));
        $modes = array();
        $delimiters = array();
        $start = 0;
        $end = min(array($chunk_length, iconv_strlen($data, "UTF-8")));
        
        $lines = array_chunk(mb_split($line_end, $data), $chunk_length);
        
        while($start < iconv_strlen($data)) {
            
            foreach($lines[$iteration] as $line) {
                
            }
            
            $iteration++;
        }
    }
    
    private function get_array($hash, $id, $default) {
        if(!is_set($hash[$id])) {
            return $default;
        }
        
        return $hash[$id];
    }
}
?>