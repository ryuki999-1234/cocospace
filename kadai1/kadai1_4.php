<!-- 4.formで文字列データを飛ばし、その値を表示させる(POSTでもGETでも可) -->
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <title>送信フォーム</title>
</head>

<body>
  <form method="POST" action="kadai1_4.php">
    <input id="comment" type="text" name="comment"/>
    <input type="submit" value="送信" />
  </form>
  <?php
  if (!empty($_POST['comment'])) {
    $text = $_POST['comment'];
    echo "<br>「" . $text . "」(送信内容)送信を受け付けました。";
  }
  ?>
</body>

</html>