<?php
require 'config.php';
require 'functions.php';

$db = init_db_connection();
try {
    $stmt = $db->query('DESCRIBE dining_menu');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
