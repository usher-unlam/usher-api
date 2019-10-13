<?php
    //$con = mysqli_connect("localhost", "root", "", "usuarios");
    require_once 'db_connect_web.php';
    $link = conectar();
    
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $newpass = $_POST["newpass"];
    
    $response = array();
    
    $checkexist = mysqli_prepare($link, "SELECT username FROM users WHERE username = ? AND password LIKE ?");
    if($checkexist){
      mysqli_stmt_bind_param($checkexist, "ss", $username, $password);
      mysqli_stmt_execute($checkexist);     
      if(!mysqli_stmt_fetch($checkexist)){
        $response["succes"] = false;
        $response["error"] = "Usuario inexistente o contraseña incorrecta";
      }else{
        $statement = mysqli_prepare($link, "UPDATE usher_web.users SET name = ?, surname = ?, password = ? WHERE username = ? AND password LIKE ?");
        mysqli_stmt_bind_param($statement, "ssss", $name, $surname, $newpass, $username, $password);
        mysqli_stmt_execute($statement);
        if($statement){
          $response["succes"] = true;
        }
      }
    }  
    echo json_encode($response);
?>