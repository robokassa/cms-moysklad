<?
	include 'prolog.php';

	$log_fname = 'logs/sklad-settings.txt';
	include 'grabber.php';

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
?>
<html lang='ru'>
<head>
	<title>Настройки модуля Робокасса</title>

	<base href="<?= $cfg['base_url']?>">

	<link rel='stylesheet' href='css/style.css'>
	<link rel='stylesheet' href='css/sklad-settings.css'>
</head>
<body>
	<?
		if (!empty($alert)) {
			echo "<div class='alert ".$alert['type']."'>".$alert['text']."</div>";
		}

		if (is_null($sklad)) {
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

			$data = json_decode($resp, true);
			$sklad_account = explode('@', $data['uid'])[1];

			$sklad = new Sklad($sklad_account);

			if ($sklad === false)
				die('User settings error');

			$_SESSION['account'] = $sklad_account;
		}


		$settings = $sklad->settings();
	?>


	<form action='<?= $cfg['base_url'] ?>/sklad-settings.php' method='POST'>
		<input type='hidden' name='act' value='save'>
		<input type='hidden' name='d_account' value='<?=$settings['sklad_account']?>'>

	<div class="warehouse">

      <div class="warehouse__support warehouse__support--hidden">
        <button class="warehouse__cross">
          <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="https://www.w3.org/2000/svg">
            <path d="M8.93418 1.0002L1.06553 9" stroke="#607D8B" stroke-width="2"/>
            <path d="M8.93408 8.86867L1.06543 1.00001" stroke="#607D8B" stroke-width="2"/>
          </svg>
        </button>
        <h2 class="warehouse__support-title">Клиентская поддержка Robokassa</h2>
        <p class="warehouse__support-text">Eсли у вас возникли вопросы напишите в поддержку из Личного Кабинета Robokassa и приложите скриншоты настроек модуля.</p>

      </div>
<? /*
      <div class="warehouse__background-header"></div>

      <div class="warehouse__header">
        <p class="warehouse__header-text">Оплата через Robokassa</p>
      </div>
*/ ?>
        <div class="warehouse__info-text">
<!--          <h3 class="warehouse__info-title">Оплата через Robokassa</h3> -->
          <p class="warehouse__info-text" style="margin-right:100px;">Есть трудности?</p>
        </div>

        <div class="warehouse__navigation">
          <span class="warehouse__navigation-name">
            Настройки:
          </span>
          <button type="button" class="warehouse__navigation-item warehouse__navigation-item--notification">
            Уведомления
          </button>
          <button type="button" class="warehouse__navigation-item warehouse__navigation-item--api warehouse__navigation-item--active">
            API
          </button>
          <button type="button" class="warehouse__navigation-item warehouse__navigation-item--payment">
            Платежи
          </button>
        </div>

        <div class="warehouse__variants">

          <div class="warehouse__navigation-content warehouse__navigation-content--notification">
            <div class="warehouse__fields-wrapper warehouse__fields-wrapper--notification">
<? /*
              <div class="warehouse__field">
                <span class="warehouse__description">E-mail "От"</span>
                <input placeholder="E-mail для поля From" class="warehouse__input" type="text" name='d_email_from' value="<?=$settings['email_from']?>">
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш E-mail</p>
                </div>
              </div>
              <div class="warehouse__field">
                <span class="warehouse__description">SMTP (сервер:порт)</span>
                <input placeholder="Пример - smtp.yandex.ru:465" class="warehouse__input" type="text" name='d_email_smtp' value="<?=$settings['email_smtp']?>">
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш SMTP</p>
                </div>
              </div>
              <div class="warehouse__field">
                <span class="warehouse__description">Пароль</span>
                <input placeholder="Вставьте пароль" class="warehouse__input" type="password" name='d_email_pass' value="<?=$settings['email_pass']?>">
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш пароль</p>
                </div>
              </div>
*/ ?>
              <div class="warehouse__field">
                <span class="warehouse__description">E-mail для копии</span>
                <input class="warehouse__input" placeholder="Можно несколько, через запятую" type="text" name='d_email_copy' value="<?=$settings['email_copy']?>">
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш E-mail</p>
                </div>
              </div>
              <div class="warehouse__field">
                <span class="warehouse__description">Название магазина</span>
                <input class="warehouse__input" placeholder="Можно оставить пустым" type="text" name='d_shop_name' value="<?=$settings['shop_name']?>">
              </div>
              <button class="warehouse__button">Применить</button>
            </div>

            <div class="attention attention--notification">
              <h4 class="attention__title">Внимание!</h4>
              <p class="attention__text">
                Eсли вы не укажете настройки почты, ваш покупатель не получит письмо со ссылкой на оплату.
              </p>
			  <? /*
              <p class="attention__text">
                Документацию по настройке почтового сервера вы можете найти здесь:
              </p>
              <div class="attention__mail-wrapper">
                <a class="attention__mail" href="https://support.google.com/mail/answer/7126229?hl=ru" target="_blank">Google</a>
                <a class="attention__mail" href="https://yandex.ru/support/mail/mail-clients.html" target="_blank">Яндекс</a>
                <a class="attention__mail" href="https://help.mail.ru/mail/mailer/popsmtp" target="_blank">Mail.ru</a>
              </div>
			  */ ?>
            </div>
          </div>

          <div class="warehouse__navigation-content warehouse__navigation-content--api warehouse__navigation-content--show">
            <div class="warehouse__fields-wrapper warehouse__fields-wrapper--api">
              <div class="warehouse__field">
                <span class="warehouse__description">ID магазина</span>
                <input class="warehouse__input" type="text" name="d_shop_id" value="<?=$settings['robo_shop']?>">
                <button class="warehouse__question-wrapper warehouse__question-id">
                  <svg class="warehouse__question" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="https://www.w3.org/2000/svg">
                    <path d="M8.83686 13.8273C8.18232 13.8273 7.60959 13.3364 7.69141 12.6818C7.69141 12.0273 8.18232 11.5364 8.83686 11.5364C9.49141 11.5364 9.98232 12.0273 9.98232 12.6818C9.98232 13.3364 9.49141 13.8273 8.83686 13.8273Z" fill="#455A64"/>
                    <path d="M11.6997 8.18182C11.4543 8.50909 11.127 8.75455 10.7179 9L10.227 9.32728C9.98153 9.49091 9.8179 9.65455 9.73608 9.9C9.65426 10.0636 9.65426 10.2273 9.65426 10.3909V10.4727H7.77244V10.3091C7.77244 9.65455 7.77244 9.32728 8.09972 9C8.50881 8.59091 8.99972 8.18182 9.49063 7.93637C9.65426 7.85455 9.73608 7.77273 9.8179 7.60909C9.98153 7.44546 10.0634 7.2 10.0634 6.95455C10.0634 6.70909 9.98153 6.38182 9.8179 6.13637C9.65426 5.89091 9.32699 5.80909 8.9179 5.80909C8.59062 5.80909 8.18153 5.97273 8.0179 6.21818C7.85426 6.46364 7.77244 6.79091 7.77244 7.11818V7.2H5.89062V7.11818C5.97244 5.97273 6.38153 5.07273 7.1179 4.58182C7.69062 4.25455 8.26335 4.09091 8.9179 4.09091C9.73608 4.09091 10.5543 4.25455 11.2088 4.74546C11.8634 5.23637 12.1906 5.97273 12.1088 6.70909C12.1088 7.36364 11.9452 7.77273 11.6997 8.18182Z" fill="#455A64"/>
                    <path d="M17 9C17 13.4183 13.4183 17 9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9Z" stroke="#455A64" stroke-width="2" stroke-miterlimit="10"/>
                  </svg>
                </button>
                <div class="warehouse__popup warehouse__popup--id">
                  <p class="warehouse__popup-text">ID магазина указан в карточке магазина Личного кабинета Robokassa</p>
                </div>
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш ID магазина</p>
                </div>
              </div>
              <div class="warehouse__field">
                <span class="warehouse__description">Пароль 1</span>
                <input class="warehouse__input" type="password" name='d_shop_key_1' value="<?= $settings['robo_key_1']?>">
                <button class="warehouse__question-wrapper warehouse__question-pass1">
                  <svg class="warehouse__question" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="https://www.w3.org/2000/svg">
                    <path d="M8.83686 13.8273C8.18232 13.8273 7.60959 13.3364 7.69141 12.6818C7.69141 12.0273 8.18232 11.5364 8.83686 11.5364C9.49141 11.5364 9.98232 12.0273 9.98232 12.6818C9.98232 13.3364 9.49141 13.8273 8.83686 13.8273Z" fill="#455A64"/>
                    <path d="M11.6997 8.18182C11.4543 8.50909 11.127 8.75455 10.7179 9L10.227 9.32728C9.98153 9.49091 9.8179 9.65455 9.73608 9.9C9.65426 10.0636 9.65426 10.2273 9.65426 10.3909V10.4727H7.77244V10.3091C7.77244 9.65455 7.77244 9.32728 8.09972 9C8.50881 8.59091 8.99972 8.18182 9.49063 7.93637C9.65426 7.85455 9.73608 7.77273 9.8179 7.60909C9.98153 7.44546 10.0634 7.2 10.0634 6.95455C10.0634 6.70909 9.98153 6.38182 9.8179 6.13637C9.65426 5.89091 9.32699 5.80909 8.9179 5.80909C8.59062 5.80909 8.18153 5.97273 8.0179 6.21818C7.85426 6.46364 7.77244 6.79091 7.77244 7.11818V7.2H5.89062V7.11818C5.97244 5.97273 6.38153 5.07273 7.1179 4.58182C7.69062 4.25455 8.26335 4.09091 8.9179 4.09091C9.73608 4.09091 10.5543 4.25455 11.2088 4.74546C11.8634 5.23637 12.1906 5.97273 12.1088 6.70909C12.1088 7.36364 11.9452 7.77273 11.6997 8.18182Z" fill="#455A64"/>
                    <path d="M17 9C17 13.4183 13.4183 17 9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9Z" stroke="#455A64" stroke-width="2" stroke-miterlimit="10"/>
                  </svg>
                </button>
                <div class="warehouse__popup warehouse__popup--pass1">
                  <p class="warehouse__popup-text">Пароль 1 задаётся в «Технических настройках магазина» в Личном кабинете Robokassa.</p>
                </div>
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш пароль</p>
                </div>
              </div>
              <div class="warehouse__field">
                <span class="warehouse__description">Пароль 2</span>
                <input class="warehouse__input" type="password" name='d_shop_key_2' value="<?=$settings['robo_key_2']?>">
                <button class="warehouse__question-wrapper warehouse__question-pass2">
                  <svg class="warehouse__question" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="https://www.w3.org/2000/svg">
                    <path d="M8.83686 13.8273C8.18232 13.8273 7.60959 13.3364 7.69141 12.6818C7.69141 12.0273 8.18232 11.5364 8.83686 11.5364C9.49141 11.5364 9.98232 12.0273 9.98232 12.6818C9.98232 13.3364 9.49141 13.8273 8.83686 13.8273Z" fill="#455A64"/>
                    <path d="M11.6997 8.18182C11.4543 8.50909 11.127 8.75455 10.7179 9L10.227 9.32728C9.98153 9.49091 9.8179 9.65455 9.73608 9.9C9.65426 10.0636 9.65426 10.2273 9.65426 10.3909V10.4727H7.77244V10.3091C7.77244 9.65455 7.77244 9.32728 8.09972 9C8.50881 8.59091 8.99972 8.18182 9.49063 7.93637C9.65426 7.85455 9.73608 7.77273 9.8179 7.60909C9.98153 7.44546 10.0634 7.2 10.0634 6.95455C10.0634 6.70909 9.98153 6.38182 9.8179 6.13637C9.65426 5.89091 9.32699 5.80909 8.9179 5.80909C8.59062 5.80909 8.18153 5.97273 8.0179 6.21818C7.85426 6.46364 7.77244 6.79091 7.77244 7.11818V7.2H5.89062V7.11818C5.97244 5.97273 6.38153 5.07273 7.1179 4.58182C7.69062 4.25455 8.26335 4.09091 8.9179 4.09091C9.73608 4.09091 10.5543 4.25455 11.2088 4.74546C11.8634 5.23637 12.1906 5.97273 12.1088 6.70909C12.1088 7.36364 11.9452 7.77273 11.6997 8.18182Z" fill="#455A64"/>
                    <path d="M17 9C17 13.4183 13.4183 17 9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9Z" stroke="#455A64" stroke-width="2" stroke-miterlimit="10"/>
                  </svg>
                </button>
                <div class="warehouse__popup warehouse__popup--pass2">
                  <p class="warehouse__popup-text">Пароль 2 задаётся в «Технических настройках магазина» в Личном кабинете Robokassa.</p>
                </div>
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш пароль</p>
                </div>
              </div>
              <div class="warehouse__field">
                <span class="warehouse__description">Тестирование оплаты</span>
                <div class="warehouse__radio-wrapper">
                  <div class="warehouse__radio-item">
                    <input class="warehouse__input-radio" name="d_test" id="tumbler-pay1" value="on" type="radio">
                    <label class="warehouse__label" for="tumbler-pay1">Включить</label>
                  </div>
                  <div class="warehouse__radio-item">
                    <input class="warehouse__input-radio" name="d_test" id="tumbler-pay2" value="off" type="radio">
                    <label class="warehouse__label" for="tumbler-pay2">Отключить</label>
                  </div>
                </div>
              </div>
              <div class="warehouse__field warehouse__field--test">
                <span class="warehouse__description">Тестовый пароль 1</span>
                <input class="warehouse__input" type="password" name='d_shop_key_test_1' value="<?=$settings['robo_key_test_1']?>">
                <button class="warehouse__question-wrapper warehouse__question-pass-test1">
                  <svg class="warehouse__question" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="https://www.w3.org/2000/svg">
                    <path d="M8.83686 13.8273C8.18232 13.8273 7.60959 13.3364 7.69141 12.6818C7.69141 12.0273 8.18232 11.5364 8.83686 11.5364C9.49141 11.5364 9.98232 12.0273 9.98232 12.6818C9.98232 13.3364 9.49141 13.8273 8.83686 13.8273Z" fill="#455A64"/>
                    <path d="M11.6997 8.18182C11.4543 8.50909 11.127 8.75455 10.7179 9L10.227 9.32728C9.98153 9.49091 9.8179 9.65455 9.73608 9.9C9.65426 10.0636 9.65426 10.2273 9.65426 10.3909V10.4727H7.77244V10.3091C7.77244 9.65455 7.77244 9.32728 8.09972 9C8.50881 8.59091 8.99972 8.18182 9.49063 7.93637C9.65426 7.85455 9.73608 7.77273 9.8179 7.60909C9.98153 7.44546 10.0634 7.2 10.0634 6.95455C10.0634 6.70909 9.98153 6.38182 9.8179 6.13637C9.65426 5.89091 9.32699 5.80909 8.9179 5.80909C8.59062 5.80909 8.18153 5.97273 8.0179 6.21818C7.85426 6.46364 7.77244 6.79091 7.77244 7.11818V7.2H5.89062V7.11818C5.97244 5.97273 6.38153 5.07273 7.1179 4.58182C7.69062 4.25455 8.26335 4.09091 8.9179 4.09091C9.73608 4.09091 10.5543 4.25455 11.2088 4.74546C11.8634 5.23637 12.1906 5.97273 12.1088 6.70909C12.1088 7.36364 11.9452 7.77273 11.6997 8.18182Z" fill="#455A64"/>
                    <path d="M17 9C17 13.4183 13.4183 17 9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9Z" stroke="#455A64" stroke-width="2" stroke-miterlimit="10"/>
                  </svg>
                </button>
                <div class="warehouse__popup warehouse__popup--pass-test1">
                  <p class="warehouse__popup-text">Тестовый Пароль 1 задаётся в «Технических настройках магазина» в разделе тестовых платежей в Личном кабинете Robokassa.</p>
                </div>
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш пароль</p>
                </div>
              </div>
              <div class="warehouse__field warehouse__field--test">
                <span class="warehouse__description">Тестовый пароль 2</span>
                <input class="warehouse__input" type="password" name='d_shop_key_test_2' value="<?=$settings['robo_key_test_2']?>">
                <button class="warehouse__question-wrapper warehouse__question-pass-test2">
                  <svg class="warehouse__question" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="https://www.w3.org/2000/svg">
                    <path d="M8.83686 13.8273C8.18232 13.8273 7.60959 13.3364 7.69141 12.6818C7.69141 12.0273 8.18232 11.5364 8.83686 11.5364C9.49141 11.5364 9.98232 12.0273 9.98232 12.6818C9.98232 13.3364 9.49141 13.8273 8.83686 13.8273Z" fill="#455A64"/>
                    <path d="M11.6997 8.18182C11.4543 8.50909 11.127 8.75455 10.7179 9L10.227 9.32728C9.98153 9.49091 9.8179 9.65455 9.73608 9.9C9.65426 10.0636 9.65426 10.2273 9.65426 10.3909V10.4727H7.77244V10.3091C7.77244 9.65455 7.77244 9.32728 8.09972 9C8.50881 8.59091 8.99972 8.18182 9.49063 7.93637C9.65426 7.85455 9.73608 7.77273 9.8179 7.60909C9.98153 7.44546 10.0634 7.2 10.0634 6.95455C10.0634 6.70909 9.98153 6.38182 9.8179 6.13637C9.65426 5.89091 9.32699 5.80909 8.9179 5.80909C8.59062 5.80909 8.18153 5.97273 8.0179 6.21818C7.85426 6.46364 7.77244 6.79091 7.77244 7.11818V7.2H5.89062V7.11818C5.97244 5.97273 6.38153 5.07273 7.1179 4.58182C7.69062 4.25455 8.26335 4.09091 8.9179 4.09091C9.73608 4.09091 10.5543 4.25455 11.2088 4.74546C11.8634 5.23637 12.1906 5.97273 12.1088 6.70909C12.1088 7.36364 11.9452 7.77273 11.6997 8.18182Z" fill="#455A64"/>
                    <path d="M17 9C17 13.4183 13.4183 17 9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9Z" stroke="#455A64" stroke-width="2" stroke-miterlimit="10"/>
                  </svg>
                </button>
                <div class="warehouse__popup warehouse__popup--pass-test2">
                  <p class="warehouse__popup-text">Тестовый Пароль 2 задаётся в «Технических настройках магазина» в разделе тестовых платежей в Личном кабинете Robokassa.</p>
                </div>
                <div class="warehouse__popup-alert warehouse__popup-alert--hidden">
                  <p class="warehouse__popup-alert-text">Проверьте ваш пароль</p>
                </div>
              </div>
              <button class="warehouse__button">Применить</button>
            </div>

            <div class="attention attention--api">
				<div class="warehouse__info-text" style="padding-bottom:18px;"><a href='https://docs.robokassa.ru/media/1693/moysklad.pdf' target="_blank">Инструкция по использованию модуля</a></div>

              <h4 class="attention__title">Внимание!</h4>
              <p class="attention__text">Для правильной работы модуля примените в Личном кабинете Robokassa в разделе «Технические настройки магазина» следующие параметры:</p>
              <p class="attention__name">Result URL: <span id='copy_result'></span> </p>
              <div class="attention__link">
                <span class="attention__link-text" id='copy_text'><?= $cfg['base_url'] ?>robo-payment.php?a=result</span>
                <button class="attention__link-button" id='copy'>
                  <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="https://www.w3.org/2000/svg">
                    <rect class="attention__svg-link-stroke" x="5" y="1" width="12" height="12" rx="1" stroke="#455A64" stroke-width="2"/>
                    <rect class="attention__svg-link-fill" y="2" width="2" height="14" fill="#455A64"/>
                    <path class="attention__svg-link-fill" d="M16 16L16 18L2 18C0.89543 18 -4.82823e-08 17.1046 0 16L16 16Z" fill="#455A64"/>
                  </svg>
                </button>
              </div>
              <p class="attention__name">Метод отсылки данных:</p>
              <p class="attention__item-text"><span class="attention__item">POST</span></p>
              <p class="attention__name">Метод шифрования:</p>
              <p class="attention__item-text"><span class="attention__item">MD5</span></p>
            </div>
          </div>

          <div class="warehouse__navigation-content warehouse__navigation-content--payment">
            <div class="warehouse__fields-wrapper warehouse__fields-wrapper--payment">
              <div class="warehouse__field">
                <span class="warehouse__description">Где ваш бизнес?</span>
                <div class="warehouse__select" data-state="">
                  <span class="warehouse__select__title-flag warehouse__select__title-flag--russia"></span>
                  <div class="warehouse__select__title" data-default="Option 2">
                    Россия</div>
                  <div class="warehouse__select__content">
                    <input id="singleSelect0" class="warehouse__select__input" type="radio" name="d_country" value='russia' />
                    <label for="singleSelect0" class="warehouse__select__label">Россия</label>
                    <input id="singleSelect1" class="warehouse__select__input" type="radio" name="d_country" value='russia' />
                    <label for="singleSelect1" class="warehouse__select__label warehouse__select__label--active">
                      <span class="warehouse__select__flag">
                        <svg width="30" height="20" viewBox="0 0 30 20" fill="none" xmlns="https://www.w3.org/2000/svg">
                          <rect x="0.5" y="0.5" width="29" height="19" fill="white" stroke="#ECEFF1"/>
                          <rect y="13" width="30" height="7" fill="#F4511E"/>
                          <rect y="6" width="30" height="7" fill="#1976D2"/>
                        </svg>
                      </span>
                      Россия
                    </label>
                    <input id="singleSelect2" class="warehouse__select__input" type="radio" name="d_country" value='kazah'/>
                    <label for="singleSelect2" class="warehouse__select__label">
                      <span class="warehouse__select__flag">
                        <svg width="30" height="20" viewBox="0 0 30 20" fill="none" xmlns="https://www.w3.org/2000/svg">
                          <rect width="30" height="20" fill="#00B4C8"/>
                          <rect x="3" width="3" height="20" fill="#FFCA00"/>
                          <g clip-path="url(#clip0)">
                          <path d="M19.1367 4.28898C19.4346 4.34385 19.5874 4.17923 19.6305 3.95581C19.6893 3.65792 19.5639 2.15277 19.5639 2.15277C19.5639 2.15277 18.8701 3.49329 18.8113 3.7951C18.7643 4.01852 18.8388 4.23018 19.1367 4.28898Z" fill="#FFCA00"/>
                          <path d="M20.2263 4.62223C20.5085 4.7359 20.6888 4.60263 20.7751 4.39097C20.8927 4.10876 21.0612 2.61145 21.0612 2.61145C21.0612 2.61145 20.1205 3.79126 20.0029 4.07348C19.9167 4.28122 19.948 4.50464 20.2263 4.62223Z" fill="#FFCA00"/>
                          <path d="M21.2338 5.15927C21.4886 5.32389 21.6924 5.22982 21.8178 5.04168C21.9864 4.7869 22.445 3.34839 22.445 3.34839C22.445 3.34839 21.2887 4.32046 21.1201 4.57524C20.9947 4.76338 20.9829 4.99072 21.2338 5.15927Z" fill="#FFCA00"/>
                          <path d="M22.1156 5.88435C22.3351 6.09602 22.5507 6.04114 22.7114 5.88043C22.927 5.66485 23.656 4.34393 23.656 4.34393C23.656 4.34393 22.3351 5.07299 22.1195 5.28857C21.9588 5.44927 21.9039 5.66877 22.1156 5.88435Z" fill="#FFCA00"/>
                          <path d="M22.8405 6.76641C23.013 7.01727 23.2364 7.00551 23.4246 6.88008C23.6793 6.71153 24.6514 5.55524 24.6514 5.55524C24.6514 5.55524 23.2129 6.01384 22.9581 6.18238C22.77 6.31173 22.672 6.51163 22.8405 6.76641Z" fill="#FFCA00"/>
                          <path d="M23.3775 7.76969C23.499 8.04798 23.7185 8.08326 23.9302 7.99702C24.2124 7.87944 25.3922 6.93872 25.3922 6.93872C25.3922 6.93872 23.891 7.10727 23.6088 7.22485C23.3971 7.31109 23.2639 7.49139 23.3775 7.76969Z" fill="#FFCA00"/>
                          <path d="M24.0442 8.36939C23.8208 8.4125 23.6522 8.56537 23.711 8.86326C23.7737 9.16116 23.9854 9.23563 24.2088 9.19252C24.5067 9.13372 25.8472 8.43994 25.8472 8.43994C25.8472 8.43994 24.3421 8.31059 24.0442 8.36939Z" fill="#FFCA00"/>
                          <path d="M23.8247 10.0001C23.8286 10.3058 24.0207 10.4195 24.248 10.4195C24.5538 10.4195 26.004 10.0001 26.004 10.0001C26.004 10.0001 24.5538 9.58069 24.248 9.58069C24.0168 9.58069 23.8247 9.69436 23.8247 10.0001Z" fill="#FFCA00"/>
                          <path d="M25.847 11.5599C25.847 11.5599 24.5065 10.8662 24.2086 10.8074C23.9852 10.7642 23.7696 10.8387 23.7108 11.1366C23.6559 11.4345 23.8206 11.5834 24.044 11.6305C24.3419 11.6893 25.847 11.5599 25.847 11.5599Z" fill="#FFCA00"/>
                          <path d="M23.3775 12.2264C23.2639 12.5087 23.3971 12.689 23.6088 12.7752C23.891 12.8928 25.3922 13.0613 25.3922 13.0613C25.3922 13.0613 24.2124 12.1167 23.9302 12.003C23.7185 11.9168 23.4951 11.9481 23.3775 12.2264Z" fill="#FFCA00"/>
                          <path d="M22.8408 13.2336C22.6761 13.4884 22.7702 13.6883 22.9583 13.8176C23.2131 13.9862 24.6516 14.4448 24.6516 14.4448C24.6516 14.4448 23.6796 13.2924 23.4248 13.1199C23.2366 12.9945 23.0093 12.9827 22.8408 13.2336Z" fill="#FFCA00"/>
                          <path d="M22.7113 14.1194C22.5506 13.9587 22.3311 13.8999 22.1155 14.1155C21.9038 14.335 21.9548 14.5506 22.1194 14.7113C22.335 14.9269 23.6559 15.6559 23.6559 15.6559C23.6559 15.6559 22.9268 14.335 22.7113 14.1194Z" fill="#FFCA00"/>
                          <path d="M22.445 16.6516C22.445 16.6516 21.9864 15.2131 21.8178 14.9583C21.6924 14.7701 21.4886 14.6682 21.2338 14.8407C20.9829 15.0131 20.9947 15.2366 21.1201 15.4247C21.2926 15.6795 22.445 16.6516 22.445 16.6516Z" fill="#FFCA00"/>
                          <path d="M20.2266 15.3778C19.9483 15.4993 19.913 15.7188 20.0032 15.9265C20.1208 16.2087 21.0615 17.3885 21.0615 17.3885C21.0615 17.3885 20.8929 15.8873 20.7753 15.609C20.6891 15.3974 20.5088 15.2641 20.2266 15.3778Z" fill="#FFCA00"/>
                          <path d="M14.88 15.4248C15.0054 15.2366 15.0211 15.0093 14.7663 14.8408C14.5116 14.6761 14.3077 14.7702 14.1823 14.9583C14.0138 15.2131 13.5552 16.6516 13.5552 16.6516C13.5552 16.6516 14.7115 15.6796 14.88 15.4248Z" fill="#FFCA00"/>
                          <path d="M13.8842 14.1155C13.6647 13.9039 13.4491 13.9548 13.2884 14.1195C13.0728 14.335 12.3438 15.656 12.3438 15.656C12.3438 15.656 13.6647 14.9269 13.8803 14.7113C14.041 14.5506 14.0998 14.3311 13.8842 14.1155Z" fill="#FFCA00"/>
                          <path d="M13.1595 13.2336C12.987 12.9827 12.7675 12.9945 12.5755 13.1199C12.3207 13.2885 11.3486 14.4448 11.3486 14.4448C11.3486 14.4448 12.7871 13.9862 13.0419 13.8176C13.2301 13.6922 13.3281 13.4884 13.1595 13.2336Z" fill="#FFCA00"/>
                          <path d="M12.6221 12.2263C12.5006 11.9441 12.2811 11.9128 12.0734 12.0029C11.7911 12.1205 10.6113 13.0612 10.6113 13.0612C10.6113 13.0612 12.1126 12.8927 12.3948 12.7751C12.6025 12.6889 12.7397 12.5086 12.6221 12.2263Z" fill="#FFCA00"/>
                          <path d="M12.2891 11.1366C12.2263 10.8387 12.0147 10.7642 11.7913 10.8074C11.4934 10.8662 10.1489 11.5599 10.1489 11.5599C10.1489 11.5599 11.6541 11.6854 11.952 11.6266C12.1832 11.5834 12.3478 11.4345 12.2891 11.1366Z" fill="#FFCA00"/>
                          <path d="M12.1793 10.0001C12.1754 9.69436 11.9873 9.58069 11.756 9.58069C11.4503 9.58069 10 10.0001 10 10.0001C10 10.0001 11.4503 10.4195 11.756 10.4195C11.9833 10.4195 12.1793 10.3058 12.1793 10.0001Z" fill="#FFCA00"/>
                          <path d="M12.289 8.86332C12.3439 8.56543 12.1793 8.41256 11.9559 8.36945C11.658 8.31065 10.1528 8.43608 10.1528 8.43608C10.1528 8.43608 11.4934 9.12986 11.7952 9.18865C12.0186 9.23569 12.2302 9.16513 12.289 8.86332Z" fill="#FFCA00"/>
                          <path d="M12.6221 7.76982C12.7358 7.48761 12.6025 7.3073 12.3909 7.22107C12.1086 7.10348 10.6074 6.93494 10.6074 6.93494C10.6074 6.93494 11.7872 7.87565 12.0695 7.99324C12.2811 8.08339 12.5045 8.05204 12.6221 7.76982Z" fill="#FFCA00"/>
                          <path d="M12.5755 6.88008C12.7675 7.00943 12.991 7.02119 13.1595 6.76641C13.3241 6.51163 13.2301 6.31173 13.0419 6.18238C12.7871 6.01384 11.3486 5.55524 11.3486 5.55524C11.3486 5.55524 12.3207 6.71153 12.5755 6.88008Z" fill="#FFCA00"/>
                          <path d="M13.8842 5.88435C14.0958 5.66485 14.041 5.44927 13.8803 5.28857C13.6647 5.07299 12.3438 4.34393 12.3438 4.34393C12.3438 4.34393 13.0728 5.66485 13.2884 5.88043C13.4491 6.04114 13.6686 6.09993 13.8842 5.88435Z" fill="#FFCA00"/>
                          <path d="M23.119 10.0078C23.119 7.17777 20.8221 4.88086 17.9921 4.88086C15.1621 4.88086 12.8652 7.17385 12.8652 10.0078C12.8652 12.8377 15.1621 15.1347 17.9921 15.1347C20.826 15.1307 23.119 12.8377 23.119 10.0078Z" fill="#FFCA00"/>
                          <path d="M18 4.17932C18.3057 4.1754 18.4194 3.98334 18.4194 3.756C18.4194 3.45027 18 2 18 2C18 2 17.5806 3.45027 17.5806 3.756C17.5806 3.98334 17.6942 4.17932 18 4.17932Z" fill="#FFCA00"/>
                          <path d="M19.1364 15.711C18.8385 15.7737 18.7641 15.9815 18.8072 16.2049C18.866 16.5028 19.5597 17.8472 19.5597 17.8472C19.5597 17.8472 19.6891 16.3421 19.6264 16.0442C19.5833 15.8169 19.4343 15.6522 19.1364 15.711Z" fill="#FFCA00"/>
                          <path d="M18.4194 16.2441C18.4194 16.0168 18.3057 15.8208 18 15.8208C17.6942 15.8247 17.5806 16.0168 17.5806 16.2441C17.5806 16.5499 18 18.0001 18 18.0001C18 18.0001 18.4194 16.5499 18.4194 16.2441Z" fill="#FFCA00"/>
                          <path d="M16.3731 16.0442C16.3143 16.3421 16.4397 17.8472 16.4397 17.8472C16.4397 17.8472 17.1335 16.5067 17.1923 16.2049C17.2354 15.9815 17.1649 15.7698 16.8631 15.711C16.5652 15.6522 16.4162 15.8169 16.3731 16.0442Z" fill="#FFCA00"/>
                          <path d="M15.7734 15.3778C15.4911 15.2641 15.3108 15.3974 15.2246 15.609C15.107 15.8912 14.9385 17.3885 14.9385 17.3885C14.9385 17.3885 15.8792 16.2087 15.9968 15.9265C16.0869 15.7188 16.0556 15.4954 15.7734 15.3778Z" fill="#FFCA00"/>
                          <path d="M14.7663 5.15927C15.0172 4.9868 15.0054 4.76338 14.88 4.57524C14.7115 4.32046 13.5552 3.34839 13.5552 3.34839C13.5552 3.34839 14.0138 4.7869 14.1823 5.04168C14.3077 5.22982 14.5116 5.32781 14.7663 5.15927Z" fill="#FFCA00"/>
                          <path d="M15.7738 4.62223C16.0521 4.50072 16.0874 4.28122 16.0012 4.07348C15.8836 3.79126 14.9429 2.61145 14.9429 2.61145C14.9429 2.61145 15.1114 4.11268 15.229 4.39097C15.3113 4.60263 15.4916 4.7359 15.7738 4.62223Z" fill="#FFCA00"/>
                          <path d="M16.8631 4.28918C17.161 4.22646 17.2354 4.0148 17.1923 3.79138C17.1335 3.49349 16.4398 2.14905 16.4398 2.14905C16.4398 2.14905 16.3104 3.65419 16.3731 3.95209C16.4162 4.18334 16.5652 4.34797 16.8631 4.28918Z" fill="#FFCA00"/>
                          </g>
                          <defs>
                          <clipPath id="clip0">
                          <rect width="16" height="16" fill="white" transform="translate(10 2)"/>
                          </clipPath>
                          </defs>
                        </svg>
                      </span>
                      Казахстан
                    </label>
   <? //                 <input id="singleSelect3" class="warehouse__select__input" type="radio" name="d_country" value='kazah-2' /> ?>
                  </div>
                </div>
              </div>
	<div id='russian-tax'>
              <div class="warehouse__field">
                <span class="warehouse__description">Фискализация чеков</span>
                <div class="warehouse__radio-wrapper">
                  <div class="warehouse__radio-item">
                    <input class="warehouse__input-radio" name="d_fisk" id="tumbler1" value="on" type="radio">
                    <label class="warehouse__label" for="tumbler1">Включить</label>
                  </div>
                  <div class="warehouse__radio-item">
                    <input class="warehouse__input-radio" name="d_fisk" id="tumbler2" value="off" type="radio">
                    <label class="warehouse__label" for="tumbler2">Отключить</label>
                  </div>
                </div>
              </div>
              <div class="warehouse__field warehouse__field--fiscalization">
                <span class="warehouse__description">Налогообложение</span>
                <div class="warehouse__select warehouse__select-tax" data-state="">
                  <div class="warehouse__select__title warehouse__select__title-tax" data-default=""></div>
                  <div class="warehouse__select__content">
                    <input id="d_sn_osn" class="warehouse__select__input" type="radio" name="d_sn" value="osn" />
                    <label for="d_sn_osn" class="warehouse__select__label warehouse__select__label-tax">Общая СН</label>
                    <input id="d_sn_osn" class="warehouse__select__input" type="radio" name="d_sn" value="osn" />
                    <label for="d_sn_osn" class="warehouse__select__label warehouse__select__label-tax">Общая СН</label>
					<input id="d_sn_usn_income" class="warehouse__select__input" type="radio" name="d_sn" value="usn_income" />
                    <label for="d_sn_usn_income" class="warehouse__select__label warehouse__select__label-tax">Упрощённая СН (доходы)</label>
                    <input id="d_sn_usn_income_outcome" class="warehouse__select__input" type="radio" name="d_sn" value="usn_income_outcome" />
                    <label for="d_sn_usn_income_outcome" class="warehouse__select__label warehouse__select__label-tax">Упрощённая СН (доходы минус расходы)</label>
                    <input id="d_sn_envd" class="warehouse__select__input" type="radio" name="d_sn" value="envd" />
                    <label for="d_sn_envd" class="warehouse__select__label warehouse__select__label-tax">Единый налог на вмененный доход</label>
                    <input id="d_sn_esn" class="warehouse__select__input" type="radio" name="d_sn" value="esn" />
                    <label for="d_sn_esn" class="warehouse__select__label warehouse__select__label-tax">Единый сельскохозяйственный налог</label>
                    <input id="d_sn_patent" class="warehouse__select__input" type="radio" name="d_sn" value="patent" />
                    <label for="d_sn_patent" class="warehouse__select__label warehouse__select__label-tax">Патентная СН</label>
                  </div>
                </div>
              </div>
              <div class="warehouse__field warehouse__field--fiscalization">
                <span class="warehouse__description">Налоговая ставка</span>
                <div class="warehouse__select warehouse__select-rate" data-state="">
                  <div class="warehouse__select__title warehouse__select__title-rate" data-default=""></div>
                  <div class="warehouse__select__content">
                    <input id="d_vat_none" class="warehouse__select__input" type="radio" name="d_vat"  value="none"/>
                    <label for="d_vat_none" class="warehouse__select__label warehouse__select__label-rate">Без НДС</label>
                    <input id="d_vat_none" class="warehouse__select__input" type="radio" name="d_vat"  value="none"/>
                    <label for="d_vat_none" class="warehouse__select__label warehouse__select__label-rate">Без НДС</label>
                    <input id="d_vat_vat0" class="warehouse__select__input" type="radio" name="d_vat"  value="vat0"/>
                    <label for="d_vat_vat0" class="warehouse__select__label warehouse__select__label-rate">НДС по ставке 0%</label>
                    <input id="d_vat_vat10" class="warehouse__select__input" type="radio" name="d_vat"  value="vat10"/>
                    <label for="d_vat_vat10" class="warehouse__select__label warehouse__select__label-rate">НДС чека по ставке 10%</label>
                    <input id="d_vat_vat20" class="warehouse__select__input" type="radio" name="d_vat"  value="vat20"/>
                    <label for="d_vat_vat20" class="warehouse__select__label warehouse__select__label-rate">НДС чека по ставке 20%</label>
                    <input id="d_vat_vat110" class="warehouse__select__input" type="radio" name="d_vat"  value="vat110" />
                    <label for="d_vat_vat110" class="warehouse__select__label warehouse__select__label-rate">НДС чека по расчётной ставке 10/110</label>
                    <input id="d_vat_vat120" class="warehouse__select__input" type="radio" name="d_vat"  value="vat120"/>
                    <label for="d_vat_vat120" class="warehouse__select__label warehouse__select__label-rate">НДС чека по расчётной ставке 20/120</label>
                  </div>
                </div>
              </div>
              <div class="warehouse__field warehouse__field--fiscalization">
                <span class="warehouse__description">Способ расчёта</span>
                <div class="warehouse__select warehouse__select-method" data-state="">
                  <div class="warehouse__select__title warehouse__select__title-method" data-default=""></div>
                  <div class="warehouse__select__content">
                    <input id="d_pm_full_prepayment" class="warehouse__select__input" type="radio" name="d_pm" value="full_prepayment" />
                    <label for="d_pm_full_prepayment" class="warehouse__select__label warehouse__select__label-method">Предоплата 100%</label>
                    <input id="d_pm_full_prepayment" class="warehouse__select__input" type="radio" name="d_pm" value="full_prepayment" />
                    <label for="d_pm_full_prepayment" class="warehouse__select__label warehouse__select__label-method">Предоплата 100%</label>
                    <input id="d_pm_prepayment" class="warehouse__select__input" type="radio" name="d_pm" value="prepayment" />
                    <label for="d_pm_prepayment" class="warehouse__select__label warehouse__select__label-method">Предоплата</label>
                    <input id="d_pm_advance" class="warehouse__select__input" type="radio" name="d_pm" value="advance" />
                    <label for="d_pm_advance" class="warehouse__select__label warehouse__select__label-method">Аванс</label>
                    <input id="d_pm_full_payment" class="warehouse__select__input" type="radio" name="d_pm" value="full_payment" />
                    <label for="d_pm_full_payment" class="warehouse__select__label warehouse__select__label-method">Полный расчёт</label>
                    <input id="d_pm_partial_payment" class="warehouse__select__input" type="radio" name="d_pm" value="partial_payment" />
                    <label for="d_pm_partial_payment" class="warehouse__select__label warehouse__select__label-method">Частичный расчёт и кредит</label>
                    <input id="d_pm_credit" class="warehouse__select__input" type="radio" name="d_pm" value="credit" />
                    <label for="d_pm_credit" class="warehouse__select__label warehouse__select__label-method">Передача в кредит</label>
                    <input id="d_pm_credit_payment" class="warehouse__select__input" type="radio" name="d_pm" value="credit_payment" />
                    <label for="d_pm_credit_payment" class="warehouse__select__label warehouse__select__label-method">Оплата кредита</label>
                  </div>
                </div>
              </div>
              <div class="warehouse__field warehouse__field--fiscalization">
                <span class="warehouse__description">Предмет расчёта</span>
                <div class="warehouse__select warehouse__select-sub" data-state="">
                  <div class="warehouse__select__title warehouse__select__title-sub" data-default=""></div>
                  <div class="warehouse__select__content">
                    <input id="d_po_commodity" class="warehouse__select__input" type="radio" name="d_po" value="commodity" />
                    <label for="d_po_commodity" class="warehouse__select__label warehouse__select__label-sub">Товар</label>
                    <input id="d_po_commodity" class="warehouse__select__input" type="radio" name="d_po" value="commodity" />
                    <label for="d_po_commodity" class="warehouse__select__label warehouse__select__label-sub">Товар</label>
                    <input id="d_po_excise" class="warehouse__select__input" type="radio" name="d_po" value="excise" />
                    <label for="d_po_excise" class="warehouse__select__label warehouse__select__label-sub">Подакцизный товар</label>
                    <input id="d_po_job" class="warehouse__select__input" type="radio" name="d_po" value="job" />
                    <label for="d_po_job" class="warehouse__select__label warehouse__select__label-sub">Работа</label>
                    <input id="d_po_service" class="warehouse__select__input" type="radio" name="d_po" value="service" />
                    <label for="d_po_service" class="warehouse__select__label warehouse__select__label-sub">Услуга</label>
                    <input id="d_po_gambling_bet" class="warehouse__select__input" type="radio" name="d_po" value="gambling_bet" />
                    <label for="d_po_gambling_bet" class="warehouse__select__label warehouse__select__label-sub">Ставка азартной игры</label>
                    <input id="d_po_gambling_prize" class="warehouse__select__input" type="radio" name="d_po" value="gambling_prize" />
                    <label for="d_po_gambling_prize" class="warehouse__select__label warehouse__select__label-sub">Выигрыш азартной игры</label>
                    <input id="d_po_intellectual_activity" class="warehouse__select__input" type="radio" name="d_po" value="intellectual_activity" />
                    <label for="d_po_intellectual_activity" class="warehouse__select__label warehouse__select__label-sub">Предоставление результатов интеллектуального труда</label>
                    <input id="d_po_payment" class="warehouse__select__input" type="radio" name="d_po" value="payment" />
                    <label for="d_po_payment" class="warehouse__select__label warehouse__select__label-sub">Платёж</label>
                    <input id="d_po_agent_commission" class="warehouse__select__input" type="radio" name="d_po" value="agent_commission" />
                    <label for="d_po_agent_commission" class="warehouse__select__label warehouse__select__label-sub">Агентское вознаграждение</label>
                    <input id="d_po_another" class="warehouse__select__input" type="radio" name="d_po" value="another" />
                    <label for="d_po_another" class="warehouse__select__label warehouse__select__label-sub">Иной предмет расчёта</label>
                  </div>
                </div>
              </div>
		</div>
              <button class="warehouse__button">Применить</button>
            </div>

            <div class="attention attention--payment">

            </div>
          </div>

        </div>

      </form>

      <div class="overlay overlay--invis"></div>

    </div>

	<script src="js/navigation.js"></script>
	<script src="js/example-select.js"></script>
	<script src="js/select-tax.js"></script>
	<script src="js/select-rate.js"></script>
	<script src="js/select-method.js"></script>
	<script src="js/select-subject.js"></script>
	<script src="js/input.js"></script>
	<script src='https://code.jquery.com/jquery-3.5.1.min.js'></script>

<script>
(function() {
    var h = -1;
    var win = null;
    function sendExpand() {
        if (typeof win != 'undefined' && win && document.body.scrollHeight !== h) {
            h = document.body.scrollHeight;
            const sendObject = {
                height: h
            };
            win.postMessage(sendObject, '*');
        }
    }
    window.addEventListener('load', function () {
        win = parent;
        sendExpand();
    });
    setInterval(sendExpand, 250);
})();

$(document).ready(function() {
	if (<?= intval($settings['robo_test']) ?>) {
		$('#tumbler-pay1').click();
	} else {
		$('#tumbler-pay2').click();
	}

	if (<?= intval($settings['robo_fisk']) ?>) {
		$('#tumbler1').click();
	} else {
		$('#tumbler2').click();
	}

	$('[for=d_sn_<?= $settings['robo_sn'] ?>]').click();
	$('[for=d_po_<?= $settings['robo_pobject'] ?>]').click();
	$('[for=d_pm_<?= $settings['robo_pmethod'] ?>]').click();
	$('[for=d_vat_<?= $settings['robo_vat'] ?>]').click();

	$('.alert').on('click', function() {
		$(this).hide()
	});

	if ("<?= $settings['robo_country'] ?>" == "russia") {
		$('[for=singleSelect1]').click();
		$('#russian-tax').show();
	} else {
		$('[for=singleSelect2]').click();
		$('#russian-tax').hide();
	}


	$('input[name=d_country]').on('change', function() {
		if ($(this).val() == 'kazah') {
			$('#russian-tax').hide();
		} else {
			$('#russian-tax').show();
		}
	});


	$('#copy').on('click', function(e) {
		e.preventDefault();

		var ct = document.getElementById("copy_text");
		copyText(ct);
	});
});

function copyText(element) {
  var range, selection, worked;

  if (document.body.createTextRange) {
    range = document.body.createTextRange();
    range.moveToElementText(element);
    range.select();
  } else if (window.getSelection) {
    selection = window.getSelection();
    range = document.createRange();
    range.selectNodeContents(element);
    selection.removeAllRanges();
    selection.addRange(range);
  }

  try {
    document.execCommand('copy');
    $('#copy_result').text('Скопировано');
  }
  catch (err) {
//    alert('unable to copy text');
  }
}

</script>
</body>
</html>