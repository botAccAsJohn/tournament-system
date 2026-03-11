<?php

try {
function dbConnection(){
    $conn = new PDO("mysql:host=localhost;dbname=tournament_system", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (!$conn) {
        die("Connection failed: " . $conn->errorInfo());
    }
    return $conn;
}
}catch (Exception $e){
    echo "Connection failed: " . $e->getMessage();
}

?>