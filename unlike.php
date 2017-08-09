<?php

	session_start();
	require('dbconnect.php');

	//前提：$_GET['tweet_id']でlikeしたいtweet_idが取得できる

	//ログインチェック
	if (isset($_SESSION['login_member_id'])){

		$sql = sprintf('DELETE FROM `likes` WHERE `tweet_id`=%d AND `member_id`=%d',$_GET['tweet_id'],$_SESSION['login_member_id']);
		
		// SQL文実行
    	$stmt = $dbh->prepare($sql);
    	$stmt->execute();

	}
	//トップページに戻る
	header("Location: index.php");
	exit();

?>