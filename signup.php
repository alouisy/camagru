<?php
	session_start();
	
	require_once('config/functions.php');

	if (islogged()) {
		header('Location: index.php');
		exit;	
	}
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		
		if (empty($_POST["login"])) {
			$loginErr = "Un nom d'utilisateur est requis pour s'inscrire sur le site.";
		} else {
			$login = test_input($_POST["login"]);
			if (!preg_match("#^[a-zA-Z0-9_-]+$#", $login)) {
				$loginErr = "Seul les lettres (a-z <> A-Z), les chiffres (0-9), les underscores (_) et les tirets (-) sont autorisés";
			}
		}

		if (empty($_POST["pass"])) {
			$passErr = "Un mot de passe est requis pour s'inscrire sur le site.";
		} else {
			$pass = test_input($_POST["pass"]);
			if (!preg_match("#[a-z]+#", $pass) || !preg_match("#[A-Z]+#", $pass) || !preg_match("#[0-9]+#", $pass)) {
				$passErr = "Pour être accepté, votre MDP doit contenir au minimum 8 caractères dont obligatoirement une majuscule (A-Z), une minuscule (a-z) et un chiffre (0-9).";
			}
		}
	
		if (empty($_POST["c_pass"])) {
			$c_passErr = "Un mot de passe est requis pour s'inscrire sur le site.";
		} else {
			$c_pass = test_input($_POST["c_pass"]);
			if (!preg_match("#[a-z]+#", $c_pass) || !preg_match("#[A-Z]+#", $c_pass) || !preg_match("#[0-9]+#", $c_pass)) {
				$c_passErr = "Pour être accepté, votre MDP doit contenir au minimum 8 caractères dont obligatoirement une majuscule (A-Z), une minuscule (a-z) et un chiffre (0-9).";
			}
		}
	
		if (empty($_POST["mail"])) {
			$mailErr = "Une adresse e-mail est requise pour s'inscrire sur le site.";
		} else {
			$mail = test_input($_POST["mail"]);
			if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				$mailErr = "Format d'e-mail invalide, veuillez vérifier votre saisie.";
			}
		}
	
		if (empty($_POST["c_mail"])) {
			$c_mailErr = "Veuillez ressaisir votre adresse e-mail une seconde fois.";
		} else {
			$c_mail = test_input($_POST["c_mail"]);
			if (!filter_var($c_mail, FILTER_VALIDATE_EMAIL)) {
				$c_mailErr = "Format d'e-mail invalide, veuillez vérifier votre saisie.";
			}
		}
	
		if (empty($_POST["gender"])) {
			$genderErr = "Il est nécessaire de préciser son genre pour s'inscrire sur le site.";
		} else {
			$gender = test_input($_POST["gender"]);
		}
		
		if ($mail != $c_mail)
			$mailErr = $c_mailErr = "Les adresses mail ne correspondent pas, veuillez verifier votre saisie.";
		if ($pass != $c_pass)
			$passErr = $c_passErr = "Les mots de passe ne correspondent pas, veuillez verifier votre saisie.";
		
		if (!empty($_POST['gender']) AND !empty($_POST['login']) AND !empty($_POST['mail']) AND !empty($_POST['c_mail']) AND !empty($_POST['pass']) AND !empty($_POST['c_pass'])) {
			if (empty($loginErr) && empty($passErr) && empty($c_passErr) && empty($mailErr) && empty($c_mailErr) && empty($genderErr)) {
				$keylenght = 15;
				$key = '';
				for ($i=1;$i<$keylenght;$i++) {
					$key .= mt_rand(0,9);
				}
				$res = create_user($DB, $login, $pass, $mail, $gender, $key);
				if ($res == 0) {
					$message = 'Bienvenue sur Azztagram.com<br/>Le meilleur de tous les réseaux sociaux dans un seul et meme site !<br />Créez, likez, commentez et partagez en illimité sur Azztagram mais surtout intérragissez avec des milliers de personnes grace a la magie du web.<br />Nous vous souhaitons une formidable aventure parmis nous et a tres vite pour votre premiere publication.<br/><br/>L\'équipe Azztagram.com';
					send_mail($mail, "Bienvenu sur Azztagram.com", $message);
					sleep(5);
					$message = 'Bonjour '.$login.',<br/>Nous vous confirmons le bon déroulement de votre inscription sur le site Azztagram.com, et vous souhaitons une belle aventure photographique.<br/>Pour acceder à votre compte personnel, veuillez confirmer votre adresse e-mail en cliquant sur le lien suivant :<br/><br/><a href="https://azzxl.com/camagru/confirmation.php?login='.urlencode($login).'&token='.$key.'">Confirmer mon adresse e-mail</a><br/><br/>Rappel de vos identifiants :<br/><br/>- Login = '.$login.'<br/>- Mail = '.$mail.'<br/><br/>L\'équipe Azztagram.com';
					send_mail($mail, "Confirmation d'inscription - Azztagram.com", $message);
					echo '<script>alert("Votre compte a bien été créé ! Veuillez confirmer votre inscription en cliquant sur le lien que nous venons de vous envoyer par mail.");document.location.href="login.php"</script>';
				}
				else if ($res == 1)
					$error = "Le nom d'utilisateur que vous avez choisi est déjà pris, veuillez en choisir un autre.";
				else
					$error = "Cette adresse e-mail est déjà présente dans notre base de donnée, veuillez en utiliser une autre si vous souhaitez créer un nouveau compte.";
			}	
		}
		else
			$error = "Vous devez remplir TOUS les champs du formulaire pour vous inscrire.";
	}
?>

<?php include('src/header.php'); ?>

	<div id="signup-container" align="center">
		<div id="signup-logo">
			<img src="img/42icon.png" alt="logo">
		</div>
		<div id="signup-title">
			<h2>Inscription</h2>
		</div>
		<div id="signup-main">
			<form id="signup-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<table id="signup-table">
					<tr>
						<td align="right">
							<label>Sexe :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="radio" name="gender" value="male" id="male"/> <label for="male">Homme</label>
							<input type="radio" name="gender" value="female" id="female"/> <label for="female">Femme</label>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($genderErr)) { echo $genderErr; } ?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="login">Nom d'utilisateur :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="text" name="login" id="login" placeholder="Votre pseudo" size="25" value="<?php if(isset($login)) { echo $login; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if (isset($loginErr)) { echo $loginErr; }?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="mail">Adresse mail :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="email" name="mail" id="mail" placeholder="Votre adresse e-mail" size="25" value="<?php if(isset($mail)) { echo $mail; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($mailErr)) { echo $mailErr; } ?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="c_mail">Adresse mail (Confirmation) :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="email" name="c_mail" id="c_mail" placeholder="Confirmez votre e-mail" size="25" value="<?php if(isset($c_mail)) { echo $c_mail; }?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($c_mailErr)) { echo $c_mailErr; } ?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="pass">Mot de passe :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="password" pattern=".{8,}" title="Le MDP doit contenir au minimum 8 caractères dont des chiffres (0-9), des minuscules (a-z) et des majuscules (A-Z)." name="pass" id="pass" placeholder="Votre mot de passe" size="25" value="<?php if(isset($pass)) { echo $pass; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($passErr)) { echo $passErr; }?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="c_pass">Mot de passe (Confirmation) :</label> <span class="error">*</span>
						</td>
						<td align="left">
							<input type="password" pattern=".{8,}" title="Le MDP doit contenir au minimum 8 caractères dont des chiffres (0-9), des minuscules (a-z) et des majuscules (A-Z)." name="c_pass" id="c_pass" placeholder="Confirmez votre mot de passe" size="25" value="<?php if(isset($c_pass)) { echo $c_pass; } ?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if(isset($c_passErr)) { echo $c_passErr; } ?></span>
						</td>
					</tr>
				</table>
				<input type="reset">
				<input type="submit" value="Valider">													
			</form>
			<?php
			if(isset($error))
			{
				echo '<br /><div align="center">'.$error.'</div>';
			}
			?>
		</div>
	</div>

<?php include('src/footer.php'); ?>
