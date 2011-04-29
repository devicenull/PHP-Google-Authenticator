<?php
	require_once('googleauth.php');
	
	/*
	*	You can use this information to generate codes for the example:
	*	
	*	https://www.google.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth://totp/root@localhost.localdomain%3Fsecret%3D7BRJU34GPOWBEQOF
	*	Your new secret key is: 7BRJU34GPOWBEQOF
	*/
	
	$ga = new GoogleAuth();
	
	$secretkey = '7BRJU34GPOWBEQOF';
	$currentcode = '143250';
	
	if ($ga->checkCode($secretkey,$currentcode))
	{
		echo "Code is valid\n";
	}
	else
	{
		echo "Invalid code\n";
	}