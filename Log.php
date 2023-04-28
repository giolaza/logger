<?php

/**
 * Log.php
 *
 * @package    GL
 * @author     Giorgi Lazashvili <giolaza@gmail.com>
 *
 * --CONFIGURATION--
 * (constant) GIOLAZA_SHOW_ERRORS - true or false;
 * (constant) GIOLAZA_SAVE_ERRORS - true or false;
 * (constant) GIOLAZA_LOGS_FOLDER - log folder, if not set is root folder
 *
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
     * Logs an error message, displays it if specified, and saves it to a log file if specified.
     *
     * @param string $text Error message to be logged
     * @param string $filename Optional filename to save the log
     * @param bool $engineForceStop Optional flag to force stop the engine after logging
     * @param bool $displayErrors Optional flag to display errors
     * @return void
     */
    public static function logError(string $text, string $filename = '.logs.php', bool $engineForceStop = true, bool $displayErrors = true): void
    {
        $saveErrors = defined('GIOLAZA_SAVE_ERRORS') ? GIOLAZA_SAVE_ERRORS : true;
        $projectFolder = defined('GIOLAZA_LOGS_FOLDER') ? GIOLAZA_LOGS_FOLDER : $_SERVER['DOCUMENT_ROOT'];

        $filename = strtolower(substr($filename, -4)) != '.log' ? $filename . '.log' : $filename;
        $fileLink = $projectFolder . '/' . $filename;
        if ($displayErrors) {
            self::showLog($text);
        }

        if (!$saveErrors) {
            if ($engineForceStop) {
                die(PHP_EOL . 'Engine force stop...' . PHP_EOL);
            }
            return;
        }

        if (!is_dir($projectFolder) && !@mkdir($projectFolder, 0775, true)) {
            self::handleError('GL ENGINE ERROR - log folder "' . $projectFolder . '" does not exist and was unable to create it, please fix it to see GL system .logs', $engineForceStop);

            return;
        }

        if (!file_exists($fileLink)) {
            if (!self::fillEmpty($fileLink, $filename)) {
                self::handleError('GL ENGINE ERROR - unable to create log file "' . $fileLink . '", please fix it to see GL system .logs', $engineForceStop);

                return;
            }
        }

        if (!is_writable($fileLink)) {
            self::handleError('GL ENGINE ERROR - file "' . $fileLink . '" is not writable, please fix it to see GL system .logs', $engineForceStop);

            return;
        }

        if (filesize($fileLink) < 100) {
            self::fillEmpty($fileLink, $filename);
        }

        $debug = array_filter(debug_backtrace(), function ($value) {
            return $value['function'] !== 'logError' && $value['function'] !== 'saveLog';
        });

        $sections = [
            'TIME' => date('r'),
            'ERROR' => $text,
            'Debug' => print_r($debug, 1),
            'GET' => print_r($_GET, 1),
            'POST' => print_r($_POST, 1),
            'Request' => print_r($_REQUEST, 1)
        ];

        $textToWrite = '';
        foreach ($sections as $title => $content) {
            $textToWrite .= self::logTitle($title);
            $textToWrite .= $content . PHP_EOL . self::logLines(100, 1);
        }

        file_put_contents($fileLink, $textToWrite, FILE_APPEND);

        if ($engineForceStop) {
            die(PHP_EOL . 'Log saved...' . '<br>' . PHP_EOL . 'Engine force stop...' . PHP_EOL);
        }
    }

    /**
     * Displays the log message on the screen with proper formatting.
     *
     * @param string $string Log message to be displayed
     * @return void
     */
    public static function showLog(string $string): void
    {
        $showErrors = defined('GIOLAZA_SHOW_ERRORS') && GIOLAZA_SHOW_ERRORS;

        $text = PHP_EOL
            . '<!-- ----------------------------------------------------------------------------- -->' . PHP_EOL
            . '<!-- ----------------------------------------------------------------------------- -->' . PHP_EOL;

        if ($showErrors) {
            $text .= PHP_EOL . '<div style="text-align: center"><b style="color: darkred">' . PHP_EOL
                . '<h2>' . 'SOMETHING WENT WRONG' . '</h2>' . PHP_EOL
                . $string . PHP_EOL
                . '</b></div>' . PHP_EOL;
        }
        else {
            $text .= PHP_EOL . '<div style="text-align: center"><b style="color: darkred">' . PHP_EOL
                . '<h2>' . 'SOMETHING WENT WRONG' . '</h2>' . PHP_EOL
                . '</b></div>' . PHP_EOL;
        }

        $text .= PHP_EOL
            . '<!-- ----------------------------------------------------------------------------- -->' . PHP_EOL
            . '<!-- ----------------------------------------------------------------------------- -->' . PHP_EOL;

        echo $text;
    }

    private static function handleError(string $message, bool $engineForceStop): void
    {
        @error_log($message);
        if ($engineForceStop) {
            die(PHP_EOL . 'Engine force stop...' . PHP_EOL);
        }
    }

    /**
     * Fills an empty log file with standard text.
     *
     * @param string $fileLink File link to the log file
     * @param string $filename Filename of the log file
     * @return bool Returns true on success
     */
    public static function fillEmpty(string $fileLink, string $filename): bool
    {
        $standardText = ' * ' . $filename . PHP_EOL
            . ' *' . PHP_EOL
            . ' * @category   System' . PHP_EOL
            . ' * @package    GL' . PHP_EOL
            . ' * @author     Giorgi Lazashvili <giolaza@gmail.com>' . PHP_EOL
            . ' * @version    4.0 (28 APR 2023)' . PHP_EOL
            . ' * @created    (' . date('r') . ')' . PHP_EOL
            . ' *' . PHP_EOL
            . ' *' . PHP_EOL;

        try{
            file_put_contents($fileLink, $standardText, FILE_APPEND);
            chmod($fileLink, 0775);
        } catch (Exception $e){
            return false;
        }


        return true;
    }

    /**
     * Creates a formatted string for a log title.
     *
     * @param string $textSTR Title text
     * @param string $symbol Symbol to be used for the title (default is '*')
     * @return string Formatted log title string
     */
    public static function logTitle(string $textSTR, string $symbol = '*'): string
    {
        $text = PHP_EOL . PHP_EOL;
        $text .= $symbol . $symbol . $symbol;
        $text .= strtoupper($textSTR);
        $text .= PHP_EOL;

        return $text;
    }

    /**
     * Creates a formatted string of repeating symbols for log formatting purposes.
     *
     * @param int $x Width of the formatted string
     * @param int $y Height of the formatted string
     * @param string $symbol Symbol to be repeated (default is '-')
     * @return string Formatted string of repeating symbols
     */
    public static function logLines(int $x, int $y, string $symbol = '-'): string
    {
        $text = PHP_EOL . PHP_EOL;
        for ($i = 0; $i < $y; $i++) {
            $text .= str_repeat($symbol, $x);
            $text .= PHP_EOL;
        }
        $text .= PHP_EOL;

        return $text;
    }
}


