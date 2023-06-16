<?php

namespace Mhassan654\Uraefrisapi\Services;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;

class CustomLogger
{
    public function __invoke(array $config)
    {
        $logDir = storage_path('logs');
        $datePatternConfiguration = [
            'default' => 'Y-m-d',
            'everHour' => 'Y-m-d-H',
            'everMinute' => 'Y-m-d-H:i',
        ];
        $numberOfDaysToKeepLog = 30;
        $fileSizeToRotate = 1 * 1024 * 1024; // in bytes

        // Create the log directory if it does not exist
        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logger = new Logger($config['name']);

        // Add a daily rotate file handler
        $handler = new RotatingFileHandler($logDir.'/'.$config['name'].'-'.date('Y-m-d').'.log', $numberOfDaysToKeepLog, $config['level'], true, null, true, $fileSizeToRotate);
        $handler->setFilenameFormat('{date}-{filename}', 'Y-m-d');
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $logger->pushHandler($handler);

        // Add a console handler
        $consoleHandler = new \Monolog\Handler\StreamHandler('php://stdout', $config['level']);
        $consoleHandler->setFormatter(new LineFormatter(null, null, true, true));
        $logger->pushHandler($consoleHandler);

        // Add processors
        $logger->pushProcessor(new IntrospectionProcessor());
        $logger->pushProcessor(new WebProcessor());

        return $logger;
    }
}
