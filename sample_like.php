<?php
	session_start();

	require('dbconnect.php');

	if (isset($_GET['tweet_id'])){

		//GET送信されたtweet_idを取得
		$tweet_id = $_GET['tweet_id'];

		//SQL文作成（likesテーブルのINSERT文）
		$sql = sprintf('INSERT INTO `likes` (`member_id`, `tweet_id`) VALUES (%d, %d);',
			$_SESSION['login_member_id'],
			$tweet_id);
	
		$stmt = $dbh->prepare($sql);
    	$stmt->execute();
      
      	header("Location: index.php");
	  	exit();

	}

?>