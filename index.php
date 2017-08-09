<?php
  session_start();  //SESSION変数を使うときは必ず記述！

  //データベースに接続
  // 外部ファイルから処理の読み込み
  // 外部ファイル内でエラーが出ると、処理を中断する
  require('dbconnect.php');

  //ログインチェック
  //ログイン中とみなせる条件
  // 1.セッションにログインしてる人のmember_idが保存されている
  // 2.最後のアクションから1時間以内であること
  if (isset($_SESSION['login_member_id']) && ($_SESSION['time'] + 3600 > time())){
    //ログインしてる
    // 最終アクション時間を更新
    $_SESSION['time'] = time();

    //問題
    // login.phpを参考に、ログインしている人のデータを取得してください。取得できたら「ようこそ●●さん！」の部分をログインしてる人のnick_nameが表示されるように修正しましょう

    

    $sql = 'SELECT * FROM `members` WHERE `member_id`=?';
    
    $data = array($_SESSION['login_member_id']); //入力されたデータを指定

    //SQL実行
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //データ取得
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
  }else{
    //ログインしてない
    header('Location: login.php');
  }

  //投稿を記録する（「つぶやく」ボタンをクリックしたとき）
  if (!empty($_POST)){
    //つぶやき欄に何か書かれていたら、DBに値を登録する
    if ($_POST['tweet'] != ''){

      //投稿用INSERT文を作成
      $sql = 'INSERT INTO `tweets` SET `tweet`=?,
                                      `member_id`=?,
                                    `reply_tweet_id`=?,
                                    `created`=NOW()';
    
      //SQL文実行
      $data = array($_POST['tweet'],$_SESSION['login_member_id'],$_POST['reply_tweet_id']);
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);

      //画面再表示（再送信防止）
      header('Location: index.php');
      exit();

    }

  }

  //SELECT文作成（一覧表示用のデータを取得）
  // $sql = 'SELECT * FROM `tweets`;';
  // ORDER BY `created` DESC -> 作成日が新しい順に並べる
  // DESC 降順　数字が大きいものから小さいものに並べる
  // ASC(省略可能) 昇順　数字が小さいものから大きいものに並べる
  
  //ページング機能
  $page = '';

  //パラメータが存在したら、ページ番号を取得
  if (isset($_GET['page'])){
    $page = $_GET['page'];
  }

  //パラメータが存在しない場合は、ページ番号を1とする
  if ($page == ''){
    $page = 1;
  }

  // 1以下のイレギュラーな数値が入ってきた場合はページ番号を1とする(max:中の複数の数値の中で最大の数値を返す関数)
  $page = max($page,1);
  // max(-1,1)という指定の場合、大きい方の1が結果として返される


  // データの件数から最大ページ数を計算する
  $max_page = 0;

  //宿題：このSQL文を実行して、取得したデータ数をvar_dumpで表示しましょう
  $sql = "SELECT COUNT(*) AS `cnt` FROM `tweets` WHERE `delete_flag`=0";

  $stmt = $dbh->prepare($sql);
  $stmt->execute();

  //データ数取得
  $cnt = $stmt->fetch(PDO::FETCH_ASSOC);

  var_dump($cnt['cnt']);

  $start = 0;
  //1ページ目:0
  //2ページ目:10
  //3ページ目:20


  $tweet_number = 10;  //1ページに何個つぶやきを出すか指定
  //小数点を切り上げた計算結果を代入
  $max_page = ceil($cnt['cnt'] / $tweet_number);

  //パラメータのページ番号が最大ページ数を超えていれば、最後のページ数に設定する(min:指定された複数の数値の中で最小の数値を返す関数)
  $page = min($page,$max_page);
  //min(100,3) と指定されてたら、3が返ってくる

  $start = ($page -1) * $tweet_number;

  $sql =sprintf('SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE `tweets`.`delete_flag` = 0 ORDER BY `tweets`.`created` DESC LIMIT %d,%d',$start,$tweet_number);

  // $data = array($start,$tweet_number);
  //SQL文実行
  $stmt = $dbh->prepare($sql);
  $stmt->execute();

  $tweets = array();
  //データを取得して配列に保存
  while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {

    //$recordにfalseが代入されたとき、処理が終了します（データの一番最後まで取得してしまい、次に取得するデータが存在しない時）
    //$tweets[]...配列の一番最後に新しいデータを追加する 

  	//like数の取得
  	$sql = 'SELECT COUNT(*) as `like_count` FROM `likes` WHERE `tweet_id`='.$record['tweet_id'];
  	 //SQL文実行
  	$stmt_cnt = $dbh->prepare($sql);
  	$stmt_cnt->execute();
  	$like_cnt = $stmt_cnt->fetch(PDO::FETCH_ASSOC);

  	//like状態の取得（ログインユーザーごと）
  	$sql = 'SELECT COUNT(*) as `like_count` FROM `likes` WHERE `tweet_id`='.$record['tweet_id'].' AND `member_id`='.$_SESSION['login_member_id'];
  	 //SQL文実行
  	$stmt_flag = $dbh->prepare($sql);
  	$stmt_flag->execute();
  	$like_flag_cnt = $stmt_flag->fetch(PDO::FETCH_ASSOC);

  	if ($like_flag_cnt['like_count'] == 0){
  		$like_flag = false; //likeされてない
  	}else{
  		$like_flag = true; //likeされてる
  	}

    $tweets[] = array(
        "tweet"=>$record['tweet'],
        "nick_name"=>$record['nick_name'],
        "picture_path"=>$record['picture_path'],
        "created"=>$record['created'],
        "tweet_id"=>$record['tweet_id'],
        "reply_tweet_id"=>$record['reply_tweet_id'],
        "member_id"=>$record['member_id'],
        "like_flag"=>$like_flag,
        "like_count"=>$like_cnt['like_count']
      );
    //演習：like_countを使って、Like数を表示しましょう
    //宿題 like_flagを使って、「いいね！」か、「いいねを取り消す」どちらかを表示しましょう。
  }
  

  //返信ボタン(Re)が押されたとき
  if (isset($_GET['tweet_id'])){
    //返信したいつぶやきデータを取得（ニックネームも一緒に）
    $sql ='SELECT * FROM `tweets` INNER JOIN `members` ON `tweets`.`member_id`=`members`.`member_id` WHERE tweet_id=?';

    $data = array($_GET['tweet_id']);
    // SQL文実行
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    //データ取得
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    //テキストエリアに表示する文字を作成「@返信したいつぶやき(つぶやいた人のニックネーム)」
    $re_str = '@'.$record["tweet"].'('.$record["nick_name"].')';

    //While文使ってる場合
    // $re_str = '@'.$tweets[0]["tweet"].'('.$tweets[0]["nick_name"].')';

    var_dump($re_str);

    $reply_tweet_id = $_GET['tweet_id'];
  }else{
    $reply_tweet_id = 0;
  }


?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">

  </head>
  <body>
  <!-- <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div> -->
          <!-- /.navbar-collapse -->
      <!-- </div> -->
      <!-- /.container-fluid -->
  <!-- </nav> -->
  <!-- include…エラーが外部ファイル内で発生したとしても処理を継続する（表示系の処理によく使用される） -->
  <?php include('header.php'); ?>

  <div class="container">
    <div class="row">
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<?php echo $record['nick_name']; ?>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form">
            <!-- つぶやき -->
            <div class="form-group">
              <label class="col-sm-4 control-label">つぶやき</label>
              <div class="col-sm-8">
              <?php if (!empty($re_str)){ ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!" ><?php echo $re_str; ?></textarea>
              <?php }else{ ?>
                <textarea name="tweet" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
              <?php } ?>
              <input type="hidden" name="reply_tweet_id" value="<?php echo $reply_tweet_id; ?>" />
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <li>
                  <?php if ($page > 1){ ?>
                  <a href="index.php?page=<?php echo $page -1; ?>" class="btn btn-default">前</a>
                  <?php }else{ ?>
                      前
                  <?php } ?>
                </li>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <li>
                  <?php if ($page < $max_page){ ?>
                  <a href="index.php?page=<?php echo $page +1; ?>" class="btn btn-default">次</a>
                  <?php }else{ ?>
                      次
                  <?php } ?>  
                </li>
          </ul>
        </form>
      </div>

      <div class="col-md-8 content-margin-top">
      <!-- foreach文：指定された配列の個数分繰り返し処理を行う制御文 -->
      <?php foreach ($tweets as $tweet_each) { ?>
        <div class="msg">
          <img src="member_picture/<?php echo $tweet_each['picture_path']; ?>" width="48" height="48">
          <p>
            <?php echo $tweet_each["tweet"]; ?><span class="name"> (<?php echo $tweet_each["nick_name"]; ?>) </span>
            [<a href="index.php?tweet_id=<?php echo $tweet_each["tweet_id"]; ?>">Re</a>]
          </p>
          <p class="day">
            <a href="view.php?tweet_id=<?php echo $tweet_each["tweet_id"]; ?>">
              <?php echo $tweet_each["created"]; ?>
            </a>
            <!-- いまログインしてる人のつぶやきであれば、編集、削除ボタンを表示しましょう。
            ヒント：$_SESSION['login_member_id']にいまログインしている人のmember_idが保存されています。
             -->
            <?php if ($_SESSION['login_member_id']==$tweet_each["member_id"]){ ?>
            [<a href="edit.php?tweet_id=<?php echo $tweet_each["tweet_id"]; ?>" style="color: #00994C;">編集</a>]
            [<a href="delete.php?tweet_id=<?php echo $tweet_each["tweet_id"]; ?>" style="color: #F33;" onclick="return confirm('本当に削除しますか?');">削除</a>]
            <?php } ?>
            <small><i class="fa fa-thumbs-up"></i><?php echo $tweet_each['like_count']; ?></small>
            <?php if ($tweet_each["like_flag"] == false){ ?>
            <a href="like.php?tweet_id=<?php echo $tweet_each["tweet_id"]; ?>"><small>いいね！</small></a>
            <?php }else{ ?>
            <a href="unlike.php?tweet_id=<?php echo $tweet_each["tweet_id"]; ?>"><small>いいねを取り消す</small></a>
            <?php } ?>
            <!-- 返信元の記事がある場合、下記リンクを表示し、返信元の記事が確認できるURLをセットしましょう -->
            <?php if (!empty($tweet_each["reply_tweet_id"])){ ?>
            <a href="view.php?tweet_id=<?php echo $tweet_each["reply_tweet_id"]; ?>">
              返信元のつぶやき
            </a>
            <?php } ?>
          </p>
        </div>

      <?php } ?>
       
        
     

        <!-- <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき３<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:03
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき２<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:02
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
        <div class="msg">
          <img src="http://c85c7a.medialib.glogster.com/taniaarca/media/71/71c8671f98761a43f6f50a282e20f0b82bdb1f8c/blog-images-1349202732-fondo-steve-jobs-ipad.jpg" width="48" height="48">
          <p>
            つぶやき１<span class="name"> (Seed kun) </span>
            [<a href="#">Re</a>]
          </p>
          <p class="day">
            <a href="view.html">
              2016-01-28 18:01
            </a>
            [<a href="#" style="color: #00994C;">編集</a>]
            [<a href="#" style="color: #F33;">削除</a>]
          </p>
        </div>
      </div> -->

    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
