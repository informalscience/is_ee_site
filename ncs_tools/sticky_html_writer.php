<?php

function sticky_writer($ncs_results, $db_results, $check_string){
	$html = "";
	if(!isset($db_results[$check_string])) return "";
	
	foreach($db_results[$check_string] as $s){
		$html .= '<div class="resource-result">';
		$html .= '<div class="resource-result-img">';
		if($s["main_image"]){
			$html .= '<img src="http://informalscience.org/images/projects/logos/'.$s['main_image'].'">';
		}
		else{
			$html .= '<img src="/images/broaderimpacts/thumbnail.png">';	
		}
		$html .= '</div>';

		$html .= '<div class="resource-result-text">';

		$html .= '<h5><a href="'.$ncs_results[$s["ncs_id"]]["record_url"].'" target="blank">'.$ncs_results[$s["ncs_id"]]["title"].'</a></h5>';
		$html .= '<p>'.$ncs_results[$s["ncs_id"]]["description"].'</p>';
		$html .= '<h5><a href="'.$ncs_results[$s["ncs_id"]]["record_url"].'" target="blank" class="blueButton recordLink">RECORD LINK</a></h5>';
		$html .= '</div>';
		$html .= '</div>';		
	}

	return $html;
}

?>