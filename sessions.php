<?php
    require_once 'db_connect_web.php';
    $link = conectar();
    
    /*if(isset($_POST['session_id'])){
      $session = $_POST["session_id"];
    }*/
    
    if($link){
	//Si recibo una fecha en particular busco la sesión correspondiente. Caso contrario traigo todas.
      if(isset($_POST['date'])){
        $date = $_POST["date"];
        $statement = mysqli_prepare($link, "SELECT * FROM sessions WHERE start_date = ?");
		mysqli_stmt_bind_param($statement, "s", $date);
      }
      else{
        $statement = mysqli_prepare($link, "SELECT * FROM sessions");
      }
      
      if($statement){		
        mysqli_stmt_execute($statement);
        mysqli_stmt_store_result($statement);
        mysqli_stmt_bind_result($statement, $session_id, $start_date, $end_date, $comment);
        }
    }
       
    $response = array();
    $response["succes"] = false; 
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;  
        $response[$session_id]["session"] = $session_id;
        $response[$session_id]["start"] = $start_date;
        $response[$session_id]["end"] = $end_date;
        $response[$session_id]["comment"] = $comment;
    }
    
    echo json_encode($response);
    
    $link->close();
?>