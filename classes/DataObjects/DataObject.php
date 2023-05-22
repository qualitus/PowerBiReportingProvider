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
use ilDBInterface;

abstract class DataObject implements DataObjectInterface
{
    /**
     * Table name for DataObject
     * You MUST set this parameter inside your
     * DataObject to define the table name.
     */
    protected string $use_table;

    /**
     * ID field in database table
     * This is used as index.
     * You MUST set this parameter inside your
     * DataObject to define the table index field.
     */
    protected string $use_index;

    private ilDBInterface $database;

    /**
     * If you override this function, you SHOULD use
     * the parent::__construct at the beginning of
     * your own constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->database = $DIC->database();
    }

    /**
     * Get next sequence id
     */
    final protected function getNextId(): int
    {
        return $this->database->nextId($this->use_table);
    }

    /**
     * Load all entries from database
     * This is not recommended. You should use _loadById() instead.
     * @return list<array<string, mixed>>
     */
    final protected function _load(): array
    {
        $select = 'SELECT * FROM `' . $this->use_table . '`;';

        $result = $this->database->query($select);

        return $this->database->fetchAll($result);
    }

    /**
     * Load a specific entry be its ID
     * This is the recommended function to load the data
     * into your object. Just use this function inside
     * your objects __construct() and assign the returned
     * data to your objects parameters.
     * @param int $id Entry ID from $use_index field
     * @return null|array<string, mixed> Array with database values like [ field_name => field_value ]
     */
    final protected function _loadById(int $id): ?array
    {
        $select = 'SELECT * FROM `' . $this->use_table . '` WHERE ' . $this->use_index . ' = ' .
            $this->database->quote($id, ilDBConstants::T_INTEGER);

        $result = $this->database->query($select);

        $res = $this->database->fetchAll($result);

        return $res[0] ?? null;
    }

    /**
     * Create a new entry in database
     *
     * @param list<string> $fields Array of fields
     * @param list<string> $types Array of field types
     * @param list<mixed> $values Array of values to save
     */
    final protected function _create(array $fields, array $types, array $values): bool
    {
        $query = 'INSERT INTO `' . $this->use_table . '` ';
        $query .= '(`' . implode('`, `', $fields) . '`) ';
        $query .= 'VALUES (' . implode(', ', array_fill(0, count($fields), '%s')) . ') ';

        $res = $this->database->manipulateF(
            $query,
            $types,
            $values
        );

        return $res > 0;
    }

    /**
     * Update an entry in database
     * @param list<string> $fields Array of fields
     * @param list<string> $types Array of field types
     * @param list<mixed> $values Array of values to save
     */
    final protected function _update(array $fields, array $types, array $values, int $whereIndex): bool
    {
        $query = 'UPDATE `' . $this->use_table . '` SET ';
        $query .= implode(' = %s,', $fields) . ' = %s ';
        $query .= 'WHERE ' . $this->use_index . ' = ' . $this->database->quote($whereIndex, 'integer') . ';';

        $res = $this->database->manipulateF(
            $query,
            $types,
            $values
        );

        return $res > 0;
    }

    final protected function _delete(int $whereIndex): bool
    {

        $query = 'DELETE FROM `' . $this->use_table . '` WHERE ' . $this->use_index . ' = ' .
            $this->database->quote($whereIndex, 'text') . ';';

        $res = $this->database->manipulate($query);

        return $res > 0;
    }
}
