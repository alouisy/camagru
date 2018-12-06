<?php
	session_start();

	require_once('config/functions.php');
?>

<?php include 'src/header.php'; ?>

<?php
	$imagesParPage = 5; //Nous allons afficher 5 messages par page.

	$query = $DB->query('SELECT id_pictures FROM pictures');
	$total = $query->rowCount();

	//Nous allons maintenant compter le nombre de pages.
	$nombreDePages = ceil($total / $imagesParPage);
	 
	if(isset($_GET['p'])) {// Si la variable $_GET['p'] existe...
		$pageActuelle = intval($_GET['p']);
		
		if($pageActuelle > $nombreDePages) {// Si la valeur de $pageActuelle (le numéro de la page) est plus grande que $nombreDePages...
			$pageActuelle = $nombreDePages;
		}
	}
	else {// Sinon
		$pageActuelle = 1; // La page actuelle est la n°1    
	}
	 
	$premiereEntree = ($pageActuelle - 1) * $imagesParPage; // On calcul la première entrée à lire

	$query = $DB->prepare('SELECT * FROM pictures ORDER BY id_pictures DESC LIMIT ?,?');
	$query->bindValue(1, $premiereEntree, PDO::PARAM_INT);
	$query->bindValue(2, $imagesParPage, PDO::PARAM_INT);
	$query->execute();
	$ancre = 0;
	$page = '?';
	echo '<section class="container"><div class="wall">';
	while($donnees_images = $query->fetch()) { // On lit les entrées une à une grâce à une boucle
		//Je vais afficher les messages dans des petits tableaux. C'est à vous d'adapter pour votre design...
		//De plus j'ajoute aussi un nl2br pour prendre en compte les sauts à la ligne dans le message.
		$username = get_username_by_id($DB, $donnees_images['id_user']);
		$imagepath = 'pictures/' . $donnees_images['img_name'];
		$nb_likes = get_nb_of_likes($DB, $donnees_images['id_pictures']);
		$legend = $donnees_images['img_legend'];
		$allcomments = get_all_comments($DB, $donnees_images['id_pictures']);
		$ancre += 1;
		if ($pageActuelle != 1)
			$page = '?p='.$pageActuelle.'&';
		echo '<article class="postbox" id="'.$ancre.'">
				<header class="topbox">
					<div class="userpp">
						<img src="img/avatar.png" alt="pp" class="pp">
					</div>
					<div class="usercredits">
						<span><a href="user.php?u='.$username.'" style="text-decoration:none;color:black;">'.$username.'</a></span>
					</div>
				</header>
				<div class="imgbox">
					<a href="post.php?id='.$donnees_images['id_pictures'].'"><img class="postpic" alt="image" src="'.$imagepath.'"></a>
				</div>
				<div class="imgreaction">
					<section class="likearea">
						<form class="likeform" name="likeform" method="post" action="like.php'.$page.'a='.$ancre.'">
							<input type="hidden" name="pic_hidden" id="pic_hidden" value="'.$donnees_images['id_pictures'].'">
							<input type="image" name="submit" src="img/like.png" border="0" alt="Submit">
						</form>
						<div class="likediv">'.$nb_likes.' Likes</div>
						<div class="fbthings">
							<div class="fb-like" data-href="https://azzxl.com/camagru/post.php?id='.$donnees_images['id_pictures'].'" data-layout="button" data-action="like" data-size="small" data-show-faces="true" data-share="true"></div>
						</div>
					</section>
					<section class="descarea">
						<span>'.$legend.'</span>
					</section>
					<div class="commenttextarea">
						'.$allcomments.'						
					</div>
					<section class="commentformarea">
						<form class="commentform" name="commentform" method="post" action="comment.php'.$page.'a='.$ancre.'">
							<input type="hidden" name="my_hidden" id="my_hidden" value="'.$donnees_images['id_pictures'].'">
							<textarea class="commentfield" name="comment" placeholder="Ajouter un commentaire..." maxlength="280"></textarea>
							<input class="commentbutton" type="image" name="submit" src="img/send4.png" border="0"  alt="Submit">
						</form>
					</section>
				</div>
			</article><br /><br />';
	    //J'ai rajouté des sauts à la ligne pour espacer les posts.   
	}
	$query->closeCursor();
		if ($total == 0) {
		$nombreDePages = 1;
		echo '<div align="center">Soyez la première personne à poster du contenu sur notre site !<br />Vous êtes un privilégié 😁❤</div>';
	}
	echo '</div><p align="center" class="pagesindex">Page : '; //Pour l'affichage, on centre la liste des pages
	for($i=1; $i <= $nombreDePages; $i++) { //On fait notre boucle
		//On va faire notre condition
		if($i == $pageActuelle) { //Si il s'agit de la page actuelle...
			echo ' [ '.$i.' ] '; 
		}	
		else { //Sinon...
			echo ' <a href="index.php?p='.$i.'">'.$i.'</a> ';
		}
	}
	echo '</p></section>';
?>


<?php include 'src/footer.php'; ?>