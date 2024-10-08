<?php
$xpdo_meta_map['gtsAPIFieldGroupTableLink']= array (
  'package' => 'gtsapi',
  'version' => '1.1',
  'table' => 'gtsapi_field_group_table_links',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'group_field_id' => 0,
    'table_field_id' => 0,
  ),
  'fieldMeta' => 
  array (
    'group_field_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => true,
      'default' => 0,
    ),
    'table_field_id' => 
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
    'group_field_id' => 
    array (
      'alias' => 'group_field_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'group_field_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'table_field_id' => 
    array (
      'alias' => 'table_field_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'table_field_id' => 
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
    'gtsAPIFieldGroup' => 
    array (
      'class' => 'gtsAPIFieldGroup',
      'local' => 'group_field_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'gtsAPIFieldTable' => 
    array (
      'class' => 'gtsAPIFieldTable',
      'local' => 'table_field_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
