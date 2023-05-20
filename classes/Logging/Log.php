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

use DateTimeZone;
use DateTimeImmutable;
use Exception;

class Log implements Logger
{
    /** @var list<Writer> */
    private array $writer = [];

    public function __construct()
    {
    }

    public function __destruct()
    {
        $this->shutdown();
    }

    public function shutdown(): void
    {
        foreach ($this->writer as $writer) {
            $writer->shutdown();
        }
    }

    /**
     * @return array<int, string>
     */
    public static function getPriorities(): array
    {
        return [
            self::EMERG => 'EMERG',
            self::ALERT => 'ALERT',
            self::CRIT => 'CRIT',
            self::ERR => 'ERR',
            self::WARN => 'WARN',
            self::NOTICE => 'NOTICE',
            self::INFO => 'INFO',
            self::DEBUG => 'DEBUG',
        ];
    }

    public function addWriter(Writer $writer, int $priority = 1): void
    {
        $this->writer[] = $writer;
    }

    public function removeWriter(Writer $writer): void
    {
        $key = array_search($writer, $this->writer, true);
        if ($key !== false) {
            unset($this->writer[$key]);
        }
    }

    public function log(int $priority, $message, array $extra = []): void
    {
        if ($priority < 0 || ($priority >= count(self::getPriorities()))) {
            throw new Exception(
                sprintf(
                    '$priority must be an integer > 0 and < %d; received %s',
                    count(self::getPriorities()),
                    print_r($priority, true)
                )
            );
        }

        if (is_object($message) && !method_exists($message, '__toString')) {
            throw new Exception('$message must implement magic __toString() method');
        }

        if (is_array($message)) {
            $message = var_export($message, true);
        }

        $timestamp = new DateTimeImmutable('@' . time(), new DateTimeZone('UTC'));

        $priorities = self::getPriorities();
        foreach ($this->writer as $writer) {
            $writer->write([
                'timestamp' => $timestamp,
                'priority' => $priority,
                'priorityName' => $priorities[$priority],
                'message' => (string) $message,
                'extra' => $extra
            ]);
        }
    }

    public function emerg($message, array $extra = []): void
    {
        $this->log(self::EMERG, $message, $extra);
    }

    public function alert($message, array $extra = []): void
    {
        $this->log(self::ALERT, $message, $extra);
    }

    public function crit($message, array $extra = []): void
    {
        $this->log(self::CRIT, $message, $extra);
    }

    public function err($message, array $extra = []): void
    {
        $this->log(self::ERR, $message, $extra);
    }

    public function info($message, array $extra = []): void
    {
        $this->log(self::INFO, $message, $extra);
    }

    public function warn($message, array $extra = []): void
    {
        $this->log(self::WARN, $message, $extra);
    }

    public function notice($message, array $extra = []): void
    {
        $this->log(self::NOTICE, $message, $extra);
    }

    public function debug($message, array $extra = []): void
    {
        $this->log(self::DEBUG, $message, $extra);
    }
}
