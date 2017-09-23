<?php

$config = array(
    "db_host" => "localhost",
    "db_user" => "root",
    "db_pass" => "",
    "db_name" => "admin_root",
);
$db = new PDO("mysql:host=" . $config['db_host'] . ";dbname=" . $config['db_name'] . ";", $config['db_user'], $config['db_pass']);
$query = file_get_contents($_config['rootPath'] . 'version/database.sql');
$stmt = $db->prepare($query);
$stmt->execute();
