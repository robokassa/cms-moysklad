<?php

	if ($cfg['debug']) {
		$request = $_SERVER['REQUEST_URI'];
		$get = $_SERVER['QUERY_STRING'];
		$input = file_get_contents('php://input');
		$post = print_r($_POST, true);
		$headers = apache_request_headers();

		$log = date('d.m.Y H:i:s')."\n".$_SERVER['REQUEST_METHOD']." URL:".$request."\n-------------------\nGET: ".$get."\nPOST: ".$post."\nINPUT: ".$input."\n".print_r($headers, true)."\n======================\n";

		file_put_contents($log_fname, $log, FILE_APPEND);
	}