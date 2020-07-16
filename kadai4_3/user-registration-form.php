<?php
require_once '../smarty/libs/Smarty.class.php';
require_once 'pdo-connect.php';

$smarty = new Smarty();
$smarty->template_dir = 'templates/';
$smarty->compile_dir  = 'templates_c/';

$pdo = pdo();
$message = "";

$postRegistration = "";
$displayName = "";
$displayMail = "";
$displayPass = "";


if (!empty($_POST["registration"])) {
  try {
    //挿入SQL文
    $sql = 'UPDATE kadai4_user_table set flag=1 where name=?';; //SQLステートメントの作成
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($_POST["name"]));

    $postRegistration = "registration";
  } catch (PDOException $e) {
    $message =  $e->getMessage() . " - " . $e->getLine() . PHP_EOL;
  }
}

if (empty($_GET["urltoken"])) {
  header("Location: login-form.php");
  exit();
} else {
  try {
    $stmt = $pdo->query('SELECT * FROM kadai4_user_table');
    $results = $stmt->fetchall();
    foreach ($results as $row) {
      if (password_verify($row['name'], $_GET["urltoken"])) {
        $displayName = $row["name"];
        $displayMail = $row["mail"];

        $password_hide = str_repeat('*', strlen($row["name"]));
        $displayPass = $password_hide;
        break;
      }
    }
    if (!isset($password_hide)) {
      //検索結果がないとき(urltokenの一致がないとき)
      header("Location: login-form.php");
      exit();
    }
  } catch (PDOException $e) {
    //エラーメッセージと行番号表示
    $message = $e->getMessage() . " - " . $e->getLine() . PHP_EOL;
  }
}

$pdo = NULL;

$smarty->assign('displayName', $displayName);
$smarty->assign('displayMail', $displayMail);
$smarty->assign('displayPass', $displayPass);
$smarty->assign('postRegistration', $postRegistration);
$smarty->assign('message', $message);

$smarty->display('user-registration-form.html');

?>