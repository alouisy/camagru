<?php session_start(); ?>
<?php include 'src/header.php'; ?>
			<div id="404-container" align="center">
				<div id="404-logo">
					<img src="img/404.png" alt="logo">
				</div>
				<div id="404-title">
					<h2>Erreur 404</h2>
				</div>
				<div id="404-main">
					Cette page n'existe pas ou n'existe plus.<br />
					Nous vous prions de nous excuser pour la gêne occasionnée.
				</div>
				<div id="404-redirect" style="font-size: small">
					Nous allons vous rediriger...
				</div>
			</div>
<?php include 'src/footer.php'; ?>
<?php header('Refresh: 10; URL=index.php'); exit(); ?>