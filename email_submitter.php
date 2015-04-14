<?php

require_once('/var/www/settings.php');
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset("utf8");

$script_message = "";

// 173.163.247.62 is Ideum!
// 54.225.171.85 is informalscience server
$whitelist = array('173.163.247.62','54.225.171.85');
if(in_array($_SERVER['REMOTE_ADDR'],$whitelist) && ($_GET['func'] == 'moveRecord' || $_GET['func'] == 'deleteRecord')){
	
	$what_function = $_GET['func'];
	$holding_pen_id = "";
	$new_ncs_id = "";
	$title = "";
	$what_type = array();
	$is_project = 0;
	$is_evaluation = 0;
	$is_research = 0;
	$is_reference = 0;

	// parse GET parameters
	if(isset($_GET['srcId']))	$holding_pen_id = $_GET['srcId'];
	if(isset($_GET['dstId'])) $new_ncs_id = $_GET['dstId'];
	if(isset($_GET['title'])) $title = $_GET['title'];

	// are we a project, evaluation, research, and/or a reference?
	if(isset($_GET['resourceType']) && count($_GET['resourceType']) > 0){
		foreach($_GET['resourceType'] as $single_type){
			if(strpos($single_type,'Project Descriptions') === 0) $is_project = 1;
			if(strpos($single_type,'Evaluation Reports') === 0) $is_evaluation = 1;
			if(strpos($single_type,'Research Products') === 0) $is_research = 1;
			if(strpos($single_type,'Reference Materials') === 0) $is_reference = 1;
		}
	}

	$what_type = array("project"=>$is_project,"evaluation"=>$is_evaluation,"research"=>$is_research,"reference"=>$is_reference);

	foreach($what_type as $key=>$value){
		if($value == 1){
			// check if record is already in our database
			$does_record_exist_query = "SELECT * FROM ncs_".$key." WHERE holding_pen_id = '{$holding_pen_id}' ";

			// record is in our system
			if($db->query($does_record_exist_query)->num_rows > 0){
				$script_message.= 'Record exists<br />';

				// update our record
				$update_record_query = "UPDATE ncs_".$key." SET ncs_id = '{$new_ncs_id}', title = '{$title}' WHERE holding_pen_id = '{$holding_pen_id}' ";
				$db->query($update_record_query);

				$email_to = "";
				$email_query = "SELECT exp_members.email FROM ncs_".$key." LEFT JOIN exp_members ON ncs_".$key.".submitter_id=exp_members.member_id WHERE holding_pen_id = '{$holding_pen_id}' ";
				$email_results = $db->query($email_query);
				while($row = $email_results->fetch_array(MYSQLI_ASSOC)){
					$email_to = $row['email'];
				}

				// construct email
				if($email_to != ""){
					$script_message.= 'Sending '.$_GET['func'].' email to '.$email_to.'<br />';
					$host_url = $_SERVER['HTTP_HOST'];
					$from = 'admin@informalscience.org';
					$subject = ucwords($key).' Submission Approval';
					$headers = array();
					$headers[] = "MIME-Version: 1.0";
					$headers[] = "Content-type: text/html; charset=iso-8859-1";
					$headers[] = "From: <".$from.">";
					$headers[] = "Reply-To: <".$from.">";
					$headers[] = "Subject: ".$subject;
					$headers[] = "X-Mailer: PHP/".phpversion();
					$message = 'Hello,<br />The '.$key.' "'.$title.'" you submitted at <a href="http://informalscience.org">informalscience.org</a>';
					if($what_function == 'moveRecord'){ // success email
						$message.= ' has been successfully approved.';
						$what_section = "";
						if($key == 'project') $what_section = 'projects';
						if($key == 'evaluation') $what_section = 'evaluation';
						if($key == 'research' || $key == 'reference') $what_section = 'research';
						$message.= '<br />You can view it by visiting <a href="http://'.$host_url.'/'.$what_section.'/'.$new_ncs_id.'">this link</a>.';
					}	

					// send email
					if(mail($email_to, $subject, $message, implode("\r\n", $headers))){
						$script_message.= 'Successfully sent email<br />';
					}
					else{
						$script_message.= 'Failure sending email<br />';
					}
				}
				else{
					$script_message.= 'User email not found<br />';
				}
			}
			else if($what_function == 'moveRecord'){ // record is not in our system
				$insert_record_query = "INSERT INTO ncs_".$key." (ncs_id,title) VALUES ('{$new_ncs_id}','{$title}') ";
				if($db->query($insert_record_query)){
					$script_message.= 'Successfully entered new record into database';
				}
				else{
					$script_message.= 'Failure entering new record into database';	
				}
			}
		}
	}

	if($what_function == 'deleteRecord' && $_GET['srcCol'] == '1363799840933'){
		$email_to = "";
		if($email_to == ""){
			$email_query = "SELECT exp_members.email FROM ncs_project LEFT JOIN exp_members ON ncs_project.submitter_id=exp_members.member_id WHERE holding_pen_id = '{$holding_pen_id}' ";
			$email_results = $db->query($email_query);
			while($row = $email_results->fetch_array(MYSQLI_ASSOC)){
				$email_to = $row['email'];
			}
		}
		if($email_to == ""){
			$email_query = "SELECT exp_members.email FROM ncs_evaluation LEFT JOIN exp_members ON ncs_evaluation.submitter_id=exp_members.member_id WHERE holding_pen_id = '{$holding_pen_id}' ";
			$email_results = $db->query($email_query);
			while($row = $email_results->fetch_array(MYSQLI_ASSOC)){
				$email_to = $row['email'];
			}
		}
		if($email_to == ""){
			$email_query = "SELECT exp_members.email FROM ncs_reference LEFT JOIN exp_members ON ncs_reference.submitter_id=exp_members.member_id WHERE holding_pen_id = '{$holding_pen_id}' ";
			$email_results = $db->query($email_query);
			while($row = $email_results->fetch_array(MYSQLI_ASSOC)){
				$email_to = $row['email'];
			}
		}
		if($email_to == ""){
			$email_query = "SELECT exp_members.email FROM ncs_research LEFT JOIN exp_members ON ncs_research.submitter_id=exp_members.member_id WHERE holding_pen_id = '{$holding_pen_id}' ";
			$email_results = $db->query($email_query);
			while($row = $email_results->fetch_array(MYSQLI_ASSOC)){
				$email_to = $row['email'];
			}
		}

		// construct email
		if($email_to != ""){
			$script_message.= 'Sending '.$_GET['func'].' email to '.$email_to.'<br />';
			$host_url = $_SERVER['HTTP_HOST'];
			$from = 'admin@informalscience.org';
			$subject = 'InformalScience Submission Rejection';
			$headers = array();
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=iso-8859-1";
			$headers[] = "From: <".$from.">";
			$headers[] = "Reply-To: <".$from.">";
			$headers[] = "Subject: ".$subject;
			$headers[] = "X-Mailer: PHP/".phpversion();
			$message = 'Hello,<br />The resource you submitted at <a href="http://informalscience.org">informalscience.org</a> has been rejected';
			// send email
			if(mail($email_to, $subject, $message, implode("\r\n", $headers))){
				$script_message.= 'Successfully sent email<br />';
			}
			else{
				$script_message.= 'Failure sending email<br />';
			}
		}
		else{
			$script_message.= 'User email not found<br />';
		}
	}

	echo $script_message;
	
}

?>