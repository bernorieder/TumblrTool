<?php

ini_set( 'default_charset', 'UTF-8' );

?>

<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	
	<title>TumblrTool</title>
	
	<link rel="stylesheet" type="text/css" href="main.css" />
</head>

<body>

	<h1>TumblerTool</h1>
	
	<p>This script retrieves posts tagged with a specific term from tumblr (the /tagged API endpoint documented <a href="https://www.tumblr.com/docs/en/api/v2#tagged-method" target="_blank">here</a>) and creates:
	<ul>	
		<li>a tabular file containing basic descriptions of the retrieved posts;</li>
		<li>a co-tag file (GDF format) to analyze e.g. in <a href="http://gephi.org" target="_blank">gephi</a>;</li>
	</ul>
	</p>
	
	<p>Source code and some documentation are available <a href="https://github.com/bernorieder/TumblrTool" target="_blank">here</a>.</p>
	
	<hr />
	
	<table>
		<form action="tagnet.php" method="get">
			
			<tr>
				<td colspan="3">1) choose a tag:</td>
			</tr>
			
			<tr>
				<td></td>
				<td>tag:</td>
				<td><input type="text" name="tag" /></td>
			</tr>
			
			<tr>
				<td colspan="3">2) choose a method:</td>
			</tr>
			
			
			<tr>
				<td><input type="radio" name="mode" value="last" checked="checked" /></td>
				<td>last posts:</td>
				<td><input type="text" name="iterations" max="100" value="1" /> (one iteration gets 20 items, max. 100 iterations) </td>
			</tr>
			
			<tr>
				<td><input type="radio" name="mode" value="daterange" /></td>
				<td>date range:</td>
				<td><input type="text" name="date_end" max="100" /> - <input type="text" name="date_start" max="100" /> (YYYY-MM-DD, more recent date second)</td>
			</tr>
			
			
			<tr>
				<td colspan="3">3) HTML output:</td>
			</tr>
			
			<tr>
				<td></td>
				<td>show HTML:</td>
				<td><input type="checkbox" name="htmloutput" /> (adds HTML result tables in addition to the file exports)</td>
			</tr>
			
			<tr>
				<td></td>
				<td>show images:</td>
				<td>
					<select name="showimages">
						<option value="off">no preview</option>
						<option value="200">width: 200px</option>
						<option value="500">width: 500px</option>
						<option value="original">original size</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" /></td>
			</tr>
		</form>
	</table>

</body>
</html>