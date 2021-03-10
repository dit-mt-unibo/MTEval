<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Evaluation Results</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        .wrapper{
            width: 500px;
            margin: 0 auto;
        }
    </style>
	<link rel="stylesheet" href="css/date.css">    
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
$(function () {
  
	$( "#from" ).datepicker({
	  dateFormat: 'yy-mm-dd',
	  defaultDate: ( ff = getQueryVariable("from")) == null ? new Date() : ff,
      changeMonth: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#to" ).datepicker( "option", "minDate", selectedDate );
      }
    });
	
	$("#from").on('focus', function() { $(this).datepicker("show"); });
	
    $( "#to" ).datepicker({
	  dateFormat: 'yy-mm-dd',
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) {
        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
	
	$("#to").on('focus', function() { $(this).datepicker("show"); });
	
	
$("#furbo").click(function() {
	var from = $('#from').val();
	var to = $('#to').val();
	if((from > "") && (to > "")) {
		var dateValue = "id = " + getQueryVariable("id") + " da " + from + " fino a "+ to;
		$("#result").text(dateValue);
		var newUrl = window.location.origin + window.location.pathname + "?id=" + getQueryVariable("id") + "&from=" + from + "&to=" + to;
		$("#result").text(newUrl);
		window.location.replace(newUrl);
	} else {
		$("#result").text("no date selected");
	}
});

$( "#from" ).val(getQueryVariable("from"));
$( "#to" ).val(getQueryVariable("to"));

function getQueryVariable(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
            return pair[1];
        }
    }
    return null;
}

});

</script>
</head>
<body>
	<h1><center>Evaluation results</center></h1>
	<div align='center'>
		<label>time window: &nbsp;</label>
		<label for="from">from</label>
		<input type="text" id="from" name="from" />
		<label for="to">to</label>
		<input type="text" id="to" name="to" />
		<button id="furbo">Update</button>
		<div id="result"></div>
	</div>
	
    <div class="container">

<?php
function init_matrix(& $array, $x, $y)
{
	for ($i = 0; $i < $x; $i++) {
		$array[$i] = array();
		for ($j = 0; $j < $y; $j++) {
			$array[$i][$j]=0;
		}
	}
}

// Check existence of id parameter before processing further
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Include config file
    require_once "config.php";
	
    $systems = [];
	$surveyName = "";
	
	$sqlsurvey = "select * from mteval.survey where surveyid = ".trim($_GET["id"]);
	$resultSurvey = $link->query($sqlsurvey);
	if ($resultSurvey->num_rows > 0) {
		// output data of each row
		while($row = $resultSurvey->fetch_assoc()) {
			// surveyid, name, type, created, numsystems, numsentences, systems
			$surveyName = $row["name"];
			$systems = explode(';',$row["systems"]);
			$numsystems = $row["numsystems"];
			if($numsystems != count($systems))
				echo "Found ".$systems." instead of ".$numsystems;
			$numsentences = $row["numsentences"];
			break; // we only expect one record.
		}
	} else {
		echo "0 results";
		exit();
	}
	
	echo "<h1><center>" . $surveyName . "</center></h1>";
	
	// add filter for dates
	
	// 
	
	$displayrawdata = false;
	if(isset($_GET["raw"]))
	{
		$rawv = strtolower($_GET["raw"]);
		if( $rawv == "true" || intval($rawv) == 1)
			$displayrawdata = true;
	}
		
	$withDates = false;
    // Prepare a select statement
	if(isset($_GET["from"]))
	{
		$withDates = true;
		$sql = "select * from mteval.sentencescore where responseid in ( SELECT id FROM mteval.response where surveyid = ? and timestamp >= ? and timestamp <= ?)";
	}
	else
		$sql = "select * from mteval.sentencescore where responseid in ( SELECT id FROM mteval.response where surveyid = ?)";
    
	$best = array_fill(0, $numsystems, 0);
	$worst = array_fill(0, $numsystems, 0);
	$total_answers = 0;
	
	$last_response_id = -1;
	$response_count = 0;
	
	$best_per_sentence = array();
	$worst_per_sentence = array();
	for ($i = 0; $i < $numsentences; $i++) {
		$best_per_sentence[$i]= $best;
	}
	
	for ($i = 0; $i < $numsentences; $i++) {
		$worst_per_sentence[$i]= $best;
	}
	
	
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
		if($withDates)
		{
			mysqli_stmt_bind_param($stmt, "iss", $param_id, $param_from, $param_to);
			// set date params
			$param_from = trim($_GET["from"]);
			$param_to = trim($_GET["to"]);
		}
		else
		{
			mysqli_stmt_bind_param($stmt, "i", $param_id);
		}
        
        // Set parameters
        $param_id = trim($_GET["id"]);
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
    
			$total_answers = mysqli_num_rows($result);
            if($total_answers > 0){
				if($displayrawdata) {
					echo "<table class='table table-bordered table-striped'>";
					echo "<thead>";
					echo "<tr>";
					echo "<th>resp#</th>";
					echo "<th>sent#</th>";
					echo "<th>score</th>";
					echo "</tr>";
					echo "</thead>";
					echo "<tbody>";
				}
                while($row = mysqli_fetch_array($result)){
					if($displayrawdata){
						echo "<tr>";
						echo "<td>" . $row['responseid'] . "</td>";
						echo "<td>" . $row['sentenceid'] . "</td>";
						echo "<td>" . $row['score'] . "</td>";
						echo "</tr>";
					}
					
					$scores = json_decode($row['score']);
					if(!isset($scores[0]))
						$scores[0]=NULL;
					if(!isset($scores[1]))
						$scores[1]=NULL;
						
					$current_response_id = $row['responseid'];
					if( $current_response_id != $last_response_id)
					{
						$last_response_id = $current_response_id;
						$response_count++;
					}
					
					if( $scores != null)
					{
						if( isset($best[$scores[0]]) ) 
							$best[$scores[0]] = $best[$scores[0]]+1;
						else
							$best[$scores[0]] = 1;
							
						if( isset($worst[$scores[1]]) ) 
							$worst[$scores[1]] = $worst[$scores[1]]+1;
						else
							$worst[$scores[1]] = 1;
						
						// update stats by sentence Id:
						$s_id = $row['sentenceid'];
						if( isset($scores[0]) )//&& $scores[0] != NULL)
							$best_per_sentence[$s_id][$scores[0]] = $best_per_sentence[$s_id][$scores[0]] +1;
						if( isset($scores[1]) ) // && $scores[1] != NULL)
							$worst_per_sentence[$s_id][$scores[1]] = $worst_per_sentence[$s_id][$scores[1]] +1;	
							
					}
						
                }
				
				if($displayrawdata){
					echo "</tbody>";                            
					echo "</table>";
				}
                // Free result set
                mysqli_free_result($result);
				
				
            } else{
                // No votes yet.
				echo "No evaluation received yet for this task (id = " . $param_id . ")";
				$total_answers = 1;
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
		for ($i = 0; $i < $numsystems; $i++) {
			$bestpercent = $best[$i]*100 / $total_answers;
			$worstpercent = $worst[$i]*100 / $total_answers;
			echo "<tr><td>" . $systems[$i] . "</td>";
			$prettyb = str_replace(" ","&nbsp;",str_pad($best[$i], 5, " ", STR_PAD_LEFT));
			$prettyw = str_replace(" ","&nbsp;",str_pad($worst[$i], 5, " ", STR_PAD_LEFT));
			echo "<td>" . $prettyb . " (" . number_format($bestpercent, 2, ',', '') . "%)</td>";
			echo "<td>" . $prettyw . " (" . number_format($worstpercent, 2, ',', '') . "%)</td>";	
			echo "</tr>";  
		}
		echo "</table>";
		
		echo "<div id='totalcount'>We have received ". $response_count ." responses</div>";
		
		if($response_count == 0)
			$response_count = 1;
			
		// sentence by sentence:
		echo "<table class='table table-bordered table-striped'>";
		echo "<thead><tr>";
		echo "<th  class='col-xs-2'>Sent #</th>";
		for ($i = 0; $i < $numsystems; $i++) {
			echo "<th class='col-xs-2'>" . $systems[$i] . "</th>";
		}
        echo "</tr></thead>";
		
		echo "<div><p>Results by sentence: </p>";
		for ($i = 0; $i < $numsentences; $i++) {
			echo "<tr><td>" . $i . "</td>";
			for ($j = 0; $j < $numsystems; $j++) {
				$percent_best =  $best_per_sentence[$i][$j]*100 /  $response_count;
				$percent_worst = $worst_per_sentence[$i][$j]*100 /  $response_count;
				echo"<td>";
				echo "<div style='width: ". $percent_best ."%; background: rgb(128, 177, 133); overflow:visible;'>";
				echo $best_per_sentence[$i][$j]  . "</div>";
				echo "<div style='width: ". $percent_worst ."%; background: rgb(200, 0, 30); overflow:visible;'>";
				echo $worst_per_sentence[$i][$j] . "</div></td>";
			}
			echo "</tr>";  
		}
		echo "</table></div>";
		
		
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