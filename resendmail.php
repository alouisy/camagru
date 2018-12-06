<?php
	session_start();

	require_once('config/functions.php');

	if (islogged()) {
		header('Location: index.php');
		exit();	
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (isset($_POST['resend_mail']) AND !empty($_POST['resend_mail'])) {
			$resend_mail = test_input($_POST['resend_mail']);
			if (filter_var($resend_mail, FILTER_VALIDATE_EMAIL)) {
				$data = isnotconfirmed($DB, $resend_mail);
				if ($data == -1) {
					$error = "L'adresse e-mail que vous avez saisi n'est associée à aucun compte utilisateur. Veuillez vérifier votre saisie.";
				} else if ($data == 0) {
					$error = "Ce compte a déjà été confirmé. Je vous redirige...";
				} else {
					$message = 'Bonjour '.$data['login'].',<br/>Nous vous confirmons le bon déroulement de votre inscription sur le site Azztagram.com, et vous souhaitons une belle aventure photographique.<br/>Pour acceder à votre compte personnel, veuillez confirmer votre adresse e-mail en cliquant sur le lien suivant :<br/><br/><a href="https://azzxl.com/camagru/confirmation.php?login='.urlencode($data['login']).'&token='.$data['token'].'">Confirmer mon adresse e-mail</a><br/><br/>Rappel de vos identifiants :<br/><br/>- Login = '.$data['login'].'<br/>- Mail = '.$resend_mail.'<br/><br/>L\'équipe Azztagram.com';
					send_mail($resend_mail, "Confirmation d'inscription - Azztagram.com", $message);
					$error = "Un nouvel e-mail de confirmation vient de vous être envoyé, veuillez consulter votre boite mail.";
					$resend_mail = '';
				}
			} else {
				$recovery_mailErr = "Format d'e-mail invalide, veuillez vérifier votre saisie.";
			}
		} else {
			$recovery_mailErr = "Vous devez rentrer l'adresse e-mail avec laquelle vous vous êtes inscrit sur le site.";
		}
	}

?>

<?php include('src/header.php'); ?>

	<div id="resend-container" align="center">
		<div id="resend-logo">
			<img src="img/42icon.png" alt="logo">
		</div>
		<div id="resend-title">
			<h2>Réexpédier le mail de confirmation</h2>
		</div>
		<div id="resend-main">
			<form id="resend-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" accept-charset="UTF-8">
				<table id="resend-table">
					<tr>
						<td align="right">
							<label for="resend_mail">Votre adresse e-mail :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="email" name="resend_mail" id="resend_mail" placeholder="Votre adresse e-mail" size="25" value="<?php if(isset($resend_mail)) { echo $resend_mail; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($resend_mailErr)) { echo $resend_mailErr; } ?></span>
						</td>
					</tr>
				</table>
				<input type="submit" name="resend_submit" value="Valider">
			</form>
			<?php if (isset($error) AND !empty($error)) { echo '<div align="center">'.$error.'</div>'; if ($error == "Ce compte a déjà été confirmé. Je vous redirige..." OR $error == "Un nouvel e-mail de confirmation vient de vous être envoyé, veuillez consulter votre boite mail.") { header('Refresh: 5; URL=login.php'); exit(); } } ?>
		</div>
	</div>

<?php include('src/footer.php'); ?>