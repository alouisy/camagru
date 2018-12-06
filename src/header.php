<!DOCTYPE html>
<html>
	<head>
		<title>Azztagram</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<?php
			if ($_SERVER["PHP_SELF"] == '/camagru/post.php') {
				echo '<meta property="og:url" content="https://azzxl.com/camagru/post.php?id='.$id_picture.'" />
		<meta property="og:type" content="website" />
		<meta property="og:title" content="Nouveau post Azztagram.com" />
		<meta property="og:description" content="'.$legend.'" />
		<meta property="og:image" content="http://azzxl.com/camagru/'.$imagepath.'" />
		<meta property="og:image:secure_url" content="https://azzxl.com/camagru/'.$imagepath.'" />
		<meta property="og:image:width" content="640" />
		<meta property="og:image:height" content="480" />';
			}
		?>
		<link rel="stylesheet" type="text/css" href="src/mystyle.css" \>
		<link rel="stylesheet" type="text/css" href="src/gallerystyle.css" \>
		<link href="img/42icon.png" rel="icon" type="image/svg">

	</head>
	<body>
		<div id="fb-root"></div>
		<script>(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = 'https://connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.12';
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));</script>
		<div id="headernav">
			<nav align="center">
				<ul>
					<?php
						if (isset($_SESSION['id']) AND isset($_SESSION['login'])) {
							echo '<li><a href="index.php"><div><img src="img/42nav.png" alt="42logo" style="height:55px;width:140px;"></div></a></li><li><a href="user.php"><div>Ma Galerie</div></a></li><li><a href="upload_live.php"><div>Capturer</div></a></li><li><a href="file_upload.php"><div>Poster</div></a></li><li><a href="profil.php"><div>Profil</div></a></li><li><a href="logout.php"><div>Déconnexion</div></a></li><li><a href="about.php"><div>À Propos</div></a></li>';
						}
						else {
							echo '<li><a href="index.php"><div><img src="img/42nav.png" alt="42logo" style="height:55px;width:140px;"></div></a></li><li><a href="signup.php"><div>Inscription</div></a></li><li><a href="login.php"><div>Connexion</div></a></li><li><a href="about.php"><div>À Propos</div></a></li>';
						}
					?>
				</ul>
			</nav>
		</div>
		<div class="content">
