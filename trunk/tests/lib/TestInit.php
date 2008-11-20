<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PEAR/Exception.php';
require_once 'Log.php';
require_once 'MDB2.php';
require_once 'PEAR.php';

/**
 * Base Path to search for files
 */
define('BASE_PATH', 'C:\\Apache2\\htdocs\\research');

/**
 * Register autoloadclass::autoload() with SPL.
 */
spl_autoload_register(array('autoloadclass', 'autoload'));

/**
 * Autoload class
 * 
 * @package Test
 * @author Peter Halasz <skinn3r@gmail.com>
 * @license 
 * @copyright
 * @filesource 
 */
class autoloadclass {
    /**
     * autoload
     *
     * Automatically load classes when they are needed. No more mass
     * includes.
     *
     * <samp>
     * A class named Dir_Subdir_File. 
     * __autoload will look for:
     * Dir/Subdir/File.php 
     * </samp>
     * 
     * @author Peter Halasz <skinn3r@gmail.com>
     * @param string $class Class name to load
     * @return void
     */
    static public function autoload($class) {
        /* did we ask for a non-blank name? */
        if (trim($class) == '') {
            throw new RuntimeException('No class or interface named for loading!');
        }
        
        /* pre-empt further searching for the named class.
         Do not use autoload, because this method is registered with
         spl_autoload already. */
        if (class_exists($class, false)) {
            return;
        }
        
        /* convert the class name to a file path. */
        $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        
        include_once(BASE_PATH . "/" .$file);
        
        
        /* if the class was not in the file, we have a problem.
         Do not use autoload, because this method is registered with
         spl_autoload already. */
        if (!class_exists($class, false)) {
            
            throw new RuntimeException('Class ' . $class . ' could not be found in '.$file);
            
        }
    }
}

?>