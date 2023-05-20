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

interface DataObjectInterface
{
    /**
     * Load data into the DataObject
     *
     * This MUST return bool and SHOULD fill the
     * object parameters of the DataObject.
     *
     * @example
     * public function load(int $id = null): bool
     * {
     *     $data = $this->_loadById($id);
     *     if (!empty($data)) {
     *         $this->setId($data['id']);
     *         $this->setParamaterOne($data['parameter_one']);
     *         $this->setParamaterTwo($data['parameter_two']);
     *         // ...
     *         return true;
     *     } else {
     *         return false;
     *     }
     * }
     *
     * @param int|null $id	Entry ID
     * @return bool
     */
    public function load(int $id = null): bool;

    /**
     * Save an entry to database
     *
     * This SHOULD always affect only the actually
     * loaded entry. You have to check if you will
     * _create() or _update() the entry, by your own.
     *
     * @example
     * public function save(): bool
     * {
     *     $fields = [
     *         'parameter_one',
     *         'parameter_two'
     *         // ...
     *     ];
     *     $types = [
     *         'integer',
     *         'string'
     *         // ...
     *     ];
     *     $values = [
     *         $this->getParamaterOne(),
     *         $this->getParamaterOwo(),
     *         // ...
     *     ];
     *     if ( isset($this->getId()) ) {
     *         return $this->_update($fields, $types, $values, $this->getId());
     *     } else {
     *         $fields[$this->use_index] = $this->getNextId();
     *         return $this->_create($fields, $types, $values);
     *     }
     * }
     *
     *
     * @return bool
     */
    public function save(): bool;

    /**
     * Delete an entry from database
     *
     * This SHOULD always affect only the actually
     * loaded entry.
     *
     * @example
     * public function remove(): bool
     * {
     *     if ( isset($this->getId()) ) {
     *         return _delete($this->use_index)
     *     }
     *     @return false;
     * }
     *
     * @return bool
     */
    public function remove(): bool;
}
