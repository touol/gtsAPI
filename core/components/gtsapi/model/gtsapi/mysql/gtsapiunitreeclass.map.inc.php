<?php
$xpdo_meta_map['gtsAPIUniTreeClass']= array (
  'package' => 'gtsapi',
  'version' => '1.1',
  'table' => 'gtsapi_unitree_classes',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'table_id' => 0,
    'table' => '',
    'class' => '',
    'extended_modresource' => 0,
    'title_field' => '',
    'svg' => '',
  ),
  'fieldMeta' => 
  array (
    'table_id' => 
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
    'extended_modresource' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
    ),
    'title_field' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '161',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'svg' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
  'indexes' => 
  array (
    'table_id' => 
    array (
      'alias' => 'table_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'table_id' => 
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
      'unique' => false,
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
    'extended_modresource' => 
    array (
      'alias' => 'extended_modresource',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'extended_modresource' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'aggregates' => 
  array (
    'gtsAPITable' => 
    array (
      'class' => 'gtsAPITable',
      'local' => 'table_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
