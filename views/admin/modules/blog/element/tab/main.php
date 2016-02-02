<?php defined('SYSPATH') or die('No direct access allowed.');

	$orm = $helper_orm->orm();
	$labels = $orm->labels();
	$required = $orm->required_fields();

/**** for_all ****/

	if (IS_MASTER_SITE) {
		echo View_Admin::factory('form/checkbox', array(
			'field' => 'for_all',
			'errors' => $errors,
			'labels' => $labels,
			'required' => $required,
			'orm_helper' => $helper_orm,
		));
	}	
	
/**** active ****/
	
	echo View_Admin::factory('form/checkbox', array(
		'field' => 'active',
		'errors' => $errors,
		'labels' => $labels,
		'required' => $required,
		'orm_helper' => $helper_orm,
	));
	
	
/**** blog_id ****/
	
	echo View_Admin::factory('form/control', array(
		'field' => 'blog_id',
		'errors' => $errors,
		'labels' => $labels,
		'required' => $required,
		'controls' => Form::select('blog_id', array(0 => __(' - No blog - ')) + $blogs_list, (int) $orm->blog_id, array(
			'id' => 'blog_id_field',
			'class' => 'input-xxlarge',
		)),
	));
	
	
/**** public_date ****/
	
	echo View_Admin::factory('form/date', array(
		'field' => 'public_date',
		'errors' => $errors,
		'labels' => $labels,
		'required' => $required,
		'orm' => $orm,
	));
	
	
/**** title ****/
	
	echo View_Admin::factory('form/control', array(
		'field' => 'title',
		'errors' => $errors,
		'labels' => $labels,
		'required' => $required,
		'controls' => Form::input('title', $orm->title, array(
			'id' => 'title_field',
			'class' => 'input-xxlarge',
		)).$preview_link,
	));
	
	
/**** image ****/
	
	echo View_Admin::factory('form/image', array(
		'field' => 'image',
		'value' => $orm->image,
		'orm_helper' => $helper_orm,
		'errors' => $errors,
		'labels' => $labels,
		'required' => $required,
// 		'help_text' => '360x240px',
	));
	
/**** uri ****/
	
	echo View_Admin::factory('form/control', array(
		'field' => 'uri',
		'errors' => $errors,
		'labels' => $labels,
		'required' => $required,
		'controls' => Form::input('uri', $orm->uri, array(
			'id' => 'uri_field',
			'class' => 'input-xxlarge',
		)),
	));
	
/**** additional params block ****/
	
	echo View_Admin::factory('form/seo', array(
		'item' => $orm,
		'errors' => $errors,
		'labels' => $labels,
		'required' => $required,
	));
