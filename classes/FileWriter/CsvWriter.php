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

namespace QU\PowerBiReportingProvider\FileWriter;

use Exception;

/**
 * Class CsvWriter
 * @package QU\PowerBiReportingProvider\FileWriter
 * @author Ralph Dittrich <dittrich@qualitus.de>
 */
class CsvWriter
{
    private string $file_path;
    /** @var resource|false */
    private $buffer;
    /** @var list<string> */
    private array $fields;
    /** @var list<list<string>> */
    private array $data;

    public function __construct(string $file_path)
    {
        $this->file_path = $file_path;
        $this->fields = [];
        $this->data = [];
    }

    /**
     * @param list<string> $fields
     */
    public function setFields(array $fields): self
    {
        if (!empty($this->fields)) {
            throw new Exception('CSV fields are already set.');
        }

        $this->fields = $fields;
        return $this;
    }

    /**
     * @param list<list<string>> $data
     */
    public function setData(array $data): self
    {
        $this->data = [];
        foreach ($data as $row) {
            if (!is_array($row)) {
                throw new Exception('Given data is invalid. Expected array, got ' . gettype($row) . '.');
            }
            if (($rcount = count($row)) !== ($fcount = count($this->fields))) {
                throw new Exception('Given data is invalid. Expected ' . $fcount . ' fields but got ' . $rcount . '.');
            }
            $this->data[] = $row;
        }

        return $this;
    }

    /**
     * @param list<string> $row
     */
    public function addRow(array $row): self
    {
        if (($rcount = count($row)) !== ($fcount = count($this->fields))) {
            throw new Exception('Given data is invalid. Expected ' . $fcount . ' fields but got ' . $rcount . '.');
        }
        $this->data[] = $row;

        return $this;
    }

    public function writeCsv(): bool
    {
        if ($this->file_path === '') {
            throw new Exception('No file path given.');
        }

        if (empty($this->fields)) {
            throw new Exception('No fields defined.');
        }

        if (empty($this->data)) {
            throw new Exception('Cannot write empty data.');
        }

        $this->openFile();
        $this->writeRow($this->fields);
        foreach ($this->data as $row) {
            if (false === $this->writeRow($row)) {
                $this->closeFile();
                throw new Exception('Could not write all data. Stopped process.');
            }
        }

        return $this->closeFile();
    }

    /**
     * @param list<string> $row
     * @return false|int
     */
    private function writeRow(array $row)
    {
        return fputcsv($this->buffer, $row, chr(124));
    }

    private function openFile(): void
    {
        $fnpos = strrpos($this->file_path, chr(47));
        $dir_path = substr($this->file_path, 0, $fnpos);
        $filename = substr($this->file_path, ($fnpos + 1));

        if (!is_dir($dir_path) && !mkdir($dir_path, 0755, true) && !is_dir($dir_path)) {
            throw new Exception('Directory (' . $dir_path . ') does not exist and cannot be created.');
        }

        $this->buffer = fopen($this->file_path, 'ab');
        if (!is_resource($this->buffer) || $this->buffer === false) {
            throw new Exception('Cannot create file for writing.');
        }
    }

    private function closeFile(): bool
    {
        $status = false;
        if (is_resource($this->buffer)) {
            $status = fclose($this->buffer);
        }

        return $status;
    }
}
