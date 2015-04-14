<?php

// parse results
  $facets = $search_results['facets'];
  $existing_facets = $search_results['existingFilters'];

  $env_type = array();
  $resource_type = array();
  $audience = array();
  $content = array();
  $content_source = array();
  $funding_source = array();
  $results = array();
  $current_sorting = array();
  $sorting = array();

  if(isset($facets['projectResearchReferenceScope']['result'])){
    $env_type = $facets['projectResearchReferenceScope']['result'];
  }
  if(isset($facets['resourceType']['result'])){
    $resource_type = $facets['resourceType']['result'];
  }
  if(isset($facets['audience']['result'])){
    $audience = $facets['audience']['result'];
  }
  if(isset($facets['content']['result'])){
    $content = $facets['content']['result'];
  }
  if(isset($facets['icRecordType']['result'])){
    $content_source = $facets['icRecordType']['result'];
  }
  if(isset($facets['fundingSource']['result'])){
    $funding_source = $facets['fundingSource']['result'];
  }
  if(isset($search_results['results'])){
    $results = $search_results['results'];
  }
  if(isset($search_results['currentSorting'])){
    $current_sorting = $search_results['currentSorting'];
  }
  if(isset($search_results['sorting'])){
    $sorting = $search_results['sorting'];
  }


?>