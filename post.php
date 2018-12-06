<?php
	session_start();

	require_once('config/functions.php');

	if(isset($_GET['id'])) {
		$id_picture = test_input($_GET['id']);
		if ($id_picture != 0) {
			$query = $DB->prepare('SELECT * FROM pictures WHERE id_pictures = ?');
			$query->execute(array($id_picture));
			$imgexist = $query->rowCount();
			if ($imgexist == 1) {
				$data = $query->fetch();
				$query->closeCursor();
				$username = get_username_by_id($DB, intval($data['id_user']));
				$imagepath = 'pictures/' . $data['img_name'];
				$nb_likes = get_nb_of_likes($DB, $id_picture);
				$legend = $data['img_legend'];
				$allcomments = get_all_comments($DB, $id_picture);
			} else {
				echo '<script>alert("Oups ! Ce lien est invalide, vous allez être redirigé.");document.location.href="index.php"</script>';
			}
		} else {
			echo '<script>alert("Oups ! Ce lien est invalide, vous allez être redirigé.");document.location.href="index.php"</script>';
		}
	} else {
		echo '<script>alert("Oups ! Ce lien est invalide, vous allez être redirigé.");document.location.href="index.php"</script>';
	}
?>

<?php include 'src/header.php'; ?>
			<section class="container">
				<div class="wall">
					<article class="postbox">
						<header class="topbox">
							<div class="userpp">
								<img src="img/avatar.png" alt="pp" class="pp">
							</div>
							<div class="usercredits">
								<span><a href="user.php?u=<?php echo $username; ?>" style="text-decoration:none;color:black;"><?php echo $username; ?></a></span>
							</div>
						</header>
						<div class="imgbox">
							<a href="user.php?u=<?php echo $username; ?>"><img class="postpic" alt="image" src="<?php echo $imagepath; ?>"></a>
						</div>
						<div class="imgreaction">
							<section class="likearea">
								<form class="likeform" name="likeform" method="post" action="like_p.php">
									<input type="hidden" name="pic_hidden" id="pic_hidden" value="<?php echo $data['id_pictures']; ?>">
									<input type="image" name="submit" src="img/like.png" border="0" alt="Submit">
								</form>
								<div class="likediv"><?php echo $nb_likes; ?> Likes</div>
								<div class="fbthings">
									<div class="fb-like" data-href="https://azzxl.com/camagru/post.php?id=<?php echo $id_picture; ?>" data-layout="button" data-action="like" data-size="small" data-show-faces="true" data-share="true"></div>
								</div>
							</section>
							<section class="descarea">
								<span><?php echo $legend; ?></span>
							</section>
							<div class="commenttextarea">
								<?php echo $allcomments; ?>
							</div>
							<section class="commentformarea">
								<form class="commentform" name="commentform" method="post" action="comment_p.php">
									<input type="hidden" name="my_hidden" id="my_hidden" value="<?php echo $data['id_pictures']; ?>">
									<textarea class="commentfield" name="comment" placeholder="Ajouter un commentaire..." maxlength="280"></textarea>
									<input class="commentbutton" type="image" name="submit" src="img/send4.png" border="0"  alt="Submit">
								</form>
							</section>
						</div>
					</article>
					<?php if (isset($_SESSION['id']) AND $_SESSION['id'] == $data['id_user']) { ?>
					<br />
					<div align="center"><a href="delete_post.php?id=<?php echo $id_picture; ?>">Cliquez ici pour supprimer cette publication, attention cette action est irreversible !</a></div>
					<?php } ?>
				</div>
			</section>
<?php include 'src/footer.php'; ?>