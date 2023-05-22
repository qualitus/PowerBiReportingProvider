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
use DateTimeImmutable;

abstract class Base implements Logging\Writer
{
    public const DEFAULT_FORMAT = '%timestamp% %priorityName% (%priority%): %message% %extra%';

    abstract protected function doWrite(array $message): void;

    private static function formatBytes(int $bytes): string
    {
        $memoryUnits = ['', 'kilobyte(s)', 'megabyte(s)', 'gigabyte(s)'];

        $i = 0;
        while (1023 < $bytes) {
            $bytes /= 1024;
            ++$i;
        }

        return $i ? (round($bytes, 2) . ' ' . $memoryUnits[$i]) : ($bytes . ' byte(s)');
    }

    protected static function getDateTimeFormat(): string
    {
        return 'y-m-d H:i:s';
    }

    /**
     * Could be replaced by a formatter object in later releases (means: never ;-))
     */
    protected function format(array $message): string
    {
        $output = self::DEFAULT_FORMAT;
        foreach ($message as $part => $value) {
            if ('extra' === $part && (is_countable($value) && count($value) > 0)) {
                $value = $this->normalize($value);
            } elseif ('extra' === $part) {
                // Don't print an empty array
                $value = '';
            } else {
                $value = $this->normalize($value);
            }

            $output = str_replace("%$part%", (string) $value, $output);
        }

        return $output;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function normalize($value)
    {
        if (is_scalar($value) || null === $value) {
            return $value;
        }

        if ($value instanceof DateTimeImmutable) {
            return $value->format(self::getDateTimeFormat());
        }

        if (is_array($value)) {
            foreach ($value as $key => $subvalue) {
                $value[$key] = $this->normalize($subvalue);
            }

            return (string) json_encode($value, JSON_THROW_ON_ERROR);
        }

        if (is_object($value) && !method_exists($value, '__toString')) {
            return sprintf('object(%s) %s', get_class($value), json_encode($value, JSON_THROW_ON_ERROR));
        }

        if (is_resource($value)) {
            return sprintf('resource(%s)', get_resource_type($value));
        }

        if (!is_object($value)) {
            return gettype($value);
        }

        return '';
    }

    public function write(array $message): void
    {
        $this->doWrite($message);
    }
}
