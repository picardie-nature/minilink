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
		if (!$this->l) throw new Exception("Pas trouvé");
	}

	public function visite() {
		self::links()->update(array('_id' => new MongoID($this->l['_id'])), array( '$set' => array('visites' => $this->l['visites']+1)));
		return $this->l['url'];
	}

	private static function links() {
		static $liens;
		if (!isset($liens)) {
			$m = new MongoClient(DBSTR);
			$db = $m->minilink;
			$liens = $db->links;
		}
		return $liens;
	}
	public static function nouveau($url) {
		$ele = array("url" => $url, "visites" => 0, "id" => self::nextval());
		if (!self::links()->insert($ele, array("fsync"=>true))) {
			throw new Exception("erreur d'enregistrement");
		}
		return $ele["id"];
	}

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
		return base64_encode(strrev(sprintf("%0{$rp}d", $n)));
	}
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO']!='/') {
	try {
		$l = new minilien($_SERVER['PATH_INFO']);
		$url = $l->visite();
		header("Location: $url");
	} catch (Exception $e) {
		readfile("head.html");
	}
	exit();
} else {
	if (isset($_GET['url'])) {
		try {
			if ($_GET['pwd'] != PWD) {
				echo "<div class=\"alert alert-danger\"><b>Ooouups !</b> C'est pas le bon mot de passe</div>";
				exit();
			} else {
				$lien = minilien::nouveau($_GET['url']);
				echo "<div class=\"alert alert-success\">Votre lien : <input class=\"form-control\" readonly=\"readonly\" style=\"cursor:pointer;\" type=\"text\" value=\"".HOST."/{$lien}\"/> </div>";
				exit();
			}
		} catch (Exception $e) {
			echo "<div class=\"alert alert-danger\"><b>Gros ouuups  ! :</b>";
			echo htmlentities($e->getMessage());
			echo "</div>";
		}
	}
	readfile("head.html");
	?>
	<div class="container formulaire">
		<form id="form" class="form">
			<h1>Minilien</h1>
			<input id="url" type="text" class="form-control" placeholder="Adresse a réduire" autofocus/>
			<input id="pwd" type="password" class="form-control" placeholder="Mot de passe"/>
			<br/>
			<button class="btn btn-lg btn-primary btn-block" type="submit">Obtenir le lien</button>
		</form>
		<div id="resultat"></div>
	</div>
	<script>
		$('#form').submit(function () {
			$('#resultat').html('Chargement');
			$('#resultat').load(
				'?'+$.param({
					url:$('#url').val(),
					pwd:$('#pwd').val()
				})
			);
			return false;
		});
	</script>

	<?php
	readfile("foot.html");
	// formulaire et insertion
}
?>
