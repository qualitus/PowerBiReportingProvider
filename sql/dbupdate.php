<#1>
<?php
/** @var ilDBInterface $ilDB */
if (!$ilDB->tableExists('powbi_prov_index')) {
    $ilDB->createTable('powbi_prov_index', [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        ],
        'processed' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        ],
        'trigger' => [
            'type' => 'text',
            'notnull' => true,
            'default' => '',
        ],
        'timestamp' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
        ],
    ]);
    $ilDB->addPrimaryKey('powbi_prov_index', array('id'));
    $ilDB->createSequence('powbi_prov_index');
}

if (!$ilDB->tableExists('powbi_prov_options')) {
    $ilDB->createTable('powbi_prov_options', [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        ],
        'keyword' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ],
        'active' => [
            'type' => 'integer',
            'notnull' => true,
            'default' => 0,
            'length' => 1
        ],
        'field_name' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => true,
        ],
        'updated_at' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
        ],
    ]);
    $ilDB->addPrimaryKey('powbi_prov_options', array('id'));
    $ilDB->createSequence('powbi_prov_options');
}
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'id',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'id',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'timestamp',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'timestamp',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'trigger',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'trigger',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'progress',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'progress',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'assignment',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'assignment',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'obj_type',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'objectType',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'obj_title',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'objectTitle',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'refid',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'objectRefId',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'link',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'objectUrl',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'parent_title',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'objectParentCrsTitle',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'parent_refid',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'objectParentCrsRefId',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'user_mail',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'userEmailAddress',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'user_id',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'userId',],
    'updated_at' => ['integer', time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => ['integer', $ilDB->nextId('powbi_prov_options'),],
    'keyword' => ['text', 'user_login',],
    'active' => ['integer', 1,],
    'field_name' => ['text', 'userLogin',],
    'updated_at' => ['integer', time(),],
]);
?>
