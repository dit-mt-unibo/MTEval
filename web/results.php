<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Record</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        .wrapper{
            width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">

<?php
// Check existence of id parameter before processing further
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Include config file
    require_once "config.php";
    
	//TODO: read survey record, so we can find name, #sentences, #systems
	//TODO: read # of responses, so we can convert final data to %ages
	
    // Prepare a select statement
    $sql = "select * from mteval.sentencescore where responseid in ( SELECT id FROM mteval.response where surveyid = ?)";
    
	$best = array_fill(0, 4, 0);
	$worst = array_fill(0, 4, 0);
	$total_answers = 0;
	
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        // Set parameters
        $param_id = trim($_GET["id"]);
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
			$total_answers = mysqli_num_rows($result);
            if($total_answers > 0){
                echo "<table class='table table-bordered table-striped'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>resp#</th>";
                echo "<th>sent#</th>";
                echo "<th>score</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                while($row = mysqli_fetch_array($result)){
					echo "<tr>";
                    echo "<td>" . $row['responseid'] . "</td>";
                    echo "<td>" . $row['sentenceid'] . "</td>";
                    echo "<td>" . $row['score'] . "</td>";
                    echo "</tr>";
					$scores = json_decode($row['score']);
					
					if( isset($best[$scores[0]]) ) 
						$best[$scores[0]] = $best[$scores[0]]+1;
					else
						$best[$scores[0]] = 1;
						
					if( isset($worst[$scores[1]]) ) 
						$worst[$scores[1]] = $worst[$scores[1]]+1;
					else
						$worst[$scores[1]] = 1;
						
                }
                echo "</tbody>";                            
                echo "</table>";
                // Free result set
                mysqli_free_result($result);
				
            } else{
                // URL doesn't contain valid id parameter. Redirect to error page
                header("location: error.php");
                exit();
            }
            
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
		
		echo "<table class='table table-bordered table-striped'>";
		echo "<thead><tr>";
		echo "<th>System</th>";
        echo "<th>Best</th>";
        echo "<th>Worst</th>";
        echo "</tr></thead>";
		$len = count($best);
		for ($i = 0; $i < $len; $i++) {
			$bestpercent = $best[$i]*100 / $total_answers;
			$worstpercent = $worst[$i]*100 / $total_answers;
			echo "<tr><td>" . $i+1 . "</td>";
			echo "<td>" . $best[$i] . " (" . $bestpercent . "%)</td>";
			echo "<td>" . $worst[$i] . " (" . $worstpercent . "%)</td>";
			echo "</tr>";  
		}
		echo "</table>";
		
    // Close statement
    mysqli_stmt_close($stmt);
    } // end my sql prepare
    
	// Close connection
    mysqli_close($link);
 
} 
else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: error.php");
    exit();
}
?>
    </div>
</body>
</html>