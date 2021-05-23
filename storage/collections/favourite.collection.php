<?php
 return array (
  'name' => 'favourite',
  'label' => 'Favourite',
  '_id' => 'favourite',
  'fields' => 
  array (
    0 => 
    array (
      'name' => 'type',
      'label' => '',
      'type' => 'select',
      'default' => '',
      'info' => '',
      'group' => '',
      'localize' => false,
      'options' => 
      array (
        'options' => 'product, blog, event',
        'default' => 'product',
      ),
      'width' => '1-1',
      'lst' => true,
      'acl' => 
      array (
      ),
      'required' => true,
    ),
    1 => 
    array (
      'name' => 'item_id',
      'label' => '',
      'type' => 'text',
      'default' => '',
      'info' => '',
      'group' => '',
      'localize' => false,
      'options' => 
      array (
      ),
      'width' => '1-1',
      'lst' => true,
      'acl' => 
      array (
      ),
      'required' => true,
    ),
  ),
  'sortable' => false,
  'in_menu' => false,
  '_created' => 1621754831,
  '_modified' => 1621763148,
  'color' => '',
  'acl' => 
  array (
    'user' => 
    array (
      'collection_edit' => false,
      'entries_view' => true,
      'entries_create' => true,
      'entries_edit' => true,
      'entries_delete' => true,
    ),
    'publisher' => 
    array (
      'collection_edit' => false,
      'entries_view' => true,
      'entries_edit' => true,
      'entries_create' => true,
      'entries_delete' => true,
    ),
  ),
  'sort' => 
  array (
    'column' => '_created',
    'dir' => -1,
  ),
  'rules' => 
  array (
    'create' => 
    array (
      'enabled' => true,
    ),
    'read' => 
    array (
      'enabled' => true,
    ),
    'update' => 
    array (
      'enabled' => true,
    ),
    'delete' => 
    array (
      'enabled' => false,
    ),
  ),
  'icon' => '',
);