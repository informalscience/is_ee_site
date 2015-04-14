<?php	
// create current facets html
  $current_facets = array();
  $current_facets_html = "";
  $showFacet = false;
 
  foreach($existing_facets as $single_filter){
	$temp = explode("/text//record", $single_filter['value']);
	if( $single_filter['parameter'] == "qq" && strpos( $temp[0], "and" ) !== FALSE  ){
		$showFacet = true;
	}
	else if( $single_filter['parameter'] == "qq" && strpos( $temp[0], "and" ) === FALSE && strpos( $single_filter['value'], "/text//record" ) !== FALSE  ){
		$data = "";
	}
	else{
		$showFacet = true;
	}
  }
  
  if($showFacet){
	  $current_facets_html.= '<div class="projectSidebar left">';
	  $current_facets_html.= '<div class="sectionHeading">';
	  $current_facets_html.= '<span class="projectBrowseExpand">&nbsp;</span>';
	  $current_facets_html.= '<h2>Your Selections</h2>';
	  $current_facets_html.= '</div>';
	  $current_facets_html.= '<div class="projectInfo">';
	  $current_facets_html.= '<ul class="projectBrowseSelection">';
  }
  $facet_env_string = "";
  $facet_resource_string = "";
  $facet_audience_string = "";
  $facet_content_string = "";
  $facet_content_source_string = "";
  $facet_funding_string = "";
  $facet_keyword_string = "";
  $facet_other_string = "";
  
  foreach($existing_facets as $single_filter){
    // we only want to display facets that are not currently selected
	// Added a condition to avoid the filter by resources types be showed to the user 11-05-2014 aquintero
    //print_r($single_filter);
	//exit;
	
    if(!in_array($single_filter['value'],$current_facets)  ){
		
		// By default display the facet afquintero 18-10-2014
		$createFacet = true;
		$temp = explode("/text//record", $single_filter['value']);
		// Avoid to show the resource types linked to a project in the keyword panel afquintero 18-10-2014
		if( $single_filter['parameter'] == "qq" && strpos( $temp[0], "and" ) !== FALSE  ){
			$temp = explode("and", $single_filter['value']);
			$single_filter_value = str_replace(" ", "", $temp[0] );
		}
		else if( $single_filter['parameter'] == "qq" && strpos( $temp[0], "and" ) === FALSE && strpos( $single_filter['value'], "/text//record" ) !== FALSE  ){
			$createFacet = false;
		}
		else{
			$single_filter_value = $single_filter['value'];
		}
	  
		// Only create the facet when is not the actual resource type search afquintero 18-10-2014
	    if( $createFacet ){
		  $prefix = "";
		  $headers = array( "Media and Technology", "Public Educational Programs", "Professional Development, Conferences, and Networks",
                         	"Exhibitions", "K-12 and Higher Education Programs"	, "Research Products" , "Research and Evaluation Instruments",
							"Reference Materials"
						 );
		  if( in_array($single_filter_value, $headers) ){
			$prefix = "All " ;
		  }
		  
		  $single_filter_value = str_replace("\\", "", $single_filter_value);
		  if($single_filter['displayLabel'] != 'Keyword search'){
			if(strpos($single_filter_value,":")){
			 $temp_array = explode(":",$single_filter_value);
			 $single_filter_value = end($temp_array);
			}
		  }
		  $single_facet_html = "";
		  $single_facet_html.= '<li>';
		  $single_facet_html.= '<div class="filterLabel">'.$prefix . $single_filter_value.'</div>';
		  $single_facet_html.= '<a href="'.(($single_filter['displayLabel']) ? ($no_search_url) : ($the_search_url)).'search_url='.rawurlencode($single_filter['HREFremove']).'#topSearch">';
		  $single_facet_html.= '<span class="projectBrowseDelete">&nbsp;</span>';
		  $single_facet_html.= '</a>';
		  $single_facet_html.= '</li>';
		  if($single_filter['displayLabel'] == 'Project, Research and Reference Scope'){
			$facet_env_string.= $single_facet_html;
		  }
		  else if($single_filter['displayLabel'] == 'Resource Type'){
			$facet_resource_string.= $single_facet_html;
		  }
		  else if($single_filter['displayLabel'] == 'Audience'){
			$facet_audience_string.= $single_facet_html;
		  }
		  else if($single_filter['displayLabel'] == 'Content'){
			$facet_content_string.= $single_facet_html;
		  }
		  else if($single_filter['displayLabel'] == 'Record Type'){
			$facet_content_source_string.= $single_facet_html;
		  }
		  else if($single_filter['displayLabel'] == 'Funding Source'){
			$facet_funding_string.= $single_facet_html;
		  }
		  else if($single_filter['displayLabel'] == 'Keyword search'){
			$facet_keyword_string.= $single_facet_html;
		  }
		  else{
			$facet_other_string.= $single_facet_html;
		  }
		  $current_facets[] = $single_filter['value'];
	    }
    }
  }
  
  
  if($facet_env_string != ""){
    $current_facets_html.= '<li><span class="projectBrowseSelect">'.'Environment Type'.'</span></li>'.$facet_env_string; 
  }
  if($facet_resource_string != ""){
    $current_facets_html.= '<li><span class="projectBrowseSelect">'.'Resource Type'.'</span></li>'.$facet_resource_string; 
  }
  if($facet_audience_string != ""){
    $current_facets_html.= '<li><span class="projectBrowseSelect">'.'Audience'.'</span></li>'.$facet_audience_string; 
  }
  if($facet_content_string != ""){
    $current_facets_html.= '<li><span class="projectBrowseSelect">'.'Content'.'</span></li>'.$facet_content_string; 
  }
  if($facet_content_source_string != ""){
    $current_facets_html.= '<li><span class="projectBrowseSelect">'.'Content Source'.'</span></li>'.$facet_content_source_string;
  }
  if($facet_funding_string != ""){
    $current_facets_html.= '<li><span class="projectBrowseSelect">'.'Funding Source'.'</span></li>'.$facet_funding_string; 
  }
  if($facet_keyword_string != ""){
    $current_facets_html.= '<li><span class="projectBrowseSelect">'.'Keyword search'.'</span></li>'.$facet_keyword_string; 
  }
  if($facet_other_string != ""){
    $current_facets_html.= '<li><span class="projectBrowseSelect">'.'Other'.'</span></li>'.$facet_other_string; 
  }
  
  if($showFacet){
	$current_facets_html.= '</ul>';
	$current_facets_html.= '</div>';
	$current_facets_html.= '</div>';
  }
  $current_facets_html.= '<div class="clear"></div>';

// create environment type html
  $env_type_html = "";
  $env_type_html.= '<div class="projectSidebar left">';
  $env_type_html.= '<div class="sectionHeading">';
  $env_type_html.= '<span class="projectBrowseExpand projectBrowseCollapse">&nbsp;</span>';
  $env_type_html.= '<h2>Environment Type<a href="#" style="display: none;" class="tooltips" data-title="Need Text"><img class="formTooltip clearMarginTop" src="/images/uploads/home-tooltip.jpg" style="display: none;"></a></h2>';
  $env_type_html.= '</div>';
  $env_type_html.= '<div class="projectInfo" style="display: none;">';
  $env_type_html.= '<ul class="projectBrowseOption">';
  $env_type_array = array();
  if(is_array($env_type)){
    foreach($env_type as $single_filter){
      if(!in_array($single_filter['value'],$current_facets)){
        $tmp_array = array();
        $tmp_array['href'] = $single_filter['href'];
        $tmp_array['count'] = $single_filter['count'];
        $env_type_array[$single_filter['value']] = $tmp_array;
      }
    }    
  }

  ksort($env_type_array);
  $env_type_media = "";
  $env_type_public = "";
  $env_type_professional = "";
  $env_type_exhibitions = "";
  $env_type_k12 = "";
  $env_type_other = "";

  $parentHref = "";
  $parentCount = "";
  foreach($env_type_array as $key=>$single_filter){
    $single_filter_value = $key;
	$selected = false;
	
	if(strpos($single_filter_value,":") === FALSE && strpos($key,"Media and Technology") !== FALSE){
		$parentHref = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
		$parentCount = $single_filter['count'];
		$selected = true;
	}
	
	if(strpos($single_filter_value,":") === FALSE && strpos($key,"Public Educational Programs") !== FALSE){
		$parentHrefPublicEducational  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
		$parentCountPublicEducational = $single_filter['count'];
		$selected = true;
	}
	
	if(strpos($single_filter_value,":") === FALSE && strpos($key,"Professional Development, Conferences, and Networks") !== false){
		$parentHrefProfesionalDevelopment  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
		$parentCountProfesionalDevelopment = $single_filter['count'];
	}
	
	if(strpos($single_filter_value,":") === FALSE && strpos($key,"Exhibitions") !== false){
		$parentHrefExhibitions  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
		$parentCountExhibitions = $single_filter['count'];
		$selected = true;
	}
	
	if(strpos($single_filter_value,":") === FALSE && strpos($key,"K-12 and Higher Education Programs") !== false){
		$parentHrefK12  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
		$parentCountK12 = $single_filter['count'];
		$selected = true;
	}
	
	if($selected == false){
		if(strpos($single_filter_value,":")){
			$temp_array = explode(":",$single_filter_value);
			$single_filter_value = end($temp_array);
		}
		$single_env_type_html = "";
		$single_env_type_html.= '<li>';
		$single_env_type_html.= '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch">';
		$single_env_type_html.= '<p>';
		$single_env_type_html.= $single_filter_value;
		$single_env_type_html.= '<span class="browseNumber" >';
		$single_env_type_html.= '&nbsp;('.$single_filter['count'].')';
		$single_env_type_html.= '</span>';
		$single_env_type_html.= '</p>';
		$single_env_type_html.= '</a>';
		$single_env_type_html.= '</li>';
		
		if(strpos($key,"Media and Technology") !== false){
		  $env_type_media.= $single_env_type_html;
		}
		else if(strpos($key,"Public Educational Programs") !== false){
		  $env_type_public.= $single_env_type_html;
		}
		else if(strpos($key,"Professional Development, Conferences, and Networks") !== false){
		  $env_type_professional.= $single_env_type_html;
		}
		else if(strpos($key,"Exhibitions") !== false){
		  $env_type_exhibitions.= $single_env_type_html;
		}
		else if(strpos($key,"K-12 and Higher Education Programs") !== false){
		  $env_type_k12.= $single_env_type_html;
		}
		else{
		  $env_type_other.= $single_env_type_html;
		}
	}
  }

  if($env_type_media != ""){
	if( isset( $parentHref ) && $parentHref != ""){
		$env_type_html.= $parentHref.'<h3 class="resourceBrowseTitle">Media and Technology <span class="browseNumber">('. $parentCount . 
		')<span style="color: #007099"> show all </span> </span></h3></a>'.$env_type_media;
	}
	else{
		$env_type_html.= '<h3 class="resourceBrowseTitle">Media and Technology</h3>'.$env_type_media;
	}
  }
  if($env_type_public != ""){
	if( isset($parentHrefPublicEducational) && $parentHrefPublicEducational != ""){
		$env_type_html.= $parentHrefPublicEducational.'<h3 class="resourceBrowseTitle">Public Educational Programs<span class="browseNumber"> ('. $parentCountPublicEducational . ') </span> <span class="showAllSpan" > show all</span>  </h3></a>'.$env_type_public;
	}
	else{
		$env_type_html.= '<h3 class="resourceBrowseTitle">Public Educational Programs</h3>'.$env_type_public;
	}
  }
  if($env_type_professional != ""){
	if( isset($parentHrefProfesionalDevelopment) && $parentHrefProfesionalDevelopment != ""){
		$env_type_html.= $parentHrefProfesionalDevelopment.'<h3 class="resourceBrowseTitle">Professional Development, Conferences, and Networks <span class="browseNumber">('. $parentCountProfesionalDevelopment . ') </span> <span class="showAllSpan" style="margin-right:14px" > show all</span></h3></a>'.$env_type_professional;
	}
	else{
		$env_type_html.= '<h3 class="resourceBrowseTitle">Professional Development, Conferences, and Networks</h3>'.$env_type_professional;
	}
  }
  if($env_type_exhibitions != ""){
	if( isset($env_type_exhibitions) && $parentHrefExhibitions != ""){
		$env_type_html.= $parentHrefExhibitions.'<h3 class="resourceBrowseTitle">Exhibitions <span class="browseNumber">('. $parentCountExhibitions . ') <span style="color: #007099"> show all </span>  </span></h3></a>'.$env_type_exhibitions;
	}
	else{
		$env_type_html.= '<h3 class="resourceBrowseTitle">Exhibitions</h3>'.$env_type_exhibitions;
	}
  }
  if($env_type_k12 != ""){
	if( isset($parentHrefK12)  && $parentHrefK12 != ""){
		$env_type_html.= $parentHrefK12.'<h3 class="resourceBrowseTitle">K-12 and Higher Education Programs <span class="browseNumber">('. $parentCountK12 . ') <span style="color: #007099"> show all </span>  </span> </h3></a>'.$env_type_k12;
	}
	else{
		$env_type_html.= '<h3 class="resourceBrowseTitle">K-12 and Higher Education Programs</h3>'.$env_type_k12;
	}
  }
  if($env_type_other != ""){
    $env_type_html.= '<h3 class="resourceBrowseTitle">Other</h3>'.$env_type_other;
  }

  $env_type_html.= '</ul>';
  $env_type_html.= '</div>';
  $env_type_html.= '</div>';
  $env_type_html.= '<div class="clear"></div>';

// create resource type html
  if( $searchType != "projects" ){ 
	  $resource_type_html = "";
	  $resource_type_html.= '<div class="projectSidebar left">';
	  $resource_type_html.= '<div class="sectionHeading">';
	  $resource_type_html.= '<span class="projectBrowseExpand projectBrowseCollapse">&nbsp;</span>';
	  $resource_type_html.= '<h2>Resource Type<a href="#" style="display: none;" class="tooltips" data-title="Need Text"><img style="display: none;" class="formTooltip clearMarginTop" src="/images/uploads/home-tooltip.jpg"></a></h2>';
	  $resource_type_html.= '</div>';
	  $resource_type_html.= '<div class="projectInfo" style="display: none;">';
	  $resource_type_html.= '<ul class="projectBrowseOption">';
	  $resource_type_array = array();
	  if(is_array($resource_type)){
		foreach($resource_type as $single_filter){
		  // we only want to display facets that are not currently selected - we also do not want to show Project Descriptions
			if(!in_array($single_filter['value'],$current_facets)){
				$tmp_array = array();
				$tmp_array['href'] = $single_filter['href'];
				$tmp_array['count'] = $single_filter['count'];
				$resource_type_array[$single_filter['value']] = $tmp_array;
			}
			
		}    
	  }

	  ksort($resource_type_array);
	  $resource_type_evaluation = "";
	  $resource_type_instructional = "";
	  $resource_type_project = "";
	  $resource_type_reference = "";
	  $resource_type_case = "";
	  $resource_type_instruments = "";
	  $resource_type_research = "";
	  $resource_type_social = "";
	  $resource_type_other = "";
	 
	 
	  foreach($resource_type_array as $key=>$single_filter){
		///echo $key . "<br/>";
		$single_filter_value = $key;
		// Only allow three resources types aquintero 21-10-2014
		if( $searchType == "" ||
		    ( strpos($key,"Research Products")   !== false  &&  $searchType == "researchs"  ) || 
			( strpos($key,"Reference Materials") !== false  &&  $searchType == "researchs"  ) ||
			( strpos($key,"Research and Evaluation Instruments")  !==  false &&  $searchType == "researchs"  ) ||
			( strpos($key,"Evaluation Reports")  !==  false &&  $searchType == "evaluations"  )  ||
			( strpos($key,"Research and Evaluation Instruments")  !==  false &&  $searchType == "evaluations" ) 
			
		  ) 
		{
			if(strpos($single_filter_value,":") === FALSE && strpos($key,"Evaluation Reports") !== false){
				$parentHrefER  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
				$parentCountER = $single_filter['count'];
			}
			else if(strpos($single_filter_value,":") === FALSE && strpos($key,"Instructional Materials") !== false){
				$parentHrefIM  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
				$parentCountIM = $single_filter['count'];
			}
			else if(strpos($single_filter_value,":") === FALSE && strpos($key,"Project Descriptions") !== false){
				$parentHrefPD  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
				$parentCountPD = $single_filter['count'] ;
			}
			else if(strpos($single_filter_value,":") === FALSE && strpos($key,"Research Products") !== false){
				$parentHrefRP  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
				$parentCountRP = $single_filter['count'];
			}
			else if(strpos($single_filter_value,":") === FALSE && strpos($key,"Reference Materials") !== false){
				$parentHrefRM  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
				$parentCountRM = $single_filter['count'];
			}
			else if(strpos($single_filter_value,":") === FALSE && strpos($key,"Case Studies and Reviews") !== false){
				$parentHrefCS  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
				$parentCountCS = $single_filter['count'];
			}
			else if(strpos($single_filter_value,":") === FALSE && strpos($key,"Research and Evaluation Instruments") !== false){
				$parentHrefRE  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
				$parentCountRE = $single_filter['count'];
			}
			else if(strpos($single_filter_value,":") === FALSE && strpos($key,"Social Media and Online Communities") !== false){
				$parentHrefSM  = '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch" >';
				$parentCountSM = $single_filter['count'] ;
			}
			else{
				if(strpos($single_filter_value,":")){
				 $temp_array = explode(":",$single_filter_value);
				 $single_filter_value = end($temp_array);
				}
				$single_resource_type_html = "";
				$single_resource_type_html.= '<li>';
				$single_resource_type_html.= '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch">';
				$single_resource_type_html.= '<p>';
				$single_resource_type_html.= $single_filter_value;
				$single_resource_type_html.= '<span class="browseNumber">';
				$single_resource_type_html.= '&nbsp;('.$single_filter['count'].')';
				$single_resource_type_html.= '</span>';
				$single_resource_type_html.= '</p>';
				$single_resource_type_html.= '</a>';
				$single_resource_type_html.= '</li>';
				
				
				if(strpos($key,"Evaluation Reports") !== false){
				  $resource_type_evaluation.= $single_resource_type_html;
				}
				else if(strpos($key,"Instructional Materials") !== false){
				  $resource_type_instructional.= $single_resource_type_html;
				}
				else if(strpos($key,"Project Descriptions") !== false){
					echo "PD15";
					$resource_type_project.= $single_resource_type_html;
				}
				else if(strpos($key,"Research Products") !== false){
				  $resource_type_research.= $single_resource_type_html;
				}
				else if(strpos($key,"Reference Materials") !== false){
				  $resource_type_reference.= $single_resource_type_html;
				}
				else if(strpos($key,"Case Studies and Reviews") !== false){
				  $resource_type_case.= $single_resource_type_html;
				}
				else if(strpos($key,"Research and Evaluation Instruments") !== false){
				  $resource_type_instruments.= $single_resource_type_html;
				}
				else if(strpos($key,"Social Media and Online Communities") !== false){
				  $resource_type_social.= $single_resource_type_html;
				}
				else{
				  $resource_type_other.= $single_resource_type_html;
				}
			}
		}
	  }

	  if($resource_type_project != "" || (isset($parentHrefPD) && $parentHrefPD != "") ){
		if(  isset($parentHrefPD) && $parentHrefPD != ""){
			$resource_type_html.= $parentHrefPD.'<h3 class="resourceBrowseTitle">Project Descriptions <span class="browseNumber">('. $parentCountPD . ') <span style="color: #007099"> show all </span> </span></h3></a>'.$resource_type_project;
		}
		else{
			$resource_type_html.= '<h3 class="resourceBrowseTitle">Project Descriptions</h3>'.$resource_type_project;
		}
	  }
	  if($resource_type_evaluation != ""){
		if( isset($parentHrefER) &&  $parentHrefER != ""){
			$resource_type_html.= $parentHrefER.'<h3 class="resourceBrowseTitle">Evaluation Reports <span class="browseNumber">('. $parentCountER . ') <span style="color: #007099"> show all </span>  </span></h3></a>'.$resource_type_evaluation;
		}
		else{
			$resource_type_html.= '<h3 class="resourceBrowseTitle">Evaluation Reports</h3>'.$resource_type_evaluation;
		}
	  }
	  if($resource_type_research != ""){
		if( isset($parentHrefRP) && $parentHrefRP != ""){
			$resource_type_html.= $parentHrefRP.'<h3 class="resourceBrowseTitle">Research Products <span class="browseNumber">('. $parentCountRP . ') <span style="color: #007099"> show all </span>  </span></h3></a>'.$resource_type_research;
		}
		else{
			$resource_type_html.= '<h3 class="resourceBrowseTitle">Research Products</h3>'.$resource_type_research;
		}
	  }
	  if($resource_type_instruments != ""){
		if( isset($parentHrefRE) && $parentHrefRE!= ""){
			$resource_type_html.= $parentHrefRE.'<h3 class="resourceBrowseTitle">Research and Evaluation Instruments <span class="browseNumber">('. $parentCountRE . ') <span style="color: #007099"> show all </span>  </span></h3></a>'.$resource_type_instruments;
		}
		else{
			$resource_type_html.= '<h3 class="resourceBrowseTitle">Research and Evaluation Instruments</h3>'.$resource_type_instruments;
		}
	  }
	  
	  if($resource_type_reference != ""){
		if( isset($parentHrefRM) && $parentHrefRM != ""){
			$resource_type_html.= $parentHrefRM.'<h3 class="resourceBrowseTitle">Reference Materials <span class="browseNumber">('. $parentCountRM . ') <span style="color: #007099"> show all </span>  </span></h3></a>'.$resource_type_reference;
		}
		else{
			$resource_type_html.= '<h3 class="resourceBrowseTitle">Reference Materials</h3>'.$resource_type_reference;
		}
	  }
	  
	  if($resource_type_instructional != ""){
		if( isset($parentHrefIM) && $parentHrefIM != ""){
			$resource_type_html.= $parentHrefIM.'<h3 class="resourceBrowseTitle">Instructional Materials <span class="browseNumber">('. $parentCountIM . ') <span style="color: #007099"> show all </span>  </span></h3></a>'.$resource_type_instructional;
		}
		else{
			$resource_type_html.= '<h3 class="resourceBrowseTitle">Instructional Materials</h3>'.$resource_type_instructional;
		}
	  }
	  
	  
	  if($resource_type_case != ""){
		if( isset($parentHrefCS) && $parentHrefCS != ""){
			$resource_type_html.= $parentHrefCS.'<h3 class="resourceBrowseTitle">Case Studies and Reviews <span class="browseNumber">('. $parentCountCS . ') </span> <span class="showAllSpan" style="margin-right:21px" > show all</span> </h3></a>'.$resource_type_case;
		}
		else{
			$resource_type_html.= '<h3 class="resourceBrowseTitle">Case Studies and Reviews</h3>'.$resource_type_case;
		}
	  }
	  if($resource_type_social != ""){
		if(  isset($parentHrefSM) && $parentHrefSM != ""){
			$resource_type_html.= $parentHrefSM.'<h3 class="resourceBrowseTitle">Social Media and Online Communities <span class="browseNumber">('. $parentCountSM . ') <span style="color: #007099"> show all </span>  </span></h3></a>'.$resource_type_social;
		}
		else{
			$resource_type_html.= '<h3 class="resourceBrowseTitle">Social Media and Online Communities</h3>'.$resource_type_social;
		}
	  }
	  if($resource_type_other != ""){
		$resource_type_html.= '<h3 class="resourceBrowseTitle">Other</h3>'.$resource_type_other;
	  }

	  $resource_type_html.= '</ul>';
	  $resource_type_html.= '</div>';
	  $resource_type_html.= '</div>';
	  $resource_type_html.= '<div class="clear"></div>';
   }

// create audience html
  $audience_html = "";
  $audience_html.= '<div class="projectSidebar left">';
  $audience_html.= '<div class="sectionHeading">';
  $audience_html.= '<span class="projectBrowseExpand projectBrowseCollapse">&nbsp;</span>';
  $audience_html.= '<h2>Audience</h2>';
  $audience_html.= '</div>';
  $audience_html.= '<div class="projectInfo" style="display: none;">';
  $audience_html.= '<ul class="projectBrowseOption">';
  if(is_array($audience)){
    foreach($audience as $single_filter){
      // we only want to display facets that are not currently selected
      if(!in_array($single_filter['value'],$current_facets)){
        $audience_html.= '<li>';
        $audience_html.= '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch">';
        $audience_html.= '<p>';
        $audience_html.= $single_filter['value'];
        $audience_html.= '<span class="browseNumber">';
        $audience_html.= '&nbsp;('.$single_filter['count'].')';
        $audience_html.= '</span>';
        $audience_html.= '</p>';
        $audience_html.= '</a>';
        $audience_html.= '</li>';
      }
    }
  }
  $audience_html.= '</ul>';
  $audience_html.= '</div>';
  $audience_html.= '</div>';
  $audience_html.= '<div class="clear"></div>';

// create content html
  $content_html = "";
  $content_html.= '<div class="projectSidebar left">';
  $content_html.= '<div class="sectionHeading">';
  $content_html.= '<span class="projectBrowseExpand projectBrowseCollapse">&nbsp;</span>';
  $content_html.= '<h2>Content</h2>';
  $content_html.= '</div>';
  $content_html.= '<div class="projectInfo" style="display: none;">';
  $content_html.= '<ul class="projectBrowseOption">';
  if(is_array($content)){
    foreach($content as $single_filter){
      // we only want to display facets that are not currently selected
      if(!in_array($single_filter['value'],$current_facets)){
        $content_html.= '<li>';
        $content_html.= '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch">';
        $content_html.= '<p>';
        $content_html.= $single_filter['value'];
        $content_html.= '<span class="browseNumber">';
        $content_html.= '&nbsp;('.$single_filter['count'].')';
        $content_html.= '</span>';
        $content_html.= '</p>';
        $content_html.= '</a>';
        $content_html.= '</li>';
      }
    } 
  }
  $content_html.= '</ul>';
  $content_html.= '</div>';
  $content_html.= '</div>';
  $content_html.= '<div class="clear"></div>';

// create content source html

  // icon images for content source
	$content_source_images = array(
	  'CAISE' => 'caise.png',
	  'NISE' => 'nise.png',
	  'ASTC' => 'astc.png',
	  'informalscience.org' => 'informalscience.png',
	  'CitizenScience.org' => 'citsci.png',
	  'VSA' => 'vsa.png',
	  'NAME' => 'name.png',
	  'ATIS' => 'atis.png',
	  'Exhibitfiles' => 'exhibitfiles.png',
	  'KQED' => 'kqed.png',
	  'NATIVE' => 'native.png',
	  'Open Exhibits' => 'oe.png',
    'SMILE' => 'smile.png',
    'RR2P' => 'rr2p.png',
    'NIOST' => 'niost.png',
    'EBSCO' => 'ebscohost.png',
    'NASA' => 'NASA.png'
	  );

  $content_source_html = "";
  $content_source_html.= '<div class="projectSidebar left">';
  $content_source_html.= '<div class="sectionHeading">';
  $content_source_html.= '<span class="projectBrowseExpand projectBrowseCollapse">&nbsp;</span>';
  $content_source_html.= '<h2>Content Source</h2>';
  $content_source_html.= '</div>';
  $content_source_html.= '<div class="projectInfo" style="display: none;">';
  $content_source_html.= '<ul class="projectBrowseOption">';
  if(is_array($content_source)){
    foreach($content_source as $single_filter){
      // we only want to display facets that are not currently selected
      if(!in_array($single_filter['value'],$current_facets)){
        $content_source_html.= '<li>';
        $content_source_html.= '<img class="sourceIcon" height="20" width="20" alt="'.$single_filter['value'].'" src="{site_url}images/uploads/icons/'.$content_source_images[$single_filter['value']].'" />';
        $content_source_html.= '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch">';
        $content_source_html.= '<p>';
        $content_source_html.= $single_filter['value'];
        $content_source_html.= '<span class="browseNumber">';
        $content_source_html.= '&nbsp;('.$single_filter['count'].')';
        $content_source_html.= '</span>';
        $content_source_html.= '</p>';
        $content_source_html.= '</a>';
        $content_source_html.= '</li>';
      }
    } 
  }
  $content_source_html.= '</ul>';
  $content_source_html.= '</div>';
  $content_source_html.= '</div>';
  $content_source_html.= '<div class="clear"></div>';


// create funding_source html 2
  $funding_source_html = "";
  $funding_source_html.= '<div class="projectSidebar left">';
  $funding_source_html.= '<div class="sectionHeading">';
  $funding_source_html.= '<span class="projectBrowseExpand projectBrowseCollapse">&nbsp;</span>';
  $funding_source_html.= '<h2>Funding Source</h2>';
  $funding_source_html.= '</div>';
  $funding_source_html.= '<div class="projectInfo" style="display: none;">';
  $funding_source_html.= '<ul class="projectBrowseOption">';
  if(is_array($funding_source)){
    foreach($funding_source as $single_filter){
      // we only want to display facets that are not currently selected
      if(!in_array($single_filter['value'],$current_facets)){
        $funding_source_html.= '<li>';
        $funding_source_html.= '<a href="'.($the_search_url).'search_url='.rawurlencode($single_filter['href']).'#topSearch">';
        $funding_source_html.= '<p>';
        $funding_source_html.= $single_filter['value'];
        $funding_source_html.= '<span class="browseNumber">';
        $funding_source_html.= '&nbsp;('.$single_filter['count'].')';
        $funding_source_html.= '</span>';
        $funding_source_html.= '</p>';
        $funding_source_html.= '</a>';
        $funding_source_html.= '</li>';
      }
    }
  }
  $funding_source_html.= '</ul>';
  $funding_source_html.= '</div>';
  $funding_source_html.= '</div>';
  $funding_source_html.= '<div class="clear"></div>';

?>
