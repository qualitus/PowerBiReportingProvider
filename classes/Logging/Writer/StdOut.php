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

use Exception;

class StdOut extends Base
{
    /** @var resource */
    private $stream;
    private string $logSeparator = PHP_EOL;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->stream = fopen('php://stdout', 'wb', false);
        if (!$this->stream || !is_resource($this->stream)) {
            throw new Exception(sprintf(
                '"%s" cannot be opened with mode "%s"',
                'php://stdout',
                'w'
            ));
        }
    }

    public function setLogSeparator(string $logSeparator): void
    {
        $this->logSeparator = $logSeparator;
    }

    public function getLogSeparator(): string
    {
        return $this->logSeparator;
    }

    protected function doWrite(array $message): void
    {
        $line = $this->format($message) . $this->getLogSeparator();
        fwrite($this->stream, $line);
    }

    public function shutdown(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }
}
