<?php defined('SYSPATH') or die('No direct script access.');

class Model_Blog extends ORM_Base {

	protected $_table_name = 'blog';
	protected $_sorting = array('position' => 'ASC');
	protected $_deleted_column = 'delete_bit';
	protected $_has_many = array(
		'posts' => array(
			'model' => 'blog_Post',
			'foreign_key' => 'blog_id',
		),
	);

	public function labels()
	{
		return array(
			'uri' => 'URI',
			'group' => 'Group',
			'title' => 'Title',
			'status' => 'Status',
			'position' => 'Position',
			'title_tag' => 'Title tag',
			'keywords_tag' => 'Keywords tag',
			'description_tag' => 'Desription tag',
			'for_all' => 'For all sites',
		);
	}

	public function rules()
	{
		return array(
			'id' => array(
				array('digit'),
			),
			'site_id' => array(
				array('not_empty'),
				array('digit'),
			),
			'uri' => array(
				array('min_length', array(':value', 2)),
				array('max_length', array(':value', 255)),
				array('alpha_dash'),
				array(array($this, 'check_uri')),
			),
			'group' => array(
				array('not_empty'),
			),
			'title' => array(
				array('not_empty'),
				array('min_length', array(':value', 2)),
				array('max_length', array(':value', 255)),
			),
			'status' => array(
				array('not_empty'),
				array('digit'),
				array('range', array(':value', 0, 2)),
			),
			'position' => array(
				array('digit'),
			),
			'title_tag' => array(
				array('max_length', array(':value', 255)),
			),
			'keywords_tag' => array(
				array('max_length', array(':value', 255)),
			),
			'description_tag' => array(
				array('max_length', array(':value', 255)),
			),
		);
	}

	public function filters()
	{
		return array(
			TRUE => array(
				array('UTF8::trim'),
			),
			'title' => array(
				array('strip_tags'),
			),
			'title_tag' => array(
				array('strip_tags'),
			),
			'keywords_tag' => array(
				array('strip_tags'),
			),
			'description_tag' => array(
				array('strip_tags'),
			),
			'for_all' => array(
				array(array($this, 'checkbox'))
			),
		);
	}
	
	public function check_uri($value)
	{
		if ( ! $this->status) {
			return TRUE;
		}
	
		$orm = clone $this;
		$orm->clear();
	
		if ($this->loaded()) {
			$orm
				->where('id', '!=', $this->id);
		}
	
		if ($this->for_all) {
			$orm
				->site_id(NULL);
		}
	
		$orm
			->where('group', '=', $this->group)
			->where('uri', '=', $this->uri)
			->where('status', '>', 0)
			->find();
	
		return ! $orm->loaded();
	}

}
