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
    'from_table' => 'gtsAPIField',
    'link_group_table' => 'gtsAPIFieldGroupLink',
    'all' => 0,
  ),
  'fieldMeta' => 
  array (
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '191',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'from_table' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '191',
      'phptype' => 'string',
      'null' => false,
      'default' => 'gtsAPIField',
    ),
    'link_group_table' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '191',
      'phptype' => 'string',
      'null' => false,
      'default' => 'gtsAPIFieldGroupLink',
    ),
    'all' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
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
    'gtsAPIFieldGroupTableLink' => 
    array (
      'class' => 'gtsAPIFieldGroupTableLink',
      'local' => 'id',
      'foreign' => 'group_field_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
