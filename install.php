<?php

if(!is_dir('interface') && !is_dir('sql')) die('Please enter the install directory before you execute the install.php script.');

// Copy the files
exec('chown -R ispconfig:ispconfig interface');
exec('chmod -R 750 interface');
exec('cp -prf interface /usr/local/ispconfig');
exec('chmod -R 770 /usr/local/ispconfig/interface/web/automail/lib/lang/');

// Execute the SQL
include_once '/usr/local/ispconfig/server/lib/config.inc.php';
include_once '/usr/local/ispconfig/server/lib/mysql_clientdb.conf';


$db_database = $conf['db_database'];

$command = "mysql -h ".escapeshellarg($clientdb_host)." -u ".escapeshellarg($clientdb_user)." -p".escapeshellarg($clientdb_password)."  ".escapeshellarg($db_database)." < sql/automail_db.sql";
exec($command);

echo "ISPConfig Automail module has been installed.\n";

?>
