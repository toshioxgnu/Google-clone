<?php
include("../config.php");

if(isset($_POST["LinkId"])){
    $query = $con->prepare("update sites set clicks = clicks + 1 where id = :id");
    $query -> bindParam(":id", $_POST["LinkId"]);
    $query -> execute();
}else{
    echo "No LinkPast to page";
}