<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'blog' => array(
		'alias' => 'greor-blog',
		'name' => 'Blog module',
		'type' => Helper_Module::MODULE_SINGLE,
		'controller' => 'blog_entity'
	),
);