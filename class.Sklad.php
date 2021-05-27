<?
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	use PHPMailer\PHPMailer\SMTP;

	require_once('vendor/autoload.php');


Class Sklad {

	public $app_id = NULL;
	public $user_id = NULL;
	public $token = NULL;

	public $user = NULL;

	private $secret = NULL;

	public $db = NULL;
	public $states = [0 => 'Робокасса - выслать ссылку', 1 => 'Робокаcса - оплачен'];
//	public $states = [1 => 'Робокаcса - оплачен'];

	function __construct($account = '') {
		$this->db_connect();

		if (!empty($account)) {
			$query = "SELECT * FROM users WHERE sklad_account=:account OR sklad_account_id=:account LIMIT 1";
			$st = $this->db->prepare($query);
			$st->execute([':account'=>$account]);
			$row = $st->fetch();
			if (!$row)
				return false;

			$this->user = $row;
		}

		$this->secret = $cfg['app']['secret'];
	}


	function db_connect() {
		global $cfg;

		try {
			$this->db = new DB('mysql:host='.$cfg['db']['host'].';dbname='.$cfg['db']['name'].';charset=UTF8', $cfg['db']['user'], $cfg['db']['pass']);
			$this->db->exec('SET NAMES UTF8');
		} catch(PDOException  $e) {
			die('Database error: ' . $e->getMessage().' ['.$e->getCode().']');
		}
	}


	function add_account($input, $request_uri, $add_status = true) {
		global $cfg;

		$query = 'INSERT INTO users (sklad_app, sklad_account, sklad_account_id, sklad_token) VALUES (:app, :account, :account_id, :token)';
		$st = $this->db->prepare($query);
		$account_id = explode('/', $request_uri)[8];
		$pars = [':app'=>$input['appUid'], ':account'=>$input['accountName'], ':account_id'=>$account_id, ':token'=>$input['access'][0]['access_token']];
		$st->execute($pars);

		$this->user['sklad_token'] = $input['access'][0]['access_token'];

		$exists_state = [];
		$states = $this->get_invoice_state('https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/metadata');;
		foreach($states['states'] as $s)
			$exists_state[] = $s['name'];


		if (!in_array($this->states[0], $exists_state)) {
			$body = ["name" => $this->states[0], "color" => 3172531, "stateType" => "Regular"];
			$url = 'https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/metadata/states';
			$header = "Authorization: Bearer ".$this->user['sklad_token']."\nContent-Type: application/json";
			$opts = ['http' => ['method'	=> 'POST',
								'ignore_errors' => true,
								'header'	=> $header,
								'content'	=> json_encode($body)
								]];
			$ctx = stream_context_create($opts);
			$resp  = file_get_contents($url, false, $ctx);
		}

		if (!in_array($this->states[1], $exists_state)) {
			$body = ["name" => $this->states[1], "color" => 3172531, "stateType" => "Regular"];
			$url = 'https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/metadata/states';
			$header = "Authorization: Bearer ".$this->user['sklad_token']."\nContent-Type: application/json";
			$opts = ['http' => ['method'	=> 'POST',
								'ignore_errors' => true,
								'header'	=> $header,
								'content'	=> json_encode($body)
								]];
			$ctx = stream_context_create($opts);
			$resp  = file_get_contents($url, false, $ctx);
		}

		$body['entityType'] = "invoiceout";
		$body['url'] = "https://".$cfg['server']['name']."/sklad-webhook.php";
		$body['action'] = "UPDATE";

		$header = "Authorization: Bearer ".$this->user['sklad_token']."\nContent-Type: application/json";
		$opts = ['http' => ['method'	=> 'POST',
							'ignore_errors' => true,
							'header'	=> $header,
							'content'	=> json_encode($body)
							]];
		$ctx = stream_context_create($opts);
		$url = 'https://online.moysklad.ru/api/remap/1.2/entity/webhook';
		$resp  = file_get_contents($url, false, $ctx);

		return true;
	}


	function drop_account($input) {
		$query = "DELETE FROM users WHERE sklad_app=:app AND sklad_account=:account";
		$st = $this->db->prepare($query);
		$pars = [':app'=>$input['appUid'], ':account'=>$input['accountName']];
		$st->execute($pars);
	}


	function save_settings($post) {
		$query = "UPDATE users SET robo_shop=:robo_shop, robo_test=:robo_test, robo_key_1=:robo_key_1, robo_key_2=:robo_key_2, robo_key_test_1=:robo_key_test_1, robo_key_test_2=:robo_key_test_2, email_from=:email_from, email_smtp=:email_smtp, email_pass=:email_pass, email_copy=:email_copy, robo_crc=:robo_crc, robo_fisk=:robo_fisk, robo_country=:robo_country, robo_sn=:robo_sn, robo_pobject=:robo_pobject, robo_pmethod=:robo_pmethod, robo_vat=:robo_vat, shop_name=:shop_name WHERE sklad_account=:account";
		$st = $this->db->prepare($query);
		$st->execute([	':account'		=> $post['d_account'],
						':robo_shop'	=> $post['d_shop_id'],
						':robo_test'	=> $post['d_test'] == 'on' ? 1 : 0,
						':robo_key_1'	=> $post['d_shop_key_1'],
						':robo_key_2'	=> $post['d_shop_key_2'],
						':robo_key_test_1' => $post['d_shop_key_test_1'],
						':robo_key_test_2' => $post['d_shop_key_test_2'],
						':email_from'	=> $post['d_email_from'],
						':email_smtp'	=> $post['d_email_smtp'],
						':email_pass'	=> $post['d_email_pass'],
						':email_copy'	=> $post['d_email_copy'],
						':robo_crc'		=> 'MD5',
						':robo_fisk'	=> $post['d_fisk'] == 'on' ? 1 : 0,
						':robo_country'	=> $post['d_country'],
						':robo_sn'	=> $post['d_sn'],
						':robo_pobject'	=> $post['d_po'],
						':robo_pmethod'	=> $post['d_pm'],
						':robo_vat'	=> $post['d_vat'],
						':shop_name'	=> $post['d_shop_name'],
					]);

		return true;
	}


	function settings() {
		return $this->user;
	}


	function get_invoice($url) {
		$header = "Authorization: Bearer ".$this->user['sklad_token'];
		$opts = ['http' => ['method'	=> 'GET',
							'ignore_errors' => true,
							'header'	=> $header,
							]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
//		file_put_contents('invoice.txt', $resp);
		$ret = json_decode($resp, true);

		return $ret;
	}


	function get_invoice_state($url) {
		if (empty($url))
			return false;

		$header = "Authorization: Bearer ".$this->user['sklad_token'];
		$opts = ['http' => ['method'	=> 'GET',
							'ignore_errors' => true,
							'header'	=> $header,
							]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
//		file_put_contents('state.txt', $resp);
		$ret = json_decode($resp, true);

		return $ret;
	}


	function set_invoice_paid($id) {
		$states = $this->get_invoice_state('https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/metadata');;
		foreach($states['states'] as $s) {
			if ($s['name'] == $this->states[1])
				$state_id = $s['id'];
		}

		if (empty($state_id))
			return false;

		$url = 'https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/'.$id;
		$header = "Authorization: Bearer ".$this->user['sklad_token']."\nContent-Type: application/json";
		$body = ["state" => ["meta" => ["href" => "https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/metadata/states/".$state_id, "type" => "state"]]];

		$opts = ['http' => ['method'	=> 'PUT',
							'ignore_errors' => true,
							'header'	=> $header,
							'content'	=> json_encode($body)
							]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);

		if ($GLOBALS['cfg']['debug'])
			file_put_contents('setstate.txt', $resp);

		return true;
	}


	function get_positions($url) {
		if (empty($url))
			return [];

		$header = "Authorization: Bearer ".$this->user['sklad_token'];
		$opts = ['http' => ['method'	=> 'GET',
							'ignore_errors' => true,
							'header'	=> $header,
							]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
//		file_put_contents('positions.txt', $resp);
		$ret = json_decode($resp, true)['rows'];

		return $ret;
	}


	function get_demand_positions($url_demand) {
		$header = "Authorization: Bearer ".$this->user['sklad_token'];
		$opts = ['http' => ['method'	=> 'GET',
							'ignore_errors' => true,
							'header'	=> $header,
							]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents(trim($url_demand, '/').'/positions', false, $ctx);
		$ret = json_decode($resp, true)['rows'];

		return $ret;
	}


	function get_product($url) {
		$header = "Authorization: Bearer ".$this->user['sklad_token'];
		$opts = ['http' => ['method'	=> 'GET',
							'ignore_errors' => true,
							'header'	=> $header,
							]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
//		file_put_contents('product.txt', $resp);
		$ret = json_decode($resp, true);

		return $ret;
	}


	function get_agent($url) {
		$header = "Authorization: Bearer ".$this->user['sklad_token'];
		$opts = ['http' => ['method'	=> 'GET',
							'ignore_errors' => true,
							'header'	=> $header,
							]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);
//		file_put_contents('agent.txt', $resp);
		$ret = json_decode($resp, true);

		return $ret;
	}


	function dump($data) {
		file_put_contents('dump.txt', print_r($data, true), FILE_APPEND);
	}


	function mail($from, $to, $subj, $html_body, $text_body = '', $arrImages = []) {
		global $cfg;

		ob_start();

		$mail = new PHPMailer(true); // `true` enables exceptions

		try {
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;	// Enable verbose debug output

			$mail->isSMTP();
			$mail->Host = '185.59.216.74';
			$mail->Port = '25';
			$mail->setFrom('robot@robokassa.ru', 'Робокасса робот');
/*
			if (!empty($this->user['email_smtp'])) {
				$mail->isSMTP();
				$mail->Host = explode(':', $this->user['email_smtp'])[0];
				$mail->Port = explode(':', $this->user['email_smtp'])[1];

				if (!empty($this->user['email_pass'])) {
					$mail->SMTPAuth = true;
					$mail->Username = $this->user['email_from'];
					$mail->Password = $this->user['email_pass'];
				    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // !!! Обязательно требование - включение TLS (возможен вариант: PHPMailer::ENCRYPTION_STARTTLS)

					$from = $this->user['email_from'];
				}
			}

			$mail->setFrom($from, 'Robokassa Robot');
*/

			$arrTo = array_filter(explode(' ', str_replace(',', ' ', $to)));

			foreach($arrTo as $toto) {
				$mail->addAddress($toto);
			}

			$mail->isHTML(true);
			$mail->CharSet = 'UTF-8';

			$mail->Subject = $subj;
			$mail->Body    = $html_body;
			if (!empty($text_body))
				$mail->AltBody = $text_body;

			$ret = $mail->send();
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}

		$debug_out = ob_get_clean();
		file_put_contents('logs/debug_smtp.txt', $debug_out, FILE_APPEND);

		return $ret;
	}


	function generate_link($id_invoice) {
		global $cfg;
		$invoice = $this->get_invoice('https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/'.$id_invoice);

		// получить список товаров
		$positions = $this->get_positions($invoice['positions']['meta']['href']);
		foreach($positions as &$p) {
			$form_data['invoice']['items'][] = ['name' => $this->get_product($p['assortment']['meta']['href'])['name'],
												'quantity' => $p['quantity'],
												'price' => round($p['price'] / 100, 2),
												'discount' => $p['discount'],
												'vat' => $p['vat'],
												'type' => $this->get_product($p['assortment']['meta']['href'])['paymentItemType'],
												'meta_href' => $p['assortment']['meta']['href'],
												];
			$mark[$p['assortment']['meta']['href']] = '';
		}

		if (empty($invoice['demands'])) {
//			echo 'нет привязанной отгрузки';
		} else {
			$demand_positions = $this->get_demand_positions($invoice['demands'][0]['meta']['href']);

			if (is_array($demand_positions))
				foreach($demand_positions as $dp) {
					if (array_key_exists($dp['assortment']['meta']['href'], $mark) && !empty($dp['trackingCodes'][0]['cis']))
						$mark[$dp['assortment']['meta']['href']] = $dp['trackingCodes'][0]['cis'];
				}
		}

		// прописать маркировки элементам счета, если есть
		if (!empty($form_data['invoice']['items']))
			foreach($form_data['invoice']['items'] as &$item) {
				if (!empty($mark[$item['meta_href']]))
					$item['nomenclature_code'] = $mark[$item['meta_href']];
			}

		// сгенерить ссылку
		do {
			$uuid = bin2hex(random_bytes(16));
			$res  = $this->db->query("SELECT COUNT(id) as cnt FROM links WHERE id='".$uuid."' LIMIT 1");
		} while($res->fetchColumn());


		// взять e-mail для чека из привязанного контрагента
		if (!empty($invoice['agent']['meta']['href'])) {
			$agent = $this->get_agent($invoice['agent']['meta']['href']);

			if (!empty($agent['email']))
				$to['buyer'] = $agent['email'];
		}

		// сформировать данные для формы
		$form_data['invoice']['id'] = $invoice['id'];
		$form_data['invoice']['name'] = $invoice['name'];
		$form_data['invoice']['ts'] = $invoice['moment'];
		$form_data['invoice']['total'] = $invoice['sum'] / 100;
		$form_data['invoice']['vatEnabled'] = $invoice['vatEnabled'];
		$form_data['invoice']['vatSum'] = round($invoice['vatSum'] / 100, 2);
		$form_data['email'] = $to['buyer'];
		$form_data['email_copy'] = $this->user['email_copy'];
		$form_data['test'] = $this->user['robo_test'];

		$st = $this->db->prepare('INSERT INTO links (id, id_user, data, invoice_id) VALUES (:id, :id_user, :data, :invoice_id)');
		$st->execute([':id'=>$uuid, ':id_user'=>$this->user['id'], ':data'=>json_encode($form_data), ':invoice_id' => $invoice['id']]);

		return $uuid;
	}


	function send_link($id_invoice) {
		global $cfg;

		$st = $this->db->prepare('SELECT id FROM links WHERE invoice_id=:invoice_id ORDER BY ts DESC');
		$st->execute([':invoice_id' => $id_invoice]);

		if (($row = $st->fetch()) === false) {
			return false;
		}

		$invoice = $this->get_invoice('https://online.moysklad.ru/api/remap/1.2/entity/invoiceout/'.$id_invoice);

		$to['seller'] = $this->user['email_copy'];

		$tpl['link'] = $GLOBALS['cfg']['link_base_url'].$row['id'];
		$tpl['invoice']['num'] = $invoice['name'];
		$tpl['invoice']['date'] = date('d.m.Y', strtotime($invoice['moment']));
		$tpl['total'] = ($invoice['sum'] / 100);

		$html = file_get_contents('templates/letter-short.html');
		$html = str_replace('{invoice_num}', $tpl['invoice']['num'], $html);
		$html = str_replace('{invoice_date}', $tpl['invoice']['date'], $html);
		$html = str_replace('{link}', $tpl['link'], $html);
		$html = str_replace('{total}', $tpl['total'], $html);
		$html = str_replace('{base_url}', $GLOBALS['cfg']['base_url'], $html);
		$html = str_replace('{shop_name}', empty($this->user['shop_name']) ? '' : ' в магазине «'.$this->user['shop_name'].'»', $html);

		$warning = [];
		if (!empty($invoice['agent']['meta']['href']))
			$agent = $this->get_agent($invoice['agent']['meta']['href']);

		if (!empty($agent['email'])) {
			$to['buyer'] = $agent['email'];
		} else {
			$warning['html'] = "<span style='color:red;'>! У контрагента не задан e-mail - ссылка не отправлена !</span><br><br>";
			$warning['text'] = "! У контрагента не задан e-mail - ссылка не отправлена !\n\n";
		}

//			file_put_contents('logs/html.html', $html);

		$mess['html'] = $html;
		$mess['text'] = "Ссылка на оплату счета ".$tpl['invoice']['num']." от ".$tpl['invoice']['date']." в платежной системе Робокасса:\n\n".$tpl['link']."\n\nСчет на сумму: ".$tpl['total']." руб";

		if (!empty($to['buyer']))
			$this->mail('', $to['buyer'], 'Ссылка на оплату счета '.$tpl['invoice']['num'], $mess['html'], $mess['text']);
		$this->mail('', $to['seller'], 'Ссылка на оплату счета '.$tpl['invoice']['num'], $warning['html'].$mess['html'], $warning['text'].$mess['text']);

		return true;
	}


	function send_second_check($id_invoice) {
		global $cfg;

		$st = $this->db->prepare('SELECT * FROM links WHERE invoice_id=:invoice_id ORDER BY ts DESC');
		$st->execute([':invoice_id' => $id_invoice]);

		if (($row = $st->fetch()) === false) {
			$ret['text'] = 'Нет ссылки';
			$ret['result'] = false;

			return $ret;
		}

		$data = json_decode($row['data'], true);

		if (empty($data['email'])) {
			$ret['text'] = 'Нет e-mail для чека';
			$ret['result'] = false;

			return $ret;
		}

		$check_data['merchantId'] = $this->user['robo_shop'];
		$check_data['id'] = ceil(microtime(true) * 10000);
		$check_data['originId'] = $data['invoice']['name'];
		$check_data['operation'] = 'sell';
		$check_data['sno'] = $this->user['robo_sn'];
		$check_data['url'] = 'https://online.moysklad.ru';
		$check_data['total'] = $data['invoice']['total'];
		$check_data['client']['email'] = $data['email'];
		$check_data['payments'][0]['type'] = 2;
		$check_data['payments'][0]['sum'] = $data['invoice']['total'];
		$check_data['vats'][0]['type'] = $this->user['robo_vat'];
		$check_data['vats'][0]['sum'] = $data['invoice']['vatSum'];

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

			$i["name"] = $item['name'];
			$i["quantity"] = $item['quantity'];
			$i["sum"] = $item['price'] * $item['quantity'];
			$i["tax"] = $tax;
			$i["payment_method"] = "full_payment";
			$i["payment_object"] = $item_pobject;
			$i["nomenclature_code"] = empty($item['nomenclature_code']) ? '' : $item['nomenclature_code'];

			$check_data['items'][] = $i;
		}

//		print_r($check_data);

//		$cd = json_encode($check_data, JSON_UNESCAPED_SLASHES);
		$cd = json_encode($check_data);
//		$cd = str_replace('+', '-', $cd);
//		$cd = str_replace('/', '_', $cd);

//		echo $cd;

		$cd = rtrim(base64_encode($cd), '=');

		$sig = rtrim(base64_encode(md5($cd.$this->user['robo_key_1'])), '=');

		$post_data = $cd.'.'.$sig;

//		echo ']'.$post_data.'[';

		$url = 'https://ws.roboxchange.com/RoboFiscal/Receipt/Attach';
		$opts = ['http' => ['method'	=> 'POST',
							'ignore_errors' => true,
							'content'	=> $post_data
							]];
		$ctx = stream_context_create($opts);
		$resp  = file_get_contents($url, false, $ctx);

		echo $resp;

		return true;

	}
}