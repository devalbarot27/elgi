<?php

$host= 'localhost';
$db = 'dealerportal';
$user = 'postgres';
$password = '123456789';

$link = null;
try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $link = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
     echo "Connection failed wadsed: " . $e->getMessage();
}

/*$host= 'localhost';
$db = 'dealerportal';
$user = 'postgres';
$password = '';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $con = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
     echo "Connection failed wadsed: " . $e->getMessage();
}*/


$host= 'localhost';
//$db = 'orderbooking';
$db = 'complaint_management';
$user = 'postgres';
$password = '123456789';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $link1 = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


$host = 'localhost';
//$db = 'orderbooking';
$db = 'complaint_management';
$user = 'postgres';
$password = '123456789';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $con = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit; // Stop execution if the connection fails
}

/*$host = '172.16.33.74';
$db = 'scm';
$user = 'postgres';
$password = '';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $spcon = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit; // Stop execution if the connection fails
}


$host= '192.168.221.40';
$db = 'scm';
$user = 'pgweb';
$password = '';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $splink = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}*/

/*$host= '192.168.221.21';
$db = 'crm';
$user = 'postgres';
$password = '';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $ccscon = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
 */
?>
