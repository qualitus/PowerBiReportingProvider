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

use QU\PowerBiReportingProvider\Logging\Settings as LogSettings;
use QU\PowerBiReportingProvider\Logging\Logger;
use QU\PowerBiReportingProvider\Logging\TraceProcessor;
use ilLogLevel;
use ilLoggerFactory;
use ilGenericInterfaceLogOverviewPlugin;
use ilLogger;
use ReflectionClass;
use ilPowerBiReportingProviderPlugin;

class Gilo extends Base
{
    private ?ilGenericInterfaceLogOverviewPlugin $logOverviewPlugin = null;
    private bool $shutDownHandled = false;
    /** @var array<int, int> */
    private array $loggedPriorities = [];
    protected int $succeededDataSets = 0;
    private int $startTs = 0;
    private string $filename = '';
    private ilLogger $aggregatedLogger;
    private bool $has_logged = false;

    public function __construct(LogSettings $settings)
    {
        if (ilPowerBiReportingProviderPlugin::getInstance()->isPluginInstalled(
            'UIComponent',
            'uihk',
            'GenericInterfaceLogOverview'
        )) {
            $coreLoggingFactory = ilLoggerFactory::getInstance();

            $factory = ilLoggerFactory::newInstance($settings);
            $this->aggregatedLogger = $factory->getComponentLogger('PowerBiReportingProvider');

            $loggerFactoryRefl = new ReflectionClass(ilLoggerFactory::class);
            $loggerFactoryInstance = $loggerFactoryRefl->getProperty('instance');
            $loggerFactoryInstance->setAccessible(true);
            $loggerFactoryInstance->setValue($coreLoggingFactory);

            $this->aggregatedLogger->getLogger()->popProcessor();
            $this->aggregatedLogger->getLogger()->pushProcessor(new TraceProcessor(ilLogLevel::DEBUG));

            $this->filename = $settings->getLogDir() . DIRECTORY_SEPARATOR . $settings->getLogFile();

            $this->logOverviewPlugin = ilPowerBiReportingProviderPlugin::getInstance()->getPlugin(
                'UIComponent',
                'uihk',
                'GenericInterfaceLogOverview'
            );
        }
    }

    protected function doWrite(array $message): void
    {
        if ($this->logOverviewPlugin === null) {
            return;
        }

        if ($this->startTs === 0) {
            $this->startTs = time();
        }

        if (isset($message['extra']['imported_data_sets'])) {
            $this->succeededDataSets += $message['extra']['imported_data_sets'];
        }

        if (isset($message['priority'])) {
            if (!isset($this->loggedPriorities[$message['priority']])) {
                $this->loggedPriorities[$message['priority']] = 1;
            } else {
                ++$this->loggedPriorities[$message['priority']];
            }
        }

        $line = $message['message'];

        switch ($message['priority']) {
            case Logger::EMERG:
                $method = 'emergency';
                break;

            case Logger::ALERT:
                $method = 'alert';
                break;

            case Logger::CRIT:
                $method = 'critical';
                break;

            case Logger::ERR:
                $method = 'error';
                break;

            case Logger::WARN:
                $method = 'warning';
                break;

            case Logger::INFO:
                $method = 'info';
                break;

            case Logger::NOTICE:
                $method = 'notice';
                break;

            case Logger::DEBUG:
            default:
                $method = 'debug';
                break;
        }

        $this->aggregatedLogger->{$method}($line);
        $this->has_logged = true;
    }

    public function shutdown(): void
    {
        if ($this->has_logged && !$this->shutDownHandled && $this->logOverviewPlugin !== null) {
            $this->logOverviewPlugin->getReportingData(
                $this->filename,
                (int) ($this->loggedPriorities[Logger::ERR] ?? 0) + (int) ($this->loggedPriorities[Logger::CRIT] ?? 0) +
                (int) ($this->loggedPriorities[Logger::ALERT] ?? 0) + (int) ($this->loggedPriorities[Logger::EMERG] ?? 0),
                (int) ($this->loggedPriorities[Logger::WARN] ?? 0),
                $this->succeededDataSets,
                $this->startTs > 0 ? time() - $this->startTs : 0,
                $this->getHighestLoggedSeverity()
            );
        }

        unset($this->aggregated_logger);

        $this->shutDownHandled = true;
    }

    private function getHighestLoggedSeverity(): int
    {
        foreach (
            [
                Logger::EMERG,
                Logger::ALERT,
                Logger::CRIT,
                Logger::ERR,
                Logger::WARN,
                Logger::NOTICE,
                Logger::INFO,
                Logger::DEBUG
            ] as $severity
        ) {
            if (isset($this->loggedPriorities[$severity]) && $this->loggedPriorities[$severity] > 0) {
                return $severity;
            }
        }

        return PHP_INT_MAX;
    }
}
