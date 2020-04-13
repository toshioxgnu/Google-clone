<?php
ob_start();

try {
    $con = new PDO("mysql:dbname=zen;host=localhost","jose","root");
    $con -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
}catch (PDOException $e){
    echo "CONNECTION FAILED ". $e -> getMessage();
}