<?php
#in the beginning

	$apikey = 'b0139e4615044b6ea9992c836300e8fa';
	$query = "https://ssl.bing.com/webmaster/api.svc/json/SubmitUrl?apikey=$apikey";
	$body = array("siteUrl"=>'https://buzzrem.space', "url"=>'https://buzzrem.space/post/test');
	$body = json_encode($body);
	$headers = Array('Content-Type: application/json');
	$ch = curl_init($query);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_exec($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	echo  $status === 200 ? 'done' : 'not done';
?>
