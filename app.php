<?php
if (file_exists('config.php')) require('config.php');
if (!defined('DBSTR')) define('DBSTR', 'mongodb://localhost:27017');
if (!defined('PWD')) define('PWD', 'plop');
if (!defined('HOST')) define('HOST', 'http://l.picnat.fr');
if (!defined('SEQUENCE')) define('SEQUENCE', 'sequence.txt');

class minilien {
	protected $l;

	public function __construct($id) {
		$this->l = self::links()->findOne(array("id"=>basename($id)));
		if ($this->l == null) throw new Exception("Pas trouvé");
	}

	public static function byURL($url) {
		$l = self::links()->findOne(array("url"=>$url));
		if (!$l) return false;
		else return new minilien($l['id']);
	}

	public function __get($k) {
		if (isset($this->l[$k]))
			return $this->l[$k];
		return null;
	}


	/**
	 * @brief enregistre une visite sur le lien
	 */
	public function visite() {
		self::links()->update(array('_id' => new MongoID($this->l['_id'])), array( '$set' => array(
			'visites' => $this->l['visites']+1,
			'derniere_visite' => new MongoDate()
		)));
		$visite_obj = array(
			"link_id" => $this->id,
			"user_agent" => $_SERVER['HTTP_USER_AGENT'],
			"date" => new MongoDate()
		);
		if (isset($_SERVER['HTTP_REFERER']))
			$visite_obj['referer'] = $_SERVER['HTTP_REFERER'];
		self::__visites()->insert($visite_obj);
		return $this->l['url'];
	}

	/**
	 * @brief accès à la table liens
	 */
	private static function links() {
		static $liens;
		if (!isset($liens)) {
			$m = new MongoClient(DBSTR);
			$db = $m->minilink;
			$liens = $db->links;
		}
		return $liens;
	}

	/**
	 * @brief accès à la table visites
	 */
	private static function __visites() {
		static $visites;
		if (!isset($visites)) {
			$m = new MongoClient(DBSTR);
			$db = $m->minilink;
			$visites = $db->visites;
		}
		return $visites;
	}

	/**
	 * @brief enregistre un nouveau lien
	 */
	public static function nouveau($url) {
		// url deja existante ?
		$old = self::byURL($url);
		if ($old) return $old->id;

		$ele = array("url" => $url, "visites" => 0, "id" => self::nextval());
		if (!self::links()->insert($ele, array("fsync"=>true))) {
			throw new Exception("erreur d'enregistrement");
		}
		return $ele["id"];
	}

	/**
	 * @brief numéro de lien suivant
	 */
	private static function nextval() {
		$f = fopen(SEQUENCE, "r+");
		if (!flock($f, LOCK_EX)) {
			throw new Exception('ne peut pas verrouiller la séquence');
		}
		$n = intval(fgets($f));
		fseek($f, 0, SEEK_SET);
		ftruncate($f, 0);
		$n++;
		fputs($f, $n);
		flock($f, LOCK_UN);
		fclose($f);
		$p = array(3,6,9,15);
		$rp = false;
		foreach ($p as $up) {
			if ($n > pow(10,$up)) continue;
			else { $rp = $up; break; }
		}
		if (!$rp) throw new Exception('voir plus large...');
		$l = $rp;
		return self::__id($n);
		return base64_encode(strrev(sprintf("%0{$rp}d", $n)));
	}

	private static function __id($n) {
		$e = 0;
		$id = '';
		$n = (int)$n;
		while (pow(26,$e)<=$n) $e++;
		for ($i=$e-1; $i>=0; $i--) {
			$nf = (int)($n/pow(26,$i));
			$n -= $nf*pow(26,$i);
			$id .= chr(97+$nf);
		}
		return $id;
	}
}
?>
