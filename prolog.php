<?
	if (!defined('PROLOG'))
	{
		session_start();

		$dbg['start'] = microtime(true);
		$dbg['query'] = 0;

		define('PROLOG',true);

		$id		= intval($_GET['id']);
		$act	= filter_input(INPUT_GET, 'act', FILTER_SANITIZE_STRING);

		include(__DIR__.'/.cfg.php');

		spl_autoload_register(	function($cname)
								{
									$fname = __DIR__.'/class.'.$cname.'.php';
									if (file_exists($fname))
										include_once($fname);
								});

		try{
			$db = new DB('mysql:host='.$cfg['db']['host'].';dbname='.$cfg['db']['name'].';charset=UTF8', $cfg['db']['user'], $cfg['db']['pass']);
			$db->exec('SET NAMES UTF8');
		}catch(PDOException  $e){
			die('Database error: ' . $e->getMessage().' ['.$e->getCode().']');
		}


		date_default_timezone_set('UTC');
	}
