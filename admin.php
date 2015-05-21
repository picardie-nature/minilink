<?php
session_start();
if (file_exists('config.php')) require_once('config.php');
require_once('app.php');
require_once('head.html');

if (isset($_POST['pwd']) && ($_POST['pwd'] == PWD))
	$_SESSION['login'] = true;
if (isset($_GET['logout']))
	$_SESSION['login'] = null;
$lien = false;
if (isset($_POST['lien'])) {
	$path = basename($_POST['lien']);
	$lien = new minilien($path);
	$_SESSION['lien'] = $path;
} else if (isset($_SESSION['lien'])) {
	$lien = new minilien($_SESSION['lien']);
}
if ($lien) {
	if (isset($_POST['url'])) {
		$lien->change_url($_POST['url']);
		$lien = new minilien($lien->id);
	}
}

if (empty($_SESSION['login'])) {
?>
	<div class="container formulaire">
		<form id="form" class="form" method="post">
			<h1>Minilien <small>(admin)</small></h1>
			<input name="pwd" id="pwd" type="password" class="form-control" placeholder="Mot de passe"/>
			<br/>
			<button class="btn btn-lg btn-primary btn-block" type="submit">Se connecter</button>
		</form>
	</div>
<?php
} else {
?>
	<div class="container formulaire">
		<div class="row"><a class="pull-right" href="?logout=1">Fermer</a></div>
		<div class="row">
		<form class="form" method="post">
			<h1>Minilien <small>(admin)</small></h1>
			<input type="text" name="lien" placeholder="http://..."/>
		</form>
		<br/>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Voir les infos du lien</button>
		</div>
	<?php if ($lien) { ?>
		<div class="row">
		<form class="form" method="post">
			<label for="url">URL</label>
			<input type="text" id="url" name="url" value="<?php echo $lien->url; ?>"/>
			
			<button class="btn btn-xs btn-primary" type="submit">Modifier</button>
		</form>
		<div class="row">
			<div class="col-md-4">
				<h4 class="text-center">Visites</h4>
				<h1 class="text-center"><?php echo $lien->visites; ?></h1>
			</div>
			<div class="col-md-4">
				<h4 class="text-center">Dernière visite</h4>
				<p class="text-center" style="font-size: 16px; font-weight: bold;"><?php echo date('d-n-Y G:i:s', $lien->derniere_visite->sec); ?></p>
			</div>
			<div class="col-md-4">
				<h4 class="text-center">Identifiant</h4>
				<h1 class="text-center"><?php echo $lien->id; ?></h1>
			</div>
		</div>
	<?php } ?>
	</div>
<?php
}
require_once('foot.html');
?>
