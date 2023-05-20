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

namespace QU\PowerBiReportingProvider\Logging;

use ilLogLevel;
use ilLoggingSettings;

class Settings implements ilLoggingSettings
{
    private int $level ;
    private int $cache_level;
    private string $directory;
    private string $file;
    private bool $cache = false;

    public function __construct(string $directory, string $file, int $logLevel = ilLogLevel::INFO)
    {
        $this->level = $logLevel;
        $this->cache_level = ilLogLevel::DEBUG;

        $this->directory = $directory;
        $this->file = $file;
    }

    public function getLevelByComponent(string $a_component_id): int
    {
        return $this->getLevel();
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function getLogDir(): string
    {
        return $this->directory;
    }

    public function getLogFile(): string
    {
        return $this->file;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getCacheLevel(): int
    {
        return $this->cache_level;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cache;
    }

    public function isMemoryUsageEnabled(): bool
    {
        return true;
    }

    public function isBrowserLogEnabled(): bool
    {
        return false;
    }

    public function isBrowserLogEnabledForUser(string $a_login): bool
    {
        return false;
    }

    public function getBrowserLogUsers(): array
    {
        return [];
    }
}
