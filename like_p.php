<?php
	session_start();

	require_once('config/functions.php');

	if (isset($_POST['pic_hidden'])) {
		if (!empty($_POST['pic_hidden'])) {
			if (islogged() == false) {
				echo '<script>alert("Vous devez être connecté pour liker un post !");document.location.href="post.php?id='.$_POST['pic_hidden'].'";</script>';
			} else {
				$idpics = intval(test_input($_POST['pic_hidden']));
				if (!like_post($DB, $idpics, $_SESSION['id'])) {
					echo '<script>alert("Une erreur est survenue lors de votre like, veuillez réessayer dans quelques instants.");document.location.href="index.php";</script>';
				} else {
					header('Location: post.php?id='.$idpics);
					exit();
				}
			}
		} else {
			echo '<script>alert("Une erreur est survenue lors de votre like, veuillez réessayer dans quelques instants.");document.location.href="index.php";</script>';
		}
	} else {
		header('Location: index.php');
		exit();
	}
?>