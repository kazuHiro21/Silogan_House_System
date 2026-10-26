<?php
// Change these settings if your local MySQL setup uses different credentials.
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'silogan_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $error) {
    http_response_code(500);
    exit('Could not connect to the database. Check core/dbconfig.php and import core/schema.sql.');
}
