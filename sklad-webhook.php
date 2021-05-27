<?
	ob_start();
	include 'prolog.php';

	$log_fname = 'logs/wh.txt';
	include 'grabber.php';

	$request = $_SERVER['REQUEST_URI'];
	$input = json_decode(file_get_contents('php://input'), true);

if (is_array($input['events']))
	foreach($input['events'] as $ev) {
		if ($ev['meta']['type'] == 'invoiceout' && $ev['action'] == 'UPDATE') {
			$sklad = new Sklad($ev['accountId']);

			$invoice = $sklad->get_invoice($ev['meta']['href']);
			$state = $sklad->get_invoice_state($invoice['state']['meta']['href']);

			if ($state['name'] == $sklad->states[0]) {
				$tpl['invoice']['num'] = $invoice['name'];
				$tpl['invoice']['date'] = date('d.m.Y', strtotime($invoice['moment']));
				$tpl['total'] = ($invoice['sum'] / 100);

				// получить список товаров

				$positions = $sklad->get_positions($invoice['positions']['meta']['href']);
				foreach($positions as &$p) {
					$form_data['invoice']['items'][] = ['name' => $sklad->get_product($p['assortment']['meta']['href'])['name'],
														'quantity' => $p['quantity'],
														'price' => round($p['price'] / 100, 2),
														'discount' => $p['discount'],
														'vat' => $p['vat'],
														'type' => $sklad->get_product($p['assortment']['meta']['href'])['paymentItemType']
														];
				}

				// сгенерить ссылку
				do {
					$uuid = bin2hex(random_bytes(16));
					$res  = $db->query("SELECT COUNT(id) as cnt FROM links WHERE id='".$uuid."' LIMIT 1");
				} while($res->fetchColumn());

				$warning = [];
				$agent = $sklad->get_agent($invoice['agent']['meta']['href']);
				if (!empty($agent['email'])) {
					$to['buyer'] = $agent['email'];
				} else {
					$warning['html'] = "<span style='color:red;'>! У контрагента не задан e-mail - ссылка не отправлена !</span><br><br>";
					$warning['text'] = "! У контрагента не задан e-mail - ссылка не отправлена !\n\n";
				}

				// сформировать данные для формы
				$form_data['invoice']['id'] = $invoice['id'];
				$form_data['invoice']['name'] = $invoice['name'];
				$form_data['invoice']['ts'] = $invoice['moment'];
				$form_data['invoice']['total'] = $invoice['sum'] / 100;
				$form_data['invoice']['vatEnabled'] = $invoice['vatEnabled'];
				$form_data['email'] = $to['buyer'];
				$form_data['email_copy'] = $sklad->user['email_copy'];
				$form_data['test'] = $sklad->user['robo_test'];

				$st = $db->prepare('INSERT INTO links (id, id_user, data) VALUES (:id, :id_user, :data)');
				$st->execute([':id'=>$uuid, ':id_user'=>$sklad->user['id'], ':data'=>json_encode($form_data)]);

				$tpl['link'] = $cfg['link_base_url'].$uuid;
				$to['seller'] = $sklad->user['email_copy'];

				$html = file_get_contents('templates/letter-short.html');
				$html = str_replace('{invoice_num}', $tpl['invoice']['num'], $html);
				$html = str_replace('{invoice_date}', $tpl['invoice']['date'], $html);
				$html = str_replace('{link}', $tpl['link'], $html);
				$html = str_replace('{total}', $tpl['total'], $html);
				$html = str_replace('{base_url}', $cfg['base_url'], $html);
				$html = str_replace('{shop_name}', empty($sklad->user['shop_name']) ? '' : ' в магазине «'.$sklad->user['shop_name'].'»', $html);
				file_put_contents('logs/html.html', $html);
				$mess['html'] = $html;
//				$mess['html'] = "Ссылка на оплату счета ".$tpl['invoice']." в платежной системе Робокасса:<br><br><a href='".$tpl['link']."'>".$tpl['link'].'</a><br><br>Счет на сумму: '.$tpl['total'];
				$mess['text'] = "Ссылка на оплату счета ".$tpl['invoice']['num']." от ".$tpl['invoice']['date']." в платежной системе Робокасса:\n\n".$tpl['link']."\n\nСчет на сумму: ".$tpl['total']." руб";

				if (!empty($to['buyer']))
					$sklad->mail('root@robo.int3.me', $to['buyer'], 'Ссылка на оплату счета '.$tpl['invoice']['num'], $mess['html'], $mess['text']);
				$sklad->mail('root@robo.int3.me', $to['seller'], 'Ссылка на оплату счета '.$tpl['invoice']['num'], $warning['html'].$mess['html'], $warning['text'].$mess['text']);
			}

		}
	}


	$stdout = ob_get_contents();
	ob_end_clean();
	if (!empty($stdout))
		file_put_contents('stdout-wh.txt', $stdout, FILE_APPEND);
