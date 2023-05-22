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

use ILIAS\DI\Container;
use QU\PowerBiReportingProvider\Lock\PidBasedLocker;
use QU\PowerBiReportingProvider\Logging\Log;
use QU\PowerBiReportingProvider\Logging\Logger;
use QU\PowerBiReportingProvider\Logging\Settings as LogSettings;
use QU\PowerBiReportingProvider\Logging\Writer\Ilias;
use QU\PowerBiReportingProvider\Logging\Writer\Gilo;
use QU\PowerBiReportingProvider\Logging\Writer\StdOut;
use QU\PowerBiReportingProvider\Task\ReportingProvider;

class ilPowerBiReportingProviderPlugin extends \ilCronHookPlugin
{
    private const CTYPE = 'Services';
    private const CNAME = 'Cron';
    private const SLOT_ID = 'crnhk';
    public const PLUGIN_ID = 'powbi_rep_prov';
    public const PLUGIN_NAME = 'PowerBiReportingProvider';
    public const PLUGIN_SETTINGS = 'qu_crnhk_powbi_rep_prov';
    public const PLUGIN_NS = 'QU\PowerBiReportingProvider';

    private static ?self $instance = null;
    /** @var array<string, array<string, array<string, bool>>> */
    private static array $activePluginsCheckCache = [];
    /** @var array<string, array<string, array<string, ilPlugin>>> */
    private static array $activePluginsCache = [];

    private ilSetting $settings;
    private Container $dic;

    public function __construct(
        ilDBInterface $db,
        ilComponentRepositoryWrite $component_repository,
        string $id
    ) {
        global $DIC;

        $this->dic = $DIC;

        parent::__construct($db, $component_repository, $id);

        $this->settings = new ilSetting(self::PLUGIN_SETTINGS);
    }

    public static function getInstance(): self
    {
        global $DIC;

        if (self::$instance instanceof self) {
            return self::$instance;
        }

        /** @var ilComponentRepository $component_repository */
        $component_repository = $DIC['component.repository'];
        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC['component.factory'];

        $plugin_info = $component_repository->getComponentByTypeAndName(
            self::CTYPE,
            self::CNAME
        )->getPluginSlotById(self::SLOT_ID)->getPluginByName(self::PLUGIN_NAME);

        self::$instance = $component_factory->getPlugin($plugin_info->getId());

        return self::$instance;
    }

    protected function init(): void
    {
        $this->registerAutoloader();

        if (!isset($this->dic['plugin.powbi.export.logger.writer.ilias'])) {
            $this->dic['plugin.powbi.export.logger.writer.ilias'] = static function (Container $c): \QU\PowerBiReportingProvider\Logging\Writer {
                $logLevel = ilLoggingDBSettings::getInstance()->getLevel();

                return new Ilias($c->logger()->root(), $logLevel);
            };
        }

        if (!isset($this->dic['plugin.powbi.export.cronjob.logger'])) {
            $this->dic['plugin.powbi.export.cronjob.logger'] = static function (Container $c): \QU\PowerBiReportingProvider\Logging\Logger {
                $logger = new Log();

                $logger->addWriter(new StdOut());
                $logger->addWriter($c['plugin.powbi.export.logger.writer.ilias']);

                $tempDirectory = ilFileUtils::ilTempnam();
                ilFileUtils::makeDir($tempDirectory);
                $now = new DateTimeImmutable();
                $settings = new LogSettings($tempDirectory, 'powbi_rep_prov_' . $now->format('Y_m_d_H_i_s') . '.log');
                $logger->addWriter(new Gilo($settings));

                return $logger;
            };
        }

        if (!isset($this->dic['plugin.powbi.export.web.logger'])) {
            $this->dic['plugin.powbi.export.web.logger'] = static function (Container $c): \QU\PowerBiReportingProvider\Logging\Logger {
                $logger = new Log();

                $logger->addWriter($c['plugin.powbi.export.logger.writer.ilias']);

                return $logger;
            };
        }

        if (!isset($this->dic['plugin.powbi.cronjob.locker'])) {
            $this->dic['plugin.powbi.cronjob.locker'] = fn (Container $c): \QU\PowerBiReportingProvider\Lock\Locker => new PidBasedLocker(
                new ilSetting($this->getPluginName()),
                $c['plugin.powbi.export.cronjob.logger']
            );
        }
    }

    private function registerAutoloader(): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        if (!isset($this->dic['autoload.lc.lcautoloader'])) {
            $Autoloader = new LCAutoloader();
            $Autoloader->register();
            $Autoloader->addNamespace('ILIAS\Plugin', '/Customizing/global/plugins');
            $this->dic['autoload.lc.lcautoloader'] = static fn (\ILIAS\DI\Container $c): LCAutoloader => $Autoloader;
        }

        $this->dic['autoload.lc.lcautoloader']->addNamespace(self::PLUGIN_NS, realpath(__DIR__));
    }

    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    public function getSettings(): ilSetting
    {
        return $this->settings;
    }

    public function getCronJobInstances(): array
    {
        return [
            new ReportingProvider(
                $this,
                $GLOBALS['DIC']['plugin.powbi.cronjob.locker'],
                $GLOBALS['DIC']['plugin.powbi.export.cronjob.logger'],
                new ilSetting()
            )
        ];
    }

    public function getCronJobInstance(string $jobId): ilCronJob
    {
        return new ReportingProvider(
            $this,
            $GLOBALS['DIC']['plugin.powbi.cronjob.locker'],
            $GLOBALS['DIC']['plugin.powbi.export.cronjob.logger'],
            new ilSetting()
        );
    }

    public function isPluginInstalled(string $component, string $slot, string $plugin_class): bool
    {
        if (isset(self::$activePluginsCheckCache[$component][$slot][$plugin_class])) {
            return self::$activePluginsCheckCache[$component][$slot][$plugin_class];
        }

        /** @var ilComponentRepository $component_repository */
        $component_repository = $this->dic['component.repository'];

        $has_plugin = $component_repository->getComponentByTypeAndName(
            'Services',
            $component
        )->getPluginSlotById($slot)->hasPluginName($plugin_class);

        if ($has_plugin) {
            $plugin_info = $component_repository->getComponentByTypeAndName(
                'Services',
                $component
            )->getPluginSlotById($slot)->getPluginByName($plugin_class);
            $has_plugin = $plugin_info->isActive();
        }

        return (self::$activePluginsCheckCache[$component][$slot][$plugin_class] = $has_plugin);
    }

    public function getPlugin(string $component, string $slot, string $plugin_class): ilPlugin
    {
        if (isset(self::$activePluginsCache[$component][$slot][$plugin_class])) {
            return self::$activePluginsCache[$component][$slot][$plugin_class];
        }

        /** @var ilComponentRepository $component_repository */
        $component_repository = $this->dic['component.repository'];
        /** @var ilComponentFactory $component_factory */
        $component_factory = $this->dic['component.factory'];

        $plugin_info = $component_repository->getComponentByTypeAndName(
            'Services',
            $component
        )->getPluginSlotById($slot)->getPluginByName($plugin_class);

        $plugin = $component_factory->getPlugin($plugin_info->getId());

        return (self::$activePluginsCache[$component][$slot][$plugin_class] = $plugin);
    }

    protected function afterActivation(): void
    {
        $this->registerAutoloader();

        if (!isset($this->dic['qu.lerq.api'])) {
            if ($this->isPluginInstalled('Cron', 'crnhk', 'LpEventReportQueue')) {
                $plugin = $this->getPlugin('Cron', 'crnhk', 'LpEventReportQueue');
            }

            if (!isset($this->dic['qu.lerq.api'])) {
                $this->dic->logger()->root()->error('Could not init LpEventReportQueue API');
                return;
            }
        }

        $this->dic['qu.lerq.api']->registerProvider(
            self::PLUGIN_NAME,
            self::PLUGIN_NS,
            realpath(__DIR__),
            false
        );
    }

    protected function afterDeactivation(): void
    {
        parent::afterDeactivation();

        if (!isset($this->dic['qu.lerq.api'])) {
            if ($this->isPluginInstalled('Cron', 'crnhk', 'LpEventReportQueue')) {
                $plugin = $this->getPlugin('Cron', 'crnhk', 'LpEventReportQueue');
            }

            if (!isset($this->dic['qu.lerq.api'])) {
                $this->dic->logger()->root()->error('Could not init LpEventReportQueue API');
                return;
            }
        }

        if (isset($this->dic['qu.lerq.api'])) {
            $this->dic['qu.lerq.api']->unregisterProvider(
                self::PLUGIN_NAME,
                self::PLUGIN_NS
            );
        }
    }

    protected function beforeUninstall(): bool
    {
        return $this->deactivate();
    }
}
