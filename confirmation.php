<?php
	require_once('config/functions.php');

	if (isset($_GET['login'], $_GET['token']) AND !empty($_GET['login']) AND !empty($_GET['token'])) {

		$login = test_input(urldecode($_GET['login']));
		$token = test_input($_GET['token']);

		if (usedLogin($DB, $login)) {
			$query = $DB->prepare('SELECT * FROM users WHERE login = ? AND token = ?');
			$query->execute(array($login, $token));
			$loginexist = $query->rowCount();
			if ($loginexist == 1) {
				$user = $query->fetch();
				if($user['confirmed'] == 0) {
					$update = $DB->prepare('UPDATE users SET confirmed = 1 WHERE login = ? AND token = ?');
					$update->execute(array($login, $token));
					$status = "Votre compte a bien été confirmé ! Je vous redirige...";
				} else {
					$status = "Ce compte a déjà été confirmé ! Je vous redirige...";
				}
			} else {
				$status = "Ce lien de confirmation est invalide, assurez vous d'utiliser le lien present dans le mail. Je vous redirige...";
			}
		} else {
			$status = "Ce lien de confirmation est invalide, assurez vous d'utiliser le lien present dans le mail. Je vous redirige...";
		}
	} else {
		$status = "Ce lien de confirmation est invalide, assurez vous d'utiliser le lien present dans le mail. Je vous redirige...";
	}
?>

<?php include('src/header.php'); ?>

<?php if(isset($status) AND !empty($status)) { echo '<div align="center">'.$status.'</div>'; }?>

<?php include('src/footer.php'); ?>

<?php header('Refresh: 5; URL=login.php'); ?>