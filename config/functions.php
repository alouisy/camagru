<?php
	require_once('connect.php');

	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	function auth(PDO $DB, $username, $password) {
		$h_pass = hash('sha256', $password);
		$query = $DB->prepare('SELECT * FROM users WHERE login = :login AND password = :password');
		$query->execute(array(
			'login' => $username,
			'password' => $h_pass));
		$userexist = $query->rowCount();
		if ($userexist == 1) {
			$userdata = $query->fetch();
			$query->closeCursor();
			if ($userdata['confirmed'] == 0)
				return 2;
			$_SESSION['id'] = $userdata['id'];
			$_SESSION['login'] = $userdata['login'];
			$_SESSION['mail'] = $userdata['mail'];
			return 1;
		}
		else
			return 0;
	}

	function isnotconfirmed(PDO $DB, $mail) {
		$query = $DB->prepare('SELECT login, token, confirmed FROM users WHERE mail = ?');
		$query->execute(array($mail));
		$userexist = $query->rowCount();
		if ($userexist == 1) {
			$userdata = $query->fetch();
			$query->closeCursor();
			if ($userdata['confirmed'] == 0)
				return array('login' => $userdata['login'], 'token' => $userdata['token']);
			else
				return 0;
		}
		return -1;
	}

	function islogged() {
		if (isset($_SESSION['id']) AND isset($_SESSION['login']))
			return true;
		return false;
	}
	
	function usedLogin(PDO $DB, $username) {
		$query = $DB->prepare('SELECT id FROM users WHERE login = :login');
		$query->execute(array('login' => $username));
		$loginexist = $query->rowCount();
		if ($loginexist == 0)
			return false;
		else
			return true;
	}
	
	function usedMail(PDO $DB, $mail) {
		$query = $DB->prepare('SELECT id FROM users WHERE mail = :mail');
		$query->execute(array('mail' => $mail));
		$mailexist = $query->rowCount();
		if ($mailexist == 0)
			return false;
		else
			return true;
	}
	
	function create_user(PDO $DB, $username, $password, $mail, $gender, $key) {
		$checkLogin = usedLogin($DB, $username);
		$checkMail = usedMail($DB, $mail);
		if ($checkLogin == false) {
			if ($checkMail == false) {
				$sex = ($gender == 'male') ? 'M' : 'F';
				$h_pass = hash('sha256', $password);
				$query = $DB->prepare('INSERT INTO users (gender, login, password, mail, creation_date, token) VALUES (:gender, :login, :password, :mail, NOW(), :token)');
				$query->execute(array(
					'gender' => $sex,
					'login' => $username,
					'password' => $h_pass,
					'mail' => $mail,
					'token' => $key));
				return 0;
			}
			else
				return 2;
		}
		else
			return 1;
	}

	function add_img_in_db(PDO $DB, $filename, $user_id, $legend) {
		$filename = basename($filename);
		$query = $DB->prepare('INSERT INTO pictures (id_user, img_name, img_legend, upload_date) VALUES (:id_user, :img_name, :img_legend, NOW())');
		$query->execute(array(
			'id_user' => $user_id,
			'img_name' => $filename,
			'img_legend' => $legend));
		return true;
	}
	
	function get_idbymail_in_db(PDO $DB, $mail) {
		$query = $DB->prepare('SELECT id FROM users WHERE mail = :mail');
		$query->execute(array('mail' => $mail));
		$userexist = $query->rowCount();
		if ($userexist == 1) {
			$answer = $query->fetch();
			$query->closeCursor();
			return intval($answer['id']);
		} else {
			return 0;
		}
	}

	function get_id_by_username(PDO $DB, $login) {
		$query = $DB->prepare('SELECT id FROM users WHERE login = ?');
		$query->execute(array($login));
		$userexist = $query->rowCount();
		if ($userexist == 1) {
			$answer = $query->fetch();
			$query->closeCursor();
			return intval($answer['id']);
		} else {
			return 0;
		}
	}

	function get_mail_in_db(PDO $DB, $id) {
		$query = $DB->prepare('SELECT mail FROM users WHERE id = :id');
		$query->execute(array('id' => $id));
		$answer = $query->fetch();
		$data = $answer['mail'];
		$query->closeCursor();
		return $data;
	}
	
	function get_date_in_db(PDO $DB, $id) {
		$query = $DB->prepare('SELECT creation_date FROM users WHERE id = :id');
		$query->execute(array('id' => $id));
		$answer = $query->fetch();
		$data = $answer['creation_date'];
		$query->closeCursor();
		return $data;
	}

	function get_username_by_id(PDO $DB, $id) {
		$query = $DB->prepare('SELECT login FROM users WHERE id = :id');
		$query->execute(array('id' => $id));
		$answer = $query->fetch();
		$data = $answer['login'];
		$query->closeCursor();
		return $data;
	}

	function get_owner_id(PDO $DB, $id) {
		$query = $DB->prepare('SELECT id_user FROM pictures WHERE id_pictures = :id_pictures');
		$query->execute(array('id_pictures' => $id));
		$answer = $query->fetch();
		$data = $answer['id_user'];
		$query->closeCursor();
		return $data;
	}

	function get_last_pics(PDO $DB, $id) {
		$query = $DB->prepare('SELECT id_pictures, img_name FROM pictures WHERE id_user = :id ORDER BY upload_date DESC LIMIT 0,3');
		$query->execute(array('id' => $id));
		$answer = $query->fetchAll();
		$query->closeCursor();
		return $answer;
	}

	function get_nb_of_likes(PDO $DB, $id) {
		$query = $DB->prepare('SELECT COUNT(*) AS nb_likes FROM likes WHERE id_picture = :id AND status = 0');
		$query->execute(array('id' => $id));
		$answer = $query->fetch();
		$data = $answer['nb_likes'];
		$query->closeCursor();
		return $data;
	}

	function get_all_comments(PDO $DB, $id) {
		$allcomments = '<ul>';
		$count = 0;
		$query = $DB->prepare('SELECT id_user, comment_text FROM comments WHERE id_picture = :id ORDER BY comment_date ASC');
		$query->execute(array('id' => $id));
		while($answer = $query->fetch()) {
			$username = get_username_by_id($DB, $answer['id_user']);
			$allcomments .= '<li><span class="commentuser"><a href="user.php?u='.$username.'" style="text-decoration:none;color:black;">'.$username.'</a></span> <span class="commentmsg">'.$answer['comment_text'].'</span></li>';
			$count++;
		}
		$allcomments .= '</ul>';
		if ($count == 0) {
			$allcomments = '<span class="zerocomment">Soyez le premier à commenter ce post...</span>';
		}
		$query->closeCursor();
		return $allcomments;
	}

	function get_like_status(PDO $DB, $id_picture, $id_user) {
		$query = $DB->prepare('SELECT status FROM likes WHERE id_picture = :id_picture AND id_user = :id_user');
		$query->execute(array(
			'id_picture' => $id_picture,
			'id_user' => $id_user));
		$answer = $query->fetch();
		if (!$answer) {
			$query->closeCursor();
			return 0;
		}
		$data = $answer['status'];
		$query->closeCursor();
		if ($data == '0')
			return 2;
		return 1;
	}

	function add_comment_on_post(PDO $DB, $id_picture, $id_user, $comment_text) {
		$query = $DB->prepare('SELECT COUNT(*) AS is_exist FROM pictures WHERE id_pictures = :id');
		$query->execute(array('id' => $id_picture));
		$answer = $query->fetch();
		if ($answer['is_exist'] == 0) {
			$query->closeCursor();
			return false;
		}
		$query->closeCursor();
		$query = $DB->prepare('INSERT INTO comments (id_picture, id_user, comment_text, comment_date) VALUES (:id_picture, :id_user, :comment_text, NOW())');
		$query->execute(array(
			'id_picture' => $id_picture,
			'id_user' => $id_user,
			'comment_text' => $comment_text));
		return true;
	}

	function like_post(PDO $DB, $id_picture, $id_user) {
		$status = get_like_status($DB, $id_picture, $id_user);
		echo $status;
		if ($status == 0) {
			$query = $DB->prepare('INSERT INTO likes (id_user, id_picture, status) VALUES (:id_user, :id_picture, :status)');
			$query->execute(array(
				'id_user' => $id_user,
				'id_picture' => $id_picture,
				'status' => $status));
		}
		else {
			$query = $DB->prepare('UPDATE `likes` SET `status` = :status WHERE `likes`.`id_user` = :id_user AND `likes`.`id_picture` = :id_picture');			
			if ($status == 1)
				$status = 0;
			else if ($status == 2)
				$status = 1;
			$query->execute(array(
				'status' => $status,
				'id_user' => $id_user,
				'id_picture' => $id_picture));
		}
		return true;
	}

	function delete_post(PDO $DB, $id_picture) {
		$query = $DB->prepare('SELECT img_name FROM pictures WHERE id_pictures = :id_picture');
		$query->execute(array('id_picture' => $id_picture));//or die(print_r($DB->errorInfo()));
		$answer = $query->fetch();
		$query->closeCursor();
		$query = $DB->prepare('DELETE FROM likes WHERE id_picture = :id_picture');
		$query->execute(array('id_picture' => $id_picture));//or die(print_r($DB->errorInfo()));
		$query = $DB->prepare('DELETE FROM comments WHERE id_picture = :id_picture');
		$query->execute(array('id_picture' => $id_picture));//or die(print_r($DB->errorInfo()));
		$query = $DB->prepare('DELETE FROM pictures WHERE id_pictures = :id_picture');
		$query->execute(array('id_picture' => $id_picture));//or die(print_r($DB->errorInfo()));
		unlink('pictures/'.$answer['img_name']);
		return true;
	}

	function delete_user(PDO $DB, $id_user) {
		$query = $DB->prepare('DELETE FROM likes WHERE id_user = :id_user');
		$query->execute(array('id_user' => $id_user));//or die(print_r($DB->errorInfo()));
		$query = $DB->prepare('DELETE FROM comments WHERE id_user = :id_user');
		$query->execute(array('id_user' => $id_user));//or die(print_r($DB->errorInfo()));
		$query = $DB->prepare('SELECT id_pictures FROM pictures WHERE id_user = :id_user');
		$query->execute(array('id_user' => $id_user));//or die(print_r($DB->errorInfo()));
		while($answer = $query->fetch()) {
			delete_post($DB, $answer['id_pictures']);
		}
		$query->closeCursor();
		$query = $DB->prepare('DELETE FROM pictures WHERE id_user = :id_user');
		$query->execute(array('id_user' => $id_user));//or die(print_r($DB->errorInfo()));
		$query = $DB->prepare('DELETE FROM users WHERE id = :id_user');
		$query->execute(array('id_user' => $id_user));//or die(print_r($DB->errorInfo()));
		return true;
	}

	function send_mail($to, $subject, $content) {
	
		$headers = "From: noreply_camagru@azzxl.com\r\n";
		//$headers .= "Reply-To: noreply_camagru@azzxl.com\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=utf-8\r\n";
		$headers .= "Content-Transfer-Encoding: 8bit";
		
		$message = '<html>';
		$message .= '<head>';
		$message .= '<meta charset="utf-8"><title>Azztagram</title>';
		$message .= '</head>';
		$message .= '<body style="background-image:url(https://azzxl.com/camagru/img/gradien.png);background-attachment:fixed;background-repeat:no-repeat;background-size:cover;font-family:Avenir, Helvetica, Verdana, sans-serif;">';
		$message .= '<div align="center" style="position:fixed;left:0px;top:0px;width:100%;height:70px;z-index:100;background-color:white;border-bottom:solid black 1px;">';
		$message .= '<div style="display:inline-block;vertical-align:top;width:140px;height:55px;">';
		$message .= '<a href="https://azzxl.com/camagru/index.php" style="display:inline-block;vertical-align:top;width:140px;height:55px;text-decoration:none;"><img src="https://azzxl.com/camagru/img/42nav.png" alt="photo" style="display:block;vertical-align:top;width:140px;height:55px;"></a>';
		$message .= '</div>';
		$message .= '</div>';
		$message .= '<div align="center" style="padding-top:85px;padding-bottom:55px;color:black;">';
		$message .= '<h2>'.$subject.'</h2>';
		$message .= $content;
		$message .= '</div>';
		$message .= '<footer style="position:fixed;display:block;z-index:99;text-align:center;color:white;font-size:small;background-color:black;border-top:solid white 1px;box-sizing:border-box;bottom:0;left:0;width:100%;height:55px;"><address style="display:block;box-sizing:border-box;margin:0 auto;width:100%;">';
		$message .= 'Crée par © alouisy- en 2017<br />Vous pouvez visitez mon portfolio à l\'adresse : <a href="https://azzxl.com" style="text-decoration:none;color:rgba(255, 93, 192, 0.84);font-size:small;">https://azzxl.com</a><br />Ainsi que me contacter par mail à l\'adresse : <a href="mailto:alouisy-@student.42.fr" style="text-decoration:none;color:rgba(255, 93, 192, 0.84);font-size:small;">alouisy-@student.42.fr</a>';
		$message .= '</address></footer>';
		$message .= '</body>';
		$message .= '</html>';

		mail($to, $subject, $message, $headers);
	}

	function send_notif(PDO $DB, $id, $login, $idpics, $comment) {
		$ownerid = get_owner_id($DB, $idpics);
		$query = $DB->prepare('SELECT notifications FROM users WHERE id = ?');
		$query->execute(array($ownerid));
		$allow = $query->fetch();
		$query->closeCursor();
		if (intval($allow['notifications']) == 1 AND $id != $ownerid) {
			$owner = get_username_by_id($DB, $ownerid);
			$mail = get_mail_in_db($DB, $ownerid);
			$message = 'Bonjour, '.$owner.' !<br />Votre image <a href="https://azzxl.com/camagru/post.php?id='.$idpics.'">https://azzxl.com/camagru/post.php?id='.$idpics.'</a><br />a été commentée par le membre <a href="https://azzxl.com/camagru/user.php?u='.$login.'">@'.$login.'</a>.<br />Son commentaire est le suivant :<br />"'.$comment."\"<br /><br />Cordialement,<br />L'équipe Azztagram.com";
			send_mail($mail, "Vous avez un nouveau commentaire sur votre post - Azztagram.com", $message);
		}
	}
	
	function get_notification_status(PDO $DB, $id) {
		$query = $DB->prepare('SELECT notifications FROM users WHERE id = ?');
		$query->execute(array($id));
		$status = $query->fetch();
		$query->closeCursor();
		if (intval($status['notifications']) == 1) {
			$result = array(array(1, 'checked', 'Activé'), array(0, '', 'Désactivé'));
		} else {
			$result = array(array(0, '', 'Désactivé'), array(1, 'checked', 'Activé'));
		}
		return $result;
	}

	function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) { 
		// creating a cut resource 
		$cut = imagecreatetruecolor($src_w, $src_h); 
		
		// copying relevant section from background to the cut resource 
		imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h); 
		
		// copying relevant section from watermark to the cut resource 
		imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h); 
		
		// insert cut resource to destination image 
		imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct); 
    }

	function thumbnail_box($img, $box_w, $box_h) {
	    //create the image, of the required size
	    $new = imagecreatetruecolor($box_w, $box_h);
	    if($new === false) {
	        //creation failed -- probably not enough memory
	        return null;
	    }
	    //Fill the image with a light grey color
	    //(this will be visible in the padding around the image,
	    //if the aspect ratios of the image and the thumbnail do not match)
	    //Replace this with any color you want, or comment it out for black.
	    //I used grey for testing =)
	    $fill = imagecolorallocate($new, 0, 0, 0);
	    imagefill($new, 0, 0, $fill);
	
	    //compute resize ratio
	    $hratio = $box_h / imagesy($img);
	    $wratio = $box_w / imagesx($img);
	    $ratio = min($hratio, $wratio);
	
	    //if the source is smaller than the thumbnail size, 
	    //don't resize -- add a margin instead
	    //(that is, dont magnify images)
	    if($ratio > 1.0)
	        $ratio = 1.0;
	
	    //compute sizes
	    $sy = floor(imagesy($img) * $ratio);
	    $sx = floor(imagesx($img) * $ratio);
	
	    //compute margins
	    //Using these margins centers the image in the thumbnail.
	    //If you always want the image to the top left, 
	    //set both of these to 0
	    $m_y = floor(($box_h - $sy) / 2);
	    $m_x = floor(($box_w - $sx) / 2);
	
	    //Copy the image data, and resample
	    //
	    //If you want a fast and ugly thumbnail,
	    //replace imagecopyresampled with imagecopyresized
	    if(!imagecopyresampled($new, $img,
	        $m_x, $m_y, //dest x, y (margins)
	        0, 0, //src x, y (0,0 means top left)
	        $sx, $sy,//dest w, h (resample to this size (computed above)
	        imagesx($img), imagesy($img)) //src w, h (the full size of the original)
	    ) {
	        //copy failed
	        imagedestroy($new);
	        return null;
	    }
	    //copy successful
	    return $new;
	}
?>
