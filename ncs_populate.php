<?php

exit()

require_once('/var/www/settings.php');
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset("utf8");

// $url = 'http://api.informalscience.org/search/json?n=750&page=1';
// $url = 'http://api.informalscience.org/search/json?n=750&page=2';

$results = json_decode(file_get_contents($url),true);

$count = 0;
foreach($results['results'] as $value){

	// get title and ncs id
	$title = $value['record']['resource']['title']['values'][0]['value'];
	$title = $db->real_escape_string($title);
	$ncs_id = $value['record']['administrative']['recordID']['values'][0]['value'];

	$count++;
	echo $count.' - '.$ncs_id.' : ';

	// figure out which table to put this record in
	$resource_type = $value['record']['resource']['resourceType']['values'];
	$type = array('project'=>0,'evaluation'=>0,'research'=>0,'reference'=>0);
	foreach($resource_type as $single_type){
		if($single_type['value'] == 'Project Descriptions') $type['project'] = 1;
		if($single_type['value'] == 'Evaluation Reports') $type['evaluation'] = 1;
		if($single_type['value'] == 'Research Products') $type['research'] = 1;
		if($single_type['value'] == 'Reference Materials') $type['reference'] = 1;
	}

	// update or insert into database
	foreach($type as $key=>$value){
		if($value == 1){
			
			$check_query = "SELECT * FROM ncs_".$key." WHERE title='{$title}' ";
			if($db->query($check_query)->num_rows){
				echo 'exists!';
			}
			else{
				$insert_query = "INSERT INTO ncs_".$key." (title,ncs_id) VALUES ('{$title}','{$ncs_id}') ";
				if($db->query($insert_query)){
					echo 'inserted!';
				}
			}

		}
	}

	echo '<br />';

}

?>