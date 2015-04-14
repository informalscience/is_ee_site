<?php

$response = array();

if(isset($_POST['string'])){
	if($_POST['key'] != 'nioumlpwjbc4sctnkh21awio'){
		exit();
	}
	
	
	$resources = json_decode($_POST['string']);
	$type = $_POST['type'];
	$url =  $_POST['url'];

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

	$db->query("DELETE FROM sticky_resources WHERE type='{$type}' ");

	foreach($resources as $resource){
		$query = sprintf("INSERT INTO sticky_resources (ncs_id, position, title, type) VALUES ('%s', '%s', '%s', '%s')",
			$db->real_escape_string($resource->ncs_id),
			$db->real_escape_string($resource->position),
			$db->real_escape_string($resource->title),
			$db->real_escape_string($type)
		);
		if(!$db->query($query)){
			$response['error'] = "Error inserting into database";
			echo json_encode($response);
			exit();
		}
	}

	
	$response['success'] = "Successfully entered into database";
	echo json_encode($response);
	exit();

}
else{
	$response['error'] = 'Must have post data';
	echo json_encode($response);
}

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
			 $db->real_escape_string($url) 
			 );
	if(!$db->query($query)){
		return $false;
	}
	return true;
}

function updateType($db, $type, $url){
	$query = sprintf("UPDATE sticky_categories SET url = '%s' WHERE type = '%s'",
			 $db->real_escape_string($url),
			 $db->real_escape_string($type )
			 );
	if(!$db->query($query)){
		return $false;
	}
	return true;
}

?>