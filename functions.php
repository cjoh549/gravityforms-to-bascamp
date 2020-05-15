<?php

// Defines
define( 'FL_CHILD_THEME_DIR', get_stylesheet_directory() );
define( 'FL_CHILD_THEME_URL', get_stylesheet_directory_uri() );

add_action( 'gform_after_submission_27', 'process_banner_ad', 10, 2 );
function process_banner_ad( $entry, $form ) {

	$webhook_url = "https://hooks.zapier.com/hooks/catch/7552002/oi2lwai/";
	
	$data['title'] = $entry[2] . " - " . $entry[1];
	
	$body = "";
	$body .= "<b>Client:</b> {$entry[1]}<br>\n";
	$body .= "<b>Project:</b> {$entry[2]}<br><br>\n";
	$body .= "<b>Logo:</b> <img src='{$entry[6]}'><br><br>\n";
	$body .= "<b>Notes:</b> {$entry[7]}<br>\n";

	$data['body'] = $body;
	
	$data['assignee'] = $entry[5];
	
	$data['due_date'] = handle_date($entry[8], "-3 days");
	
	$todo_result = send_to_zapier($data, $webhook_url);
}  

function handle_date($date, $modify) {
	
	echo "Date: $date, $modify";

	$due_date = date_create($date);
	date_modify($due_date, $modify);
	
	// due date would be a on a Saturday, change to Friday
	if (date_format($due_date,"N") == "6")
		date_modify($due_date, "-1 day");
		
	// due date would be a on a Sunday, change to Friday
	if (date_format($due_date,"N") == "7")
		date_modify($due_date, "-2 days");
		
	// task was created on weekend with a 24 hours due date, 
	// so the due date is now in the past, set it to Monday
	if (date_format($date,"U") > date_format($due_date,"U"))	
		date_modify($due_date, "+3 days");
		
	echo date_format($due_date,"Y-m-d");
	
	return date_format($due_date,"Y-m-d");
}

function send_to_zapier($data, $url) {
	
	if (SEND_ZAPIER == TRUE) {
		$data = wp_remote_post($url, array(
			'headers'   => array('Content-Type' => 'application/json; charset=utf-8'),
			'body'      => json_encode($data),
			'method'    => 'POST'
		));
		
		display_debug($data, 1);
		
		return $data;
		
	} else {	
		return FALSE;
	}
}

function handle_serialized_strings($ser_str) {

	if ($ser_str == "") {
		return "";
	}
	
	$ser_arr = unserialize($ser_str);

	$txt = "";
	foreach ($ser_arr as $str) {
		$txt .= $str . "<br>\n";
	}
	
	return $txt;
}

function handle_uploads($file_handle, $label, $is_image = FALSE) {
	
	if ($file_handle == "" || $file_handle == "[]") 
		return "";
	
	$tmp_str = str_replace("[", "", $file_handle);
	$tmp_str = str_replace("]", "", $tmp_str);
	$tmp_str = str_replace('"', "", $tmp_str);
	$tmp_str = stripslashes($tmp_str);
	
	$file_arr = explode(",", $tmp_str);

	$text = "<b>$label: </b><br>\n";
	
	foreach ($file_arr as $file) {
		if ($is_image == TRUE) {
			$text .= "<a href='$file' target='_new'><img src='$file'></a><br>\n";
		}
		$text .= "<a href='$file' target='_new'>Download</a><br>\n";
	}
	
	$text .= "<br>\n";
	
	return $text;
}
