<?
	Class DB extends PDO {
		function __construct($dsn, $user='', $pass='') {
			$pdo = parent::__construct($dsn, $user, $pass, $options = []);
			$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['DBStatement']);
			return $pdo;
		}


		public function query($query) {
			global $dbg;
			$dbg['query']++;

			return parent::query($query);
		}


		public function exec($query) {
			global $dbg;
			$dbg['query']++;

			return parent::exec($query);
		}
	}


	Class DBStatement extends PDOStatement {
		public function execute($par=NULL) {
			global $dbg;
			$dbg['query']++;
			return parent::execute($par);
		}

		public function fetch($par=PDO::FETCH_ASSOC, $orient=NULL, $offset=NULL) {
			return parent::fetch($par, $orient, $offset);
		}
	}
