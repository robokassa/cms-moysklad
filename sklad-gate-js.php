<?
	ob_start();

	session_set_cookie_params(['SameSite' => 'None', 'secure' => true]);

	include 'prolog.php';

	$log_fname = 'logs/sklad-gate-js.txt';
	include 'grabber.php';

	$v_account = empty($_SESSION['account']) ? $_POST['account'] : $_SESSION['account'];

	switch($_POST['act']) {
		case 'check_link_for_invoice':
			$st = $db->prepare('SELECT id FROM links WHERE invoice_id=:invoice_id ORDER BY ts DESC');
			$st->execute([':invoice_id' => $_POST['id_invoice']]);

			if (($row = $st->fetch()) !== false) {
				$out['link'] = $cfg['link_base_url'].$row['id'];
				$out['result'] = true;
			} else {
				$out['result'] = false;
			}

			break;
		case 'generate_link':
//			file_put_contents('session.txt', $_POST['id_invoice']."\n".print_r($_SESSION, true)."\n---\n", FILE_APPEND);
			$sklad = new Sklad($v_account);
			$link = $sklad->generate_link($_POST['id_invoice']);

			$out['link'] = $link;

			if (!$link) {
				$out['result'] = false;
			} else {
				$out['link'] = $cfg['link_base_url'].$link;
				$out['result'] = true;
			}

			break;
		case 'send_link':
			$st = $db->prepare('SELECT id FROM links WHERE invoice_id=:invoice_id ORDER BY ts DESC');
			$st->execute([':invoice_id' => $_POST['id_invoice']]);

			$sklad = new Sklad($v_account);
			if (($row = $st->fetch()) !== false) {
				$res = $sklad->send_link($_POST['id_invoice']);

				if (!$res) {
					$out['result'] = false;
				} else {
					$out['result'] = true;
				}
			} else {

				$out['text'] = 'Ссылки нет';
				$out['error'] = true;
				$out['result'] = true;
			}

			break;
		case 'second_check':
			$st = $db->prepare('SELECT id FROM links WHERE invoice_id=:invoice_id ORDER BY ts DESC');
			$st->execute([':invoice_id' => $_POST['id_invoice']]);

			if (($row = $st->fetch()) !== false) {
				$sklad = new Sklad($v_account);
				$res = $sklad->send_second_check($_POST['id_invoice']);

				if (!$res || !$res['result']) {
					$out['text']	= $res['text'];
					$out['error']	= true;
					$out['result']	= false;
				} else {
					$out['text']	= 'Чек выслан';
					$out['result']	= true;
				}
			} else {
				$out['text']	= 'Ссылки нет';
				$out['error']	= true;
				$out['result']	= false;
			}

			break;
	}

	$stdout = ob_get_contents();
	ob_end_clean();
	if (!empty($stdout))
		file_put_contents('stdout-gate-js.txt', $stdout, FILE_APPEND);

	Header('Content-type: application/json');
	echo json_encode($out);
