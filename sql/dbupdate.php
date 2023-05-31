<#1>
<?php
/** @var ilDBInterface $ilDB */
if (!$ilDB->tableExists('powbi_prov_index')) {
    $ilDB->createTable('powbi_prov_index', [
        'id' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        ],
        'processed' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        ],
        'trigger' => [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => true,
            'default' => '',
        ],
        'timestamp' => [
            'type' => ilDBConstants::T_INTEGER,
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
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true,
            'default' => 0,
        ],
        'keyword' => [
            'type' => ilDBConstants::T_TEXT,
            'length' => 255,
            'notnull' => true,
        ],
        'active' => [
            'type' => ilDBConstants::T_INTEGER,
            'notnull' => true,
            'default' => 0,
            'length' => 1
        ],
        'field_name' => [
            'type' => ilDBConstants::T_TEXT,
            'length' => 255,
            'notnull' => true,
        ],
        'updated_at' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => false,
        ],
    ]);
    $ilDB->addPrimaryKey('powbi_prov_options', array('id'));
    $ilDB->createSequence('powbi_prov_options');
}
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'id',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'id',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'timestamp',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'timestamp',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'trigger',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'trigger',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'progress',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'progress',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'assignment',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'assignment',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'obj_type',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'objectType',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'obj_title',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'objectTitle',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'refid',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'objectRefId',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'link',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'objectUrl',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'parent_title',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'objectParentCrsTitle',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'parent_refid',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'objectParentCrsRefId',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'user_mail',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'userEmailAddress',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'user_id',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'userId',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
$ilDB->insert('powbi_prov_options', [
    'id' => [ilDBConstants::T_INTEGER, $ilDB->nextId('powbi_prov_options'),],
    'keyword' => [ilDBConstants::T_TEXT, 'user_login',],
    'active' => [ilDBConstants::T_INTEGER, 1,],
    'field_name' => [ilDBConstants::T_TEXT, 'userLogin',],
    'updated_at' => [ilDBConstants::T_INTEGER, time(),],
]);
?>
