<?php
	require('../../../wp-blog-header.php');
	global $user_email;
	if($_REQUEST['y_name'] && $_REQUEST['d_report']) {
		$name		=	$_REQUEST['y_name'];
		$email		=	$user_email;
		$url		=	get_bloginfo('url');
		$subject	=	"Report for WP Statistics";
		$body0		=	$_REQUEST['d_report'];
		$body		=	"Name: $name \n\n Email: $email \n\n Blog: $url \n\n Description Problem: $body0";
		$to			=	"mst404@gmail.com";
		$sender		=	get_option('blogname');
		$headers	=	"MIME-Version: 1.0\r\n";
		$headers	=	"Content-type: text/html; charset=utf-8\r\n";
		$headers	=	"From: $email \r\nX-Mailer: $sender";	
		mail($to, $subject, $body, $headers);
		echo __('Thanks for your report!', 'wp_statistics');
	} else {
		_e('Error! Please Enter all field', 'wp_statistics');
	}
?>