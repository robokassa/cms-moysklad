<?
	include 'prolog.php';

	$log_fname = 'logs/robo-result.txt';
	include 'grabber.php';

	switch($_GET['a']) {
		case 'success':
			echo 'Спасибо за оплату';
			break;
		case 'fail':
			echo 'Во время оплаты произошла ошибка. Попробуйте повторить';
			break;
		case 'result':
			echo "OK".$_POST['InvId'];

			$code = filter_input(INPUT_POST, 'shp_id', FILTER_SANITIZE_STRING);
			$sklad = new Sklad();

			$st = $sklad->db->prepare('SELECT links.data, users.sklad_account_id FROM links, users WHERE links.id=:id AND links.id_user=users.id LIMIT 1');
			$st->execute([':id' => $code]);
			$row = $st->fetch();
			if ($row === false)
				break;

			$data = json_decode($row['data'], true);

			$sklad = new Sklad($row['sklad_account_id']);
			$sklad->set_invoice_paid($data['invoice']['id']);

			break;
		default:

	}

