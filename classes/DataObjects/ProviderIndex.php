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

    /** @var int */
    private $id;
    /** @var int */
    private $processed;
    /** @var string */
    private $trigger;
    /** @var int */
    private $timestamp;

    public function getId()
    {
        return $this->id;
    }

    public function getProcessed(): int
    {
        return $this->processed;
    }

    public function setProcessed(int $processed): ProviderIndex
    {
        $this->processed = $processed;
        return $this;
    }

    public function getTrigger(): string
    {
        return $this->trigger;
    }

    public function setTrigger(string $trigger): ProviderIndex
    {
        $this->trigger = $trigger;
        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): ProviderIndex
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function load(int $id = null): bool
    {
        if (isset($id)) {
            $data = $this->_loadById($id);
        } else {
            $data = $this->_load();
            $data = end($data);
        }

        if (!empty($data)) {
            $this->id = $data['id'];
            $this->setProcessed($data['processed']);
            $this->setTrigger($data['trigger']);
            $this->setTimestamp($data['timestamp']);
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
            ilDBConstants::T_INTEGER,
        ];
        $values = [
            $this->getProcessed(),
            $this->getTrigger(),
            $this->getTimestamp(),
            $this->getNextId(),
        ];

        return $this->_create($fields, $types, $values);
    }

    public function remove(): bool
    {
        return false;
    }
}
