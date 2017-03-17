<?php

ini_set( 'default_charset', 'UTF-8' );

?>

<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	
	<title>TumblrTool</title>
	
	<link rel="stylesheet" type="text/css" href="http://labs.polsys.net/main.css" />
	<link href="https://fonts.googleapis.com/css?family=Droid+Sans|Muli:700" rel="stylesheet">
</head>

<body>

<div id="fullpage">

	<div class="headTab">
		<div class="leftHead">TumblrTool</div>
		<div class="rightHead">
			<a href="http://thepoliticsofsystems.net">blog</a>
			<a href="http://labs.polsys.net">software</a>
			<a href="http://thepoliticsofsystems.net/papers-and-talks/">research</a>
			<a href="https://www.digitalmethods.net">DMI</a>
			<a href="http://thepoliticsofsystems.net/about/">about</a>
		</div>
	</div>


	<div class="rowTab">
		<div class="fullTab">
			<p>This script retrieves posts tagged with a specific term from tumblr (the /tagged API endpoint documented <a href="https://www.tumblr.com/docs/en/api/v2#tagged-method" target="_blank">here</a>) and creates:
			<ul>	
				<li>a tabular file containing basic descriptions of the retrieved posts;</li>
				<li>a co-tag file (GDF format) to analyze e.g. in <a href="http://gephi.org" target="_blank">gephi</a>;</li>
			</ul>
			</p>
			
			<p>Source code and some documentation are available <a href="https://github.com/bernorieder/TumblrTool" target="_blank">here</a>.</p>
		</div>
	</div>
	
	<div class="rowTab">
		<div class="sectionTab"><h1>Parameters</h1></div>
	</div>
	
	<form action="tagnet.php" method="get">
	
	
	<div class="rowTab">
		<div class="sectionTab"><h2>1) Choose a tag:</h2></div>
	</div>
	
	<div class="rowTab">
		<div class="leftTab">Tag:</div>
		<div class="rightTab">
			<input type="text" name="tag" />
		</div>
	</div>
	
	
	<div class="rowTab">
		<div class="sectionTab"><h2>2) Choose a method:</h2></div>
	</div>

	<div class="rowTab">
		<div class="leftTab">
			<input type="radio" name="mode" value="last" checked="checked" /> Last posts:
		</div>
		<div class="rightTab">
			<input type="text" name="iterations" max="100" value="1" /> (one iteration gets 20 items, max. 100 iterations)
		</div>
	</div>
	
	<div class="rowTab">
		<div class="leftTab">
			<input type="radio" name="mode" value="daterange" /> Date range:
		</div>
		<div class="rightTab">
			<input type="text" name="date_end" max="100" /> - <input type="text" name="date_start" max="100" /> (YYYY-MM-DD, more recent date second)
		</div>
	</div>
			
	
	<div class="rowTab">
		<div class="sectionTab"><h2>3) HTML output:</h2></div>
	</div>
	
	<div class="rowTab">
		<div class="leftTab">Show HTML:</div>
		<div class="rightTab">
			<input type="checkbox" name="htmloutput" /> (shows result table in browser in addition to the file exports)
		</div>
	</div>
	
	<div class="rowTab">
		<div class="leftTab">Show images:</div>
		<div class="rightTab">
			<select name="showimages">
				<option value="off">no preview</option>
				<option value="200">width: 200px</option>
				<option value="500">width: 500px</option>
				<option value="original">original size</option>
			</select>
		</div>
	</div>
	
	<div class="rowTab">
		<div class="fullTab">
			<input type="submit" />
		</div>
	</div>

</div>

</body>
</html>