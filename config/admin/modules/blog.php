<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
	'a2' => array(
		'resources' => array(
			'blog_entity_controller' => 'module_controller',
			'blog_element_controller' => 'module_controller',
			'blog' => 'module',
			'blog_post' => 'module',
		),
		'rules' => array(
			'allow' => array(
				'controller_access_1' => array(
					'role' => 'base',
					'resource' => 'blog_entity_controller',
					'privilege' => 'access',
				),
				'controller_access_2' => array(
					'role' => 'base',
					'resource' => 'blog_element_controller',
					'privilege' => 'access',
				),

				
				'blog_add' => array(
					'role' => 'full',
					'resource' => 'blog',
					'privilege' => 'add',
				),
				'blog_edit_1' => array(
					'role' => 'super',
					'resource' => 'blog',
					'privilege' => 'edit',
				),
				'blog_edit_2' => array(
					'role' => 'full',
					'resource' => 'blog',
					'privilege' => 'edit',
					'assertion' => array('Acl_Assert_Argument', array(
						'site_id' => 'site_id'
					)),
				),
				'blog_hide' => array(
					'role' => 'full',
					'resource' => 'blog',
					'privilege' => 'hide',
					'assertion'	=> array('Acl_Assert_Site', array(
						'site_id' => SITE_ID,
						'site_id_master' => SITE_ID_MASTER
					)),
				),
				'blog_fix_all' => array(
					'role' => 'super',
					'resource' => 'blog',
					'privilege' => 'fix_all',
				),
				'blog_fix_master' => array(
					'role' => 'main',
					'resource' => 'blog',
					'privilege' => 'fix_master',
				),
				'blog_fix_slave' => array(
					'role' => 'full',
					'resource' => 'blog',
					'privilege' => 'fix_slave',
				),

				
				'blog_post_edit_1' => array(
					'role' => 'super',
					'resource' => 'blog_post',
					'privilege' => 'edit',
				),
				'blog_post_edit_2' => array(
					'role' => 'base',
					'resource' => 'blog_post',
					'privilege' => 'edit',
					'assertion' => array('Acl_Assert_Argument', array(
						'site_id' => 'site_id'
					)),
				),
				'blog_post_hide' => array(
					'role' => 'full',
					'resource' => 'blog_post',
					'privilege' => 'hide',
					'assertion' => array('Acl_Assert_Site', array(
						'site_id' => SITE_ID,
						'site_id_master' => SITE_ID_MASTER
					)),
				),
			),
			'deny' => array()
		)
	),
);