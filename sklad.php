<?
	ob_start();

	include 'prolog.php';

	$token_body = [
        "sub" => 'robokassa-1.robokassa',
        "iat" => time(),
        "exp" => time() + 300,
        "jti" => bin2hex(random_bytes(32))
    ];

	$jwt = JWT::encode($token_body, $cfg['app']['secret']);

	header("Content-Type: application/json");
	header("Authorization: Bearer ".$jwt);

	$log_fname = 'logs/sklad.txt';
	include 'grabber.php';

	$request = $_SERVER['REQUEST_URI'];
	$input = json_decode(file_get_contents('php://input'), true);

	switch($input['cause']) {
		case 'Install':
			$sklad = new Sklad();
			$sklad->add_account($input, $request, false);

			$ret['status'] = 'Activated';

			break;
		case 'Uninstall':
			$sklad = new Sklad();
			$sklad->drop_account($input);

			$ret = '';

			break;
		default:
			$ret['status'] = 'Activated';
	}

	$stdout = ob_get_contents();
	ob_end_clean();
	if (!empty($stdout))
		file_put_contents('logs/stdout_sklad.txt', $stdout, FILE_APPEND);

	echo json_encode($ret);