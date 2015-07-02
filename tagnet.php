<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">

	<title>TumblrTool</title>
	
	<link rel="stylesheet" type="text/css" href="main.css" />
	
	<style type="text/css">
		
		table, td, th {
		    border-color: #000;
		    border-style: solid;
		}
		
		table {
			width: 100%;
		    border-width: 0 0 1px 1px;
		    border-spacing: 0;
		    border-collapse: collapse;
		}
		
		td, th {
		    margin: 0;
		    padding: 3px;
		    border-width: 1px 1px 0 0;
		}

	</style>
</head>

<body>
	
<h1>TumblrTool</h1>

<?php

// ----- conf -----
ini_set('default_charset', 'UTF-8');
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 3000);


// ----- load -----
include "conf.php";


// check query parameter
if(!isset($_GET["tag"])) { echo "missing tag"; exit; }
if(!isset($_GET["iterations"]) || preg_match("/\D/", $crawldepth) || $_GET["iterations"] > 100) { echo "iteration parameter problem"; exit; }

$tag = $_GET["tag"];
$iterations = $_GET["iterations"];
$htmloutput = ($_GET["htmloutput"] == "on") ? true:false;
$showimages = ($_GET["showimages"] == "off") ? false:$_GET["showimages"];

// api URL
$url = "http://api.tumblr.com/v2/tagged?tag=".urlencode($tag)."&limit=20&api_key=" . $api_key . "&before=";


// retrieve $iterations	 packs of 20 images
$results = array();
$before = "";

echo "Getting ".$iterations." iterations of 20 images: "; flush(); ob_flush();

for($i = 0; $i < $iterations; $i++) {
	
	$data = file_get_contents($url . $before);
	$data = json_decode($data);
	
	//print_r($data); exit;

	$before = $data->response[count($data->response) - 1]->timestamp;
	$results = array_merge($results,$data->response);

	echo $i . " "; flush(); ob_flush();
	sleep(0.5);
}


// create graph and lists
$tags = array();
$edges = array();
$posts = array();


foreach($results as $item) {

	//print_r($item);

	$posts[$item->id] = array("id" => $item->id,
							  "type" => $item->type,
							  "date" => $item->date,
							  "date_unix" => $item->timestamp,
							  "caption" => $item->caption,
							  "note_count" => $item->note_count,
							  "post_url" => $item->post_url,
							  "tags" => implode(", ",$item->tags),
							  "photo" => $item->photos[0]->original_size->url						  
							  );
										
										
	//print_r($posts); exit;

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


// create GDF output
$gdf = "nodedef>name VARCHAR,label VARCHAR,count INT\n";
foreach($tags as $key => $value) {
	$gdf .= md5($key) . "," . $key . "," . $value . "\n";
}

$gdf .= "edgedef>node1 VARCHAR,node2 VARCHAR,weight INT\n";

foreach($edges as $key => $value) {
	$tmpedge = explode("_|||_", $key);
	$gdf .= md5($tmpedge[0]) . "," . md5($tmpedge[1]) . "," . $value . "\n";
}


// create tab output
$tab_posts = implode("\t",array_keys($posts[array_shift(array_keys($posts))])) . "\n";

foreach($posts as $post) {
	$tab_posts .= implode("\t", $post) ."\n";
}


$filename = "tumblr_" . $tag . "_" .date("Y_m_d-H_i_s");


file_put_contents($filename."_media.tab", $tab_posts);
file_put_contents($filename."_cotag.gdf", $gdf);

echo '<br /><br />The script has extracted tags from ' . count($posts) . ' posts.<br /><br />

your files:<br />
<a href="'.$base_url.$filename.'_media.tab">'.$filename.'_media.tab</a><br />
<a href="'.$base_url.$filename.'_cotag.gdf">'.$filename.'_cotag.gdf</a><br /><br />';

// HTML data table
if($htmloutput) {
	
	//print_r($media);
	
	echo '<table>';
	
	echo '<tr>';
	foreach(array_keys($posts[array_shift(array_keys($posts))]) as $title) {
		echo '<th>'.$title.'</th>';
	}
	echo '</tr>';
	
	foreach($posts as $post) {
		
		echo '<tr>';
		foreach($post as $element) {
						
			
			if(preg_match("/\.jpg/", $element) || preg_match("/\.png/", $element) || preg_match("/\.gif/", $element)) {

				if($showimages == false) {
					echo '<td>'.$element.'</td>';	
				} else if($showimages == "original") {
					echo '<td><img src="'.$element.'"></td>';	
				} else {
					echo '<td><img src="'.$element.'" width="'.$showimages.'"></td>';	
				}

			} else if(preg_match("/http:/", $element) || preg_match("/http:/", $element)) {
				echo '<td><a href="'.$element.'">'.$element.'</a></td>';
			} else {
				echo '<td>'.$element.'</td>';
			}
		}
		echo '</tr>';
	}
	echo '</table>';
}

?>

</body>
</html>