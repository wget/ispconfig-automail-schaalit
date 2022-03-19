<?php
$module['name'] = 'automail';
$module['title'] = 'Automail';
$module['template'] = 'module.tpl.htm';
$module['startpage'] = 'automail/automail_list.php';
$module['tab_width'] = '';
$module['order'] = '50';

$items = array();
$items[] = array( 
	'title'   => 'Info',
	'target'  => 'content',
	'link'    => 'automail/about.php'
);

$module['nav'][] = array( 
	'title' => 'Info',
	'open'  => 1,
	'items' => $items
);

?>
