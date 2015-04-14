<?php

require_once('/var/www/ee/ex_simple_xml_element.php');

function process_submit($resource,$id){
  
	$record_xml_string = "";

//	$record_xml->addAttribute('xmlns','http://informalscience.org/InformalCommonsItem');
//	$record_xml->addAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
//	$record_xml->addAttribute('xsi:schemaLocation','http://informalscience.org/InformalCommonsItem file:///Users/dblue/git/informalCommons/schema/informalCommons_item/InformalCommonsItem.xsd');

	$record_xml = new ExSimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><record xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://informalscience.org/InformalCommonsItem" xsi:schemaLocation="http://informalscience.org/InformalCommonsItem /var/tomcat/ncs/schema/informalCommons_item/informalCommonsItem.xsd"></record>');

	$administrative_xml = $record_xml->addChild('administrative');
	$administrative_xml->addChild('recordID',$id);
	$administrative_xml->addChild('entryDate', date('Y-m-d'));
  
	// submitter information
	$submitter_xml = $administrative_xml->addChild('submitter');
	$submitter_xml->addChild('nameFirst',$resource['submitter_firstname']);
	$submitter_xml->addChild('nameLast',$resource['submitter_lastname']);
	$submitter_xml->addChild('institutionName', '');
	$submitter_xml->addChild('email',$resource['submitter_email']);

	$ic_specific_xml = $record_xml->addChild('icSpecific');
	$ic_specific_xml->addChild('icRecordType','CAISE');
	$ic_specific_xml->addChild('recordUrl',$resource['url']);

	$resource_xml = $record_xml->addChild('resource');
	$resource_xml->addChildCData('title',$resource['title']);

	// citation information
	$citation_fields = array('citation_id', 'citation_id_type', 'citation_pub',
	  'citation_volume', 'citation_number', 'citation_page');
	$citation_non_empty = array();
	foreach($citation_fields as $field_name) {
	  if(!empty($resource[$field_name])) {
	      $citation_non_empty[] = $field_name;
	  }
	}
	if(count($citation_non_empty)) {
    $citation_xml = $resource_xml->addChild('citation');
    if(!empty($resource['citation_id'])) {
      $citation_id = $citation_xml->addChild('identifier');
      $citation_id->addCData($resource['citation_id']);
      if(!empty($resource['citation_id_type']))
        $citation_id->addAttribute('identifierType',$resource['citation_id_type']);
    }
    add_cdata($citation_xml,'publicationName', @$resource['citation_pub']);
    add_cdata($citation_xml,'volume', @$resource['citation_volume']);
    add_cdata($citation_xml,'number', @$resource['citation_number']);
    add_cdata($citation_xml,'startingPage', @$resource['citation_page']);
	}
	// funding source
	for($i=0;$i<count($resource['funding_source']);$i++){
	  $funding_fields = array('award_number', 'award_amount', 'nsf_funding_program');
		$funding_xml = $resource_xml->addChild('funding');
		$funding_xml->addChild('fundingSource', $resource['funding_source'][$i]);
		add_child($funding_xml, 'fundingAwardNumber', @$resource['award_number'][$i]);
		//$funding_xml->addChild('fundingAwardNumber',$resource['award_number'][$i]);
		if(!empty($resource['award_amount'][$i]))
		  $funding_xml->addChild('fundingAwardAmount',str_replace(array(',','$'),'',$resource['award_amount'][$i]));
		
		if($resource['funding_source'][$i] == 'NSF' && $resource['nsf_funding_program'][$i] != "")
		  $funding_xml->addChild('fundingNSFProgram',$resource['nsf_funding_program'][$i]);
	}

	// begin and end date
	if($resource['begin_date'] != "") $resource_xml->addChild('beginDate',date('Y-m-d',strtotime($resource['begin_date'])));
	if($resource['end_date'] != "") $resource_xml->addChild('endDate',date('Y-m-d',strtotime($resource['end_date'])));

	// organization name
	$organization_xml = $resource_xml->addChild('contributor');
	$organization_xml->addChild('role','Contributor');
	$organization_xml->addChild('organizationName',$resource['lead_organization']);

	// add contributors
	for($i=0;$i<count($resource['contributor']);$i++){
		if($resource['contributor'][$i] != ""){
			$contributor_first_name = "";
			$contributor_last_name = "";
			$parts = explode(' ', $resource['contributor'][$i]);
			$tmp_contributor_name = array(implode(' ', array_slice($parts, 0, -1)), end($parts));
			if($tmp_contributor_name[0] != "") $contributor_first_name = $tmp_contributor_name[0];
			if($tmp_contributor_name[1] != "") $contributor_last_name = $tmp_contributor_name[1];
			$contributor_xml = $resource_xml->addChild('contributor');

			$contributor_xml->addChild('role',$resource['contributor_type'][$i]);
			$contributor_xml->addChild('personFirstName',$contributor_first_name);
			$contributor_xml->addChild('personLastName',$contributor_last_name);
			$contributor_xml->addChildCData('organizationName',$resource['contributor_org'][$i]);
			$contributor_xml->addChild('contributorId',$resource['contributor_id'][$i]);
		}
	}

	// add description
	$resource_xml->addChildCData('description',$resource['description']);
	
	
	foreach($resource['audience'] as $single_type){
		$resource_xml->addChildCData('audience',$single_type);
	}
	
	//$resource_xml->addChildCData('accessRights',$resource['access_rights']);
	add_cdata($resource_xml, 'accessRights', @$resource['access_rights']);
	
	foreach($resource['content'] as $single_type){
		$resource_xml->addChildCData('content',$single_type);
	}
	
	// add resourceType, projectResearchReferenceScope, audience, content
	foreach($resource['resource_type'] as $single_type){
		$resource_xml->addChildCData('resourceType',$single_type);
	}
	foreach($resource['instrument_type'] as $single_type){
		$resource_xml->addChildCData('resourceType',$single_type);	
	} 
	foreach($resource['env_type'] as $single_type){
		$resource_xml->addChildCData('projectResearchReferenceScope',$single_type);
	}

	/*
	// add mimeType, accessRights, resourceFormat
	$resource_xml->addChildCData('mimeType',$resource['mime_type']);
	$resource_xml->addChildCData('resourceFormat',$resource['resource_format']);
  */
  
	// add website urls
	foreach($resource['website_url'] as $single_url){
		if($single_url != ""){
			$website_xml = $resource_xml->addChild('relatedUrl');
			$website_xml->addCData($single_url);
			$website_xml->addAttribute('kind','Has part');
			$website_xml->addAttribute('type','Project Website');	
		}
	}

	// optional fields
	$optional_xml = $record_xml->addChild('optional');
	if(@$resource['zip_code']) {
	  $location_xml = $optional_xml->addChild('location');
	  $location_xml->addChild('zipCode',$resource['zip_code']);
	}
	
	$record_xml_string = rawurlencode($record_xml->saveXML());

	return $record_xml_string;

}

function add_child(&$parent, $Name, $value) {
  if($value)
    $parent->addChild($name, $value);
}

function add_cdata(&$parent, $name, $value) {
  if($value)
    $parent->addChildCData($name, $value);
}

function process_error($id,$url){
	require_once('/var/www/settings.php');
	$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

	$url = $db->real_escape_string($url);

	$insert_query = "INSERT INTO ncs_errors (holding_pen_id, error_url) VALUES ('{$id}','{$url}') ";

	$message = "Failure submitting to the NCS. ";
	if($db->query($insert_query)){
		$message.= "Error has been logged.";
	}
	else{
		$message.= "Error has not been logged";
	}

	return $message;

}

?>
