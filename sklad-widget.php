<?
	session_set_cookie_params(['SameSite' => 'None', 'secure' => true]);

	include 'prolog.php';

	$log_fname = 'logs/sklad-widget.txt';
	include 'grabber.php';
/*
	$sklad = NULL;

	if ($_POST['act'] == 'save') {
		if (($_POST['d_account'] != $_SESSION['account']) && !empty($_SESSION['account']))
			die('Auth error');

		$sklad = new Sklad($_POST['d_account']);
		if ($sklad->save_settings($_POST)) {
			$alert['type'] = 'ok';
			$alert['text'] = 'Сохранено';
		}
		$sklad = new Sklad($_POST['d_account']);
	}
*/
?>
<html lang='ru'>
<head>
	<title>Виджет модуля Робокасса</title>

	<base href="<?= $cfg['base_url'] ?>">

	<link rel='stylesheet' href='css/sklad-settings.css'>
	<link rel='stylesheet' href='css/style.css'>

	<script src='https://code.jquery.com/jquery-3.5.1.min.js'></script>
</head>
<body>
	<?
		if (empty($_SESSION['account'])) {
			$context_key = filter_input(INPUT_GET, 'contextKey');

			$token_body = [
				"sub" => $cfg['app']['uid'],
				"iat" => time(),
				"exp" => time() + 300,
				"jti" => bin2hex(random_bytes(32))
			];

			$jwt = JWT::encode($token_body, $cfg['app']['secret']);

			$header = "Authorization: Bearer ".$jwt;
			$opts = ['http' => ['method'	=> 'POST',
								'ignore_errors' => true,
								'header'	=> $header,
								'content'	=> ''
								]];
			$ctx = stream_context_create($opts);
			$url = $cfg['sklad']['url'].'/context/'.$context_key;
			$resp  = file_get_contents($url, false, $ctx);

//			file_put_contents('resp.txt', date('d.m.Y H:i:s').'-1 contextKey:['.$_GET['contextKey'].'] '.print_r($_SESSION, true), FILE_APPEND);

			$data = json_decode($resp, true);
			$sklad_account = explode('@', $data['uid'])[1];

			$sklad = new Sklad($sklad_account);

			if ($sklad === false)
				die('User settings error');

			$_SESSION['account'] = $sklad_account;

//			file_put_contents('resp.txt', date('d.m.Y H:i:s').'-2 contextKey:['.$_GET['contextKey'].'] '.print_r($_SESSION, true), FILE_APPEND);
		} else {
//			file_put_contents('resp.txt', date('d.m.Y H:i:s').'-3 contextKey:['.$_GET['contextKey'].'] '.print_r($_SESSION, true), FILE_APPEND);
		}


//		$settings = $sklad->settings();

	?>


	<div class="robo-sklad">
		<div>
			<label id='robo_send_link_result'>Ссылка на оплату:</label>
			<button class="warehouse__button" id='robo_link'>Сформировать</button>
			<button id='robo_copy_link' title='Скопировать ссылку'></button>
			<button id='robo_send_link' title='Выслать ссылку контрагенту'></button>
		</div>
		<div id='robo_link_text'>&nbsp;</div>
		<div>
			<label id='robo_send_second_result'>Второй чек:</label>
			<button class="warehouse__button" id='robo_send_second'>Выслать</button>
		</div>
	</div>

<script>
	var invoice_id = '0';
	var account = '<?= $_SESSION['account'] ?>';

	window.addEventListener("message", (event) => {
		if (event.data.name == 'Open' && event.data.extensionPoint == 'document.invoiceout.edit') {
			invoice_id = event.data.objectId;
			$.post('https://sklad.robokassa.ru/sklad-gate-js.php', {act: 'check_link_for_invoice', id_invoice: invoice_id, account: account}, function(res) {
				console.log(res);
				if (res.result == true) {
					$('#robo_link_text').text(res.link);
				} else {
					$('#robo_link_text').text('');
				}
			});


		};
	}, false);

	$(document).ready(function() {

		$('#robo_link').on('click', function(e){
			$.post('https://sklad.robokassa.ru/sklad-gate-js.php', {act: 'generate_link', id_invoice: invoice_id, account: account}, function(res) {
				console.log(res);

				if (res.result == true) {
					$('#robo_link_text').text(res.link);
				}
			});

			$('#robo_send_link_result').removeClass();
			$('#robo_send_link_result').addClass('ok');
			$('#robo_send_link_result').text('Ссылка готова');
			$('#robo_send_link_result').show();
			setTimeout(function() {
				$('#robo_send_link_result').fadeOut(400, function() {
					$('#robo_send_link_result').text('Ссылка на оплату:');
					$('#robo_send_link_result').removeClass('ok');
					$('#robo_send_link_result').show();
				});
			}, 3000);
		});

		$('#robo_copy_link').on('click', function(e) {
			e.preventDefault();
			CopyToClipboard('robo_link_text');

			$('#robo_send_link_result').removeClass();
			$('#robo_send_link_result').addClass('ok');
			$('#robo_send_link_result').text('Скопировано');
			$('#robo_send_link_result').show();
			setTimeout(function() {
				$('#robo_send_link_result').fadeOut(400, function() {
					$('#robo_send_link_result').text('Ссылка на оплату:');
					$('#robo_send_link_result').removeClass('ok');
					$('#robo_send_link_result').show();
				});
			}, 3000);
		});

		$('#robo_send_link').on('click', function(e) {
			e.preventDefault();
			$.post('https://sklad.robokassa.ru/sklad-gate-js.php', {act: 'send_link', id_invoice: invoice_id, account: account}, function(res) {
				if (res.result == true) {
					if (!res.text) {
						var text = 'Cсылка выслана';
					} else {
						var text = res.text;
					}

					if (res.error == true) {
						res_class = 'error';
					} else {
						res_class = 'ok';
					}
					$('#robo_send_link_result').removeClass();
					$('#robo_send_link_result').addClass(res_class);
					$('#robo_send_link_result').text(text);
					$('#robo_send_link_result').show();
					setTimeout(function() {
						$('#robo_send_link_result').fadeOut(400, function() {
							$('#robo_send_link_result').text('Ссылка на оплату:');
							$('#robo_send_link_result').removeClass(res_class);
							$('#robo_send_link_result').show();
						});
					}, 3000);
				} else {

				}
			});

		});

		$('#robo_send_second').on('click', function(e){
			e.preventDefault();
/*
			$.post('https://sklad.robokassa.ru/sklad-gate-js.php', {act: 'second_check', id_invoice: invoice_id}, function(res) {
				if (!res.text) {
					var text = 'Второй чек выслан';
				} else {
					var text = res.text;
				}

				if (res.error == true) {
					res_class = 'error';
				} else {
					res_class = 'ok';
				}

				$('#robo_send_second_result').removeClass();
				$('#robo_send_second_result').addClass(res_class);
				$('#robo_send_second_result').text(text);
				$('#robo_send_second_result').show();
				setTimeout(function() {
					$('#robo_send_second_result').fadeOut(400, function() {
						$('#robo_send_second_result').text('Второй чек:');
						$('#robo_send_second_result').removeClass(res_class);
						$('#robo_send_second_result').show();
					});
				}, 3000);
				});
		});
*/
				$('#robo_send_second_result').removeClass();
				$('#robo_send_second_result').addClass('error');
				$('#robo_send_second_result').text('Функция будет активирована после 1 февраля');
				$('#robo_send_second_result').show();
				setTimeout(function() {
					$('#robo_send_second_result').fadeOut(400, function() {
						$('#robo_send_second_result').text('Второй чек:');
						$('#robo_send_second_result').removeClass(res_class);
						$('#robo_send_second_result').show();
					});
				}, 3000);
				});

	});

	function CopyToClipboard(id) {
		var r = document.createRange();
		r.selectNode(document.getElementById(id));
		window.getSelection().removeAllRanges();
		window.getSelection().addRange(r);
		document.execCommand('copy');
		window.getSelection().removeAllRanges();
	}
</script>

</body>
</html>