<?php

function generate_add_yourself_string($parsed_record){
	
	// start contributors
	  $record_add_yourself_string = "";
	  if(count($parsed_record['contributors']) > 0){
	    foreach($parsed_record['contributors'] as $single_person){
	      if($single_person['id'] == ""){
	        $record_add_yourself_string.= '<div>';
	        $record_add_yourself_string.= '<input type="radio" title="'.$single_person['role'].'" name="name" value="'.$single_person['name'].'"/>';
	        $record_add_yourself_string.= '<span>'.$single_person['name'].'</span>';
	        $record_add_yourself_string.= '<input type="hidden" class="person_organization" value="'.$single_person['org'].'" />';
	        $record_add_yourself_string.= '</div>';
	      }
	    }
	  }

	// create add yourself html
	  $add_yourself_html = "";
	  $add_yourself_html.= '<div id="add_'.$parsed_record['ncs_id'].'" class="projectOptionsAddY" style="display:none">';
	  $add_yourself_html.= '<form class="form_add_yourself" method="post">';
	  $add_yourself_html.= '<div class="projectOptionsIntContainer" style="width: 840px;">';
	  $add_yourself_html.= '<p class="clearMarginTop">Is this your work? Follow these steps to connect your profile to this resource.</p>';
	  $add_yourself_html.= '<div class="addLeftCol">';
	  $add_yourself_html.= '<h5>Role:</h5>';
	  $add_yourself_html.= '<ul class="addYourself">';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Principal Investigator" />Principal Investigator</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Co-Principal Investigator" />Co-Principal Investigator</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Contributor" />Contributor</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Evaluator" />Evaluator</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Former Principal Investigator" />Former Principal Investigator</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Former Co-Principal Investigator" />Former Co-Principal Investigator</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Author" />Author</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Project Manager" />Project Manager</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Contact" />Contact</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Publisher" />Publisher</label></li>';
	  $add_yourself_html.= '<li><label class="projectOptionsRadioBut"><input type="radio" class="validate[required] projectRadio" name="role" value="Editor" />Editor</label></li>';
	  $add_yourself_html.= '</ul>';
	  $add_yourself_html.= '</div>';
	  $add_yourself_html.= '<h5>Organization:</h5><br/><input class="evalOrgField validate[required]" type="text" name="organization" />';
	  $add_yourself_html.= '<div class="associateYourself">';   
	  $add_yourself_html.= '<div class="existing_names left">';
	  $add_yourself_html.= '<h5>Associate Your Profile with an Existing Team Member:</h5>';
	  $add_yourself_html.= '<div class="projectConnectInfo">';
	  $add_yourself_html.= '<p>For NSF funded resources, some team member names have been automatically added from FASTLANE abstracts. Select your name and press "ADD" to connect your user profile.</p>';
	  $add_yourself_html.= '<p>If your name does not currently appear on this resource, when you press "ADD", your name will be connected as it appears on your profile. All changes are subject to approval before being displayed.</p>';
	  $add_yourself_html.= '</div>';
	  $add_yourself_html.= '<ul class="addYourself" style="width: 170px;"> ';
	  $add_yourself_html.= $record_add_yourself_string;
	  $add_yourself_html.= '</ul>';
	  $add_yourself_html.= '</div>';
	  $add_yourself_html.= '<input type="hidden" readonly="readonly" name="current_ncs_id" value="'.$parsed_record['ncs_id'].'" />';
	  $add_yourself_html.= '<input type="hidden" readonly="readonly" name="current_user" value="{screen_name}" />';
	  $add_yourself_html.= '<input type="hidden" readonly="readonly" name="current_user_id" value="{member_id}" />';
	  $add_yourself_html.= '<!-- SUBMITTER FIELDS -->';
	  $add_yourself_html.= '<input readonly="readonly" type="hidden" name="submitter_name" value="{screen_name}"/>';
	  $add_yourself_html.= '<input readonly="readonly" type="hidden" name="submitter_email" value="{email}"/>';
	  $add_yourself_html.= '<input readonly="readonly" type="hidden" name="submitter_id" value="{member_id}"/>';
	  $add_yourself_html.= '<!-- END SUBMITTER FIELDS -->';
	  $add_yourself_html.= '<div class="projectOptionsBut">';
	  $add_yourself_html.= '<input type="submit" name="submit_add_yourself" value="Add" class="blueButtonPlain marginTop" />';
	  $add_yourself_html.= '<a class="reset_me blueButtonPlain marginTop" />Deselect</a>';
	  $add_yourself_html.= '</div>';
	  $add_yourself_html.= '</div>';
	  $add_yourself_html.= '</div>';
	  $add_yourself_html.= '</form>';
	  $add_yourself_html.= '</div>';

	return $add_yourself_html;
	
}

?>