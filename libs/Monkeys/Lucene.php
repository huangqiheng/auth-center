<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

abstract class Monkeys_Lucene
{
    const LUCENE_DIR = '/lucene';

    private static $_index;

    /**
    * @throws Zend_Search_Lucene_exception
    */
    public static function getIndex()
    {
        if (!@self::$_index) {
            try {
                self::$_index = Zend_Search_Lucene::open(APP_DIR . self::LUCENE_DIR);
            } catch (Zend_Search_Lucene_Exception $e) {
                self::$_index = Zend_Search_Lucene::create(APP_DIR . self::LUCENE_DIR);
                Zend_Registry::get('logger')->log('Created Lucene index file', Zend_Log::INFO);
            }
        }

        return self::$_index;
    }

    public static function optimizeIndex()
    {
        $index = self::getIndex();
        $index->optimize();
    }

    public static function checkPcreUtf8Support()
    {
        if (@preg_match('/\pL/u', 'a') == 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function clearIndex()
    {
        // need to remove the locks, otherwise error under windows
        self::getIndex()->removeReference();
        self::$_index = null;

        self::_rmdirr(APP_DIR . self::LUCENE_DIR);
    }

    /** * Delete a file, or a folder and its contents
    *
    * @author      Aidan Lister <aidan@php.net>
    * @version     1.0.1
    * @param       string   $dirname    Directory to delete
    * @return      bool     Returns TRUE on success, FALSE on failure
    */
    private static function _rmdirr($dirname)
    {
        // Sanity check
        if (!file_exists($dirname)) {
            return false;
        }
        // Simple delete for a file
        if (is_file($dirname)) {
            return unlink($dirname);
        }
        // Loop through the folder
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            // Skip pointers and dot directories
            if (substr($entry, 0, 1) == '.') {
                continue;
            }
            // Deep delete directories
            if (is_dir("$dirname/$entry")) {
                self::_rmdirr("$dirname/$entry");
            } else {
                unlink("$dirname/$entry");
            }
        }
        // Clean up
        $dir->close();

        return true;
    }
}
