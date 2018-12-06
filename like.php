<?php
	session_start();

	require_once('config/functions.php');

	$page = '';
	$ancre = '';
	if (isset($_GET['p']) && !empty($_GET['p'])) {
		$page = '?p='.test_input($_GET['p']);
	}
	if (isset($_GET['a']) && !empty($_GET['a'])) {
		$ancre = '#'.test_input($_GET['a']);
	}
	if (isset($_POST['pic_hidden'])) {
		if (!empty($_POST['pic_hidden'])) {
			if (islogged() == false) {
				echo '<script>alert("Vous devez être connecté pour liker un post !");document.location.href="index.php'.$page.$ancre.'"</script>';
			} else {
				$idpics = intval(test_input($_POST['pic_hidden']));
				if (!like_post($DB, $idpics, $_SESSION['id'])) {
					echo '<script>alert("Une erreur est survenue lors de votre like, veuillez réessayer dans quelques instants.");document.location.href="index.php'.$page.$ancre.'"</script>';
				} else {
					header('Location: index.php'.$page.$ancre);
				}
			}
		} else {
			echo '<script>alert("Une erreur est survenue lors de votre like, veuillez réessayer dans quelques instants.");document.location.href="index.php'.$page.$ancre.'"</script>';
		}
	} else {
		header('Location: index.php'.$page.$ancre);
	}
?>