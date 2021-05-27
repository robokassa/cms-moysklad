<?
	include 'prolog.php';

	$log_fname = 'logs/payform.txt';
	include 'grabber.php';

//	$code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_EMAIL);

	$request = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
	$code = end($request);

	$sklad = new Sklad();
	$st = $sklad->db->prepare('SELECT users.robo_shop, users.robo_crc, users.robo_fisk, users.robo_key_1, users.robo_key_2, users.robo_key_test_1, users.robo_key_test_2, links.data, users.robo_country, users.robo_sn FROM users, links WHERE links.id=:id AND users.id=links.id_user LIMIT 1');
	$st->execute([':id' => $code]);

	$row = $st->fetch();
	if (!$row) {
//		die('Ссылка устарела');
		die('Возникла ошибка записи cookie - обратитесь в Тех.поддержку Робокассы');
	}

	$data = json_decode($row['data'], true);

	if ($data['test']) {
		$pass_1 = $row['robo_key_test_1'];
		$is_test = 1;
	} else {
		$pass_1 = $row['robo_key_1'];
		$is_test = 0;
	}

//	$receipt['sno'] = 'osn';

	$receipt['sno'] = $row['robo_sn'];
	$receipt['payment_method'] = $row['robo_pmethod'];
	$receipt['payment_object'] = $row['robo_pobject'];
//	$receipt['tax'] = $row['robo_vat'];

	foreach($data['invoice']['items'] as $item) {
		if ($data['invoice']['vatEnabled']) {
			$tax = 'vat'.$item['vat'];
		} else {
			$tax = 'none';
		}

		switch($item['type']) {
			case 'SERVICE':
				$item_pobject = 'service';

				break;
			case 'GOOD':
				$item_pobject = 'commodity';

				break;
			default:
				$item_pobject = '';
		}

		$ipars = [	'name' => $item['name'],
					'quantity' => $item['quantity'],
					'sum' => $item['quantity'] * $item['price'] * (1 - $item['discount'] / 100),
					'tax' => $tax,
					'payment_object' => $item_pobject,
				];

		if (!empty($item['nomenclature_code']))
			$ipars['nomenclature_code'] = $item['nomenclature_code'];
		$arrReceipt['items'][] = $ipars;
	}

//	file_put_contents('arrReceipt', print_r($arrReceipt, true));

	if (intval($data['invoice']['name']) == $data['invoice']['name']) {
		$v_inv_id = intval($data['invoice']['name']);
		$v_inv_text = $data['invoice']['name'];
	} else {
		$v_inv_id = 0;
		$v_inv_text = $data['invoice']['name'];
	}

	$receipt = urlencode(json_encode($arrReceipt));

	if ($row['robo_fisk'] && $row['robo_country'] == 'russia') {
		$src_sign = $row['robo_shop'].':'.$data['invoice']['total'].':'.$v_inv_id.($row['robo_fisk'] ? ':'.$receipt : '').':'.$pass_1.':shp_id='.$code.':shp_msid='.rawurlencode($v_inv_text);
	} else {
		$src_sign = $row['robo_shop'].':'.$data['invoice']['total'].':'.$v_inv_id.':'.$pass_1.':shp_id='.$code.':shp_msid='.rawurlencode($v_inv_text);
	}

	switch($row['robo_crc']) {
		case 'MD5':
			$sign = md5($src_sign);
			break;
		case 'RIPEMD160':
			$sign = hash('ripemd160', $src_sign);
			break;
		case 'SHA1':
			$sign = hash('sha1', $src_sign);
			break;
		case 'SHA256':
			$sign = hash('sha256', $src_sign);
			break;
		case 'SHA384':
			$sign = hash('sha384', $src_sign);
			break;
		case 'SHA512':
			$sign = hash('sha512', $src_sign);
			break;
	}
?>
<html lang='ru'>
<head>
	<title>Форма оплаты счета <?= $v_inv_text ?> от <?= date('d.m.Y', strtotime($data['invoice']['ts'])) ?> через Робокасса</title>
</head>
<body onload="document.forms[0].submit()">
	<form action='https://auth.robokassa.ru/Merchant/Index.aspx' method="POST">
		<input type="hidden" name="MerchantLogin" value="<?= $row['robo_shop'] ?>">
		<input type="hidden" name="OutSum" value="<?= $data['invoice']['total'] ?>">
		<input type="hidden" name="InvId" value="<?= $v_inv_id ?>">
		<input type="hidden" name="Description" value="Оплата счета <?= $v_inv_text ?> от <?= date('d.m.Y', strtotime($data['invoice']['ts'])) ?>">
		<input type="hidden" name="SignatureValue" value="<?= $sign ?>">
		<input type="hidden" name="isTest" value="<?= $is_test ?>">
		<input type="hidden" name="shp_id" value="<?= $code ?>">
		<input type="hidden" name="shp_msid" value="<?= $v_inv_text ?>">
		<? if (!empty($data['email'])): ?>
			<input type="hidden" name="Email" value="<?= $data['email'] ?>">
		<? endif; ?>

		<? if ($row['robo_fisk'] && $row['robo_country'] == 'russia'): ?>
			<input type="hidden" name="Receipt" value="<?= $receipt ?>">
		<? endif; ?>

		<input type="submit" value="Оплатить">
	</form>

</body>
</html>