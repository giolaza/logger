<?php declare(strict_types=1);

use GioLaza\Logger\Log;
use PHPUnit\Framework\TestCase;


final class StackTest extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        define("GIOLAZA_SAVE_ERRORS", true);
        define("GIOLAZA_SHOW_ERRORS", false);
        define("GIOLAZA_LOGS_FOLDER", __DIR__ . '/.logs');
    }

    public function testCanWriteLog(): void
    {
        $logFileName = microtime(true) . rand() . '.log';
        $filePath = GIOLAZA_LOGS_FOLDER . '/' . $logFileName;
        Log::logError('Test name', $logFileName, false, false);

        $this->assertFileExists($filePath);

        unlink($filePath);
    }

    public function testCanDisplayErrors(): void
    {
        $logFileName = microtime(true) . rand() . '.log';
        $filePath = GIOLAZA_LOGS_FOLDER . '/' . $logFileName;

        ob_start();
        Log::logError('Test name', $logFileName, false);
        unlink($filePath);
        $output = ob_get_clean();

        // Check if the output is a non-empty string
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }
}