<?php

$host= 'localhost';
$db = 'dealerportal';
$user = 'postgres';
$password = '123456789';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $link = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$host= 'localhost';
$db = 'dealerportal';
$user = 'postgres';
$password = '123456789';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $dpconn = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$host= 'localhost';
$db = 'dealerportal';
$user = 'postgres';
$password = '123456789';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $db = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}



$host= 'localhost';
$db = 'orderbooking';
$db = 'complaint_management';
$user = 'postgres';
$password = '123456789';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $con = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$host= 'localhost';
$db = 'orderbooking';
$db = 'complaint_management';
$user = 'postgres';
$password = '123456789';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $con_pgweb = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$host= 'localhost';
$db = 'orderbooking';
$db = 'complaint_management';
$user = 'postgres';
$password = '123456789';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $ordbk = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


/*$host= 'intra';
$db = 'orderbooking';
$user = 'postgres';
$password = '';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $iob = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


$host= '172.16.33.68';
$db = 'lps';
$user = 'postgres';
$password = '';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $link1 = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}


$host= '172.16.33.68';
$db = 'orderbooking';
$user = 'postgres';
$password = '';

try {
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $iconn = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   // echo "Connected to the $db database successfully!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}*/


?>
