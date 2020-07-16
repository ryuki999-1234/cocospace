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

//SignInがclickされたとき
if (!empty($_POST["signin"])) {
  if (!empty($_POST["name"])  && !empty($_POST["pass"])) {
    $name = $_POST["name"];
    $pass = $_POST["pass"];

    try {
      // 選択SQL文
      $sql = 'SELECT * FROM kadai4_user_table where name=? and pass=? and flag=1'; //SQLステートメントの作成
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array($name, $pass));

      if ($row = $stmt->fetch()) {  //検索結果があるとき
        // if (password_verify($password, $row['password'])) {  //入力されたパスワードとハッシュ化されたパスワードが一致するとき
        session_start();
        session_regenerate_id(true);
        $_SESSION["NAME"] = $row['name'];    //ユーザ名

        header("Location: bulletin-board-with-file.php");
        exit();  // 処理終了

      } else {
        // 認証失敗
        $message = '<p>ユーザー名あるいはパスワードに誤りがあります。</p>';
      }
    } catch (PDOException $e) {
      $error = 
      $message = '<p>'.$e->getMessage() . " - " . $e->getLine() . PHP_EOL.'</p>';
    }
  } else {
    $message =  "<p>Error:Name or Password Empty</p>";
  }
}

$smarty->assign('message', $message);

// DBに格納された行を順に処理

$sql = 'SELECT * FROM kadai4_user_table'; //SQLステートメントの作成
$stmt = $pdo->query($sql);

$results = $stmt->fetchAll();
$smarty->assign('results', $results);

$pdo = NULL;

$smarty->display('login-form.html');

?>