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
        $statement = mysqli_prepare($link, "SELECT session_id, start_date, end_date, comment FROM sessions WHERE start_date = ? ");
		mysqli_stmt_bind_param($statement, "s", $date);
      }
      elseif(isset($_POST['isrunning'])){
		    $dateYesterday = new DateTime();
		    $dateYesterday->add(DateInterval::createFromDateString('yesterday'));
        $dYest = $dateYesterday->format('Y,m,d');
        $statement = mysqli_prepare($link, "SELECT session_id, start_date, end_date, comment FROM sessions WHERE start_date >= ? AND end_date = 0 ");
		  mysqli_stmt_bind_param($statement, "s", $dYest);
      }
      else{
        $statement = mysqli_prepare($link, "SELECT session_id, start_date, end_date, comment FROM sessions WHERE end_date > 0");
      }
      
      if($statement){		
        mysqli_stmt_execute($statement);
        mysqli_stmt_store_result($statement);
        mysqli_stmt_bind_result($statement, $session_id, $start_date, $end_date, $comment);
        }
    }
    
    $response = array();
    $response["succes"] = false; 
    
    $pos = 0;
    
    while(mysqli_stmt_fetch($statement)){
        $response["succes"] = true;
        $response[$pos]["session"] = $session_id;
        $response[$pos]["start"] = $start_date;
        $response[$pos]["end"] = $end_date;
        $response[$pos]["comment"] = $comment;
        $pos++;
    }
    
    echo json_encode($response);
    
    $link->close();
?>