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

namespace QU\PowerBiReportingProvider\DataObjects;

use ilDBConstants;

class ProviderIndex extends DataObject
{
    protected string $use_table = 'powbi_prov_index';
    protected string $use_index = 'id';

    private ?int $id = null;
    private int $processed = 0;
    private ?string $trigger = null;
    private ?int $timestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProcessed(): int
    {
        return $this->processed;
    }

    public function setProcessed(int $processed): self
    {
        $this->processed = $processed;
        return $this;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): self
    {
        $this->trigger = $trigger;
        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function load(int $id = null): bool
    {
        if (is_int($id) && $id > 0) {
            $data = $this->_loadById($id);
        } else {
            $data = $this->_load();
            $data = end($data);
        }

        if (is_array($data) && $data !== []) {
            $this->id = (int) $data['id'];
            $this->setProcessed((int) $data['processed']);
            $this->setTrigger($data['trigger']);
            $this->setTimestamp((int) $data['timestamp']);
            return true;
        }

        return false;
    }

    public function save(): bool
    {
        $fields = [
            'processed',
            'trigger',
            'timestamp',
            $this->use_index
        ];
        $types = [
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_TEXT,
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_INTEGER
        ];

        $id = $this->getNextId();
        $values = [
            $this->getProcessed(),
            $this->getTrigger(),
            $this->getTimestamp(),
            $id
        ];

        $status = $this->_create($fields, $types, $values);
        if ($status) {
            $this->id = $id;
        }

        return $status;
    }

    public function remove(): bool
    {
        return false;
    }
}
