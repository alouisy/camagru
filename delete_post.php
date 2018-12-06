<?php
	session_start();

	require_once('config/functions.php');

	if (isset($_GET['id']) && !empty($_GET['id'])) {
		if (islogged() == false) {
			echo '<script>alert("Oups !\nVous ne devriez pas être là, je vous redirige.");document.location.href="index.php"</script>';
		}
		else {
			$id_picture = intval(test_input($_GET['id']));
			$query = $DB->prepare('SELECT id_user FROM pictures WHERE id_pictures = :id_picture');
			$query->execute(array('id_picture' => $id_picture));
			$data = $query->fetch();
			$query->closeCursor();
			if ($_SESSION['id'] != $data['id_user']) {
				echo '<script>alert("Désolé vous n\'avez rien à faire là ;)");document.location.href="user.php"</script>';
			}
			else {
				delete_post($DB, $id_picture);
				echo '<script>alert("Votre post, ainsi que les données relatives ont été supprimé avec succès.");document.location.href="user.php"</script>';
			}
		}
	}
	else
		echo '<script>alert("Oups !\nVous ne devriez pas être là, je vous redirige.");document.location.href="index.php"</script>';
?>