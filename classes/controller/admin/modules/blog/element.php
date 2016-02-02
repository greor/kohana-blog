<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Admin_Modules_Blog_Element extends Controller_Admin_Modules_Blog {

	private $filter_type_options;

	public function before()
	{
		parent::before();

		if (empty($this->blog_id)) {
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['entity'],
			));
			$this->request->current()
				->redirect($this->back_url);
		}
		
		$this->filter_type_options = array(
			'all' => __('all'),
			'own' => __('own'),
		);
	}

	public function action_index()
	{
		$blog_orm = ORM::factory('blog')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->blog_id)
			->find();
		if ( ! $blog_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$orm = ORM::factory('blog_Post')
			->where('blog_id', '=', $this->blog_id);
		
		$this->_apply_filter($orm);
			
		$paginator_orm = clone $orm;
		$paginator = new Paginator('admin/layout/paginator');
		$paginator
			->per_page(20)
			->count($paginator_orm->count_all());
		unset($paginator_orm);
		
		$list = $orm
			->paginator($paginator)
			->find_all();
		
		$this->template
			->set_filename('modules/blog/element/list')
			->set('list', $list)
			->set('hided_list', $this->get_hided_list($orm->object_name()))
			->set('filter_type_options', $this->filter_type_options)
			->set('paginator', $paginator);
			
		$this->left_menu_blog_add($blog_orm);
		$this->left_menu_element_list();
		$this->left_menu_element_add();
		
		$this->title = __('List');;
	}

	private function _apply_filter($orm)
	{
		$filter_query = $this->request->query('filter');

		if ( ! empty($filter_query)) {
			$title = Arr::get($filter_query, 'title');
			if ( ! empty($title)) {
				$orm->where('title', 'like', '%'.$title.'%');
			}

			$type = Arr::get($filter_query, 'type');
			if ( ! empty($type) AND $type == 'own') {
				$orm->where('site_id', '=', SITE_ID);
			}
		}
	}

	public function action_edit()
	{
		$blog_orm = ORM::factory('blog')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->blog_id)
			->find();
		if ( ! $blog_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$request = $this->request->current();
		$id = (int) $this->request->current()->param('id');
		$helper_orm = ORM_Helper::factory('blog_Post');
		$orm = $helper_orm->orm();
		if ( (bool) $id) {
			$orm
				->where('id', '=', $id)
				->find();
		
			if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'edit')) {
				throw new HTTP_Exception_404();
			}
			$this->title = __('Edit post');
		} else {
			$this->title = __('Add post');
		}
		
		if (empty($this->back_url)) {
			$query_array = array(
				'group' => $this->group_key,
				'blog' => $this->blog_id,
			);
			$query_array = Paginator::query($request, $query_array);
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['element'],
				'query' => Helper_Page::make_query_string($query_array),
			));
		}
		
		if ($this->is_cancel) {
			$request
				->redirect($this->back_url);
		}
		
		$errors = array();
		$submit = $request->post('submit');
		if ($submit) {
			try {
				if ( (bool) $id) {
					$orm->updater_id = $this->user->id;
					$orm->updated = date('Y-m-d H:i:s');
					$reload = FALSE;
				} else {
					$orm->site_id = SITE_ID;
					$orm->creator_id = $this->user->id;
					$orm->blog_id = $this->blog_id;
					$reload = TRUE;
				}
				
				$values = $this->meta_seo_reset(
					$this->request->current()->post(),
					'meta_tags'
				);
				
				$values['public_date'] = $this->value_multiple_date($values, 'public_date');
				if (empty($values['uri']) OR row_exist($orm, 'uri', $values['uri'])) {
					$values['uri'] = transliterate_unique($values['title'], $orm, 'uri');
				}
				
				
				
				$helper_orm->save($values + $_FILES);
				
				if ($reload) {
					if ($submit != 'save_and_exit') {
						$this->back_url = Route::url('modules', array(
							'controller' => $request->controller(),
							'action' => $request->action(),
							'id' => $orm->id,
							'query' => Helper_Page::make_query_string($request->query()),
						));
					}
						
					$request
						->redirect($this->back_url);
				}
			} catch (ORM_Validation_Exception $e) {
				$errors = $this->errors_extract($e);
			}
		}

		// If add action then $submit = NULL
		if ( ! empty($errors) OR $submit != 'save_and_exit') {
			
			$this->left_menu_blog_add($blog_orm);
			$this->left_menu_element_list();
			$this->left_menu_element_add();
			
			$blogs_list = ORM::factory('blog')
				->order_by('position', 'asc')
				->find_all()
				->as_array('id', 'title');

			if ( ! $orm->loaded()) {
				$orm->blog_id = $this->blog_id;
			}

			$properties = $helper_orm->property_list();
			
			$this->template
				->set_filename('modules/blog/element/edit')
				->set('helper_orm', $helper_orm)
				->set('blogs_list', $blogs_list)
				->set('properties', $properties);
			
			if (Helper_Module::check_module('kubikrubik-photo')) {
				$injector = $this->injectors['photo'];
				if ($orm->loaded()) {
					try {
						$this->hook_list_content[] = $injector->get_hook($orm);
						
						$this->menu_left_add( $injector->menu_list($orm->photo_album) );
						$this->menu_left_add( $injector->menu_add($orm->photo_album) );
						$this->menu_left_add( $injector->menu_fix($orm->photo_album) );
						
					} catch (ORM_Validation_Exception $e) {
						$errors = array_merge($errors, $this->errors_extract($e));
					}
				}
			}
			
			$this->template
				->set('errors', $errors);
		}
		else {
			$request
				->redirect($this->back_url);
		}
	}
	
	public function action_view()
	{
		$blog_orm = ORM::factory('blog')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->blog_id)
			->find();
		if ( ! $blog_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$request = $this->request->current();
		$id = (int) $request->param('id');
		$helper_orm = ORM_Helper::factory('blog_Post');
		$orm = $helper_orm->orm();
		$orm
			->where('id', '=', $id)
			->find();
			
		if ( ! $orm->loaded()) {
			throw new HTTP_Exception_404();
		}
				
		if (empty($this->back_url)) {
			$query_array = array(
				'group' => $this->group_key,
				'blog' => $this->blog_id,
			);
			$query_array = Paginator::query($request, $query_array);
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['element'],
				'query' => Helper_Page::make_query_string($query_array),
			));
		}
		
		$this->template
			->set_filename('modules/blog/element/view')
			->set('helper_orm', $helper_orm)
			->set('blog', $blog_orm->as_array());
		
		$this->title = __('Viewing');
		$this->left_menu_blog_add($blog_orm);
		$this->left_menu_element_list();
		$this->left_menu_element_add();
	}

	public function action_delete()
	{
		$blog_orm = ORM::factory('blog')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->blog_id)
			->find();
		if ( ! $blog_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$request = $this->request->current();
		$id = (int) $request->param('id');
		
		$helper_orm = ORM_Helper::factory('blog_Post');
		$orm = $helper_orm->orm();
		$orm
			->where('blog_id', '=', $this->blog_id)
			->and_where('id', '=', $id)
			->find();
		
		if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'edit')) {
			throw new HTTP_Exception_404();
		}
		
		if ($this->element_delete($helper_orm)) {
			if (empty($this->back_url)) {
				$query_array = array(
					'group' => $this->group_key,
					'blog' => $this->blog_id,
				);
				$query_array = Paginator::query($request, $query_array);
				$this->back_url = Route::url('modules', array(
					'controller' => $this->controller_name['element'],
					'query' => Helper_Page::make_query_string($query_array),
				));
			}
		
			$request
				->redirect($this->back_url);
		}
	}

	public function action_visibility()
	{
		$blog_orm = ORM::factory('blog')
			->where('group', '=', $this->group_key)
			->and_where('id', '=', $this->blog_id)
			->find();
		if ( ! $blog_orm->loaded()) {
			throw new HTTP_Exception_404();
		}
		
		$request = $this->request->current();
		$id = (int) $request->param('id');
		$mode = $request->query('mode');
		
		$orm = ORM::factory('blog_Post')
			->where('blog_id', '=', $this->blog_id)
			->and_where('id', '=', $id)
			->find();
		
		if ( ! $orm->loaded() OR ! $this->acl->is_allowed($this->user, $orm, 'hide')) {
			throw new HTTP_Exception_404();
		}
		
		if ($mode == 'hide') {
			$this->element_hide($orm->object_name(), $orm->id);
		} elseif ($mode == 'show') {
			$this->element_show($orm->object_name(), $orm->id);
		}
		
		if (empty($this->back_url)) {
			$query_array = array(
				'group' => $this->group_key,
				'blog' => $this->blog_id,
			);
			$query_array = Paginator::query($request, $query_array);
			
			$this->back_url = Route::url('modules', array(
				'controller' => $this->controller_name['element'],
				'query' => Helper_Page::make_query_string($query_array),
			));
		}
		
		$request
			->redirect($this->back_url);
	}
	
	protected function _get_breadcrumbs()
	{
		$breadcrumbs = parent::_get_breadcrumbs();
		
		$query_array = array(
			'group' => $this->group_key,
			'blog' => $this->blog_id
		);
		
		$category_orm = ORM::factory('blog')
			->and_where('id', '=', $this->blog_id)
			->find();
		if ($category_orm->loaded()) {
			$breadcrumbs[] = array(
				'title' => $category_orm->title,
				'link' => Route::url('modules', array(
					'controller' => $this->controller_name['element'],
					'query' => Helper_Page::make_query_string($query_array),
				)),
			);
		}
		
		$action = $this->request->current()
			->action();
		if (in_array($action, array('edit', 'view'))) {
			$id = (int) $this->request->current()->param('id');
			$element_orm = ORM::factory('blog_Post')
				->where('id', '=', $id)
				->find();
			if ($element_orm->loaded()) {
				switch ($action) {
					case 'edit':
						$_str = ' ['.__('edition').']';
						break;
					case 'view':
						$_str = ' ['.__('viewing').']';
						break;
					default:
						$_str = '';
				}
				
				$breadcrumbs[] = array(
					'title' => $element_orm->title.$_str,
				);
			} else {
				$breadcrumbs[] = array(
					'title' => ' ['.__('new post').']',
				);
			}
		}
		
		return $breadcrumbs;
	}
} 
