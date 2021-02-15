<?php
echo "Hello<br>";

$json = file_get_contents('php://input');
$obj = json_decode($json);

echo $obj->id;
echo "<br>";

//echo print_r($obj->resp, true);

$i = 0;
foreach($obj->resp as $individual_response){
    $best = $individual_response->best;
	$worst = $individual_response->worst;
	echo "sentence: ",$i,"<br>";
	echo "best: ",$best,"<br>";
	echo "worst: ",$worst,"<br>";
	$i++;
}

//echo print_r($obj);

//echo $_POST["id"];
//$obj = json_decode($_POST, true);
//echo print_r($obj,true);

//echo print_r($_POST);

?>
