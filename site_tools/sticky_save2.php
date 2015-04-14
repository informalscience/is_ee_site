<?php


	


	$type = $_GET['type'];
	$url = $_GET['url'];
	$url = "http://informalscience.org/evaluation/browse?type=evaluations&search=&search_url=http%3A%2F%2Fapi.informalscience.org%2Fsearch%2Fjson%3FresourceType%3DEvaluation%2BReports%253AFormative%26projectResearchReferenceScope%3DMedia%2Band%2BTechnology#topSearch";
	echo htmlentities($url);
	
	//Create the file
	$file = fopen("testfile.txt", "w"); 
	$txt = $url;
	fwrite($file, $txt);
	//$txt = json_decode($_POST['url']);
	fwrite($file, $txt);
	fclose($file);
	

	require_once('/var/www/settings.php');
	$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	$db->set_charset("utf8");

	if($type == ""){
		$response['error'] = "Type cannot be empty";
		echo json_encode($response);
		exit();
	}
	
	// Save the url to show more resources for the actual type
	if( !existType($db, $type) ){
		if( !createType($db, $type, $url))
		{
			$response['error'] = "Cannot save the url";
			echo json_encode($response);
			exit();
		}
	}
	else{
		if( !updateType($db, $type, $url) ){
			$response['error'] = "Cannot update the url";
			echo json_encode($response);
			exit();
		}
	}

	mysqli_close($db);
	$response['success'] = "Successfully entered into database";
	echo json_encode($response);
	exit();

	function existType($db, $type){	
		$exist = false;
		$result = $db->query("SELECT * FROM sticky_categories WHERE type='{$type}' ");
		/*while ($row = $result->fetch_row()) {
			print_r($row);
		}*/
		if( $result->num_rows >= 1){
			$exist =  true;
		}
		else{
			$exist =  false;
		}
		$result->close();
		return $exist;
	}
	
	function createType($db, $type, $url){
		$query = sprintf("INSERT INTO sticky_categories (type, url) VALUES ('%s', '%s')",
				 $db->real_escape_string($type),
				 $db->real_escape_string(htmlentities($url))
				 );
		if(!$db->query($query)){
			return $false;
		}
		return true;
	}
	
	function updateType($db, $type, $url){
		$query = sprintf("UPDATE sticky_categories SET url = '%s' WHERE type = '%s'",
				 $db->real_escape_string(htmlentities($url)),
				 $db->real_escape_string($type)
				 );
		print_r($query);
		if(!$db->query($query)){
			return $false;
		}
		return true;
	}


?>