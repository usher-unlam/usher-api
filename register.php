<?php
    //$con = mysqli_connect("localhost", "root", "", "usuarios");
    require_once 'db_connect_web.php';
    $link = conectar();
    
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $access = $_POST["access"];
    
    $response = array();
    
    $checkexist = mysqli_prepare($link, "SELECT username FROM users WHERE username = ?");
    if($checkexist){
      mysqli_stmt_bind_param($checkexist, "s", $username);
      mysqli_stmt_execute($checkexist);     
      if(mysqli_stmt_fetch($checkexist)){
        $response["succes"] = false;
        $response["error"] = "Usuario existente";
      }else{
        $statement = mysqli_prepare($link, "INSERT INTO users (name, surname, username, password, access) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($statement, "sssss", $name, $surname, $username, $password, $access);
        mysqli_stmt_execute($statement);
        if($statement){
          $response["succes"] = true;
        }
      }
    }  
    echo json_encode($response);
?>