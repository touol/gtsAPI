<?php
$xpdo_meta_map['gtsAPIToken']= array (
  'package' => 'gtsapi',
  'version' => '1.1',
  'table' => 'gtsapi_tokens',
  'extends' => 'xPDOObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'token' => '',
    'user_id' => 0,
    'valid_till' => NULL,
    'created_at' => NULL,
    'updated_at' => NULL,
    'ip' => '',
    'active' => 0,
  ),
  'fieldMeta' => 
  array (
    'token' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '161',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'user_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'valid_till' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'created_at' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'updated_at' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'ip' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '161',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
    ),
  ),
  'indexes' => 
  array (
    'token' => 
    array (
      'alias' => 'token',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'token' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'user_id' => 
    array (
      'alias' => 'user_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'user_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'active' => 
    array (
      'alias' => 'active',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'active' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
);
