<?php

function GetIp(){
      if (!empty($_SERVER['HTTP_CLIENT_IP']))
      //check ip from share internet
      {
        $ip=$_SERVER['HTTP_CLIENT_IP'];
      }
      elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
      //to check ip is pass from proxy
      {
        $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
      }
      else
      {
        $ip=$_SERVER['REMOTE_ADDR'];
      }
      return $ip;
}

    // Include config file
    require_once "config.php";
	
	$json = file_get_contents('php://input');
	$obj = json_decode($json);

	echo $obj->id;
	echo "<br>";
	
	
	$i = 0;
	foreach($obj->resp as $individual_response){
		$best = $individual_response->best;
		$worst = $individual_response->worst;
		echo "sentence: ",$i,"<br>";
		echo "best: ",$best,"<br>";
		echo "worst: ",$worst,"<br>";
		$i++;
	}
	
	// Check input errors before inserting in database
    if($obj->id > 0){
        // Prepare an insert statement
        $sql = "INSERT INTO response (surveyid, uuid, timestamp) VALUES (?, ?, now())";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "is", $param_surveyid, $param_uuid);
            
            // Set parameters
            $param_surveyid = $obj->id;
            $param_uuid = GetIp(); //TODO: create a proper GDPR-compliant uuid at client and store in a cookie
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Record created successfully. 
				$responseid = mysqli_insert_id($link);
				echo "creato con id : ", $responseid;
	
				$i = 0;
				foreach($obj->resp as $individual_response){
					$best = $individual_response->best;
					$worst = $individual_response->worst;
					echo "sentence: ",$i,"<br>";
					echo "best: ",$best,"<br>";
					echo "worst: ",$worst,"<br>";
					
					$score = "[". $best . "," . $worst . "]";
					$sqlSentence = "INSERT INTO sentencescore (responseid, sentenceid, score) VALUES ($responseid, $i, '$score')";
					if (mysqli_query($link, $sqlSentence)) {
						echo "New record created successfully";
					} else {
						echo "Error: " . $sqlSentence . "<br>" . mysqli_error($link);
					}
					$i++;
				}
				
            } else{
				echo "Something went wrong. Please try again later.";
			}
			
			// Close statement
			mysqli_stmt_close($stmt);
			
		}
		else
			echo("Cannot prepare sql statement, please try again later.");
	}
    
    // Close connection
    mysqli_close($link);

?>
