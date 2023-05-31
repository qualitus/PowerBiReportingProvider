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

namespace QU\PowerBiReportingProvider\Task;

use ilCronJobResult;
use ilDBInterface;
use ilPowerBiReportingProviderPlugin;
use ilSetting;
use QU\LERQ\Model\QueueModel;
use QU\PowerBiReportingProvider\APIEndpoint\Report;
use QU\PowerBiReportingProvider\DataObjects\ProviderIndex;
use QU\PowerBiReportingProvider\DataObjects\TrackingOptions;
use QU\PowerBiReportingProvider\FileWriter\CsvWriter;
use QU\PowerBiReportingProvider\Lock\Locker;
use QU\PowerBiReportingProvider\Logging\Logger;
use Throwable;

class ReportingProvider extends BaseTask
{
    public const JOB_NAME = 'POWBI_REPORTING_PROVIDER';

    private ilPowerBiReportingProviderPlugin $plugin;
    private ilSetting $settings;
    private Locker $locker;
    private Logger $logger;

    public function __construct(
        ilPowerBiReportingProviderPlugin $plugin,
        Locker $locker = null,
        Logger $logger = null,
        ilSetting $settings = null
    ) {
        $this->plugin = $plugin;
        $this->locker = $locker;
        $this->logger = $logger;
        $this->settings = $settings;
    }

    public function getId(): string
    {
        return self::JOB_NAME;
    }

    public function hasCustomSettings(): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return $this->plugin->txt('cronjob_title');
    }

    public function getDescription(): string
    {
        return $this->plugin->txt('cronjob_description');
    }

    public function activationWasToggled(ilDBInterface $db, ilSetting $setting, bool $a_currently_active): void
    {
        parent::activationWasToggled($db, $setting, $a_currently_active);

        $this->locker->releaseLock();
    }

    public function run(): ilCronJobResult
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_OK);

        if (!$this->plugin->isPluginInstalled('Cron', 'crnhk', 'LpEventReportQueue')) {
            $this->logger->info('Plugin LpEventReportQueue is not available or not active.');
            $this->logger->shutdown();
            $result->setStatus(\ilCronJobResult::STATUS_FAIL);
            $result->setMessage('Plugin LpEventReportQueue is not available or not active.');
            return $result;
        }

        $this->logger->info('Started job.');

        if ($this->locker->acquireLock()) {
            $this->logger->info('Acquired lock.');

        } else {
            $this->logger->info('Script is probably running, please remove the lock if you are sure no task is running.');
            $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
            $result->setMessage('Task is currently running/locked');

            $this->logger->shutdown();

            return $result;
        }

        try {
            $last_run = new ProviderIndex();
            $last_run->load();

            // $last_processed is the id of the last processed queue item
            $last_processed = 0;
            if ($last_run->getId() !== null) {
                $last_processed = $last_run->getProcessed();
                $this->logger->info('Last run at ' . $last_run->getTimestamp());
            } else {
                $this->logger->info('First run');
            }

            // get export configs
            $export_path = $this->settings->get('export_path', '/tmp');
            $export_filename = $this->settings->get('export_filename', 'powbi_export');
            $export_limit = $this->settings->get('export_limit', '0');

            // create filter params
            $filter_params = [
                'start' => $last_processed,
            ];
            $filter_params['limit'] = $export_limit > 0 ? (int) $export_limit : -1;

            if ($this->settings->get('ignoreNotAttempted_' . $this->plugin->getId(), '0')) {
                $filter_params['excluded_progress'] = 'no_attempted';
            }

            $this->logger->info('Collecting Settings and Options');

            // get queue collection and prepare data for csv
            $trackingOptions = new TrackingOptions();
            $trackingOptions->load();
            $report = new Report();

            $this->logger->info('Trying to get filtered event data');
            $events = $report->getFilteredEvents($filter_params);
            if ($events !== [] && $events['data'] !== []) {
                $this->logger->info('Found data for ' . count($events['data']) . ' events.');

                $this->logger->info('Preparing CSV headers');
                // collect export fields
                $fieldnames = [];
                foreach ($trackingOptions->getAvailableOptions() as $keyword) {
                    $opt = $trackingOptions->getOptionByKeyword($keyword);
                    if (!$opt || !$opt->isActive()) {
                        continue;
                    }
                    $fieldnames[$keyword] = $opt->getFieldName();
                }

                $this->logger->info('Resolving filepath');
                $file_path = $this->resolveFilepath($export_path, $export_filename);

                $status = $this->generateCsv($events['data'], $fieldnames, $file_path);
                if (!$status) {
                    $result->setMessage('Finished Job with error');
                }

                /** @var \QU\LERQ\Model\QueueModel $latest_event */
                $latest_event = end($events['data']);
                $last_processed = $latest_event->getId();
                $new_run = new ProviderIndex();
                $new_run->setTimestamp(time());
                $new_run->setTrigger('cron');
                $new_run->setProcessed($last_processed);
                $new_run->save();

                $result->setStatus(ilCronJobResult::STATUS_OK);
                if ($result->getMessage() === '') {
                    $result->setMessage('Finished job without errors');
                }
            } else {
                $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
                $result->setMessage('Finished without processing. No data to export found.');
            }

        } catch (Throwable $t) {
            $result->setStatus(ilCronJobResult::STATUS_FAIL);
            $result->setMessage($t->getMessage());
            $this->logger->err($t->getMessage());
        }

        $this->locker->releaseLock();
        $this->logger->info('Finished job.');
        $this->logger->shutdown();

        return $result;
    }

    /**
     * @param list<QueueModel> $data
     * @param array<string, string> $fieldnames
     */
    private function generateCsv(array $data, array $fieldnames, string $file_path): bool
    {
        $this->logger->info('Start CSV write process');
        $writer = new CsvWriter($file_path);

        $writer->setFields(array_values($fieldnames));
        $writer->setData(
            $this->prepareData($data, $fieldnames)
        );

        $this->logger->info('Finished CSV write process');
        return $writer->writeCsv();
    }

    /**
     * @param list<QueueModel> $data
     * @param array<string, string> $fieldnames
     * @return list<array<string, string>
     */
    private function prepareData(array $data, array $fieldnames): array
    {
        $prepared = [];
        $first = true;
        foreach ($data as $queueItem) {
            $set = [];
            $set[$fieldnames['id']] = $queueItem->getId();
            $set[$fieldnames['timestamp']] = '';
            if (is_numeric($queueItem->getTimestamp())) {
                $dt = new \Datetime();
                $dt->setTimestamp($queueItem->getTimestamp());
                $dt->setTimezone(new \DateTimeZone('UTC'));
                $set[$fieldnames['timestamp']] = $dt->format('c');
            }

            if (array_key_exists('trigger', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['trigger']);
                }
                $set[$fieldnames['trigger']] = $queueItem->getEvent();
            }

            if(array_key_exists('progress', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['progress']);
                }
                $set[$fieldnames['progress']] = $queueItem->getProgress();
            }

            if(array_key_exists('assignment', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['assignment']);
                }
                $set[$fieldnames['assignment']] = $queueItem->getAssignment();
            }

            $objData = $queueItem->getObjData();
            if ($first) {
                $this->logger->debug(var_export((string) $objData, true));
                $this->logger->debug(var_export(array_key_exists('obj_type', $fieldnames), true));
                $this->logger->debug(var_export($fieldnames, true));
            }
            if(array_key_exists('obj_type', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['obj_type']);
                }
                $set[$fieldnames['obj_type']] = $objData->getType();
            }

            if(array_key_exists('obj_title', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['obj_title']);
                }
                $set[$fieldnames['obj_title']] = $objData->getTitle();
            }

            if(array_key_exists('refid', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['refid']);
                }
                $set[$fieldnames['refid']] = $objData->getRefId();
            }

            if(array_key_exists('link', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['link']);
                }
                $set[$fieldnames['link']] = $objData->getLink();
            }

            if(array_key_exists('parent_title', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['parent_title']);
                }
                $set[$fieldnames['parent_title']] = $objData->getCourseTitle();
            }

            if(array_key_exists('parent_refid', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['parent_refid']);
                }
                $set[$fieldnames['parent_refid']] = $objData->getCourseRefId();
            }

            if(array_key_exists('user_mail', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['user_mail']);
                }
                $set[$fieldnames['user_mail']] = $queueItem->getUserData()->getEmail();
            }

            if(array_key_exists('user_id', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['user_id']);
                }
                $set[$fieldnames['user_id']] = $queueItem->getUserData()->getUsrId();
            }

            if(array_key_exists('user_login', $fieldnames)) {
                if ($first) {
                    $this->logger->debug('getting ' . $fieldnames['user_login']);
                }
                $set[$fieldnames['user_login']] = $queueItem->getUserData()->getLogin();
            }

            $prepared[] = $set;
            unset($set);
            $first = false;
        }

        return $prepared;
    }

    private function resolveFilepath(string $path, string $filename): string
    {
        if(false !== ($spos = strpos($filename, '['))) {
            $epos = strpos($filename, ']');
            $date_format = substr($filename, ($spos + 1), ($epos - 1));
            $filename = substr($filename, 0, $spos) . date($date_format) . substr($filename, ($epos + 1));
        }

        if (substr($filename, -4) !== '.csv') {
            $filename .= '.csv';
        }

        if (substr($path, -1) !== '/') {
            $path .= '/';
        }

        return $path . $filename;
    }
}
