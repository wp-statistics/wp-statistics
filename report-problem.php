<?php
	include_once('../../../wp-load.php');

	if(is_super_admin()) {
		global $user_email;
		if($_REQUEST['y_name'] && $_REQUEST['d_report']) {
			$name		=	$_REQUEST['y_name'];
			$email		=	$user_email;
			$url		=	get_bloginfo('url');
			$subject	=	__('Report a problem for WP Statistics', 'wp_statistics');
			$report	=	$_REQUEST['d_report'];
			$body		=	"Name: $name \n\n Email: $email \n\n Blog: $url \n\n Description Problem: $report";
			$to			=	"mst404@gmail.com";
			$sender		=	get_option('blogname');
			$headers	=	"MIME-Version: 1.0\r\n";
			$headers	=	"Content-type: text/html; charset=utf-8\r\n";
			$headers	=	"From: $email \r\nX-Mailer: $sender";

			if(wp_mail($to, $subject, $body, $headers)) {
				_e('Thanks for your report!', 'wp_statistics');
			} else {
				_e('An error has occurred!', 'wp_statistics');
			}
		} else {
			_e('Error! Please Enter all field', 'wp_statistics');
		}
	} else {
		wp_die(__('Access is Denied!', 'wp_statistics'));
	}
?>