<?php defined('SYSPATH') or die('No direct script access.');

return array
(
	'blog' => array(
		'uri_callback' => '(/<blog_uri>(/<post_uri>-<post_id>.html))(?<query>)',
		'defaults' => array(
			'directory' => 'modules',
			'controller' => 'blog',
			'action' => 'index',
		)
	),
);

