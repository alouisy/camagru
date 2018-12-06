<?php
	session_start();

	require_once('config/functions.php');

	if (islogged() == false) {
		header('Location: index.php');
		exit;	
	}
	$status = get_notification_status($DB, $_SESSION['id']);
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (isset($_POST['pass']) AND !empty($_POST['pass'])) {
			$pass = htmlspecialchars($_POST['pass']);
			$h_pass = hash('sha256', $pass);
			$query = $DB->prepare('SELECT * FROM users WHERE id = ? AND password = ?');
			$query->execute(array($_SESSION['id'], $h_pass));
			$goodpwd = $query->rowCount();
			if ($goodpwd == 1) {
				$succes = array();
				if (isset($_POST["notif"]) AND !empty($_POST["notif"])) {
					$notif = intval(test_input($_POST["notif"]));
					if (($notif == 0 OR $notif == 1) AND $notif != $status[0][0]) {
						$new = ($status[0][0] == 1) ? 0 : 1;
						$update = $DB->prepare('UPDATE users SET notifications = ? WHERE id = ? AND password = ?');
						$update->execute(array($new, $_SESSION['id'], $h_pass));
						$status = get_notification_status($DB, $_SESSION['id']);
						array_push($succes, 'Votre paramètre de notification a bien été changé !');
					}
				} else {
					$notifErr = "Il est nécessaire de sélectionner si OUI ou NON vous voulez recevoir des notifications.";
				}
				if (isset($_POST['n_login']) AND !empty($_POST['n_login'])) {
					$n_login = test_input($_POST['n_login']);
					if (preg_match("#^[a-zA-Z0-9_-]+$#", $n_login)) {
						if (!usedLogin($DB, $n_login)) {
							$query = $DB->prepare('UPDATE users SET login = ? WHERE id = ? AND password = ?');
							$query->execute(array($n_login, $_SESSION['id'], $h_pass));
							$_SESSION['login'] = $n_login;
							array_push($succes, 'Votre login a bien été changé !');
							$n_login = '';
						} else {
							$n_loginErr = "Le pseudo que vous avez choisi est déjà pris, veuillez en choisir un autre.";
						}
					} else {
						$n_loginErr = "Seul les lettres (a-z <> A-Z), les chiffres (0-9), les underscores (_) et les tirets (-) sont autorisés";
					}
				}
				if (isset($_POST['n_mail']) AND !empty($_POST['n_mail'])) {
					$n_mail = test_input($_POST['n_mail']);
					if (filter_var($n_mail, FILTER_VALIDATE_EMAIL)) {
						if (!usedMail($DB, $n_mail)) {
							$query = $DB->prepare('UPDATE users SET mail = ? WHERE id = ? AND password = ?');
							$query->execute(array($n_mail, $_SESSION['id'], $h_pass));
							$_SESSION['mail'] = $n_mail;
							array_push($succes, 'Votre adresse e-mail a bien été changé !');
							$n_mail = '';
						} else {
							$n_mailErr = "L'adresse e-mail que vous avez choisi est déjà présente dans notre base de donnée, veuillez en choisir une autre.";
						}
					} else {
						$n_mailErr = "Format d'e-mail invalide, veuillez vérifier votre saisie.";
					}
				}
				if (isset($_POST['n_pass'], $_POST['c_pass']) AND !empty($_POST['n_pass']) AND !empty($_POST['c_pass'])) {
					if ($_POST['n_pass'] == $_POST['c_pass']) {
						$n_pass = test_input($_POST['n_pass']);
						if (preg_match("#[a-z]+#", $n_pass) AND preg_match("#[A-Z]+#", $n_pass) AND preg_match("#[0-9]+#", $n_pass)) {
							$query = $DB->prepare('UPDATE users SET password = ? WHERE id = ? AND password = ?');
							$query->execute(array(hash('sha256', $n_pass), $_SESSION['id'], $h_pass));
							array_push($succes, 'Votre mot de passe a bien été changé !');
							$n_pass = '';
						} else {
							$n_passErr = "Pour être accepté, votre MDP doit contenir au minimum 8 caractères dont obligatoirement une majuscule (A-Z), une minuscule (a-z) et un chiffre (0-9).";
						}
					} else {
						$n_passErr = "Les MDP ne correspondent pas, veuillez vérifier votre saisie";
						$c_passErr = "Les MDP ne correspondent pas, veuillez vérifier votre saisie";
					}
				}
			} else {
				$pass = '';
				$passErr = "Le mot de passe saisit n'est pas valide, vérifiez votre entrée.";
			}
		}
	}
?>

<?php include 'src/header.php'; ?>
			<div id="infos-container" align="center">
				<div id="infos-logo">
					<img src="img/42icon.png" alt="logo">
				</div>
				<div id="infos-title">
					<h2>Mes infos personnelles</h2>
				</div>
				<div id="infos-main">
					<table id="infos-table">
						<tr>
							<td align="right">
								Pseudo :
							</td>
							<td>
								<?php echo $_SESSION['login']; ?>
							</td>
						</tr>
						<tr>
							<td align="right">
								Adresse mail :
							</td>
							<td>
								<?php echo $_SESSION['mail']; ?>
							</td>
						</tr>
						<tr>
							<td align="right">
								Date d'inscription :
							</td>
							<td>
								<?php echo get_date_in_db($DB, $_SESSION['id']); ?>
							</td>
						</tr>
						<tr>
							<td align="right">
								Notifications par mail :
							</td>
							<td>
								<?php echo $status[0][2]; ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div id="update-container" align="center">
				<div id="update-title">
					<h2>Modifier mes données personnelles</h2>
				</div>
				<div id="update-main">
					<form id="update-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" accept-charset="UTF-8">
						<table id="update-table">
							<tr>
								<td align="right">
									<label>Recevoir des notifications par mail :</label>
								</td>
								<td>
									<input type="radio" name="notif" value="01" id="yes" <?php echo $status[0][1]; ?>/> <label for="yes">Oui</label>
									<input type="radio" name="notif" value="00" id="no" <?php echo $status[1][1]; ?>/> <label for="no">Non</label>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span class="error"><?php if (isset($notifErr)) { echo $notifErr; }?></span>
								</td>
							</tr>
							<tr>
								<td align="right">
									<label for="n_login">Nouveau pseudo :</label>
								</td>
								<td>
									<input type="text" name="n_login" id="n_login" value="<?php if (isset($n_login)) { echo $n_login; }?>">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span class="error"><?php if (isset($n_loginErr)) { echo $n_loginErr; }?></span>
								</td>
							</tr>
							<tr>
								<td align="right">
									<label for="n_mail">Nouvelle adresse mail :</label>
								</td>
								<td>
									<input type="mail" name="n_mail" id="n_mail" value="<?php if (isset($n_mail)) { echo $n_mail; }?>">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span class="error"><?php if (isset($n_mailErr)) { echo $n_mailErr; }?></span>
								</td>
							</tr>
							<tr>
								<td align="right">
									<label for="n_pass">Nouveau mot de passe :</label>
								</td>
								<td>
									<input type="password" pattern=".{8,}" title="Le MDP doit contenir au minimum 8 caractères dont des chiffres (0-9), des minuscules (a-z) et des majuscules (A-Z)." name="n_pass" id="n_pass" value="<?php if (isset($n_pass)) { echo $n_pass; }?>">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span class="error"><?php if (isset($n_passErr)) { echo $n_passErr; }?></span>
								</td>
							</tr>
							<tr>
								<td align="right">
									<label for="c_pass">Confirmez le nouveau mot de passe :</label>
								</td>
								<td>
									<input type="password" pattern=".{8,}" title="Le MDP doit contenir au minimum 8 caractères dont des chiffres (0-9), des minuscules (a-z) et des majuscules (A-Z)." name="c_pass" id="c_pass" value="<?php if (isset($c_pass)) { echo $c_pass; }?>">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span class="error"><?php if (isset($c_passErr)) { echo $c_passErr; }?></span>
								</td>
							</tr>
							<tr>
								<td align="right">
									<label for="pass">Mot de passe actuel :</label> <span class="error">*</span>
								</td>
								<td>
									<input type="password" name="pass" id="pass" value="<?php if (isset($pass)) { echo $pass; }?>">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<span class="error"><?php if (isset($passErr)) { echo $passErr; }?></span>
								</td>
							</tr>
						</table>
						<input type="submit" name="update" value="Valider les changements">
					</form>
					<?php if (isset($succes) AND !empty($succes)) { foreach ($succes as &$msg) { echo '<br /><span>'.$msg.'</span>'; } } ?>
				</div>
			</div>
			<div id="unsing-container" align="center">
				<div id="unsing-title">
					<h2>Supprimer mon compte</h2>
				</div>
				<div id="unsing-main">
					<a href="unsign.php?u=<?php echo $_SESSION['id']; ?>">Cliquez ici pour vous désinscrire, attention cette action est irreversible !</a>
				</div>
			</div>
<?php include 'src/footer.php'; ?>