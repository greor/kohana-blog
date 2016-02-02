<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules_Blog extends Controller_Admin_Front {

	protected $module_config = 'blog';
	protected $menu_active_item = 'modules';
	protected $title = 'Blogs';
	protected $sub_title = 'Blogs';
	
	protected $blog_id;
	protected $group_options;
	protected $group_key;
	protected $controller_name = array(
		'entity' => 'blog_entity',
		'element' => 'blog_element',
	);
	
	protected $injectors = array(
		'photo' => array('Injector_Photo', array(
			'group' => 'blog',
		))
	);
	
	public function before()
	{
		parent::before();
		
		$this->blog_id = (int) Request::current()->query('blog');
		$this->template
			->bind_global('BLOG_ID', $this->blog_id);
	
		$this->group_options = Kohana::$config->load('_blog.groups');
		$this->_sort_group_options($this->group_options);
		$this->template
			->bind_global('GROUP_OPTIONS', $this->group_options);
			
		$this->group_key = Request::current()->query('group');
		if (empty($this->group_key)) {
			$this->group_key = 'common';
		}
		$this->template
			->bind_global('GROUP_KEY', $this->group_key);
			
		$query_controller = $this->request->query('controller');
		if ( ! empty($query_controller) AND is_array($query_controller)) {
			$this->controller_name = $this->request->query('controller');
		}
		$this->template
			->bind_global('CONTROLLER_NAME', $this->controller_name);
		
		$this->title = __($this->title); 
		$this->sub_title = __($this->sub_title); 
	}
	
	private function _sort_group_options( & $options)
	{
		$array = array();
		if (isset($options['common'])) {
			$array['common'] = $options['common'];
			unset($options['common']);
		}
		asort($options);
		$options = array_merge($array, $options);
	}
	
	protected function layout_aside()
	{
		$menu_items = array_merge_recursive(
			Kohana::$config->load('admin/aside/blog')->as_array(),
			$this->menu_left_ext
		);
		
		return parent::layout_aside()
			->set('menu_items', $menu_items)
			->set('replace', array(
				'{GROUP_KEY}' => $this->group_key,
				'{BLOG_ID}' =>	$this->blog_id,
			));
	}

	protected function left_menu_blog_add($orm)
	{
		if ($this->acl->is_allowed($this->user, $orm, 'add') ) {
			$this->menu_left_add(array(
				'blog' => array(
					'sub' => array(
						'add' => array(
							'title' => __('Add blog'),
							'link' => Route::url('modules', array(
								'controller' => $this->controller_name['entity'],
								'action' => 'edit',
								'query' => 'group={GROUP_KEY}'
							)),
						),
					),
				),
			));
		}
	}
	
	protected function left_menu_blog_fix($orm)
	{
		$can_fix_all = $this->acl->is_allowed($this->user, $orm, 'fix_all');
		$can_fix_master = $this->acl->is_allowed($this->user, $orm, 'fix_master');
		$can_fix_slave = $this->acl->is_allowed($this->user, $orm, 'fix_slave');
		
		if ($can_fix_all OR $can_fix_master OR $can_fix_slave) {
			$this->menu_left_add(array(
				'blog' => array(
					'sub' => array(
						'fix' => array(
							'title' => __('Fix positions'),
							'link'  => Route::url('modules', array(
								'controller' => $this->controller_name['entity'],
								'action' => 'position',
								'query' => 'group={GROUP_KEY}&mode=fix'
							)),
						),
					),
				),
			));
		}
	}
	
	protected function left_menu_element_list()
	{
		if (empty($this->back_url)) {
			$link = Route::url('modules', array(
				'controller' => $this->controller_name['element'],
				'query' => 'group={GROUP_KEY}&blog={BLOG_ID}'
			));
		} else {
			$link = $this->back_url;
		}
		
		$this->menu_left_add(array(
			'blog_elements' => array(
				'title' => __('Posts list'),
				'link' => $link,
				'sub' => array(),
			),
		));
	}
	
	protected function left_menu_element_add()
	{
		$link = Route::url('modules', array(
			'controller' => $this->controller_name['element'],
			'action' => 'edit',
			'query' => 'group={GROUP_KEY}&blog={BLOG_ID}'
		));
		
		if ( ! empty($this->back_url)) {
			$link .= '&back_url='.urlencode($this->back_url);
		}
		
		$this->menu_left_add(array(
			'blog_elements' => array(
				'sub' => array(
					'add' => array(
						'title' => __('Add post'),
						'link' => $link,
					),
				),
			),
		));
	}
	
	protected function _get_breadcrumbs()
	{
		$query_array = array(
			'group' => $this->group_key,
		);
	
		return array(
			array(
				'title' => __('Blogs'),
				'link' => Route::url('modules', array(
					'controller' => $this->controller_name['entity'],
					'query' => Helper_Page::make_query_string($query_array),
				)),
			)
		);
	}
}

