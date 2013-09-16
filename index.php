<html>
<head>
	<title></title>
</head>
<body>

<pre>

<?php

if(!isset($_GET["query"])) {
	echo "call the script with ?query=whatyouwanttoquery";
	exit;
}

$query = $_GET["query"];

$url = "http://api.tumblr.com/v2/tagged?tag=".$query."&limit=20&api_key=" . $api_key . "&before=";
$results = array();
$before = "";

for($i = 0; $i < 50; $i++) {
	$data = file_get_contents($url . $before);
	$data = json_decode($data);

	$before = $data->response[count($data->response) - 1]->timestamp;

	$results = array_merge($results,$data->response);
}


//print_r($results);
//exit;

$gdf = "nodedef>name VARCHAR,label VARCHAR,count INT\n";

$tags = array();
$edges = array();

foreach($results as $item) {
	//echo implode(",",$item->tags) . "\n";

	$tmptags = $item->tags;

	for($i = 0; $i < count($tmptags); $i++) {

		$tmptags[$i] = strtolower($tmptags[$i]);

		if(!isset($tags[$tmptags[$i]])) {
			$tags[$tmptags[$i]] = 1;
		} else {
			$tags[$tmptags[$i]]++;
		}

		for($j = $i; $j < count($tmptags); $j++) {

			$tmptags[$j] = strtolower($tmptags[$j]);

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

asort($tags);

$tags = array_reverse($tags);

foreach($tags as $key => $value) {
	//echo $key . ":" . $value . '<br/>';
	$gdf .= md5($key) . "," . $key . "," . $value . "\n";
}

$gdf .= "edgedef>node1 VARCHAR,node2 VARCHAR,weight INT\n";

foreach($edges as $key => $value) {
	$tmpedge = explode("_|||_", $key);
	$gdf .= md5($tmpedge[0]) . "," . md5($tmpedge[1]) . "," . $value . "\n";
}

//print_r($tags);
//print_r($edges);

echo $gdf;

?>

</pre>

</body>
</html>