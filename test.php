<?php 
	echo "you have reached here<br>";
	$data = file_get_contents('php://input');
	echo $data;
	echo "<br>api key is".$_GET['apikey'];



	$ch = curl_init($query);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
?>