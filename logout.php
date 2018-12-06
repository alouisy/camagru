<?php
	session_start();

	require_once('config/functions.php');

	if (islogged() == false) {
		header('Location: index.php');
		exit;	
	}
	$user = $_SESSION['login'];
	$_SESSION = array();
	session_destroy();
	echo '<script>alert("Au revoir '.$user.'");document.location.href="index.php"</script>';
?>

<?php include('src/header.php'); ?>

<?php echo '<div align="center">À bientot '.$user.' !</div>'; ?>

<?php include('src/footer.php'); ?>

<?php header('Refresh: 5; URL=index.php'); ?>