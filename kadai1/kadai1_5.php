<!-- 5. 4 で受け取った値を表示ではなくテキストファイルに保存する -->
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <title>送信フォーム</title>
</head>

<body>
  <form method="POST" action="kadai1_5.php">
    <input id="comment" type="text" name="comment"/>
    <input type="submit" value="送信" />
  </form>
  <?php
  if (!empty($_POST['comment'])) {
    $text = $_POST['comment'];
    echo "<br>「" . $text . "」(送信内容)送信を受け付けました。";

    $filename = "kadai1_5.txt";

    $fp = @fopen($filename, "w+") or die("ファイルを開けませんでした。");
    fwrite($fp,  $text);
    fclose($fp);
  }
  ?>
</body>

</html>