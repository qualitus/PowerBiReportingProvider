<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace QU\PowerBiReportingProvider\Logging\Writer;

use QU\PowerBiReportingProvider\Logging;
use ilLogLevel;
use ilLoggerFactory;
use ilLogger;
use ReflectionClass;

class File extends Base
{
    private ilLogger $aggregated_logger;

    public function __construct(Logging\Settings $settings)
    {
        $coreLoggingFactory = ilLoggerFactory::getInstance();

        $factory = ilLoggerFactory::newInstance($settings);
        $this->aggregated_logger = $factory->getComponentLogger('PowerBiReportingProvider');

        $loggerFactoryRefl = new ReflectionClass(ilLoggerFactory::class);
        $loggerFactoryInstance = $loggerFactoryRefl->getProperty('instance');
        $loggerFactoryInstance->setAccessible(true);
        $loggerFactoryInstance->setValue($coreLoggingFactory);

        $this->aggregated_logger->getLogger()->popProcessor();
        $this->aggregated_logger->getLogger()->pushProcessor(new Logging\TraceProcessor(ilLogLevel::DEBUG));
    }

    protected function doWrite(array $message): void
    {
        $line = $message['message'];

        switch ($message['priority']) {
            case Logging\Logger::EMERG:
                $method = 'emergency';
                break;

            case Logging\Logger::ALERT:
                $method = 'alert';
                break;

            case Logging\Logger::CRIT:
                $method = 'critical';
                break;

            case Logging\Logger::ERR:
                $method = 'error';
                break;

            case Logging\Logger::WARN:
                $method = 'warning';
                break;

            case Logging\Logger::INFO:
                $method = 'info';
                break;

            case Logging\Logger::NOTICE:
                $method = 'notice';
                break;

            case Logging\Logger::DEBUG:
            default:
                $method = 'debug';
                break;
        }

        $this->aggregated_logger->{$method}($line);
    }

    public function shutdown(): void
    {
        unset($this->aggregated_logger);
    }
}
