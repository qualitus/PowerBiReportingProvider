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

class TrackingOption extends DataObject
{
    protected string $use_table = 'powbi_prov_options';
    protected string $use_index = 'id';

    /** @var int */
    private $id;

    /** @var string */
    private $keyword;

    /** @var bool */
    private $active;

    /** @var string */
    private $field_name;

    /** @var int */
    private $updated_at;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return TrackingOption
     */
    public function setId(int $id): TrackingOption
    {
        if (!isset($this->id)) {
            $this->id = $id;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getKeyword(): string
    {
        return $this->keyword;
    }

    /**
     * @param string $keyword
     * @return TrackingOption
     */
    public function setKeyword(string $keyword): TrackingOption
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return TrackingOption
     */
    public function setActive(bool $active): TrackingOption
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->field_name;
    }

    /**
     * @param string $field_name
     * @return TrackingOption
     */
    public function setFieldName(string $field_name): TrackingOption
    {
        $this->field_name = $field_name;
        return $this;
    }

    /**
     * @return int
     */
    public function getUpdatedAt(): int
    {
        return $this->updated_at;
    }

    /**
     * @param int $updated_at
     * @return TrackingOption
     */
    public function setUpdatedAt(int $updated_at): TrackingOption
    {
        $this->updated_at = $updated_at;
        return $this;
    }


    /**
     * @inheritDoc
     *
     * The id must be given at this point
     */
    public function load(int $id = null): bool
    {
        if (isset($id)) {
            $data = $this->_loadById($id);

            if (!empty($data)) {
                $this->id = $data['id'];
                $this->setKeyword($data['keyword']);
                $this->setActive(($data['active'] == true));
                $this->setFieldName($data['field_name']);
                $this->setUpdatedAt($data['updated_at']);
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $fields = [
            'keyword',
            'active',
            'field_name',
            'updated_at',
        ];
        $types = [
            'text',
            'integer',
            'text',
            'integer',
        ];
        $values = [
            $this->getKeyword(),
            ($this->isActive() == true ? 1 : 0),
            $this->getFieldName(),
            time(),
        ];

        return $this->_update($fields, $types, $values, $this->id);
    }

    /**
     * @return bool
     */
    public function remove(): bool
    {
        return false;
    }

}
