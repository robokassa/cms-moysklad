<?
	$cfg['db']['host'] = "localhost";
	$cfg['db']['name'] = "mysklad";
	$cfg['db']['user'] = "mysklad";
	$cfg['db']['pass'] = "";

	$cfg['server']['name'] = 'sklad.robokassa.ru';
	$cfg['base_url'] = 'https://'.$cfg['server']['name'].'/';
	$cfg['link_base_url'] = $cfg['base_url'].'payment_form/';

	$cfg['app']['secret'] = 'TO6JV1zpldDMuqsqlr7u3fkbWRPaRqngaQuGhHi2Zd5ZSR4LJJIk9l4IKR0ZWfuboHIlcPyEH5Xex7vfwRo33bYF6ACl94j1DGD8OPan28liSChCwtdZE1dl0VyBAIpH';
	$cfg['app']['uid'] = 'robokassa-1.robokassa';

	$cfg['sklad']['url'] = 'https://online.moysklad.ru/api/vendor/1.0'; // продакшн

	$cfg['debug'] = true; // будут писаться логи вызова каждого скрипта в каталог /logs