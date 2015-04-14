<?php

require_once('/var/www/settings.php');
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset("utf8");

function get_info_about_member($db, $member_id, $group_id){

  	$get_member_query =
  		"SELECT groups_members.member_id, exp_members.screen_name, exp_channel_data.field_id_94, 
			exp_channel_data.field_id_107, exp_matrix_data.col_id_5, exp_matrix_data.field_id, exp_matrix_data.row_order
			FROM groups_members 
			LEFT JOIN exp_members ON exp_members.member_id=groups_members.member_id 
			LEFT JOIN exp_channel_titles ON exp_channel_titles.author_id=groups_members.member_id
			LEFT JOIN exp_channel_data ON exp_channel_titles.entry_id=exp_channel_data.entry_id
			LEFT JOIN exp_matrix_data ON exp_matrix_data.entry_id=exp_channel_data.entry_id 
			WHERE groups_members.group_id = '{$group_id}' 
			AND groups_members.member_id = '{$member_id}'
			AND exp_channel_titles.channel_id=38";

		$member = array('id'=>"",'name'=>"",'title'=>"",'img'=>"",'org'=>"");
		$member_results = $db->query($get_member_query);
		if($member_results->num_rows){
			foreach($member_results->result_array() as $row){
				$member['id'] = $row['member_id'];
				$member['name'] = $row['screen_name'];
				$member['title'] = $row['field_id_94'];
				$member['img'] = $row['field_id_107'];
				if($row['col_id_5'] && $row['row_order'] == 1 && $row['field_id'] == 95) $member['org'] = $row['col_id_5'];
			}
		}

		return $member;
}

function make_member_html_on_group_details_page($member){
  $member_html = '<div class="single_member currentMEntry">';
  $member_html.= '<div class="memberDetailInterior">';
  if($member['img'] == ""){
  	$member_html.= '<img width="60" height="60" class="left" src="/images/uploads/list-no-image-default-lg.png" alt="'.$member['name'].'" />';
  }
  else{
  	$member_html.= '<img width="60" height="60" class="left" src="/images/member_profile_pics/small/'.$member['img'].'" alt="'.$member['name'].'" />';	
  }
  $member_html.= '<div class="currentMemberDetails left">';    
  $member_html.= '<p><a href="{path=community/users/profile}/'.$member['id'].'">'.$member['name'].'</a></p>';
  $member_html.= '<p class=" ellipsisMaker">'.$member['title'].'</p>';
  $member_html.= '<p class="institutionText ellipsisMaker"><em>'.$member['org'].'</em></p>';
  $member_html.= '</div>';
  $member_html.= '<div class="right">';
  $member_html.= '<input readonly="readonly" type="hidden" class="single_member_id" value="'.$member['id'].'" />';
  $member_html.= '</div><!-- 1 -->';
  $member_html.= '</div><!-- 2 -->';
  $member_html.= '</div><!-- 3 -->';

  return $member_html;
}

function make_member_html_on_group_member_page($member, $is_admin){
	$member_html = "";
	$member_html.= '<div class="single_member">';
	if($member['img'] == ""){
		$member_html.= '<img width="60" height="60" class="left bottomMargin" src="/images/uploads/list-no-image-default-lg.png" alt="'.$member['name'].'" />';
	}
	else{
		$member_html.= '<img width="60" height="60" class="left bottomMargin" src="/images/member_profile_pics/small/'.$member['img'].'" alt="'.$member['name'].'" />';	
	}      
	$member_html.= '<div class="memberEntry left">';
	$member_html.= '<p><a href="/community/users/profile/'.$member['id'].'">'.$member['name'].'</a></p>';
	$member_html.= '<p class="ellipsisMaker">'.$member['title'].'</p>';
	$member_html.= '<p><em>'.$member['org'].'</em></p>';
	$member_html.= '</div>';
	$member_html.= '<div class="approvals left">';
	if($is_admin) $member_html.= '<a href="#" class="rejectMember admin_approval_button" id="delete">Remove</a>';
	$member_html.= '<input readonly="readonly" type="hidden" class="single_member_id" value="'.$member['id'].'" />';
	$member_html.= '</div>';
	$member_html.= '<a style="margin-top: 10px;" class="blueButton right" href="/community/users/profile/'.$member['id'].'">View Profile</a>';
	$member_html.= '</div>';
	$member_html.= '<div class="clear"></div>';

	return $member_html;
}

function make_document_html_on_group_documents_page($document){
	$documents_html = "";
	$documents_html.= '<div>';
  $documents_html.= '<div class="documentList">';
  $documents_html.= '<a href="/documents/group_documents/'.rawurldecode($document['document_url']).'">'.$document['document_name'].'</a>';
  $documents_html.= '</div>';
  $documents_html.= '<div class="documentListActions">';
  $documents_html.= '<a class="downloadDocument" target="_blank" href="/documents/group_documents/'.rawurldecode($document['document_url']).'">Download</a>';
  $documents_html.= '<a id="'.$document['id'].'" href="#" class="remove_doc removeAddLink">Remove</a>';
  $documents_html.= '<input type="hidden" readonly="readonly" class="document_secret" value="'.$document['document_secret'].'" />';
  $documents_html.= '<div class="removeMessage"></div>';
  $documents_html.= '</div>';
  $documents_html.= '<div class="clear"></div>';
	$documents_html.= '</div>';

  return $documents_html;
}

function generate_error_message($msg){
	return "<div class='form_error'>$msg</div>";
}

function generate_success_message($msg){
	return "<div class='form_success'><span>Success!</span> $msg</div>";
}