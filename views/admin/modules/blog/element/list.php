<?php defined('SYSPATH') or die('No direct access allowed.'); 

	$is_initial_request = Request::current()->is_initial();

	if ($is_initial_request) {
		echo View_Admin::factory('layout/breadcrumbs', array(
			'breadcrumbs' => $breadcrumbs
		));
	
		echo View_Admin::factory('modules/blog/element/list/filter', array(
			'filter_type_options' => $filter_type_options
		));
	}
	
	if ($list->count() <= 0) {
		return;
	}

	$query_array = array(
		'group' => $GROUP_KEY,
		'blog' => $BLOG_ID,
	);
	if ( ! empty($BACK_URL)) {
		$query_array['back_url'] = $BACK_URL;
	}

	$query_array = Paginator::query(Request::current(), $query_array);
	$delete_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'delete',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
	$edit_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'edit',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
	$view_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'view',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));

	$query_array['mode'] = 'show';
	$visibility_on_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'visibility',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
	$query_array['mode'] = 'hide';
	$visibility_off_tpl = Route::url('modules', array(
		'controller' => $CONTROLLER_NAME['element'],
		'action' => 'visibility',
		'id' => '{id}',
		'query' => Helper_Page::make_query_string($query_array),
	));
?>

	<table class="table table-bordered table-striped">
		<colgroup>
			<col class="span1">
			<col class="span5">
			<col class="span1">
			<col class="span2">
		</colgroup>
		<thead>
			<tr>
				<th><?php echo __('ID'); ?></th>
				<th><?php echo __('Title'); ?></th>
				<th><?php echo __('Date'); ?></th>
				<th><?php echo __('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php 			
		foreach ($list as $_orm):
?>
			<tr class="<?php echo view_list_row_class($_orm, $hided_list); ?>">
				<td><?php echo $_orm->id; ?></td>
				<td>
<?php
				
					if ( (bool) $_orm->active) {
						echo '<i class="icon-eye-open"></i>&nbsp;';
					} else {
						echo '<i class="icon-eye-open" style="background: none;"></i>&nbsp;';
					}
					echo HTML::chars($_orm->title);
?>
				</td>
				<td><?php echo $_orm->public_date; ?></td>
				<td>
<?php 
					echo '<div class="btn-group">';
					
						echo View_Admin::factory('layout/controls/hide', array(
							'orm' => $_orm,
							'hided_list' => $hided_list,
							'visibility_on_tpl' => $visibility_on_tpl,
							'visibility_off_tpl' => $visibility_off_tpl,
						));
					
						if ($ACL->is_allowed($USER, $_orm, 'edit')) {
							echo HTML::anchor(str_replace('{id}', $_orm->id, $edit_tpl), '<i class="icon-edit"></i> '.__('Edit'), array(
								'class' => 'btn',
								'title' => __('Edit'),
							));
							echo '<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><span class="caret"></span></a>';
							echo '<ul class="dropdown-menu">';
								echo '<li>', HTML::anchor(str_replace('{id}', $_orm->id, $delete_tpl), '<i class="icon-remove"></i> '.__('Delete'), array(
									'class' => 'delete_button',
									'title' => __('Delete'),
								)), '</li>';
							echo '</ul>';
						} else {
							echo HTML::anchor(str_replace('{id}', $_orm->id, $view_tpl), '<i class="icon-file"></i> '.__('View'), array(
								'class' => 'btn',
								'title' => __('View'),
							));
						}
					echo '</div>';
?>				
				</td>
			</tr>
<?php 
		endforeach;
?>
		</tbody>
	</table>

<?php
	if (empty($BACK_URL)) {
		$query_array = array(
			'group' => $GROUP_KEY,
			'blog' => $BLOG_ID,
		);
		$filter_query = Request::current()->query('filter');
		if ( ! empty($filter_query)) {
			$query_array['filter'] = $filter_query;
		}
		if ( ! empty($BACK_URL)) {
			$query_array['back_url'] = $BACK_URL;
		}
		$link = Route::url('modules', array(
			'controller' => $CONTROLLER_NAME['element'],
			'query' => Helper_Page::make_query_string($query_array),
		));
	} else {
		$link = $BACK_URL;
	}
	
	echo $paginator->render($link);
