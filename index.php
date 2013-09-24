<?php

// ----- conf -----
$iterations = 50;			//	how many sets of 20 images to use


// ----- load -----
include "conf.php";


// check query parameter
if(!isset($_GET["query"])) {
	echo "call the script with ?query=whatyouwanttoquery, e.g. https://lab.digitalmethods.net/~brieder/tumblr/tagnet/?query=yourtag"; exit;
}
$query = $_GET["query"];

// api URL
$url = "http://api.tumblr.com/v2/tagged?tag=".urlencode($query)."&limit=20&api_key=" . $api_key . "&before=";


// retrieve $iterations	 packs of 20 images
$results = array();
$before = "";

echo "(getting ".$iterations." iterations of 20 images) ";

for($i = 0; $i < $iterations; $i++) {
	$data = file_get_contents($url . $before);
	$data = json_decode($data);

	$before = $data->response[count($data->response) - 1]->timestamp;
	$results = array_merge($results,$data->response);

	echo $i . " "; flush(); ob_flush();
	sleep(1);
}


// create graph
$tags = array();
$edges = array();

foreach($results as $item) {

	$tmptags = $item->tags;

	// iterate over half of ajacency matrix
	for($i = 0; $i < count($tmptags); $i++) {

		$tmptags[$i] = strtolower($tmptags[$i]);
		$tmptags[$i] = preg_replace("/,/", " ", $tmptags[$i]);

		if(!isset($tags[$tmptags[$i]])) {
			$tags[$tmptags[$i]] = 1;
		} else {
			$tags[$tmptags[$i]]++;
		}

		for($j = $i; $j < count($tmptags); $j++) {

			$tmptags[$j] = strtolower($tmptags[$j]);
			$tmptags[$j] = preg_replace("/,/", " ", $tmptags[$j]);

			$tmpedge = array($tmptags[$i],$tmptags[$j]);
			asort($tmpedge);
			$tmpedge = implode("_|||_", $tmpedge);

			if(!isset($edges[$tmpedge])) {
				$edges[$tmpedge] = 1;
			} else {
				$edges[$tmpedge]++;
			}
		}
	}
}

arsort($tags);


// create output
$gdf = "nodedef>name VARCHAR,label VARCHAR,count INT\n";
foreach($tags as $key => $value) {
	$gdf .= md5($key) . "," . $key . "," . $value . "\n";
}

$gdf .= "edgedef>node1 VARCHAR,node2 VARCHAR,weight INT\n";

foreach($edges as $key => $value) {
	$tmpedge = explode("_|||_", $key);
	$gdf .= md5($tmpedge[0]) . "," . md5($tmpedge[1]) . "," . $value . "\n";
}

$filename = "tumblr_" . $query . "_" .date("Y_m_d-H_i_s") . ".gdf";

file_put_contents($filename, $gdf);

echo '<br /><br />your file: <a href="https://lab.digitalmethods.net/~brieder/tumblr/tagnet/'.$filename.'">'.$filename.'</a>';

?>