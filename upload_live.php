<?php
	session_start();

	require_once('config/functions.php');

	if (islogged() == false) {
		header('Location: index.php');
		exit;	
	}
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (!empty($_POST['my_hidden']) AND !empty($_POST['filters'])) {
			define('UPLOAD_DIR', 'pictures/');
			$img = test_input($_POST['my_hidden']);
			$img = str_replace('data:image/png;base64,', '', $img);
			$img = str_replace(' ', '+', $img);
			$data = base64_decode($img);
			$file = UPLOAD_DIR . 'img_' . $_SESSION['login'] . '_' . time() . '.png';
			$success = file_put_contents($file, $data);
			if ($success AND exif_imagetype($file) AND exif_imagetype(test_input($_POST['filters']))) {
				$size_oldimage = getimagesize($file);
				if ($size_oldimage[0] != 640 || $size_oldimage[1] != 480) {
					$oldimage = imagecreatefrompng($file);
					$thumb = thumbnail_box($oldimage, 640, 480);
					imagedestroy($oldimage);
					if(is_null($thumb)) {
						/* image creation or copying failed */
						header('HTTP/1.1 500 Internal Server Error');
						exit();
					}
					imagepng($thumb, $file, 9);
					imagedestroy($thumb);
				}
				$filter = test_input($_POST['filters']);
				$dst = imagecreatefrompng($file);
				$src = imagecreatefrompng($filter);
				imagecopymerge_alpha($dst, $src, 0, 0, 0, 0, 640, 480, 100);
				imagepng($dst, $file, 9);
				imagedestroy($dst);
				imagedestroy($src);
				$id = add_img_in_db($DB, $file, $_SESSION['id'], htmlspecialchars($_POST['legend']));
				echo '<script>alert("Votre image a été publié avec succès !");</script>';
			} else {
				unlink($file);
				echo '<script>alert("Il y a eu une erreur lors du traitement de votre image,\nles données que nous avons reçu sont corrompues. Veuillez réessayer.");document.location.href="upload_live.php";</script>';
			}
		}
	}
?>

<?php include 'src/header.php'; ?>
			<div id="webcam-container" align="center">
				<section id="webcam-preview">
					<article id="webcam-live">
						<div id="live">
							<video id="video"></video>
							<img src="filters/42.png" id="filtre" alt="filtre">
							<button id="startbutton">Prendre une photo</button>
						</div>
					</article>
					<aside id="webcam-side">
						<div id="side">
							<?php
								$count = 0;
								$lastpics = get_last_pics($DB, $_SESSION['id']);
								foreach ($lastpics as $pic) {
									$count++;
									echo '<a href="post.php?id='.$pic['id_pictures'].'"><img class="minipic" src="pictures/'.$pic['img_name'].'" class="last" alt="photo"></a>';
								}
								if ($count == 0)
									echo '<span class="last">Vous n\'avez pas encore publié de photo sur notre site</span>';
							?>
						</div>
					</aside>
				</section>

				<br />

				<section id="webcam-settings">
					<div id="settings">
						<form id="webcam-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" accept-charset="utf-8">
							<input type="hidden" name="my_hidden" id="my_hidden" value="">
							<table id="webcam-table">
								<tr>
									<td>
										<label for="filters">Choisissez un filtre :</label>
									</td>
									<td>
										<select name="filters" onchange="changeFilter(this.value)">
											<option value="filters/42.png" selected>Filtre 42</option>
											<option value="filters/deadpool.png">Filtre Deadpool</option>
											<option value="filters/superchonchon.png">Filtre Super-Chonchon</option>
											<option value="filters/cry_and_laught.png">Filtre Memes Troll</option>
											<option value="filters/cry.png">Filtre Memes Emo</option>
											<option value="filters/yao.png">Filtre Memes YaoMing</option>
											<option value="filters/tropic.png">Filtre Tropic</option>
											<option value="filters/goldframe.png">Filtre Tableau</option>
											<option value="filters/cartoon.png">Filtre Cartoon (Enfants)</option>
											<option value="filters/blank.png">Sans Filtre</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>
										<label for="legend">Ajoutez une légende :</label>
									</td>
									<td>
										<textarea class="legendfield" name="legend" maxlength="140"></textarea>
									</td>
								</tr>
							</table>
							<input type="submit" value="Publier la photo">
						</form>
					</div>
				</section>

				<br />

				<section id="webcam-result">
					<div id="result">
						<canvas id="canvas"></canvas>
						<canvas id="canvas2"></canvas>
						<img src="filters/camera.png" id="photo" alt="photo" name="photo">
					</div>
				</section>
			</div>

			<script src="src/webcam.js"></script>
			<script>
				function changeFilter(source) {
					if (source == "")
						return;
					var Filter = document.getElementById('filtre');
					Filter.src = source;
				}
			</script>
<?php include 'src/footer.php'; ?>