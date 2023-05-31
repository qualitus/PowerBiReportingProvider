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

namespace QU\PowerBiReportingProvider\Lock;

use Exception;
use ilSetting;
use QU\PowerBiReportingProvider\Logging\Logger;

class PidBasedLocker implements Locker
{
    private ilSetting $settings;
    private Logger $logger;

    public function __construct(ilSetting $settings, Logger $logger)
    {
        $this->settings = $settings;
        $this->logger   = $logger;
    }

    protected function isRunning(string $pid): bool
    {
        try {
            $result = shell_exec(\sprintf("ps %d", $pid));
            if (count(explode("\n", $result)) > 2) {
                return true;
            }
        } catch (Exception $e) {
        }

        return false;
    }

    private function writeLockedState(): void
    {
        $this->settings->set('cron_lock_status', '1');
        $this->settings->set('cron_lock_ts', (string) time());
        $this->settings->set('cron_lock_pid', (string) getmypid());
    }

    public function acquireLock(): bool
    {
        if (!$this->isLocked()) {
            $this->writeLockedState();
            return true;
        }

        $pid = (string) $this->settings->get('cron_lock_pid', '');
        if ($this->isRunning($pid)) {
            $lastLockTimestamp = (int) $this->settings->get('cron_lock_ts', (string) time());
            if ($lastLockTimestamp > time() - (60 * 60 * 1)) {
                return false;
            }
        }

        $this->writeLockedState();
        return true;
    }

    public function isLocked(): bool
    {
        return (bool) $this->settings->get('cron_lock_status', '0');
    }

    public function releaseLock(): void
    {
        $this->settings->set('cron_lock_status', '0');
        $this->settings->delete('cron_lock_ts');
        $this->settings->delete('cron_lock_pid');
    }
}
