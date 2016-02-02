<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
	'blog' => array(
		'title' => __('Blogs'),
		'link' => Route::url('modules', array(
			'controller' => 'blog_entity',
			'query' => 'group={GROUP_KEY}',
		)),
		'sub' => array(),
	),
	'blog_elements' => array(),
	'photo' => array(),
);