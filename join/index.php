<?php
  session_start(); //SESSION変数を使うとき必ず指定

// $errorという変数を用意して、入力チェックに引っかかった項目の情報を保存する
// $errorはhtmlの表示部分で、入力を促す表示を作るときに使用
// 例）もし、nick_nameに何も入っていなかった場合
// $error['nick_name'] = 'blank'; という情報を保存

//フォームからデータが送信されたとき
if (!empty($_POST)){
    //エラー項目の確認
    //ニックネームが未入力
    if ($_POST['nick_name'] == ''){
      $error['nick_name'] = 'blank';
    }

    //emailが未入力
    if ($_POST['email'] == ''){
      $error['email'] = 'blank';
    }


    //パスワードが未入力
    if ($_POST['password'] == ''){
      $error['password'] = 'blank';
    }else{
      //パスワード文字長チェック
      // ここのチェックした結果を使ってHTMLに「パスワードは4文字以上を入力してください」というメッセージを表示してください
      if (strlen($_POST['password']) < 4){
        $error['password'] = 'length';
      }
    }


    // 画像ファイルの拡張子チェック
    //jpg,gif,png　この3つを許可して他はエラーにする
    //注意：画像ファイルの拡張子を自分で手入力して変えないこと！
    //  画像のサイズは2MB以下のものを用意すること！

    $file_name = $_FILES['picture_path']['name'];
    //ファイルが指定されたときに実行
    if (!empty($file_name)){
      //拡張子を取得
      // $file_nameに「3.png」が代入されている場合、後ろ三文字を取得する
      //substr() 文字列を場所を指定して一部分切り出す関数
      // $error['picture_path']がtypeだったら「ファイルは、jpg、gif、pngのいずれかを指定してください」というエラーメッセージを表示してください。
      //チャレンジ問題！チェックする拡張子にjpegを追加してみてください
      $ext = substr($file_name, -4);
      // $ext2 = substr($file_name, -4);
      if ($ext != '.jpg' && $ext != '.gif' && $ext !='.png' && $ext != 'jpeg'){
        $error['picture_path'] = 'type';
      }

    }

    //エラーがない場合
    if (empty($error)){

      //画像をアップロードする
      // アップロード後のファイル名を作成
      $picture_path = date('YmdHis').$_FILES['picture_path']['name'];
      //$_FILES['picture_path']['tmp_name']->サーバー上に仮にファイルが置かれている場所と名前
      move_uploaded_file($_FILES['picture_path']['tmp_name'], '../member_picture/'.$picture_path);

      // セッションに値を保存
      // $_SESSION→どの画面でもアクセス可能なスーパーグローバル変数
      $_SESSION['join'] = $_POST; //POST送信されたデータを代入
      $_SESSION['join']['picture_path'] = $picture_path;

      // check.phpへ移動
      header('Location: check.php');
      exit(); //ここで処理が終了する
    }

    

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
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <!--
      designフォルダ内では2つパスの位置を戻ってからcssにアクセスしていることに注意！
     -->

  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <legend>会員登録</legend>
        <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
          <!-- ニックネーム -->
          <div class="form-group">
            <label class="col-sm-4 control-label">ニックネーム</label>
            <div class="col-sm-8">
            <!-- $_POST['nick_name']が存在したら＝POST送信されたデータの中にnick_nameの項目があったら＝ユーザーがnicknameを入力して、「確認へ」ボタンを押した後だったら -->
              <?php if (isset($_POST['nick_name'])){ ?>
                <!-- $_POST['nick_name']=''だった場合
                isset($_POST['nick_name'])→true
                empty($_POST['nick_name'])→true

                $_POST['nick_name']='seedkun'だった場合
                isset($_POST['nick_name'])→true
                empty($_POST['nick_name'])→false
                !empty($_POST['nick_name'])→true


                <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun" value="<?php echo htmlspecialchars($_POST['nick_name'],ENT_QUOTES,'UTF-8'); ?>">
              <?php }else{ ?>
                <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun">
              <?php } ?>  
              
              <!-- 本当は$error['nick_name']=='blank'の判定だけやりたい。しかし、これだけを記述した場合、$error['nick_name']が存在しない場合に文法エラーが画面に表示されてしまうので、それを防ぐため、条件の最初に存在チェックを付けている -->
              <?php if (isset($error['nick_name']) && ($error['nick_name']=='blank')){ ?>
                <p class="error">* ニックネームを入力してください。</p>
              <?php } ?>  
            </div>
          </div>
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
              <?php if (isset($_POST['email'])){ ?>
                <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com" value="<?php echo htmlspecialchars($_POST['email'],ENT_QUOTES,'UTF-8'); ?>">
              <?php }else{ ?>
                <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com">
              <?php } ?>              
              <?php if (isset($error['email']) && ($error['email']=='blank')){ ?>
                <p class="error">* メールアドレスを入力してください。</p>
              <?php } ?>  
            </div>
          </div>
          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
            <?php if (isset($_POST['password'])){ ?>
              <input type="password" name="password" class="form-control" placeholder="4文字以上入力" value="<?php echo htmlspecialchars($_POST['password'],ENT_QUOTES,'UTF-8'); ?>">
              <?php }else{ ?>
                <input type="password" name="password" class="form-control" placeholder="4文字以上入力">
              <?php } ?>
              <?php if (isset($error['password']) && ($error['password']=='blank')){ ?>
                <p class="error">* パスワードを入力してください。</p>
              <?php } ?>
              <?php if (isset($error['password']) && ($error['password']=='length')){ ?>
                <p class="error">* パスワードは4文字以上を入力してください。</p>
              <?php } ?>  
            </div>
          </div>
          <!-- プロフィール写真 -->
          <div class="form-group">
            <label class="col-sm-4 control-label">プロフィール写真</label>
            <div class="col-sm-8">
              <input type="file" name="picture_path" class="form-control">
            </div>
          </div>

          <input type="submit" class="btn btn-default" value="確認画面へ">
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../assets/js/jquery-3.1.1.js"></script>
    <script src="../assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="../assets/js/bootstrap.js"></script>
  </body>
</html>
