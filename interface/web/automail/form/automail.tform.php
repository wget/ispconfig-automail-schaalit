<?php

$form['title'] = 'Automail';
$form['description'] = '';
$form['name'] = 'automail';
$form['action'] = 'automail_edit.php';
$form['db_table'] = 'mail_config';
$form['db_table_idx'] = 'config_id';
$form['db_history'] = 'no';
$form['tab_default'] = 'server';
$form['list_default'] = 'automail_list.php';
$form['auth'] = 'no'; // yes / no

$form['auth_preset']['userid'] = 0; // 0 = id of the user, > 0 id must match with id of current user
$form['auth_preset']['groupid'] = 0; // 0 = default groupid of the user, > 0 id must match with groupid of current user
$form['auth_preset']['perm_user'] = 'riud'; //r = read, i = insert, u = update, d = delete
$form['auth_preset']['perm_group'] = 'riud'; //r = read, i = insert, u = update, d = delete
$form['auth_preset']['perm_other'] = ''; //r = read, i = insert, u = update, d = delete

$form['tabs']['server'] = array(
	'title' => 'Config',
	'width' => 70,
	'template' => 'templates/automail_edit.htm',
	'fields' => array(
		'server_id' => array (
			'datatype' => 'INTEGER',
			'formtype' => 'SELECT',
			'default' => '',
			'datasource' => array (  'type' => 'SQL',
				'querystring' => 'SELECT server_id,server_name FROM server WHERE mail_server = 1 AND mirror_server_id = 0 AND {AUTHSQL} ORDER BY server_name',
				'keyfield'=> 'server_id',
				'valuefield'=> 'server_name'
			),
			'value'  => ''
		),
		'email_provider' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'default' => '',
			'validators' => array(
				0 => array('type' => 'NOTEMPTY','errmsg' => 'email_provider_error_empty'),
				1 => array ('type' => 'REGEX', 'regex' => '/^[a-zA-Z_\-\d\.]+$/', 'errmsg'=> 'provider_error_regex'),
			),
			'value' => '',
			'width' => '15',
			'maxlength' => '255'
		),
		'display_name' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'default' => 'Powered by Automail for ISPConfig from schaal @it',
			'value' => '',
			'width' => '15',
			'maxlength' => '255'
		),
		'in_hostname' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'default' => '',
			'filters'   => array( 
					0 => array( 'event' => 'SAVE', 'type' => 'IDNTOASCII'),
					1 => array( 'event' => 'SHOW', 'type' => 'IDNTOUTF8'),
					2 => array( 'event' => 'SAVE', 'type' => 'TOLOWER')
			),
			'validators' => array(	
					0 => array('type' => 'NOTEMPTY', 'errmsg' => 'in_hostname_error_empty'),
					1 => array ('type' => 'REGEX', 'regex' => '/^[\w\.\-]{2,255}\.[a-zA-Z0-9\-]{2,30}$/', 'errmsg'=> 'in_hostname_error_regex'),
			),
			'value' => '',
			'width' => '40',
			'maxlength' => '255'
		),
		'out_hostname' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'TEXT',
			'default' => '',
			'filters'   => array( 
					0 => array( 'event' => 'SAVE', 'type' => 'IDNTOASCII'),
					1 => array( 'event' => 'SHOW', 'type' => 'IDNTOUTF8'),
					2 => array( 'event' => 'SAVE', 'type' => 'TOLOWER')
			),
			'validators' => array(	
					0 => array('type' => 'NOTEMPTY', 'errmsg' => 'out_hostname_error_empty'),
					1 => array ('type' => 'REGEX', 'regex' => '/^[\w\.\-]{2,255}\.[a-zA-Z0-9\-]{2,30}$/', 'errmsg'=> 'out_hostname_error_regex'),
			),
			'value' => '',
			'width' => '40',
			'maxlength' => '255'
		),
        'imap_port' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '143',
            'validators' => array(0 => array('type' => 'ISINT')),
            'value' => '143',
            'width' => '15'
        ),
        'pop3_port' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '110',
            'validators' => array(0 => array('type' => 'ISINT')),
            'value' => '110',
            'width' => '15'
        ),
        'smtp_port' => array(
            'datatype' => 'VARCHAR',
            'formtype' => 'TEXT',
            'default' => '587',
            'validators' => array(0 => array('type' => 'ISINT')),
            'value' => '587',
            'width' => '15'
        ),
		'use_ssl_in' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'CHECKBOX',
			'default' => 'y',
			'value' => array(0 => 'n', 1 => 'y')
		),
		'use_ssl_out' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'CHECKBOX',
			'default' => 'y',
			'value' => array(0 => 'n', 1 => 'y')
		),
		'active' => array(
			'datatype' => 'VARCHAR',
			'formtype' => 'CHECKBOX',
			'default' => 'y',
			'value' => array(0 => 'n', 1 => 'y')
		),
	)
);

