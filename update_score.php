<?php

// update a member's profile completeness gauge field

function update_score($member_id){
	$score = 0;

	require_once('/var/www/settings.php');
	$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

	$document_query = 
	"SELECT * FROM member_documents 
	LEFT JOIN exp_members ON member_documents.member_id=exp_members.member_id 
	LEFT JOIN exp_channel_titles ON exp_channel_titles.author_id=exp_members.member_id 
	WHERE exp_channel_titles.entry_id = '{$member_id}' ";
	if($db->query($document_query)->num_rows){
		$score++;
	}

	$user_results = $db->query("SELECT * FROM exp_channel_data WHERE entry_id='{$member_id}'");
	if($user_results->num_rows > 0){
		$user_array = $user_results->fetch_array(MYSQLI_ASSOC);
		if($user_array['field_id_90'] != "") $score++;
		if($user_array['field_id_91'] != "") $score++;
		if($user_array['field_id_94'] != "") $score++;
		if($user_array['field_id_95'] != "") $score++;
		if($user_array['field_id_98'] != "") $score++;
		if($user_array['field_id_99'] != "") $score++;
		if($user_array['field_id_100'] != "") $score++;
		if($user_array['field_id_102'] != "") $score++;
		if($user_array['field_id_103'] != "") $score++;
		if($user_array['field_id_105'] != "") $score++;
		if($user_array['field_id_107'] != "") $score++;
	}

	$query = "UPDATE exp_channel_data SET field_id_108='{$score}' WHERE entry_id='{$member_id}' ";
	if(!$db->query($query)){
		$error_set = true; // possible error handling for later
	}
	return $score;
}

?>