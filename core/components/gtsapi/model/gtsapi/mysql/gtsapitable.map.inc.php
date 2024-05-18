<?php
$xpdo_meta_map['gtsAPITable']= array (
  'package' => 'gtsapi',
  'version' => '1.1',
  'table' => 'gtsapi_tables',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'package_id' => 0,
    'table' => '',
    'class' => '',
    'tree' => 0,
    'authenticated' => 0,
    'groups' => '',
    'permitions' => '',
    'properties' => '',
    'autocomplete_field' => '',
    'active' => 0,
    'version' => 0,
  ),
  'fieldMeta' => 
  array (
    'package_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'table' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '161',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'class' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '161',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'tree' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
    ),
    'authenticated' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
    ),
    'groups' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'permitions' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'properties' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'autocomplete_field' => 
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
    'version' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
  ),
  'indexes' => 
  array (
    'package_id' => 
    array (
      'alias' => 'package_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'package_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'table' => 
    array (
      'alias' => 'table',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'table' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'class' => 
    array (
      'alias' => 'class',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'class' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'autocomplete_field' => 
    array (
      'alias' => 'autocomplete_field',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'autocomplete_field' => 
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
  'composites' => 
  array (
    'gtsAPIPackage' => 
    array (
      'class' => 'gtsAPIPackage',
      'local' => 'id',
      'foreign' => 'package_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
