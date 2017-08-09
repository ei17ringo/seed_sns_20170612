<?php

	session_start();
	require('dbconnect.php');

	//前提：$_GET['tweet_id']でlikeしたいtweet_idが取得できる

	//ログインチェック
	if (isset($_SESSION['login_member_id'])){

		//Insert文
		//演習：ログインしてる人が指定したtweet_idのつぶやきをlikeした情報を保存するINSERT文を作成しましょう
		// $sql = 'INSERT INTO `likes` SET `tweet_id`='.$_GET['tweet_id'].',`member_id`='.$_SESSION['login_member_id'];
		//sprintfを使った場合 
		$sql = sprintf('INSERT INTO `likes` SET `tweet_id`=%d,`member_id`=%d',$_GET['tweet_id'],$_SESSION['login_member_id']);
		
		// SQL文実行
    	$stmt = $dbh->prepare($sql);
    	$stmt->execute();

	}
	//トップページに戻る
	header("Location: index.php");
	exit();

?>