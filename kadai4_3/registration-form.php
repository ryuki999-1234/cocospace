<?php

require_once '../smarty/libs/Smarty.class.php';
require_once 'pdo-connect.php';

$smarty = new Smarty();
$smarty->template_dir = 'templates/';
$smarty->compile_dir  = 'templates_c/';

session_start();
if (!empty($_SESSION["NAME"])) {
  // header("Location: bulletin-board.php");  // メイン画面へ遷移 
  header("Location: bulletin-board-with-file.php");  // メイン画面へ遷移 
}

$message = "";
$pdo = pdo();

//SignUpがclickされたとき
if (!empty($_POST["signup"])) {
  if (!empty($_POST["name"])  && !empty($_POST["pass"]) && !empty($_POST["mail"])) {
    $name = $_POST["name"];
    $pass = $_POST["pass"];
    $mail = $_POST["mail"];

    //挿入SQL文
    try {
      $stmt = $pdo->prepare('SELECT * FROM kadai4_user_table where name=?');
      $stmt->execute(array($name));

      if ($row = $stmt->fetch()) {
        //検索結果があるとき
        $message = '<p>このユーザー名は既に登録されています</p>';
      } else {

        // mb_send_mailでメールの送信を行う
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");

        $title = "簡易掲示板本登録用メール";

        // urltokenはnameのハッシュ値
        $urltoken = password_hash($name, PASSWORD_DEFAULT);
        $url = "http://co-19-214.99sv-coco.com/kadai4_2/user-registration-form.php" . "?urltoken=" . $urltoken;
        $body = "登録確認メールの送信を受け付けました。\n24時間以内に下記のURLからご登録下さい。\n{$url}";

        $headers = "From: furukawa";
        // メールアドレス, 題名, 本文, ヘッダー
        if (mb_send_mail($mail, $title, $body, $headers)) {
          //検索結果がないとき(ユーザ名の重複がないとき)
          $sql = $pdo->prepare("INSERT INTO kadai4_user_table (name, pass, mail, flag) VALUES (:name, :pass, :mail, :flag)");

          $sql->bindParam(':name', $name, PDO::PARAM_STR);
          $sql->bindParam(':pass', $pass, PDO::PARAM_STR);
          $sql->bindParam(':mail', $mail, PDO::PARAM_STR);
          $sql->bindValue(':flag', 0, PDO::PARAM_INT);

          $sql->execute();
          $message = "<p>メール送信成功です。「" . $name . "」" .
           "のユーザ仮登録を受け付けました。<br>{$url}\nのようなURLが届きます</p>";
          
        } else {
          $message = '<p>メール送信失敗です</p>';
        }
      }
    } catch (PDOException $e) {
      //エラーメッセージと行番号表示
      $message = '<p>'.$e->getMessage() . " - " . $e->getLine() . PHP_EOL.'</p>';
    }
  } else {
    $message = '<p>Error:Name or Password or MailAddress Empty</p>';
  }
}

$smarty->assign('message', $message);

// DBに格納された行を順に処理

$sql = 'SELECT * FROM kadai4_user_table'; //SQLステートメントの作成
$stmt = $pdo->query($sql);

$results = $stmt->fetchAll();
$smarty->assign('results', $results);

$pdo = NULL;

$smarty->display('registration-form.html');

?>