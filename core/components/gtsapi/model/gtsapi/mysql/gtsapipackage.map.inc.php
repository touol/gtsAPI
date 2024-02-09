<?php
$xpdo_meta_map['gtsAPIPackage']= array (
  'package' => 'gtsapi',
  'version' => '1.1',
  'table' => 'gtsapi_packages',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name' => '',
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '161',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
  'indexes' => 
  array (
    'name' => 
    array (
      'alias' => 'name',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'name' => 
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
    'gtsAPITable' => 
    array (
      'class' => 'gtsAPITable',
      'local' => 'id',
      'foreign' => 'package_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
