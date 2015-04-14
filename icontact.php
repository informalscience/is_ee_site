<?php

if(isset($_GET['email']) && isset($_GET['fname']) && isset($_GET['lname'])){
	$email = rawurldecode($_GET['email']);
	$fname = rawurldecode($_GET['fname']);
	$lname = rawurldecode($_GET['lname']);
	$url = 'https://app.icontact.com/icp/signup.php';

	$data = array(
		'listid' => '59261',
		'specialid:59261' => 'JKGJ',
		'clientid' => '207694',
		'formid' => '4511',
		'reallistid' => '1',
		'doubleopt' => '0',
		'fields_email' => $email,
		'fields_fname' => $fname,
		'fields_lname' => $lname
		);

	$fields = "";
	foreach($data as $key => $value){
		$fields.= $key.'='.$value.'&';
	}
	rtrim($fields,'&');

	$post = curl_init();
	curl_setopt($post, CURLOPT_URL, $url);
  curl_setopt($post, CURLOPT_POST, count($data));
  curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
  curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

  $result = curl_exec($post);

  curl_close($post);

}

?>