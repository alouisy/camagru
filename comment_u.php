<?php
	session_start();

	require_once('config/functions.php');

	$page = '';
	$ancre = '';
	if (isset($_GET['u']) && !empty($_GET['u'])) {
		$user = '?u='.test_input($_GET['u']);
	} else {
		header('Location: 404.php');
		exit();
	}
	if (isset($_GET['p']) && !empty($_GET['p'])) {
		$page = '&p='.test_input($_GET['p']);
	}
	if (isset($_GET['a']) && !empty($_GET['a'])) {
		$ancre = '#'.test_input($_GET['a']);
	}
	if (isset($_POST['my_hidden'], $_POST['comment'])) {
		if (!empty($_POST['my_hidden']) && !empty($_POST['comment'])) {
			if (islogged() == false) {
				echo '<script>alert("Vous devez être connecté pour poster un commentaire !");document.location.href="user.php'.$user.$page.$ancre.'"</script>';
			} else {
				$idpics = intval(test_input($_POST['my_hidden']));
				$comment = test_input($_POST['comment']);
				if (!add_comment_on_post($DB, $idpics, $_SESSION['id'], $comment)) {
					echo '<script>alert("Une erreur est survenue lors de l\'envois de votre commentaire, veuillez réessayer dans quelques instants.");document.location.href="user.php'.$user.$page.$ancre.'"</script>';
				} else {
					send_notif($DB, $_SESSION['id'], $_SESSION['login'], $idpics, $comment);
					header('Location: user.php'.$user.$page.$ancre);
					exit();
				}
			}
		} else {
			echo '<script>alert("Une erreur est survenue lors de l\'envois de votre commentaire, veuillez réessayer dans quelques instants.");document.location.href="user.php'.$user.$page.$ancre.'"</script>';
		}
	} else {
		header('Location: user.php'.$user.$page.$ancre);
		exit();
	}
?>