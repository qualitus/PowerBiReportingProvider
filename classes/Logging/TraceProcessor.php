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

use ilTraceProcessor;

class TraceProcessor extends ilTraceProcessor
{
    private int $level;

    public function __construct(int $level)
    {
        $this->level = $level;
        parent::__construct($level);
    }

    public function __invoke(array $record): array
    {
        if ($record['level'] < $this->level) {
            return $record;
        }

        $trace = debug_backtrace();

        // shift current method
        array_shift($trace);

        // shift plugin logger
        array_shift($trace);
        array_shift($trace);
        array_shift($trace);
        array_shift($trace);

        // shift internal Monolog calls
        array_shift($trace);
        array_shift($trace);
        array_shift($trace);
        array_shift($trace);

        $trace_info = $trace[0]['class'] . '::' . $trace[0]['function'] . ':' . $trace[0]['line'];

        $record['extra'] = array_merge(
            $record['extra'],
            ['trace' => $trace_info]
        );

        return $record;
    }
}
