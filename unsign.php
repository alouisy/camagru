<?php
	session_start();
	
	require_once('config/functions.php');

	if (isset($_GET['u']) && !empty($_GET['u'])) {
		if (islogged() == false) {
			echo '<script>alert("Oups !\nVous ne devriez pas être là, je vous redirige.");document.location.href="index.php"</script>';
		} else {
			$id = test_input($_GET['u']);
			if ($id != $_SESSION['id']) {
				echo '<script>alert("Le processus de désinscription n\'a pas pu aboutir, veuillez réessayer dans quelques instants.");document.location.href="profil.php"</script>';
			}
			else {
				$_SESSION = array();
				session_destroy();
				delete_user($DB, $id);
				echo '<script>alert("Votre compte a été supprimé avec succès !\nN\'hésitez pas à revenir nous voir si l\'envie vous vient.");document.location.href="index.php"</script>';
			}
		}
	}
	else {
		echo '<script>alert("Oups !\nVous ne devriez pas être là, je vous redirige.");document.location.href="index.php"</script>';
	}
?>