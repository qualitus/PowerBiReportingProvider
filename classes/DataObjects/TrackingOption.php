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

class TrackingOption extends DataObject
{
    protected string $use_table = 'powbi_prov_options';
    protected string $use_index = 'id';

    private ?int $id = null;
    private ?string $keyword = null;
    private bool $active = false;
    private ?string $field_name = null;
    private ?int $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getKeyword(): ?string
    {
        return $this->keyword;
    }

    public function setKeyword(string $keyword): self
    {
        $this->keyword = $keyword;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getFieldName(): ?string
    {
        return $this->field_name;
    }

    public function setFieldName(string $field_name): self
    {
        $this->field_name = $field_name;
        return $this;
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(int $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }


    public function load(int $id = null): bool
    {
        if (is_int($id) && $id > 0) {
            $data = $this->_loadById($id);

            if (is_array($data) && $data !== []) {
                $this->id = (int) $data['id'];
                $this->setKeyword($data['keyword']);
                $this->setActive((int) $data['active'] === 1);
                $this->setFieldName($data['field_name']);
                $this->setUpdatedAt((int) $data['updated_at']);
                return true;
            }
        }

        return false;
    }

    public function save(): bool
    {
        $fields = [
            'keyword',
            'active',
            'field_name',
            'updated_at'
        ];
        $types = [
            ilDBConstants::T_TEXT,
            ilDBConstants::T_INTEGER,
            ilDBConstants::T_TEXT,
            ilDBConstants::T_INTEGER
        ];
        $values = [
            $this->getKeyword(),
            $this->isActive() ? 1 : 0,
            $this->getFieldName(),
            time()
        ];

        return $this->_update($fields, $types, $values, $this->id);
    }

    public function remove(): bool
    {
        return false;
    }
}
