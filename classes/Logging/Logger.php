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

interface Logger
{
    /**
     * @const int defined from the BSD Syslog message severities
     * @link  http://tools.ietf.org/html/rfc3164
     */
    public const EMERG = 0;
    public const ALERT = 1;
    public const CRIT = 2;
    public const ERR = 3;
    public const WARN = 4;
    public const NOTICE = 5;
    public const INFO = 6;
    public const DEBUG = 7;

    public function emerg($message, array $extra = []): void;

    public function alert($message, array $extra = []): void;

    public function crit($message, array $extra = []): void;

    public function err($message, array $extra = []): void;

    public function info($message, array $extra = []): void;

    public function warn($message, array $extra = []): void;

    public function notice($message, array $extra = []): void;

    public function debug($message, array $extra = []): void;

    public function shutdown(): void;
}
