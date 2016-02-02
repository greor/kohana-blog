<?php defined('SYSPATH') or die('No direct script access.');

class ORM_Helper_Blog_Post extends ORM_Helper_Property_Support {

	protected $_property_config = 'blog.properties.element';
	protected $_safe_delete_field = 'delete_bit';
	protected $_file_fields = array(
		'image' => array(
			'path' => 'upload/images/blog',
			'uri' => NULL,
			'on_delete' => ORM_File::ON_DELETE_RENAME,
			'on_update' => ORM_File::ON_UPDATE_RENAME,
		),
	);

	public function file_rules()
	{
		return array(
			'image' => array(
				array('Ku_File::valid'),
				array('Ku_File::size', array(':value', '3M')),
				array('Ku_File::type', array(':value', 'jpg, jpeg, bmp, png, gif')),
			),
		);
	}

	protected function _initialize_file_fields()
	{
		$this->_file_fields['image']['allowed_src_dirs'] = array( DOCROOT.'upload/tmp/' );
		
		parent::_initialize_file_fields();
	}
	
}
