<?php
	session_start();

	require_once('config/functions.php');

	if (islogged()) {
		header('Location: index.php');
		exit();	
	}
	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		if (empty($_POST["login"])) {
			$loginErr = "Veuillez saisir votre nom d'utilisateur.";
		} else {
			$login = test_input($_POST["login"]);
		}
	
		if (empty($_POST["pass"])) {
			$passErr = "Veuillez saisir votre mot de passe pour vous connecter sur le site.";
		} else {
			$pass = test_input($_POST["pass"]);
		}
	
		if (empty($loginErr) && empty($passErr)) {
			$res = auth($DB, $login, $pass);
			if ($res == 1) {
				header("Location: user.php");
				exit();
			} else if ($res == 2)
				$error = "Ce compte n'a pas été confirmé, veuillez consulter vos mails. Si vous n'avez rien reçu cliquez <a href=\"resendmail.php\">ici</a>";
			else
				$error = "Erreur de connexion, veuillez vérifier vos identifiants.";
		}
		else
			$error = "Vous devez remplir TOUS les champs pour vous connecter.";
	}
?>

<?php include 'src/header.php'; ?>

	<div id="login-container" align="center">
		<div id="login-logo">
			<img src="img/42icon.png" alt="logo">
		</div>
		<div id="login-title">
			<h2>Connexion</h2>
		</div>
		<div id="login-main">
			<form id="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" accept-charset="UTF-8">
				<table id="login-table">
					<tr>
						<td align="right">
							<label for="login">Nom d'utilisateur :</label> <span class="error">*</span>
						</td>
						<td>
							<input type="text" name="login" id="login" placeholder="Votre pseudo" size="25" value="<?php if (isset($login)) { echo $login; }?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if (isset($loginErr)) { echo $loginErr; }?></span>
						</td>
					</tr>
					<tr>
						<td align="right">
							<label for="pass">Mot de passe :</label> <span class="error">*</span>
						</td>
						<td>
							<input type="password" name="pass" id="pass" placeholder="Votre mot de passe" size="25" value="<?php if (isset($pass)) { echo $pass; }?>">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<span class="error"><?php if (isset($passErr)) { echo $passErr; }?></span>
						</td>
					</tr>
				</table>
				<input type="submit" name="logform" value="Se Connecter">
				<br />
				<a href="recovery.php">Mot de de passe oublié ?</a>
				<br />
				<a href="resendmail.php">Vous n'avez pas reçu de mail de confirmation ?</a>
			</form>
			<?php
			if (isset($error))
			{
				echo '<br /><div align="center">'.$error.'</div>';
			}
			?>
		</div>
	</div>


<?php include 'src/footer.php'; ?>