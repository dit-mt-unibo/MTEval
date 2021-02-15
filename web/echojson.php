<?php
echo "Hello";

$json = file_get_contents('php://input');
$obj = json_decode($json);

print_r($obj);
?>
