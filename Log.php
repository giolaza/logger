<?php

/**
 * Log.php
 *
 * @category   System
 * @package    GL
 * @author     Giorgi Lazashvili <giolaza@gmail.com>
 *
 ****************************************************************************************************
 *
 * --REQUIREMENTS--
 * (constant) GIOLAZA_SHOW_ERRORS - true or false;
 * (constant) GIOLAZA_SAVE_ERRORS - true or false;
 * (constant) GIOLAZA_LOGS_FOLDER - log folder, if not set is root folder
 *
 ****************************************************************************************************
 */

namespace GioLaza\Logger;

use Exception;

/**
 * Class Log
 * @package GioLaza\Logger
 */
class Log
{
    /**
     * @param $text
     * @param string $filename
     * @param bool $engineForceStop
     * @param bool $displayErrors
     * @throws Exception
     */
    public static function logError($text, $filename = 'logs.php', $engineForceStop = true, $displayErrors = true)
    {
        if (defined('GIOLAZA_SAVE_ERRORS')) {
            $saveErrors = GIOLAZA_SAVE_ERRORS;
        } else {
            $saveErrors = true;
        }

        if (defined('GIOLAZA_LOGS_FOLDER')) {
            $projectFolder = GIOLAZA_LOGS_FOLDER;
        } else {
            $projectFolder = $_SERVER['DOCUMENT_ROOT'];
        }

        if (strtolower(substr($filename, -4)) != '.log') {
            $filename .= '.log';
        }
        $fileLink = $projectFolder . '/___' . $filename;

        if ($displayErrors) {
            self::showLog($text);
        }

        if (!$saveErrors) {
            if ($engineForceStop) {
                die(PHP_EOL . 'Engine force stop...' . PHP_EOL);
            } else {
                return;
            }
        }

        /** if engine config allows save logs */

        /** check if folder exists */
        if (!is_dir($projectFolder)) {
            try {
                $old = umask(0);
                if (!mkdir($projectFolder, 0775)) {
                    @error_log('GL ENGINE ERROR - log folder "' . $projectFolder . '" does not exists and was unable to create it, please fix it to see GL system logs');
                    if ($engineForceStop) {
                        die(PHP_EOL . 'Engine force stop...' . PHP_EOL);
                    } else {
                        return;
                    }
                }
                umask($old);
            } catch (Exception $e) {
                @error_log('GL ENGINE ERROR - failed to create log folder "' . $projectFolder . '" Message: "' . $e->getMessage() . '"');
                if ($engineForceStop) {
                    die(PHP_EOL . 'Engine force stop...' . PHP_EOL);
                } else {
                    return;
                }
            }
        }


        /**  check if file exists */
        if (!file_exists($fileLink)) {
            //fill with standart headers
            if (!self::fill_empty($fileLink, $filename)) {
                @error_log('GL ENGINE ERROR - unable to create log file "' . $fileLink . '", please fix it to see GL system logs');
                if ($engineForceStop) {
                    die(PHP_EOL . 'Engine force stop...' . PHP_EOL);
                } else {
                    return;
                }
                //file was not created, nothing to do next
            }

        }

        /** check if can write in file */
        if (!is_writable($fileLink)) {
            @error_log('GL ENGINE ERROR - file "' . $fileLink . '" is not writable, please fix it to see GL system logs');
            if ($engineForceStop) {
                die(PHP_EOL . 'Engine force stop...' . PHP_EOL);
            } else {
                return;
            }
        }

        //check if file is empty
        if (filesize($fileLink) < 100) {
            self::fill_empty($fileLink, $filename); //fill with standart headers
        }

        //start writing

        $debug = debug_backtrace();
        foreach ($debug as $key => $value) {
            if ($value['function'] == 'logError' || $value['function'] == 'saveLog') {
                unset($debug[$key]);

                continue;
            }

            if (isset($value['object'])) {
                unset($value['object']);
            }

            $debug[$key] = $value;
        }


        $textToWrite = '';

        $textToWrite .= self::logLines(130, 2, '*');
        //save times
        $textToWrite .= self::logTitle('TIME');
        $textToWrite .= 'Microtime - ' . microtime(true) . PHP_EOL . 'Date - ' . date('r') . PHP_EOL . PHP_EOL;
        $textToWrite .= self::logLines(100, 1);

        //save error
        $textToWrite .= self::logTitle('ERROR');
        $textToWrite .= $text . PHP_EOL;
        $textToWrite .= self::logLines(100, 1);

        $textToWrite .= self::logTitle('Debug');
        $textToWrite .= PHP_EOL . PHP_EOL . print_r($debug, 1) . PHP_EOL;
        $textToWrite .= self::logLines(100, 1);

        $textToWrite .= self::logTitle('GET');
        $textToWrite .= PHP_EOL . PHP_EOL . print_r($_GET, 1) . PHP_EOL;
        $textToWrite .= self::logLines(100, 1);

        $textToWrite .= self::logTitle('POST');
        $textToWrite .= PHP_EOL . PHP_EOL . print_r($_POST, 1) . PHP_EOL;
        $textToWrite .= self::logLines(100, 1);

        $textToWrite .= self::logTitle('Request');
        $textToWrite .= PHP_EOL . PHP_EOL . print_r($_REQUEST, 1) . PHP_EOL;

        $textToWrite .= self::logLines(100, 3);

        file_put_contents($fileLink, $textToWrite, FILE_APPEND);

        //end writing

        if ($engineForceStop) {
            die(PHP_EOL . 'Log saved...' . '<br>' . PHP_EOL . 'Engine force stop...' . PHP_EOL);
        }
    }

    /**
     * @param $string
     */
    public static function showLog($string)
    {
        if (defined('GIOLAZA_SHOW_ERRORS')) {
            $showErrors = GIOLAZA_SHOW_ERRORS;
        } else {
            $showErrors = false;
        }

        $text = PHP_EOL
            . '<!-- ----------------------------------------------------------------------------- -->' . PHP_EOL
            . '<!-- ----------------------------------------------------------------------------- -->' . PHP_EOL;


        if ($showErrors) {
            $text .= PHP_EOL . '<div align="center"><b style="color:red">' . PHP_EOL
                . '<h2>' . 'SOMETHING WENT WRONG' . '</h2>' . PHP_EOL
                . $string . PHP_EOL
                . '</b></div>' . PHP_EOL;
        } else {
            $text .= PHP_EOL . '<div align="center"><b style="color:red">' . PHP_EOL
                . '<h2>' . 'SOMETHING WENT WRONG' . '</h2>' . PHP_EOL
                . '</b></div>' . PHP_EOL;
        }

        $text .= PHP_EOL
            . '<!-- ----------------------------------------------------------------------------- -->' . PHP_EOL
            . '<!-- ----------------------------------------------------------------------------- -->' . PHP_EOL;

        echo $text;
    }

    /**
     * @param $fileLink
     * @param $filename
     * @return bool
     */
    public static function fill_empty($fileLink, $filename)
    {
        $standartText = '<?php die();' . PHP_EOL . '/**' . PHP_EOL
            . ' * ' . $filename . PHP_EOL
            . ' *' . PHP_EOL
            . ' * @category   System' . PHP_EOL
            . ' * @package    GL' . PHP_EOL
            . ' * @author     Giorgi Lazashvili <giolaza@gmail.com>' . PHP_EOL
            . ' * @version    3.0 (26 MAY 2018)' . PHP_EOL
            . ' * @created    (' . date('r') . ')' . PHP_EOL
            . ' *' . PHP_EOL
            . ' *' . PHP_EOL;

        file_put_contents($fileLink, $standartText, FILE_APPEND);
        chmod($fileLink, 0775);
        return true;
    }

    /**
     * @param $x
     * @param $y
     * @param string $symbol
     * @return string
     */
    public static function logLines($x, $y, $symbol = '-')
    {
        $text = PHP_EOL . PHP_EOL;
        for ($i = 0; $i < $y; $i++) {
            for ($j = 0; $j < $x; $j++) {
                $text .= $symbol;
            }
            $text .= PHP_EOL;
        }
        $text .= PHP_EOL;

        return $text;
    }

    /**
     * @param $textSTR
     * @param string $symbol
     * @return string
     */
    public static function logTitle($textSTR, $symbol = '*')
    {
        $text = PHP_EOL . PHP_EOL;
        $text .= $symbol . $symbol . $symbol;
        $text .= strtoupper($textSTR);
        $text .= PHP_EOL;
        return $text;
    }
}


