<?php
	session_start();

	require_once('config/functions.php');

	if (islogged()) {
		header('Location: index.php');
		exit();	
	}
	if (isset($_GET['section'])) {
		$section = test_input($_GET['section']);
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			
			if (isset($_POST['verif_mail'])) {
				$verif_mail = test_input($_POST['verif_mail']);
				if (!empty($verif_mail) AND filter_var($verif_mail, FILTER_VALIDATE_EMAIL)) {
					
					if (isset($_POST['verif_code'])) {
						$verif_code = test_input($_POST['verif_code']);
						if (!empty($verif_code)) {
							
							if (isset($_POST['verif_pass'])) {
								$verif_pass = test_input($_POST['verif_pass']);
								if (!empty($verif_pass) AND preg_match("#[a-z]+#", $verif_pass) AND preg_match("#[A-Z]+#", $verif_pass) AND preg_match("#[0-9]+#", $verif_pass)) {
									
									if (isset($_POST['verif_passc'])) {
										$verif_passc = test_input($_POST['verif_passc']);
										if (!empty($verif_passc) AND  preg_match("#[a-z]+#", $verif_passc) AND preg_match("#[A-Z]+#", $verif_passc) AND preg_match("#[0-9]+#", $verif_passc)) {
											
											if ($verif_pass == $verif_passc) {
												$codeexist = $DB->prepare('SELECT id FROM recovery WHERE code = ? AND mail = ?');
												$codeexist->execute(array($verif_code, $verif_mail));
												$codeexist = $codeexist->rowCount();
												if ($codeexist == 1) {
													$h_pass = hash('sha256', $verif_pass);
													$update_pass = $DB->prepare('UPDATE users SET password = ? WHERE mail = ?');
													$update_pass->execute(array($h_pass, $verif_mail));
													$delete_recovery = $DB->prepare('DELETE FROM recovery WHERE mail = ?');
													$delete_recovery->execute(array($verif_mail));
													echo '<script>alert("Votre mot de passe a bien été modifié !\nVous pouvez dès à présent vous connecter.");document.location.href="login.php"</script>';
												} else {
													$verif_mailErr = "L'adresse e-mail ne correspond pas avec le code de vérification fourni, vérifiez votre saisie.";
													$verif_codeErr = "Le code de vérification ne correspond pas avec l'adresse e-mail fournie, vérifiez votre saisie.";
												}
											} else {
												$verif_passErr = "Les 2 mots de passe ne correspondent pas, vérifiez votre saisie.";
												$verif_passcErr = "Les 2 mots de passe ne correspondent pas, vérifiez votre saisie.";
											}
											
										} else {
											$verif_passcErr = "Pour être accepté, votre MDP doit contenir au minimum 8 caractères dont obligatoirement une majuscule (A-Z), une minuscule (a-z) et un chiffre (0-9).";
										}
									} else {
										$verif_passcErr = "Vous devez entrer une seconde fois le nouveau mot de passe.";
									}
								} else {
									$verif_passErr = "Pour être accepté, votre MDP doit contenir au minimum 8 caractères dont obligatoirement une majuscule (A-Z), une minuscule (a-z) et un chiffre (0-9).";
								}
							} else {
								$verif_passErr = "Vous devez entrer le nouveau mot de passe de votre choix pour continuer.";
							}
						} else {
							$verif_codeErr = "Le code de vérification que vous avez saisi est invalide, vérifiez votre saisie.";
						}
					} else {
						$verif_codeErr = "Vous devez entrer le code de vérification que nous vous avons envoyé par mail.";
					}
				} else {
					$verif_mailErr = "L'adresse e-mail que vous avez saisi est invalide, vérifiez votre saisie.";
				}
			} else {
				$verif_mailErr = "Vous devez entrer l'adresse e-mail associée a votre compte.";
			}
		}
	} else {
		$section = '';
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			if (isset($_POST['recovery_mail']) AND !empty($_POST['recovery_mail'])) {
				$recovery_mail = test_input($_POST['recovery_mail']);
				if (filter_var($recovery_mail, FILTER_VALIDATE_EMAIL)) {
					$mailexist = $DB->prepare('SELECT id, login FROM users WHERE mail = ?');
					$mailexist->execute(array($recovery_mail));
					$mailexist_count = $mailexist->rowCount();
					if ($mailexist_count == 1) {
						$login = $mailexist->fetch();
						$login = $login['login'];
						$mailexist->closeCursor();
						$recovery_code = '';
						for ($i = 1; $i < 8; $i++) {
							$recovery_code .= mt_rand(0,9);
						}
						$recovery_mailexist = $DB->prepare('SELECT id FROM recovery WHERE mail = ?');
						$recovery_mailexist->execute(array($recovery_mail));
						$recovery_mailexist = $recovery_mailexist->rowCount();
						if ($recovery_mailexist == 1) {
							$recovery_insert = $DB->prepare('UPDATE recovery SET code = ? WHERE mail = ?');
							$recovery_insert->execute(array($recovery_code, $recovery_mail));
						} else {
							$recovery_insert = $DB->prepare('INSERT INTO recovery (mail, code) VALUES (?, ?)');
							$recovery_insert->execute(array($recovery_mail, $recovery_code));
						}
						$message = "Bonjour ".$login.",<br/>";
						$message .= "Vous recevez cet e-mail suite à votre demande de réinitialisation de votre mot de passe sur le site Azztagram.com, si vous n'êtes pas l'auteur de cette demande ignorez simplement ce message.<br/>";
						$message .= 'Pour réinitialiser votre mot de passe et de nouveau accéder à votre compte personnel, veuillez noter le code unique suivant :<br/><br/>';
						$message .= '<b>'.$recovery_code.'</b><br/><br/>';
						$message .= "L'équipe Azztagram.com";
						send_mail($recovery_mail, "Réinitialisation de mot de passe - Azztragram.com", $message);
						echo '<script>alert("Votre demande de réinitialisation de mot de passe a bien été prise en compte !\nVeuillez compléter la procédure en copiant le code de vérification que nous venons de vous envoyer par mail.");document.location.href="recovery.php?section=code"</script>';
					} else {
						$error = "L'adresse e-mail que vous avez saisi n'est associée à aucun compte utilisateur. Veuillez vérifier votre saisie.";
					}
				} else {
					$recovery_mailErr = "Format d'e-mail invalide, veuillez vérifier votre saisie.";
				}
			} else {
				$recovery_mailErr = "Vous devez rentrer l'adresse e-mail avec laquelle vous vous êtes inscrit sur le site.";
			}
		}
	}

?>

<?php include('src/header.php'); ?>

	<div id="recovery-container" align="center">
		<div id="recovery-logo">
			<img src="img/42icon.png" alt="logo">
		</div>
		<div id="recovery-title">
			<h2>Mot de passe oublié</h2>
		</div>
		<div id="recovery-main">
			<?php if ($section == 'code') { ?>
			<form id="recovery-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>?section=code" accept-charset="UTF-8">
				<table id="recovery-table">
					<tr>
						<td align="right">
							<label for="verif_mail">Adresse mail :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="email" name="verif_mail" id="verif_mail" placeholder="Votre adresse e-mail" size="25" value="<?php if(isset($verif_mail)) { echo $verif_mail; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($verif_mailErr)) { echo $verif_mailErr; } ?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="verif_code">Code de vérification :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="text" name="verif_code" id="verif_code" placeholder="Votre code de vérification" size="25" value="<?php if(isset($verif_code)) { echo $verif_code; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($verif_codeErr)) { echo $verif_codeErr; } ?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="verif_pass">Nouveau mot de passe :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="password" pattern=".{8,}" title="Le MDP doit contenir au minimum 8 caractères dont des chiffres (0-9), des minuscules (a-z) et des majuscules (A-Z)." name="verif_pass" id="verif_pass" placeholder="Votre nouveau mot de passe" size="25" value="<?php if(isset($verif_pass)) { echo $verif_pass; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($verif_passErr)) { echo $verif_passErr; } ?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="verif_passc">Confirmation du mot de passe :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="password" pattern=".{8,}" title="Le MDP doit contenir au minimum 8 caractères dont des chiffres (0-9), des minuscules (a-z) et des majuscules (A-Z)." name="verif_passc" id="verif_passc" placeholder="Confirmez le mot de passe" size="25" value="<?php if(isset($verif_passc)) { echo $verif_passc; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($verif_passcErr)) { echo $verif_passcErr; } ?></span>
						</td>
					</tr>
				</table>
				<input type="submit" name="verif_submit" value="Valider">
			</form>
			<?php } else { ?>
			<form id="recovery-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" accept-charset="UTF-8">
				<table id="recovery-table">
					<tr>
						<td align="right">
							<label for="recovery_mail">Adresse mail :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="email" name="recovery_mail" id="recovery_mail" placeholder="Votre adresse e-mail" size="25" value="<?php if(isset($recovery_mail)) { echo $recovery_mail; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($recovery_mailErr)) { echo $recovery_mailErr; } ?></span>
						</td>
					</tr>
				</table>
				<input type="submit" name="recovery_submit" value="Valider">
				<br />
				<a href="recovery.php?section=code">Vous avez déjà un code de vérification ? Cliquez ici</a>
			</form>
			<?php } ?>
			<?php if (isset($error) AND !empty($error)) { echo '<div align="center">'.$error.'</div>'; }?>
		</div>
	</div>

<?php include('src/footer.php'); ?>
