<?php

/**
 * CalibreFx
 *
 * WordPress Themes Framework by CalibreWorks Team
 *
 * @package		CalibreFx
 * @author		CalibreWorks Team
 * @copyright	Copyright (c) 2012, Suntech Inti Perkasa.
 * @license		Commercial
 * @link		http://calibrefx.com
 * @since		Version 1.0
 * @filesource 
 * @extend Walker_Nav_Menu From WordPress
 *
 * WARNING: This file is part of the core CalibreFx framework. DO NOT edit
 * this file under any circumstances. 
 *
 * This file to handle css, js and htm minification
 *
 * @package CalibreFx
 */
class CFX_Logger {

    /**
     * Path to save log files
     *
     * @var string
     */
    protected $_log_path;

    /**
     * Level of logging
     *
     * @var int
     */
    protected $_threshold = 1;

    /**
     * Highest level of logging
     *
     * @var int
     */
    protected $_threshold_max = 0;

    /**
     * Array of threshold levels to log
     *
     * @var array
     */
    protected $_threshold_array = array();

    /**
     * Format of timestamp for log files
     *
     * @var string
     */
    protected $_date_fmt = 'Y-m-d H:i:s';

    /**
     * Whether or not the logger can write to the log files
     *
     * @var bool
     */
    protected $_enabled = TRUE;

    /**
     * Predefined logging levels
     *
     * @var array
     */
    protected $_levels = array('ERROR' => 1, 'DEBUG' => 2, 'INFO' => 3, 'ALL' => 4);

    /**
     * Initialize Logging class
     *
     * @return	void
     */
    public function __construct() {

        $this->_log_path = CALIBREFX_LOG_DIR . '/';
        
        if (!is_dir($this->_log_path) OR !$this->is_really_writable($this->_log_path)) {
            $this->_enabled = FALSE;
        }
        
        $this->_threshold = (int) LOG_THRESHOLD;
    }

    // --------------------------------------------------------------------

    /**
     * Write Log File
     *
     * Generally this function will be called using the global log_message() function
     *
     * @param	string	the error level
     * @param	string	the error message
     * @param	bool	whether the error is a native PHP error
     * @return	bool
     */
    public function write_log($level = 'error', $msg, $php_error = FALSE) {
        if ($this->_enabled === FALSE) {
            return FALSE;
        }

        $level = strtoupper($level);
        
        if ((!isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))) {
            return FALSE;
        }

        $filepath = $this->_log_path . 'log-' . date('Y-m-d') . '.php';
        $message = '';
        
        if (!file_exists($filepath)) {
            $newfile = TRUE;
            $message .= '<' . "?php if ( ! defined('CALIBREFX_DIR')) exit('No direct script access allowed'); ?" . ">\n\n";
        }

        if (!$fp = @fopen($filepath, FOPEN_WRITE_CREATE)) {
            return FALSE;
        }

        $message .= $level . ' ' . ($level === 'INFO' ? ' -' : '-') . ' ' . date($this->_date_fmt) . ' --> ' . $msg . "\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $message);
        flock($fp, LOCK_UN);
        fclose($fp);

        if (isset($newfile) && $newfile === TRUE) {
            @chmod($filepath, FILE_WRITE_MODE);
        }

        return TRUE;
    }

    function is_really_writable($file) {
        // If we're on a Unix server with safe_mode off we call is_writable
        if (DIRECTORY_SEPARATOR == '/' AND @ini_get("safe_mode") == FALSE) {
            return is_writable($file);
        }

        // For windows servers and safe_mode "on" installations we'll actually
        // write a file then read it.  Bah...
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand(1, 100) . mt_rand(1, 100));

            if (($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE) {
                return FALSE;
            }

            fclose($fp);
            @chmod($file, DIR_WRITE_MODE);
            @unlink($file);
            return TRUE;
        } elseif (!is_file($file) OR ($fp = @fopen($file, FOPEN_WRITE_CREATE)) === FALSE) {
            return FALSE;
        }

        fclose($fp);
        return TRUE;
    }

}
