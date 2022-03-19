<?php

$liste['table'] = 'mail_config';
$liste['name'] = 'automail';
$liste['table_idx']   = 'config_id';
$liste['search_prefix']  = 'search_';
$liste['records_per_page']  = '15';
$liste['file']    = 'automail_list.php';
$liste['edit_file']   = 'automail_edit.php';
$liste['delete_file']  = 'automail_del.php';
$liste['paging_tpl']  = 'templates/paging.tpl.htm';
$liste['auth']    = 'yes';


$liste['item'][] = array( 'field'  => 'active',
	'datatype' => 'VARCHAR',
	'formtype' => 'SELECT',
	'op'  => '=',
	'prefix' => '',
	'suffix' => '',
	'width'  => '',
	'value'  => array(
		'y' => $app->lng('yes_txt'),
		'n' => $app->lng('no_txt')
	)
);

$liste['item'][] = array( 'field'  => 'server_id',
	'datatype' => 'INTEGER',
	'formtype' => 'SELECT',
	'op'  => 'like',
	'prefix' => '',
	'suffix' => '',
	'datasource' => array (
		'type' => 'SQL',
		'querystring' => 'SELECT a.server_id, a.server_name FROM server a WHERE ({AUTHSQL-B}) ORDER BY a.server_name',
		'keyfield'=> 'server_id',
		'valuefield'=> 'server_name'
	),
	'width'  => '',
	'value'  => ''
);
?>
