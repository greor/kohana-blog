<?php defined('SYSPATH') or die('No direct script access.');

/*
 * FIXME создавать блог по требованию
 */

class Injector_Blog extends Injector_Base {
	
	private $controller_name = 'blog_element';
	private $tab_code = 'blog';
	private $blog_group = 'common';
	
	protected function init() {
		$module_config = Helper_Module::load_config('blog');
		$helper_acl = new Helper_ACL($this->acl);
		$helper_acl->inject(Arr::get($module_config, 'a2'));
		
		$this->blog_group = Arr::get($this->params, 'group');
	}
	
	public function get_hook($orm)
	{
		if ($orm->blog_id == 0) {
			$blog_orm = ORM::factory('blog');
			$blog_orm
				->values(array(
					'group' => $this->blog_group,
					'site_id' => $orm->site_id,
					'creator_id' => $this->user->id,
					'status' => Kohana::$config->load('_blog.status_codes.hidden'),
					'title' => $orm->title,
					'uri' => transliterate_unique($orm->title, $blog_orm, 'uri'),
					'for_all' => $orm->for_all,
				))
				->save();

			$orm->blog_id = $blog_orm->id;
			$orm->save();
			unset($blog_orm);
		}

		return array(
			array($this, 'hook_callback'),
			array($orm)
		);
	}
	
	public function hook_callback($content, $orm)
	{
		$request = $this->request;
		$back_url = $request->url();
		$query_array = $request->query();
		if ( ! empty($query_array)) {
			$back_url .= '?'.http_build_query($query_array);
		}
		$back_url .= '#tab-'.$this->tab_code;
		unset($query_array);
	
		$query_array = array(
			'group' => $this->blog_group,
			'blog' => $orm->blog_id,
			'back_url' => $back_url,
			'content_only' => TRUE
		);
		$query_array = Paginator::query($request, $query_array);
		$link = Route::url('modules', array(
			'controller' => $this->controller_name,
			'query' => Helper_Page::make_query_string($query_array),
		));
	
		$html_blog = Request::factory($link)
			->execute()
			->body();
	
		$tab_nav_html = View_Admin::factory('layout/tab/nav', array(
			'code' => $this->tab_code,
			'title' => '<b>'.__('Blog').'</b>',
		));
		$tab_pane_html = View_Admin::factory('layout/tab/pane', array(
			'code' => $this->tab_code,
			'content' => $html_blog
		));
	
		return str_replace(array(
			'<!-- #tab-nav-insert# -->', '<!-- #tab-pane-insert# -->'
		), array(
			$tab_nav_html.'<!-- #tab-nav-insert# -->', $tab_pane_html.'<!-- #tab-pane-insert# -->'
		), $content);
	}
	
	public function menu_list($orm, $tab_mode = TRUE)
	{
		if ($tab_mode) {
			$link = '#tab-'.$this->tab_code;
			$class = 'tab-control';
		} else {
			$link = Route::url('modules', array(
				'controller' => $this->controller_name,
				'query' => 'group='.$this->blog_group.'&blog='.$orm->id
			));
			$class = FALSE;
		}
	
		return array(
			'blog' => array(
				'title' => __('Posts ('.$this->blog_group.')'),
				'link' => $link,
				'class' => $class,
				'sub' => array(),
			),
		);
	}
	
	public function menu_add($orm)
	{
		if ($this->acl->is_allowed($this->user, $orm, 'edit') ) {
			$back_url = $_SERVER['REQUEST_URI'].urlencode('#tab-').$this->tab_code;
	
			return array(
				'blog' => array(
					'sub' => array(
						'add' => array(
							'title' => __('Add post'),
							'link' => Route::url('modules', array(
								'controller' => $this->controller_name,
								'action' => 'edit',
								'query' => 'group='.$this->blog_group.'&blog='.$orm->id.'&back_url='.$back_url
							)),
						),
					),
				),
			);
		}
	}
	
}