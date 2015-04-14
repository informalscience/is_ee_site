<?php

require_once('/var/www/ee/ex_simple_xml_element.php');

// this will process form data anytime we want to "add ourselves" to a project
// it wll also process form data when we make associations between projects and evaluations/research/reference
if(isset($_POST)){

	$site_url = 'http://'.$_SERVER['HTTP_HOST'].'/';
	$page_message = "";
	$ncs_validation = true;
	$base_ncs_url = 'http://54.225.171.85:8080/ncs/services/dcsws1-0?';

	if(isset($_POST['submit_add_yourself'])){
		$contributor_name = "";
		$contributor_id = "";
		$contributor_type = 'Contributor';
		$contributor_organization = "";
		$current_ncs_id = "";

		// did we select an existing member?
		if(isset($_POST['name'])){
			$contributor_name = $_POST['name'];
		}
		// or are we a new member to the team?
		else if(isset($_POST['current_user'])){
			$contributor_name = $_POST['current_user'];
		}
		// set contributor CMS id, role, organization. get current ncs id of project
		if(isset($_POST['current_user_id'])) $contributor_id = $_POST['current_user_id'];
		if(isset($_POST['role'])) $contributor_type = $_POST['role'];
		if(isset($_POST['organization'])) $contributor_organization = $_POST['organization'];

		if(isset($_POST['current_ncs_id'])) $current_ncs_id = $_POST['current_ncs_id'];

		// issue GetID request from the NCS
		$collection_name = '1363799840933';
		$get_id_url = $base_ncs_url.'verb=GetId';
		$get_id_url.= '&collection='.$collection_name;
		$get_id_results = simplexml_load_string(file_get_contents($get_id_url));

		// parse GetID request from the NCS
			$get_id = "";
			$get_id = $get_id_results->GetId->id;
			if($get_id == "") $ncs_validation = false;

		// create XML for holding pen
			$record_xml_string = "";
			if($ncs_validation){

				$record_xml_string = "";
				$edit_xml = new ExSimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>'.'<record></record>');
				$edit_xml->addAttribute('xmlns','http://informalscience.org/InformalCommonsItem');
				$edit_xml->addAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
				$edit_xml->addAttribute('xsi:schemaLocation','http://informalscience.org/InformalCommonsItem file:///Users/dblue/git/informalCommons/schema/informalCommons_item/InformalCommonsItem.xsd');
				$administrative_list = $edit_xml->addChild('administrative');
				$administrative_list->addChild('recordID',$get_id);
				
				$submitter_list = $administrative_list->addChild('submitter');
				if(isset($_POST['submitter_name'])){
					$submitter_first_name = "";
					$submitter_last_name = "";
					$parts = explode(' ', $_POST['submitter_name']);
					$tmp_submitter_name = array(implode(' ', array_slice($parts, 0, -1)), end($parts));
					if($tmp_submitter_name[0] != "") $submitter_first_name = $tmp_submitter_name[0];
					if($tmp_submitter_name[1] != "") $submitter_last_name = $tmp_submitter_name[1];
					$submitter_list->addChild('nameFirst',$submitter_first_name);
					$submitter_list->addChild('nameLast',$submitter_last_name);
				}
				else{
					$submitter_list->addChild('nameFirst',$_POST['submitter_firstname']);
					$submitter_list->addChild('nameLast',$_POST['submitter_lastname']);
				}
				
				$submitter_list->addChild('email',$_POST['submitter_email']);

				$resource_list = $edit_xml->addChild('resource');
				if($contributor_name != ""){
					$contributor_first_name = "";
					$contributor_last_name = "";
					$parts = explode(' ', $contributor_name);
					$tmp_contributor_name = array(implode(' ', array_slice($parts, 0, -1)), end($parts));
					if($tmp_contributor_name[0] != "") $contributor_first_name = $tmp_contributor_name[0];
					if($tmp_contributor_name[1] != "") $contributor_last_name = $tmp_contributor_name[1];
					$contributor_element_xml = $resource_list->addChild('contributor');
					
					$contributor_element_xml->addChild('role',$contributor_type);
					$contributor_element_xml->addChild('personFirstName',$contributor_first_name);
					$contributor_element_xml->addChild('personLastName',$contributor_last_name);
					$contributor_element_xml->addChildCData('organizationName',$contributor_organization);
					$contributor_element_xml->addChild('contributorId',$contributor_id);
				}

				$record_xml_string = rawurlencode($edit_xml->saveXML());

			}
		
		// send to the NCS holding pen
			$xml_format = 'informalCommons';
			$put_record_url = $base_ncs_url.'verb=PutRecord';
			$put_record_url.= '&xmlFormat='.$xml_format;
			$put_record_url.= '&collection='.$collection_name;
			$put_record_url.= '&id='.$get_id;
			$put_record_url.= '&dcsStatusNote='.'record+edit+for-'.$current_ncs_id.'-we+are+updating+a+contributor+or+adding+a+new+contributor';
			$put_record_url.= '&recordXml='.$record_xml_string;
			$put_record_results = simplexml_load_string(file_get_contents($put_record_url));

		// parse response from the NCS holding pen
		if(isset($put_record_results->PutRecord->result) && $put_record_results->PutRecord->result == "Success"){
			$page_message = '<span>Success!</span>Thank you for updating your record. Your updates have been submitted for approval.';
		}
		else{
			$ncs_validation = false;
		}

		// did our request failed somewhere along the process?
		if(!$ncs_validation){
			echo '<div class="form_error">Your request has failed</div>';
		}
		else{
			echo '<div class="form_success">'.$page_message.'<div>';
		}

	}

	if(isset($_POST['record_associate'])){
		$new_ncs_id = "";
		$new_ncs_title = "";
		$current_ncs_id = "";
		$current_ncs_title = "";

		if(isset($_POST['new_record_id'])) $new_ncs_id = $_POST['new_record_id'];
		if(isset($_POST['new_record_title'])) $new_ncs_title = $_POST['new_record_title'];
		if(isset($_POST['current_ncs_id'])) $current_ncs_id = $_POST['current_ncs_id'];
		if(isset($_POST['current_ncs_title'])) $current_ncs_title = $_POST['current_ncs_title'];

		$new_record_url = "";
		$current_record_url = $site_url.'projects/'.$current_ncs_id;
		if($_POST['new_record_type'] == 'Evaluation'){
			$new_record_url = $site_url.'evaluation/'.$new_ncs_id;
		}
		else{
			$new_record_url = $site_url.'research/'.$new_ncs_id;
		}

		// send Project edit to NCS holding pen

			// issue GetID request from the NCS
				$collection_name = '1363799840933';
				$get_id_url = $base_ncs_url.'verb=GetId';
				$get_id_url.= '&collection='.$collection_name;
				$get_id_results = simplexml_load_string(file_get_contents($get_id_url));

			// parse GetID request from the NCS
				$get_id = "";
				$get_id = $get_id_results->GetId->id;
				if($get_id == "") $ncs_validation = false;

			// create XML for holding pen
				$record_xml_string = "";
				if($ncs_validation){
				// generate actual xml
					$record_xml_string = "";
					$edit_xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>'.'<record></record>');
					$edit_xml->addAttribute('xmlns','http://informalscience.org/InformalCommonsItem');
					$edit_xml->addAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
					$edit_xml->addAttribute('xsi:schemaLocation','http://informalscience.org/InformalCommonsItem file:///Users/dblue/git/informalCommons/schema/informalCommons_item/InformalCommonsItem.xsd');
					$administrative_list = $edit_xml->addChild('administrative');
					$administrative_list->addChild('recordID',$get_id);
					$submitter_list = $administrative_list->addChild('submitter');
					$submitter_list->addChild('nameFirst',$_POST['submitter_firstname']);
					$submitter_list->addChild('nameLast',$_POST['submitter_lastname']);
					$submitter_list->addChild('email',$_POST['submitter_email']);
					$resource_list = $edit_xml->addChild('resource');
					$related_url_list = $resource_list->addChild('relatedUrl',$new_record_url);
					$related_url_list->addAttribute('kind','Has part');
					$related_url_list->addAttribute('title',$new_ncs_title);
					$related_url_list->addAttribute('type',$_POST['new_record_type']);
					$record_xml_string = rawurlencode($edit_xml->saveXML());
				}

			// send to the NCS holding pen
				$xml_format = 'informalCommons';
				$put_record_url = $base_ncs_url.'verb=PutRecord';
				$put_record_url.= '&xmlFormat='.$xml_format;
				$put_record_url.= '&collection='.$collection_name;
				$put_record_url.= '&id='.$get_id;
				$put_record_url.= '&dcsStatusNote='.'record+edit+for-'.$current_ncs_id.'-we+are+adding+a+relatedUrl-'.$_POST['new_record_type'];
				$put_record_url.= '&recordXml='.$record_xml_string;
				$put_record_results = simplexml_load_string(file_get_contents($put_record_url));

			// parse response from the NCS holding pen
				if(isset($put_record_results->PutRecord->result) && $put_record_results->PutRecord->result == "Success"){
					$page_message = '<span>Success!</span>Thank you for updating your record. Your updated have been submitted for approval.';
				}
				else{
					$page_message = '<div style="display:none">First Request Failed</div>';
					$ncs_validation = false;
				}

		// send Evaluation/Research/Reference edit to NCS holding pen

			// issue GetID request from the NCS
				$collection_name = '1363799840933';
				$get_id_url = $base_ncs_url.'verb=GetId';
				$get_id_url.= '&collection='.$collection_name;
				$get_id_results = simplexml_load_string(file_get_contents($get_id_url));

			// parse GetID request from the NCS
				$get_id = "";
				$get_id = $get_id_results->GetId->id;
				if($get_id == "") $ncs_validation = false;

			// create XML for holding pen
				$record_xml_string = "";
				if($ncs_validation){
				// generate actual xml
					$record_xml_string = "";
					$edit_xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>'.'<record></record>');
					$edit_xml->addAttribute('xmlns','http://informalscience.org/InformalCommonsItem');
					$edit_xml->addAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
					$edit_xml->addAttribute('xsi:schemaLocation','http://informalscience.org/InformalCommonsItem file:///Users/dblue/git/informalCommons/schema/informalCommons_item/InformalCommonsItem.xsd');
					$administrative_list = $edit_xml->addChild('administrative');
					$administrative_list->addChild('recordID',$get_id);
					$submitter_list = $administrative_list->addChild('submitter');
					$submitter_list->addChild('nameFirst',$_POST['submitter_firstname']);
					$submitter_list->addChild('nameLast',$_POST['submitter_lastname']);
					$submitter_list->addChild('email',$_POST['submitter_email']);
					$resource_list = $edit_xml->addChild('resource');
					$related_url_list = $resource_list->addChild('relatedUrl',$current_record_url);
					$related_url_list->addAttribute('kind','Is part of');
					$related_url_list->addAttribute('title',$current_ncs_title);
					$related_url_list->addAttribute('type','Project');
					$record_xml_string = rawurlencode($edit_xml->saveXML());
				}

			// send to the NCS holding pen
				$xml_format = 'informalCommons';
				$put_record_url = $base_ncs_url.'verb=PutRecord';
				$put_record_url.= '&xmlFormat='.$xml_format;
				$put_record_url.= '&collection='.$collection_name;
				$put_record_url.= '&id='.$get_id;
				$put_record_url.= '&dcsStatusNote='.'record+edit+for-'.$new_ncs_id.'-we+are+adding+a+relatedUrl-Project';
				$put_record_url.= '&recordXml='.$record_xml_string;
				$put_record_results = simplexml_load_string(file_get_contents($put_record_url));

			// parse response from the NCS holding pen
				if(isset($put_record_results->PutRecord->result) && $put_record_results->PutRecord->result == "Success"){
					$page_message = '<span>Success!</span>Thank you for updating your record. Your updates have been submitted for approval.';
				}
				else{
					$page_message = '<div style="display:none">First Request Failed</div>';
					$ncs_validation = false;
				}

		// did our request failed somewhere along the process?
		if(!$ncs_validation){
			echo '<div class="form_error">Your request has failed</div>'.$page_message;
		}
		else{
			echo '<div class="form_success">'.$page_message.'<div>';
		}

	}


}

?>