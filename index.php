<?php
require_once('app.php');

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
}
?>
