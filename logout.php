<?php
	session_start(); //SESSION変数を扱うときは絶対書く

	//セッション情報の破棄（削除）
	$_SESSION = array(); //中身を空っぽの配列で上書き

	//セッションを呼び出すために使うクッキー情報の削除
	if (ini_get("session.use_cookies")){
		$params = session_get_cookie_params();
		// クッキーの有効期限を過去にセットすると、既に無効な状態にできるので削除したのと同じ状態にできる
		setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secuer'],$params['httponly']);
	}

	//セッション情報を完全に消滅させる
	session_destroy();

	//index.phpに戻る（ログインチェックのため）
	header('Location: index.php'); 


?>