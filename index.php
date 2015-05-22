<?php
require_once('app.php');
$exclure = ['/','/index.html','/admin.php'];
if (isset($_SERVER['PATH_INFO']) && !in_array($_SERVER['PATH_INFO'], $exclure)) {
	try {
		$l = new minilien($_SERVER['PATH_INFO']);
		$url = $l->visite();
		if (!isset($_GET['direct'])) {
			header("Location: $url");
		} else {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$url");
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$headers = curl_exec($ch);
			$headers = explode("\n",$headers);
			foreach ($headers as $header) {
				if (preg_match("/(.*): (.*)/",$header,$m)) {
					switch ($m[1]) {
						case 'Content-Type':
						case 'Content-Length':
							header("{$m[1]}: {$m[2]}");
							break;
					}
				}
			}
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_NOBODY, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
			curl_exec($ch);
			curl_close($ch);
		}
	} catch (Exception $e) {
		header("HTTP/1.0 404 Not Found"); 
		readfile("head.html");
		echo "404 Not Found<br/>{$_SERVER['PATH_INFO']}";
		readfile("foot.html");
	}
	exit();
} else {
	if ($_SERVER['PATH_INFO'] == '/admin.php') {
		include 'admin.php';
		exit(0);
	}
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
}
?>
