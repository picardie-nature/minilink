<?php
require_once('app.php');
try {
	if (!isset($_GET['cmd'])) 
		throw new Exception('add a cmd argument on your request');
	switch ($_GET['cmd']) {
		case 'reduce':
			if (!isset($_GET['url'])) {
				throw new Exception('Provide url');
			}
			$lexistant = minilien::byURL($_GET['url']);
			$reused = false;
			if ($lexistant) {
				$lid = $lexistant->id;
				$reused = true;
			} else {
				$lid = minilien::nouveau($_GET['url']);
			}
			echo json_encode(array('id' => $lid,'reused'=>$reused));
			break;
		case 'status':
			if (!isset($_GET['id']))
				throw new Exception('Provide link id');
			$lien = new minilien($_GET['id']);
			$reply = array(
				"url" => $lien->url,
				"visites" => $lien->visites,
				"derniere_visite" => $lien->derniere_visite
			);
			echo json_encode($reply);
			break;
		default:
			throw new Exception('unknown cmd');
	}
} catch (Exception $e) {
	echo json_encode(array('error' => $e->getMessage()));
}
?>
