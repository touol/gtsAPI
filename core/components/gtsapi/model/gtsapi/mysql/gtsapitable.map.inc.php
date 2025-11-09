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
    'install_package' => '',
    'table' => NULL,
    'class' => '',
    'tree' => 0,
    'type' => 1,
    'authenticated' => 0,
    'groups' => '',
    'permissions' => '',
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
    'install_package' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '161',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'table' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '161',
      'phptype' => 'string',
      'null' => false,
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
    'type' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 1,
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
    'permissions' => 
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
    'install_package' => 
    array (
      'alias' => 'install_package',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'install_package' => 
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
    'gtsAPIUniTreeClass' => 
    array (
      'class' => 'gtsAPIUniTreeClass',
      'local' => 'id',
      'foreign' => 'table_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'gtsAPIPackage' => 
    array (
      'class' => 'gtsAPIPackage',
      'local' => 'package_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
