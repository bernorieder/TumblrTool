<?php
	
function doAPIRequest($url) {
	
	$run = true;
	
	while($run == true) {
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$reply = curl_exec($ch);
		
		curl_close($ch);
		
		if($reply != false) {
			$run = false;
			//$reply = json_decode($reply);
			return json_decode($reply);
		} else {
			sleep(1);
		}
	}
}		
	
?>