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
use ilLogger;

class ILIAS extends Base
{
    private ilLogger $aggregatedLogger;
    private int $logLevel;
    private Logging\TraceProcessor $processor;
    protected bool $shutdown_handled = false;

    public function __construct(ilLogger $log, int $logLevel)
    {
        $this->aggregatedLogger = $log;
        $this->logLevel = $logLevel;

        $this->processor = new Logging\TraceProcessor(ilLogLevel::DEBUG);
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

        $poppedProcessors = [];
        while ($this->aggregatedLogger->getLogger()->getProcessors() !== []) {
            $processor = $this->aggregatedLogger->getLogger()->popProcessor();
            $poppedProcessors[] = $processor;
        }
        $this->aggregatedLogger->getLogger()->pushProcessor($this->processor);
        $this->aggregatedLogger->{$method}($line);
        $this->aggregatedLogger->getLogger()->popProcessor();
        foreach (array_reverse($poppedProcessors) as $processor) {
            $this->aggregatedLogger->getLogger()->pushProcessor($processor);
        }
    }

    public function shutdown(): void
    {
        unset($this->aggregatedLogger);
        $this->shutdown_handled = true;
    }
}
