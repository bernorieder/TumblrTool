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
include "common.php";


// check query parameter
if(!isset($_GET["tag"])) { echo "missing tag"; exit; }
if(!isset($_GET["iterations"]) || preg_match("/\D/", $crawldepth) || $_GET["iterations"] > 100) { echo "iteration parameter problem"; exit; }

$tag = preg_replace("/#/","", $_GET["tag"]);
$iterations = $_GET["iterations"];
$htmloutput = ($_GET["htmloutput"] == "on") ? true:false;
$showimages = ($_GET["showimages"] == "off") ? false:$_GET["showimages"];
$mode = $_GET["mode"];


// api URL
$url = "http://api.tumblr.com/v2/tagged?tag=".urlencode($tag)."&limit=20&api_key=" . $api_key . "&before=";


if($mode == "last") {
	// retrieve $iterations	 packs of 20 images
	$results = array();
	$before = "";
	
	echo "Getting ".$iterations." iterations of 20 posts: "; flush(); ob_flush();
	
	for($i = 0; $i < $iterations; $i++) {
		
		$data = doAPIRequest($url . $before);
	
		$before = $data->response[count($data->response) - 1]->timestamp;
		$results = array_merge($results,$data->response);
	
		echo ($i + 1) . " "; flush(); ob_flush();
		sleep(0.5);
	}
}

if($mode == "daterange") {
	
	$results = array();
	$i = 0;
	
	$date_start = strtotime($_GET["date_start"] . " 23:59:59");
	$date_end = strtotime($_GET["date_end"] . " 00:00:00");
	
	$before = $date_start;
	
	echo "Getting posts between timestamp " . $date_start .  " and " . $date_end . ": "; flush(); ob_flush();
	
	while($before >= $date_end) {
		
		echo ($i + 1) . " (" . $before . ") "; flush(); ob_flush();
		$i++;
		
		$data = doAPIRequest($url . $before);
	
		$before = $data->response[count($data->response) - 1]->timestamp;
		
		foreach($data->response as $response) {
			if($response->timestamp >= $date_end) {
				$results[] = $response;
			}
		}
	
		sleep(0.5);
	}
	
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
							  "timestamp" => $item->timestamp,
							  "caption" => clean($item->caption),
							  "blog_name" => clean($item->blog_name),
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


echo '<p>The script has extracted tags from ' . count($posts) . ' posts.</p>';

if(count($posts) == 0) { exit; }


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


// file download 
$filename = "data/tumblr_" . $tag . "_" .date("Y_m_d-H_i_s");

file_put_contents($filename."_media.tab", $tab_posts);
file_put_contents($filename."_cotag.gdf", $gdf);

$files = array($filename."_media.tab",$filename."_cotag.gdf");

zipit($filename,$files);


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