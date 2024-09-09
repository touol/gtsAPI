<?php
$xpdo_meta_map['gtsAPIFieldGroup']= array (
  'package' => 'gtsapi',
  'version' => '1.1',
  'table' => 'gtsapi_field_groups',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'name' => '',
    'title' => '',
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'title' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
  'composites' => 
  array (
    'gtsAPIFieldGroupLink' => 
    array (
      'class' => 'gtsAPIFieldGroupLink',
      'local' => 'id',
      'foreign' => 'group_field_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
