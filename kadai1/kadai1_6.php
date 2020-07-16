<!-- 6. 5 のテキスト保存を追記保存で保存する
(※追記の際は、改行して1行ずつ保存されるように) -->
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <title>送信フォーム</title>
</head>

<body>
  <form method="POST" action="kadai1_6.php">
    <input id="comment" type="text" name="comment"/>
    <input type="submit" value="送信" />
  </form>
  <?php
  if (!empty($_POST['comment'])) {
    $text = $_POST['comment'];
    echo "<br>「" . $text . "」(送信内容)送信を受け付けました。";

    $filename = "kadai1_5.txt";

    $fp = @fopen($filename, "a+") or die("ファイルを開けませんでした。");
    fwrite($fp,  $text."\n");
    fclose($fp);
  }
  ?>
</body>

</html>