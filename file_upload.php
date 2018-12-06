<?php
	session_start();

	require_once('config/functions.php');

	if (islogged() == false) {
		header('Location: index.php');
		exit;
	}
	$error = '';
	$img_src = 'filters/camera.png';

	if (isset($_FILES['fupload'])) {

		if ($_FILES['fupload']['error'] == 0 AND $_FILES['fupload']['size'] <= (5 * 1024 * 1024)) {

			define('UPLOAD_DIR', 'pictures/');
			$filedata = pathinfo($_FILES['fupload']['name']);
			$upload_extension = strtolower($filedata['extension']);
			$allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');

			if (in_array($upload_extension, $allowed_extensions)) {

				if (exif_imagetype($_FILES['fupload']['tmp_name'])) {

					$filename = UPLOAD_DIR . 'img_' . $_SESSION['login'] . '_' . time() . '.png';

					if ($upload_extension != 'png') {

						$old_name = UPLOAD_DIR . basename($_FILES['fupload']['name']);
						move_uploaded_file($_FILES['fupload']['tmp_name'], $old_name);
						$old_image = imagecreatefromstring(file_get_contents($old_name));
						imagepng($old_image, $filename , 9);
						imagedestroy($old_image);
						unlink($old_name);
					} else {
						move_uploaded_file($_FILES['fupload']['tmp_name'], $filename);
					}

					$size_oldimage = getimagesize($filename);
	
					if ($size_oldimage[0] != 640 || $size_oldimage[1] != 480) {

						$oldimage = imagecreatefrompng($filename);
						$thumb = thumbnail_box($oldimage, 640, 480);
						imagedestroy($oldimage);
						if(is_null($thumb)) {
							/* image creation or copying failed */
							header('HTTP/1.1 500 Internal Server Error');
							exit();
						}
						imagepng($thumb, $filename, 9);
						imagedestroy($thumb);
					}
					$filter = $_POST['filters'];
					$dst = imagecreatefrompng($filename);
					$src = imagecreatefrompng($filter);
					imagecopymerge_alpha($dst, $src, 0, 0, 0, 0, 640, 480, 100);
					imagepng($dst, $filename, 9);
					imagedestroy($dst);
					imagedestroy($src);
					$img_src = $filename;
					add_img_in_db($DB, $filename, $_SESSION['id'], htmlspecialchars($_POST['legend']));
					echo '<script language="javascript">alert("Votre image a été publié  avec succès !");</script>';
				} else {
					$error = 'Le fichier reçu semble incorrect ou corrompu, rappel des formats d\'image acceptés : jpg, jpeg, png et gif.';
				}
			} else {
				$error = 'Le fichier reçu ne possède pas le bon format, rappel des formats d\'image acceptés : jpg, jpeg, png et gif.';
			}
		} else if ($_FILES['fupload']['error'] > 0) {
			$error = 'Erreur lors de l\'envois du fichier, veuillez réessayer.';
		} else {
			$error = 'Le fichier reçu dépasse la taille maximum autorisée !.';
		}
	}
?>

<?php include 'src/header.php'; ?>
		<div id="file-container" align="center">
			<section id="file-settings">
				<div id="settings">
					<form id="file-form" action="file_upload.php" method="POST" enctype="multipart/form-data">
						<table id="file-table">
							<tr>
								<td>
									<label for="fupload">Sélectionnez l'image à envoyer :</label>
								</td>
								<td>
									<input name="fupload" type="file"  />
								</td>
							</tr>
							<tr>
								<td>
									<label for="filters">Sélectionnez le filtre à appliquer :</label>
								</td>							
								<td>
									<select name="filters">
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
									<label for="legend">Ajoutez une légende à votre image :</label>
								</td>
								<td>
									<textarea class="legendfield" name="legend" maxlength="140"></textarea>
								</td>
							</tr>					
						</table>
						<input type="submit" value="Publier la photo" />
					</form>
					<?php if (isset($error) AND !empty($error)) { echo '<div align="center">'.$error.'</div>'; } ?>
				</div>
			</section>
			<br />
			<section id="file-result">
				<div id="result">
					<img src="<?php echo($img_src) ?>" id="photo" alt="photo">
				</div>
			</section>
		</div>
<?php include 'src/footer.php'; ?>