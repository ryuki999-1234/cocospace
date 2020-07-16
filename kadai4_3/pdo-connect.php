<?php
//データベースに接続する関数

function pdo()
{


  $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

  try {
    $sql = "CREATE TABLE IF NOT EXISTS kadai4_user_table"  //tbtestが存在しなければ作成
      . " ("
      . "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY," //主キー
      . "name char(32) NOT NULL,"    //char(32)固定長文字列32バイトまでの文字列
      . 'pass VARCHAR(128) NOT NULL,'
      . "mail VARCHAR(50) NOT NULL,"
      . "flag TINYINT(1) NOT NULL DEFAULT 0"
      . ")ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;";

    $stmt = $pdo->query($sql);
  } catch (PDOException $e) {
    //エラーメッセージと行番号表示
    echo $e->getMessage() . " - " . $e->getLine() . PHP_EOL;
  }
  try {
    $sql = "CREATE TABLE IF NOT EXISTS kadai4_bulletin_board"  //tbtestが存在しなければ作成
      . " ("
      . "id INT AUTO_INCREMENT PRIMARY KEY," //主キー
      . "name char(32),"    //char(32)固定長文字列32バイトまでの文字列
      . "comment TEXT,"     //TEXT可変長文字列2^16 -1バイトまで
      . "date DATETIME,"   //日付時刻型(xxxx-xx-xx xx:xx:xx.xxxxxxx)
      . 'pass char(32),'
      . 'file_path VARCHAR(256)'
      . ");";

    $stmt = $pdo->query($sql);
  } catch (PDOException $e) {
    //エラーメッセージと行番号表示
    echo $e->getMessage() . " - " . $e->getLine() . PHP_EOL;
  }

  return $pdo;
}
