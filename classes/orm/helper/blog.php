<?php defined('SYSPATH') or die('No direct script access.');

class ORM_Helper_Blog extends ORM_Helper {

	protected $_safe_delete_field = 'delete_bit';
	protected $_position_type = self::POSITION_COMPLEX;
	protected $_position_fields = array(
		'position' => array(
			'group_by' => array(),
		),
	);
	protected $_on_delete_cascade = array('posts');

}
