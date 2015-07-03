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


function zipit($filename,$files) {

	echo '<p>Compressing files...</p>'; flush(); ob_flush();

	$zip = new ZipArchive();
	$filename = $filename . ".zip";

	if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
    	exit("cannot open <$filename>\n");
	}

	foreach ($files as $file) {
		$cleanfile = preg_replace("/\.\/data\//", "", $file);		// cleaning up the filename to counter uncompress problems (with "." maybe?)
		$zip->addFile($file,$cleanfile);
		echo $cleanfile . "<br />";
	}

	echo '<p>Your files have been generated. ' . $zip->numFiles . ' files were zipped. ';
	echo 'Download the <a href="'.$filename.'">zip archive</a>.</p>';

	$zip->close();

	foreach ($files as $file) {
		unlink($file);
	}
}


function clean($string) {
	
	$string = preg_replace("/[\n\r\t]/", " ", $string);
	
	return $string;
}

?>