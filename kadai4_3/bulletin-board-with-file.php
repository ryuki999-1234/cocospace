<?php

require_once '../smarty/libs/Smarty.class.php';
require_once '../Cache_Lite/Cache/Lite.php';
require_once 'pdo-connect.php';

// ファイルの拡張子を判別する関数
function checkExt($filename)
{
  $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
  $video_ext = ["ogg", "ogv", "webm", "mp4"];
  $img_ext = ["gif", "jpg", "png", "bmp"];

  $ext_judge = "";
  if (in_array($ext, $img_ext)) {
    $ext_judge = "img";
  } elseif (in_array($ext, $video_ext)) {
    $ext_judge = "video";
  }
  return $ext_judge;
}

// DBから投稿一覧を表示させる関数
function displayList($pdo)
{

  try {
    //選択SQL文
    $sql = 'SELECT * from kadai4_bulletin_board';

    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
  } catch (PDOException $e) {
    $message = '<p>' . $e->getMessage() . " - " . $e->getLine() . PHP_EOL . '</p>';
  }

  for ($i = 0; $i < count($results); $i++) {
    $results[$i]['ext'] = "";
    if (!empty($results[$i]["file_path"])) {
      $results[$i]['ext'] = checkExt($results[$i]['file_path']);
    }
  }
  return $results;
}

$smarty = new Smarty();
$smarty->template_dir = 'templates/';
$smarty->compile_dir  = 'templates_c/';

$options = array(
  'cacheDir'                => 'tmp/',
  'lifeTime'                => 3600, //キャッシュ時間
  'caching'                 => true,
  'automaticCleaningFactor' => 20,
  'automaticSerialization'  => true,
  // 'pearErrorMode'           => 'CACHE_LITE_ERROR_DIE'
);

$Cache_Lite = new Cache_Lite($options);

$pdo = pdo();

session_start();
// ユーザ名の登録があるとき
if (!empty($_SESSION["NAME"])) {
  $userName = $_SESSION["NAME"];
  // chcheIdの追加
  $cacheId = $_SESSION['NAME'];
} else {
  header("Location: login-form.php");  // ログイン画面へ遷移 
  exit;
}

// ログアウトを行ったとき
if (!empty($_POST["logout"])) {
  session_destroy();
  header("Location: login-form.php"); // ログイン画面へ遷移
  exit;
}


$message = "";
$editName = $userName;
$editComment = "";
$editNumber = "";
$results = "";
$formFlag = False;

if (!empty($_POST["edit"])) {
  if (!empty($_POST["editNumber"]) && !empty($_POST["editPass"])) {
    $editNumber = $_POST["editNumber"];
    $editPass = $_POST["editPass"];
    try {
      //選択SQL文
      $sql = 'SELECT * from kadai4_bulletin_board where id=? and pass=?';

      $stmt = $pdo->prepare($sql);
      $stmt->execute(array($editNumber, $editPass));

      $results = $stmt->fetchAll();   //結果セットを配列で返す

      $match_num = $stmt->rowCount(); //直前に処理が行われた行の個数を返す
    } catch (PDOException $e) {
      $message = '<p>' . $e->getMessage() . " - " . $e->getLine() . PHP_EOL . '</p>';
    }
    if (!empty($results)) {
      $row = $results[0];
      $editName = $row['name'];
      $editComment = $row['comment'];
      $message = "現在編集モード" . "<br/>";
      $message .= "Passwordの入力の必要ありません" . "<br/>";
    }
  }
}

//Sendがclickされたとき
if (!empty($_POST["send"]) && empty($_POST["textbox"])) {
  if (!empty($_POST["name"]) && !empty($_POST["comment"]) && !empty($_POST["sendPass"])) {
    $name = $_POST["name"];
    $comment = $_POST["comment"];
    ini_set('date.timezone', 'Asia/Tokyo');
    $date = date('Y-m-d h:i:s');
    $pass = $_POST["sendPass"];

    $storeDir = "";
    $filename = "";
    // ファイルがアップロードされていた場合の処理
    if (!empty($_FILES["upFile"])) {
      if (!empty($_FILES["upFile"])) {
        // 表示の際に、正規表現で拡張子判別
        $ext_judge = checkExt($_FILES["upFile"]["name"]);
        if (!empty($ext_judge)) {
          $storeDir = "./upload-file/";
          $filename = date("YmdHis") . $_FILES["upFile"]["name"];
          // ファイルを一時フォルダから指定したディレクトリに移動します
          move_uploaded_file($_FILES['upFile']['tmp_name'], $storeDir . $filename);
          // fileパスの保存./upload-file/$filename
        }
      } else {
        $message = "サイズが超過している可能性があります。65MBまでのファイルを選択してください";
      }
    }

    $filePath = $storeDir . $filename;
    try {
      //挿入SQL文
      $sql = $pdo->prepare("INSERT INTO kadai4_bulletin_board 
          (name, comment, date, pass, file_path) VALUES (?, ?, ?, ?, ?)");
      $sql->execute(array($name, $comment, $date, $pass, $filePath));
      $formFlag = True;
    } catch (PDOException $e) {
      $message = '<p>' . $e->getMessage() . " - " . $e->getLine() . PHP_EOL . '</p>';
    }

    $message = "「" . $name . "」「" . $comment . "」「" . $filename . "」送信を受け付けました。" . "<br>";
  } else {
    $message = "Error:Name or Comment or Password Empty" . "<br>";
  }
}

// Deleteがclickされたとき
if (!empty($_POST["delete"])) {
  if (!empty($_POST["deleteNumber"]) && !empty($_POST["deletePass"])) {
    $deleteNumber = $_POST["deleteNumber"];
    $deletePass = $_POST["deletePass"];
    try {

      // 削除SQL文
      $sql = 'DELETE from kadai4_bulletin_board WHERE id=? AND pass=?';

      $stmt = $pdo->prepare($sql);
      $stmt->execute(array($deleteNumber, $deletePass));
      // 直近の SQL ステートメントによって作用した行数を返す
      $match_num = $stmt->rowCount();
      $formFlag = True;
    } catch (PDOException $e) {
      $message = '<p>' . $e->getMessage() . " - " . $e->getLine() . PHP_EOL . '</p>';
    }
    if ($match_num != 0) {
      $message = $deleteNumber . "の削除を受け付けました。" . "<br>";
    } else {
      $message = "Error:deleteNumber or Password already deleted" . "<br>";
    }
  } else {
    $message = "Error:deleteNumber or Password Empty" . "<br>";
  }
}

if (!empty($_POST["send"]) && !empty($_POST["textbox"])) {
  $sql = 'update kadai4_bulletin_board set name=?,comment=? where id=?';

  $id = $_POST["textbox"]; //変更する投稿番号
  $name = $_POST["name"];
  $comment = $_POST["comment"];

  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($name, $comment, $id));
    $formFlag = True;
  } catch (PDOException $e) {
    $message = '<p>' . $e->getMessage() . " - " . $e->getLine() . PHP_EOL . '</p>';
  }
  $message = $id . "「" . $name . "」" . "「" . $comment . "」編集を受け付けました。" . "<br>";
}

if (!empty($_POST["edit"])) {
  if (empty($_POST["editNumber"]) || empty($_POST["editPass"])) {
    $message = "Error:editNumber or Password Empty" . "<br>";
  }
}

// キャッシュが存在する場合の処理
$cache = $Cache_Lite->get($cacheId);
if ($cache) {
  $results = $cache;

  // 削除の場合、キャッシュから該当部分を削除
  if (!empty($_POST["delete"]) && $formFlag) {
    $results = [];
    foreach ((object)$cache as $row) {
      if ($row["id"] != $deleteNumber) {
        $results[] = $row;
      }
    }
    $Cache_Lite->save($results, $cacheId);

    // 編集の場合、投稿分をキャッシュに追加する
  } elseif (!empty($_POST["send"]) && !empty($_POST["textbox"]) && $formFlag) {
    $results = displayList($pdo);
    $Cache_Lite->save($results, $cacheId);

    // 新規投稿の場合、投稿分をキャッシュに追加する
  } elseif (!empty($_POST["send"]) && empty($_POST["textbox"]) && $formFlag) {
    $id = $pdo->lastInsertId();

    // file_pathから拡張子情報を取得
    $ext = "";
    if (!empty($filePath)) {
      $ext = checkExt($filePath);
    }
    $results[] = array(
      'id' => $id, 'name' => $name, "comment" => $comment,
      "date" => $date, "pass" => $pass, "file_path" => $filePath, "ext" => $ext
    );

    $Cache_Lite->save($results, $cacheId);
  }

  $cacheOnOff = "キャッシュ有効";

  // キャッシュが存在しない場合
} else {

  $results = displayList($pdo);

  $cache = $results;
  $cacheOnOff = "キャッシュなし";

  $Cache_Lite->save($cache, $cacheId);
}

$pdo = NULL;

$smarty->assign("cacheOnOff", $cacheOnOff);
$smarty->assign('message', $message);
$smarty->assign('userName', $userName);
$smarty->assign('editName', $editName);
$smarty->assign('editComment', $editComment);
$smarty->assign('editNumber', $editNumber);
$smarty->assign('results', $results);

$smarty->display('bulletin-board-with-file.html');
