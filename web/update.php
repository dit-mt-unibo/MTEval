<?php
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$name = $surveytype = $numsystems = $numsentences = $systems = "";
$name_err = $surveytype_err = $systems_err = $numsystems_err = $numsentences_err = "";
 
// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Get hidden input value
    $id = $_POST["id"];
    
    // Validate name
    $input_name = trim($_POST["name"]);
    if(empty($input_name)){
        $name_err = "Please enter a name.";
    } else{
        $name = $input_name;
    }
    
    // Validate type
    $input_surveytype = trim($_POST["surveytype"]);
    if(empty($input_surveytype)){
        $surveytype_err = "Please enter a valid survey type.";     
    } else{
        $surveytype = $input_surveytype;
    }
    
    // Validate numsystems
    $input_numsystems = trim($_POST["numsystems"]);
    if(empty($input_numsystems)){
        $numsystems_err = "Please enter the numsystems amount.";     
    } elseif(!ctype_digit($input_numsystems)){
        $numsystems_err = "Please enter a positive integer value.";
    } else{
        $numsystems = $input_numsystems;
    }
	
	// Validate numsentences
    $input_numsentences = trim($_POST["numsentences"]);
    if(empty($input_numsentences)){
        $numsentences_err = "Please enter the numsentences amount.";     
    } elseif(!ctype_digit($input_numsentences)){
        $numsentences_err = "Please enter a positive integer value.";
    } else{
        $numsentences = $input_numsentences;
    }
	
	
	// Validate systems: must be strings separated by ; -- TODO: friendly multi-line input box, one system per box, and get numsystems automatically.
    $input_systems = trim($_POST["systems"]);
    if(empty($input_systems)){
        $systems_err = "Please enter a name for each system, separated by ';'";     
    } else { 
		$array = explode(';', $input_systems);
		if(count($array) != $numsystems) {
			$systems_err = "Please enter names of ".$numsystems." systems, separated by ';'";
		}
		else{
			$systems = $input_systems;
		}
	}
	
    // Check input errors before inserting in database
    if(empty($name_err) && empty($numsentences_err) && empty($numsystems_err)){
        // Prepare an update statement
        $sql = "UPDATE survey SET name=?, type=?, numsentences=?, numsystems=?, systems=? WHERE surveyid=?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssi", $param_name, $param_surveytype, $param_numsentences, $param_numsystems, $param_systems, $param_id);
            
			// Set parameters
            $param_name = $name;
            $param_surveytype = $surveytype;
            $param_numsystems = $numsystems;
			$param_systems = $systems;
			$param_numsentences = $numsentences;
			$param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records updated successfully. Redirect to landing page
                header("location: index.php");
                exit();
            } else{
                echo "Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
} else{
    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM survey WHERE surveyid = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_id);
            
            // Set parameters
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $name = $row["name"];
                    $surveytype = $row["type"];
					$numsentences = $row["numsentences"];
					$numsystems = $row["numsystems"];
					$systems = $row["systems"];
				
                } else{
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
        
        // Close connection
        mysqli_close($link);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Record</title>
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
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header">
                        <h2>Update Record</h2>
                    </div>
                    <p>Please edit the input values and submit to update the record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                            <span class="help-block"><?php echo $name_err;?></span>
                        </div>
                         <div class="form-group <?php echo (!empty($type_err)) ? 'has-error' : ''; ?>">
                            <label>Survey Type</label>
                            <textarea name="surveytype" class="form-control"><?php echo $surveytype; ?></textarea>
                            <span class="help-block"><?php echo $surveytype;?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($numsentences_err)) ? 'has-error' : ''; ?>">
                            <label>Num of Sentences to translate</label>
                            <input type="text" name="numsentences" class="form-control" value="<?php echo $numsentences; ?>">
                            <span class="help-block"><?php echo $numsentences_err;?></span>
                        </div>
						
                        <div class="form-group <?php echo (!empty($numsystems_err)) ? 'has-error' : ''; ?>">
                            <label>Num of Systems</label>
                            <input type="text" name="numsystems" class="form-control" value="<?php echo $numsystems; ?>">
                            <span class="help-block"><?php echo $numsystems_err;?></span>
                        </div>
						<div class="form-group <?php echo (!empty($systems_err)) ? 'has-error' : ''; ?>">
                            <label>System names (separated by ;)</label>
                            <input type="text" name="systems" class="form-control" value="<?php echo $systems; ?>">
                            <span class="help-block"><?php echo $systems_err;?></span>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-default">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>