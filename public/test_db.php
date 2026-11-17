
<?php

require_once '../config/config.php';
require_once '../app/Database.php';

$db = new Database();
$conn = $db->connect();

if ($conn) {
    echo "KET NOI DATABASE THANH CONG!";
}
