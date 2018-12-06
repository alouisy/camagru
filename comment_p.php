<?php
	session_start();

	require_once('config/functions.php');

	if (isset($_POST['my_hidden'], $_POST['comment'])) {
		if (!empty($_POST['my_hidden']) && !empty($_POST['comment'])) {
			if (islogged() == false) {
				echo '<script>alert("Vous devez être connecté pour poster un commentaire !");document.location.href="post.php?id='.$_POST['my_hidden'].'";</script>';
			} else {
				$idpics = intval(test_input($_POST['my_hidden']));
				$comment = test_input($_POST['comment']);
				if (!add_comment_on_post($DB, $idpics, $_SESSION['id'], $comment)) {
					echo '<script>alert("Une erreur est survenue lors de l\'envois de votre commentaire, veuillez réessayer dans quelques instants.");document.location.href="index.php";</script>';
				} else {
					send_notif($DB, $_SESSION['id'], $_SESSION['login'], $idpics, $comment);
					header('Location: post.php?id='.$idpics);
					exit();
				}
			}
		} else {
			echo '<script>alert("Une erreur est survenue lors de l\'envois de votre commentaire, veuillez réessayer dans quelques instants.");document.location.href="index.php";</script>';
		}
	} else {
		header('Location: index.php');
		exit();
	}
?>