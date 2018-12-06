<?php
	session_start();
	
	require_once('config/functions.php');

	if (isset($_GET['u'])) {
		$username = test_input($_GET['u']);
		if(!empty($username)) {
			$user_id = get_id_by_username($DB, $username);
			if ($user_id == 0) {
				header('Location: 404.php');
				exit();
			}
		} else {
			header('Location: 404.php');
			exit();
		}
	} else if (islogged() == true){
		$username = $_SESSION['login'];
		$user_id = intval($_SESSION['id']);
	} else {
		header('Location: 404.php');
		exit();
	}
?>

<?php include 'src/header.php'; ?>

<?php
	$imagesParPage = 5;
	$query = $DB->prepare('SELECT id_pictures FROM pictures WHERE id_user = ?');
	$query->execute(array($user_id));
	$total = $query->rowCount();

	$nombreDePages = ceil($total / $imagesParPage);

	if (isset($_GET['p'])) {
		$pageActuelle = intval($_GET['p']);
		
		if ($pageActuelle > $nombreDePages) {
			$pageActuelle = $nombreDePages;
		}
	} else {
		$pageActuelle = 1;
	}

	$premiereEntree = ($pageActuelle - 1) * $imagesParPage;

	$query = $DB->prepare('SELECT * FROM pictures WHERE id_user = ? ORDER BY id_pictures DESC LIMIT ?,?');
	$query->bindValue(1, $user_id, PDO::PARAM_INT);
	$query->bindValue(2, $premiereEntree, PDO::PARAM_INT);
	$query->bindValue(3, $imagesParPage, PDO::PARAM_INT);
	$query->execute();
	$ancre = 0;
	$page = '?u='.$username.'&';	
	echo '<section class="container"><div class="wall">';
	while($donnees_images = $query->fetch()) {
		$imagepath = 'pictures/' . $donnees_images['img_name'];
		$nb_likes = get_nb_of_likes($DB, $donnees_images['id_pictures']);
		$legend = $donnees_images['img_legend'];
		$allcomments = get_all_comments($DB, $donnees_images['id_pictures']);
		$ancre += 1;
		if ($pageActuelle != 1)
			$page = '?u='.$username.'&p='.$pageActuelle.'&';
			
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
						<form class="likeform" name="likeform" method="post" action="like_u.php'.$page.'a='.$ancre.'">
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
						<form class="commentform" name="commentform" method="post" action="comment_u.php'.$page.'a='.$ancre.'">
							<input type="hidden" name="my_hidden" id="my_hidden" value="'.$donnees_images['id_pictures'].'">
							<textarea class="commentfield" name="comment" placeholder="Ajouter un commentaire..." maxlength="280"></textarea>
							<input class="commentbutton" type="image" name="submit" src="img/send4.png" border="0"  alt="Submit">
						</form>
					</section>
				</div>
			</article><br /><br />';
	}
	$query->closeCursor();
	if ($total == 0) {
		$nombreDePages = 1;
		echo '<div align="center">Vous n\'avez pas encore poster de contenu sur notre site..<br />Oubliez vos craintes et publiez quelque chose, vous allez <b>A D O R E R</b> le résultat 😉</div>';
	}
	echo '</div><p align="center" class="pagesindex">Page : ';
	for($i=1; $i <= $nombreDePages; $i++) {

		if($i == $pageActuelle) {
			echo ' [ '.$i.' ] '; 
		}	
		else {
			echo ' <a href="user.php?u='.$username.'&p='.$i.'">'.$i.'</a> ';
		}
	}
	echo '</p></section>';
?>


<?php include 'src/footer.php'; ?>