<?php

header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;
$algorithm = "";

if ($query) {


    require_once('solr-php-client/Apache/Solr/Service.php');

    $solr=new Apache_Solr_Service('localhost', 8983, '/solr/ny_core/');

	
	if (get_magic_quotes_gpc() == 1) {
		$query = stripslashes($query);
	}

	
	try {
		if ($_GET['algorithm'] == "lucene") {
			$results = $solr->search($query, 0, $limit);
		} else {
			$additionalParameters = array('sort' => 'pageRankFile desc');
			$results = $solr->search($query, 0, $limit, $additionalParameters);
		}
	} catch (Exception $e) {
		
		die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
	}
}

?>
<html>

<head>
	<title>PHP Solr Client Example</title>
</head>

<style>

.AlgoType {
  font-family: system-ui, sans-serif;
  font-size: 20px;
  font-weight: bold;
  line-height: 1.5;
  height:20px;
  width:20px;
  
}

.Submit-button {
  background-color: #cc9900;
  border: none;
  color: white;
  padding: 20px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 25px;
  margin: 4px 2px;
  cursor: pointer;
  border-radius: 20px;
}


input[type=text] {
  width: 50%;
  padding: 12px 20px;
  font-size: 20px;
  margin: 8px 0;
  box-sizing: border-box;
}

th, td {
  padding-top: 10px;
  padding-bottom: 10px;
  padding-left: 15px;
  padding-right: px;
}

</style>


<body>
	<form accept-charset="utf-8" method="get">
		<center>
			
			<h1><label for = "q">Enter your search query</label></h1>
		</center>
		</h1>
		<center><input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>" /></center>
		<br />
		<center><input type="radio" name="algorithm" checked class="AlgoType" value="lucene" <?php if (isset($_REQUEST['algorithm']) && $_REQUEST['algorithm'] == 'lucene') {
																	echo 'checked="checked"';
                                                                    $algorithm = "Lucene";
																} ?>> Lucene
			<input type="radio" name="algorithm" class="AlgoType" value="pagerank" <?php if (isset($_REQUEST['algorithm']) && $_REQUEST['algorithm'] == 'pagerank') {
																	echo 'checked="checked"';
                                                                    $algorithm = "PageRank";
																} ?>> Page Rank</center>
		<br />
		<center><input type="submit" class="Submit-button"/></center>
	</form>
	<?php

	// Results
	if ($results) {
		$total = (int) $results->response->numFound;
		$start = min(1, $total);
		$end = min($limit, $total);
		$csv = array_map('str_getcsv', file("URLtoHTML_nytimes_news.csv"));
		
	?>
        
		<div>Results <?php echo $start; ?> - <?php echo $end; ?> of <?php echo $total; ?>:</div>

		<ol>
		<?php
		
		foreach ($results->response->docs as $doc) {
			?>

			<?php
			$id = $doc->id;
			$title = $doc->title;
			$url = $doc->og_url;
			$desc = $doc->og_description;

			if ($desc == "" || $desc == null) {
				$desc = "Description not found";
			}
			if ($title == "" || $title == null) {
				$title = "title not found";
			}
			if ($url == "" || $url == null) {
				foreach ($csv as $row) {
					$cmp = "/home/hemanth/Downloads/nytimes/nytimes" + $row[0];
					if ($id == $cmp) {
						$url = $row[1];
						unset($row);
						break;
					}
				}
			}

			 ?>

			<table>
			<tr>
			<td style=font-family: Arial, Helvetica, sans-serif> <?php echo ("<a href ='" . $url . "'>" . $title . "</a>");?> </td>
            </tr> 
			
			<tr>
            <td> <?php echo ("<a href ='" . $url . "'>" . $url . "</a>");?> </td>		
            </tr>
			
        	<tr>
            <td style=font-family: Arial, Helvetica, sans-serif><?php echo htmlspecialchars($desc, ENT_NOQUOTES, 'utf-8');?></td>
        	</tr>
        	
			<tr>
            <td> <?php echo htmlspecialchars($id, ENT_NOQUOTES, 'utf-8');?></td>
        	</tr>
			</table>

			<hr> </hr>
			


		<?php	
		}
		?>
		<?php
	}
	?>

		
</body>
</html>