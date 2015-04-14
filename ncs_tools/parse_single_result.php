<?php

function parse_single_result($record){
	$record_array = array(
		'ncs_id'=>"",
		'title'=>"",
		'description'=>"",
		'short_description'=>"",
		'shorter_description'=>"",
		'content_source'=>"",
		'record_url'=>"",
		'begin_date'=>"",
		'end_date'=>"",
		'audience'=>array(),
		'audience_string'=>"",
		'content'=>array(),
		'content_string'=>"",
		'resource_type'=>array(),
		'resource_type_string'=>"",
		'env_type'=>array(),
		'env_type_string'=>"",
		'environmental_type'=>array(),
		'environmental_type_original'=>array(),
		'funding'=>array(),
		'funding_source_string'=>"",
		'funding_number_string'=>"",
		'funding_amount_string'=>"",
		'contributors'=>array(),
		'contributors_string'=>"",
		'pi'=>array(),
		'pi_string'=>"",
		'eval_author'=>array(),
		'eval_author_string'=>"",
		'organizations'=>array(),
		'organization_string'=>"",
		'organization_lead'=>"",
			'id'=>"",
			'id_type'=>"",
			'publication'=>"",
			'volume'=>"",
			'number'=>"",
			'page'=>"",
		'citation'=>array(
			'id'=>"",
			'id_type'=>"",
			'publication'=>"",
			'volume'=>"",
			'number'=>"",
			'page'=>""
			),
		'related_projects'=>array(),
		'related_websites'=>array(),
		'related_evaluation'=>array(),
		'related_products'=>array(),
		'related_casestudy'=>array(),
		'related_url'=>"",
		'ebsco'=>array(),
		'related_project_html'=>"",
		'related_evaluation_html'=>"",
		'related_research_html'=>"",
		'related_reference_html'=>"",
		'evaluation_reports_html'=>"",
		'project_products_html'=>"",
		'is_research'=>false,
		'is_reference'=>false,
		

	);

	// parse ncs results

	// ncs id
	if(isset($record['administrative']['recordID'])){
		$record_array['ncs_id'] = $record['administrative']['recordID']['values'][0]['value'];
	}     

	// title
	if(isset($record['resource']['title'])){
		$record_array['title'] = $record['resource']['title']['values'][0]['value'];
	}

	// description
	if(isset($record['resource']['description'])){
		$tmp_description = "";
		$tmper_description = "";
		$tmp_description = $record['resource']['description']['values'][0]['value'];
		$tmper_description = $tmp_description;
		$record_array['description'] = $tmp_description;
		if(strlen($tmp_description) > 340){
			$tmp_description = preg_replace('/\s+?(\S+)?$/', '', substr($tmp_description, 0, 341));
			$tmp_description.= ' [...]';
		}
		if(strlen($tmper_description) > 250){
			$tmper_description = preg_replace('/\s+?(\S+)?$/', '', substr($tmper_description, 0, 251));
			$tmper_description.= ' [...]';
		}
		$record_array['short_description'] = $tmp_description;
		$record_array['shorter_description'] = $tmper_description;
	}

	// content source
	if(isset($record['icSpecific']['icRecordType'])){
		$record_array['content_source'] = $record['icSpecific']['icRecordType']['values'][0]['value'];
	}

	// record url
	if(isset($record['icSpecific']['recordUrl'])){
		// $record_array['record_url'] = str_replace('informalscience.org','beta.informalscience.org',$record['icSpecific']['recordUrl']['values'][0]['value']);
		$record_array['record_url'] = $record['icSpecific']['recordUrl']['values'][0]['value'];
	}
	
	// begin date
	if(isset($record['resource']['beginDate'])){
		$tmp_begin_date = "";
		$tmp_begin_date = $record['resource']['beginDate']['values'][0]['value'];
		if($tmp_begin_date != "") $tmp_begin_date = date('m/d/Y',strtotime($tmp_begin_date));
		$record_array['begin_date'] = $tmp_begin_date;
	}

	// end date
	if(isset($record['resource']['endDate'])){
		$tmp_end_date = "";
		$tmp_end_date = $record['resource']['endDate']['values'][0]['value'];
		if($tmp_end_date != "") $tmp_end_date = date('m/d/Y',strtotime($tmp_end_date));
		$record_array['end_date'] = $tmp_end_date;
	}

	// audience | audience string
	if(isset($record['resource']['audience'])){
		foreach($record['resource']['audience']['values'] as $sv){
			$record_array['audience'][] = $sv['value'];
		}
		$record_array['audience_string'] = implode(" | ", $record_array['audience']);
	}

	// content | content string
	if(isset($record['resource']['content'])){
		foreach($record['resource']['content']['values'] as $sv){
			$record_array['content'][] = $sv['value'];
		}
		$record_array['content_string'] = implode(" | ", $record_array['content']);
	}

	// resource type | resource type string
	if(isset($record['resource']['resourceType'])){
		foreach($record['resource']['resourceType']['values'] as $sv){
			$record_array['resource_type'][] = $sv['value'];
			if(strpos($sv['value'],'Research Product') !== false) $record_array['is_research'] = true;
      if(strpos($sv['value'],'Reference Material') !== false) $record_array['is_reference'] = true;
      $filtered_values = array();
			$filtered_values[] = end(explode(":",$sv['value']));
		}
		$record_array['resource_type_string'] = implode(" | ",$filtered_values);
	}

	// env type | env type string
	if(isset($record['resource']['projectResearchReferenceScope'])){
		$record_array['env_type'] = $record['resource']['projectResearchReferenceScope']['values'];
		
		foreach($record_array['env_type'] as $sv){
			$record_array['env_type'][] = $sv['value'];
			$record_array['environment_type_original'][] = $sv['value'];
			$record_array['environment_type'][] = end(explode(":",$sv['value']));
		}

		$record_array['env_type_string'] = implode(" | ", $record_array['environment_type']);
	}


	// funding | funding source string | funding number string | funding amount string
	if(isset($record['resource']['funding'])){
		$record_array['funding'] = $record['resource']['funding']['values'];
	}
	if(count($record_array['funding']) > 0){
		$single_funding_source_array = array();
		$single_funding_number_array = array();
		$single_funding_amount_array = array();
		foreach($record_array['funding'] as $single_value){
			if(isset($single_value['fundingSource'])){
				$single_funding_source_array[] = $single_value['fundingSource']['values'][0]['value'];
			}
			if(isset($single_value['fundingAwardNumber'])){
				$single_funding_number_array[] = $single_value['fundingAwardNumber']['values'][0]['value'];
			}
			if(isset($single_value['fundingAwardAmount'])){
				$tmp_money = $single_value['fundingAwardAmount']['values'][0]['value'];
				if($tmp_money != "" && strpos($tmp_money,',') != true){
					$tmp_money = number_format($tmp_money);
				}
				$single_funding_amount_array[] = $tmp_money;
			}
		}
		$record_array['funding_source_string'] = implode(" | ",array_filter($single_funding_source_array));
		$record_array['funding_number_string'] = implode(" | ",array_filter($single_funding_number_array));
		$record_array['funding_amount_string'] = implode(" | ",array_filter($single_funding_amount_array));
	}

	$all_contributors = array();
	// contributor | contributor string | pi | pi string | eval author | eval author string | organizations | organizations string | organization lead
	if(isset($record['resource']['contributor'])){
		$all_contributors = $record['resource']['contributor']['values'];
	}
	if(count($all_contributors) > 0){
		
		$is_organization_set = false;
		foreach($all_contributors as $single_value){
			if(isset($single_value['role']['values'][0]['value'])){

				// set organization
				if(isset($single_value['organizationName'])){ 
					$record_array['organizations'][] = $single_value['organizationName']['values'][0]['value'];
					if(!$is_organization_set){
						$record_array['organization_lead'] = $single_value['organizationName']['values'][0]['value'];
						$is_organization_set = true;
					}
				}
				// set organization string
				$record_array['organization_string'] = implode(" | ",array_unique($record_array['organizations']));

				// set contributors
				if(isset($single_value['personFirstName']['values'][0]['value']) && isset($single_value['personLastName']['values'][0]['value']) && $single_value['personFirstName']['values'][0]['value'] != "" && $single_value['personLastName']['values'][0]['value'] != ""){
					$tmp_contributor_array = array('name'=>"",'org'=>"",'id'=>"",'role'=>"");
					$tmp_contributor_array['name'] = $single_value['personFirstName']['values'][0]['value']." ".$single_value['personLastName']['values'][0]['value'];
					
					$tmp_contributor_array['firstname'] = $single_value['personFirstName']['values'][0]['value'];
					$tmp_contributor_array['lastname'] = $single_value['personLastName']['values'][0]['value'];
					
					if(isset($single_value['organizationName']['values'][0]['value'])){
						$tmp_contributor_array['org'] = $single_value['organizationName']['values'][0]['value'];	
					}
					if(isset($single_value['contributorId']['values'][0]['value'])){
						$tmp_contributor_array['id'] = $single_value['contributorId']['values'][0]['value'];
					}
					if(isset($single_value['role']['values'][0]['value'])){
						$tmp_contributor_array['role'] = $single_value['role']['values'][0]['value'];
					}
					$record_array['contributors'][] = $tmp_contributor_array;
				}

			}
		}

		$tmp_contributor_array = array();
		foreach($record_array['contributors'] as $single_person){
			$tmp_person_string = "";
			$tmp_person_string = $single_person['name'];
			if($single_person['id'] != ""){
				$tmp_person_string = '<a href="/community/users/profile/'.$single_person['id'].'">'.$tmp_person_string.'</a>';
			}
			// pi
			if($single_person['role'] == 'Principal Investigator'){
				$record_array['pi_string'].= $tmp_person_string;
			}
			// evaluators and authors
			if($single_person['role'] == 'Evaluator' || $single_person['role'] == 'Author'){
				$record_array['eval_author_string'].= $tmp_person_string;
			}
			// all
			$tmp_person_string.= ' - '.$single_person['role'];
			$tmp_contributor_array[] = $tmp_person_string;
			// $record_array['contributors_string'].= $tmp_person_string;
		}
		$record_array['contributors_string'] = implode(' <br> ', $tmp_contributor_array);

	}

  // get citation data
  if(isset($record['resource']['citation'])){
    $my_citation = $record['resource']['citation'];
    if(isset($my_citation['identifier']['values'][0]['value'])){
    	$record_array['id'] = $my_citation['identifier']['values'][0]['value'];
      $record_array['citation']['id'] = $my_citation['identifier']['values'][0]['value'];
    }
    if(isset($my_citation['identifier']['values'][0]['attributes']['identifierType']['value'])){
    	$record_array['id_type'] = $my_citation['identifier']['values'][0]['attributes']['identifierType']['value'];
      $record_array['citation']['id_type'] = $my_citation['identifier']['values'][0]['attributes']['identifierType']['value'];
    }
    if(isset($my_citation['publicationName']['values'][0]['value'])){
    	$record_array['publication'] = $my_citation['publicationName']['values'][0]['value'];
      $record_array['citation']['publication'] = $my_citation['publicationName']['values'][0]['value'];
    }
    if(isset($my_citation['volume']['values'][0]['value'])){
    	$record_array['volume'] = $my_citation['volume']['values'][0]['value'];
      $record_array['citation']['volume'] = $my_citation['volume']['values'][0]['value'];
    }
    if(isset($my_citation['number']['values'][0]['value'])){
    	$record_array['number'] = $my_citation['number']['values'][0]['value'];
      $record_array['citation']['number'] = $my_citation['number']['values'][0]['value'];
    }
    if(isset($my_citation['startingPage']['values'][0]['value'])){
    	$record_array['page'] = $my_citation['startingPage']['values'][0]['value'];
      $record_array['citation']['page'] = $my_citation['startingPage']['values'][0]['value'];
    }
  }

  // relatedUrls
  if(isset($record['resource']['relatedUrl']['values'])){
    if(count($record['resource']['relatedUrl']['values']) > 0){
      foreach($record['resource']['relatedUrl']['values'] as $sv){
        if(isset($sv['attributes']['type'])){

        	$title = (isset($sv['attributes']['title'])) ? $sv['attributes']['title']['value'] : "";
        	$url = $sv['value'];

        	// related projects
        	if($sv['attributes']['type']['value'] == 'Project'){
	          $record_array['related_projects'][] = array('title'=>$title, 'url'=>$url);
        	}

					// related websites
        	if($sv['attributes']['type']['value'] == 'Project Website'){
        		$record_array['related_websites'][] = $url;
        	}

        	// related evaluation
					if($sv['attributes']['type']['value'] == 'Evaluation'){
	          $record_array['related_evaluation'][] = array('title'=>$title, 'url'=>$url);
        	}

        	// related products
        	if($sv['attributes']['type']['value'] == 'Research' || $sv['attributes']['type']['value'] == 'Reference' || $sv['attributes']['type']['value'] == 'Report'){
        		$record_array['related_products'][] = array('title'=>$title, 'url'=>$url);
        	}

        	// related case studies
					if($sv['attributes']['type']['value'] == 'Case Study'){
						if($title != ""){
							$record_array['related_casestudy'][] = array('title'=>$title, 'url'=>$url);	
						}
        	}

        	// related research link
        	if($sv['attributes']['type']['value'] == 'Research Link'){
        		$record_array['related_url'] = array('title'=>$title, 'url'=>$url);
        	}

        	// related ebsco full text
        	if($sv['attributes']['type']['value'] == 'EBSCO Full Text'){
        		$record_array['ebsco'][] = array('title'=>$title, 'url'=>$url);
        	}

        }
      }
    }
  }

  // project/reference/research related HTML (used on detail page)
  $record_array['related_project_html'] = generate_related_html("Projects", $record_array);
  $record_array['related_evaluation_html'] = generate_related_html("Evaluations", $record_array);
  $record_array['related_research_html'] = generate_related_html("Research", $record_array);
  $record_array['related_reference_html'] = generate_related_html("Reference", $record_array);

  $record_array['evaluation_reports_html'] = generate_project_products("evaluation", $record_array);
  $record_array['project_products_html'] = generate_project_products("products", $record_array);

	return $record_array;
}

function generate_related_html($type, $record_array){
  $record_types = array(
  	'environment_type_original'=>array('projectResearchReferenceScope[]','Environment Type'),
  	'resource_type'=>array('resourceType[]','Resource Type'),
  	'audience'=>array('audience','Audience'),
  	'content'=>array('content','Content')
	);

	$search = "";
	if($type == "Projects") $search = "projects";
	else if($type == "Evaluations") $search = "evaluation";
	else $search = "research";

  $related_base_url = '/'.$search.'/browse?type='.strtolower($type).'&search_url=';
  $related_html = '<div class="projectSidebar left sidebarTags">';
  $related_html.= '<div class="sectionHeading"><h2>Find Similar Resources</h2></div>';
  $related_html.= '<ul class="tagLinks">';
  foreach($record_types as $k=>$v){
  	if(isset($record_array[$k])){
	  	$related_html.= "<li class='audienceTop'>".$v[1]."</li>";
	  	foreach($record_array[$k] as $sv){
	  		$shown_text = end(explode(":",$sv));
	  		$related_html.= '<li><a href="'.$related_base_url.rawurlencode(API_URL.'search/json?').
	      rawurlencode($v[0].'='.str_replace(' ', '+', $sv)).'">'.$shown_text.'</a></li>';
	  	}  		
  	}
  }
  $related_html.= '</ul>';
  $related_html.= '</div>';

  return $related_html;	
}

function generate_project_products($type, $record_array){
	if($type == "evaluation"){
		$index = "related_evaluation";
		$title = "Evaluation Reports";
	}
	else if($type == "products"){
		$index = "related_products";
		$title = "Project Products";
	}
	else{
		return;
	}

	$return_html = '<div class="projectDetailContent" style="margin-top:0">';
	$return_html.= '<div class="sectionHeading"><h2>'.$title.'</h2></div>';
  $return_html.= '<ul class="projectLinks">';
  if(count($record_array[$index]) > 0){
  	for($i=0; $i<count($record_array[$index]); $i++){
  		$return_html.= '<li'.(($i < count($record_array[$index])-1) ? '':' class="last"').'>';
	  	$return_html.= '<span class="workIcon"><img src="/images/uploads/pdf-icon.png" /></span>';
  		$return_html.= '<a href="'.$record_array[$index][$i]['url'].'">';
  		if($record_array[$index][$i]['title'] != ""){
  			$return_html.= $record_array[$index][$i]['title'];
  		}
  		else{
  			$return_html.= $record_array[$index][$i]['url'];
  		}
  		$return_html.= '</a>';
			$return_html.= '</li>';
  	}
  }
  $return_html.= '</ul>';
  $return_html.= '</div>';
  if(count($record_array[$index]) == 0) $return_html = "";

  return $return_html;

}




?>