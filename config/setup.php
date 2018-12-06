<?php
	require_once('database.php');

	try {
		$DB = new PDO($DB_DSN_LIGHT, $DB_USER, $DB_PASSWORD);
		$DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$query = "CREATE DATABASE IF NOT EXISTS `camagru`";
		$exec = $DB->prepare($query);
		$exec->execute();
		
		$query = "USE `camagru`";
		$exec = $DB->prepare($query);
		$exec->execute();

		$query = "CREATE TABLE `comments` (
			`id_comments` int(11) PRIMARY KEY AUTO_INCREMENT,
			`id_picture` int(11) NOT NULL,
			`id_user` int(11) NOT NULL,
			`comment_text` varchar(280) NOT NULL,
			`comment_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP)";
		$exec = $DB->prepare($query);
		$exec->execute();

		$query = "CREATE TABLE `likes` (
			`id_likes` int(11) PRIMARY KEY AUTO_INCREMENT,
			`id_user` int(11) NOT NULL,
			`id_picture` int(11) NOT NULL,
			`status` tinyint(1) NOT NULL DEFAULT '1')";
		$exec = $DB->prepare($query);
		$exec->execute();

		$query = "CREATE TABLE `pictures` (
			`id_pictures` int(11) PRIMARY KEY AUTO_INCREMENT,
			`id_user` int(11) NOT NULL,
			`img_name` varchar(255) NOT NULL,
			`img_legend` varchar(140) DEFAULT NULL,
			`upload_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP)";
		$exec = $DB->prepare($query);
		$exec->execute();

		$query = "CREATE TABLE `recovery` (
			`id` int(11) PRIMARY KEY AUTO_INCREMENT,
			`mail` varchar(255) DEFAULT NULL,
			`code` int(11) DEFAULT NULL)";
		$exec = $DB->prepare($query);
		$exec->execute();

		$query = "CREATE TABLE `users` (
			`id` int(11)PRIMARY KEY AUTO_INCREMENT,
			`gender` varchar(1) NOT NULL,
			`login` varchar(30) NOT NULL,
			`password` varchar(64) NOT NULL,
			`mail` varchar(50) NOT NULL,
			`creation_date` datetime NOT NULL,
			`token` varchar(255) NOT NULL DEFAULT '',
			`confirmed` int(1) NOT NULL DEFAULT '0',
			`notifications` tinyint(1) NOT NULL DEFAULT '1')";
		$exec = $DB->prepare($query);
		$exec->execute();
		$exec = null;
		$DB = null;
		echo "BDD deployée !";
	}
	catch (PDOException $e) {
		echo 'Connection failed: '.$e->getMessage();
	}
?>