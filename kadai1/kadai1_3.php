<?php
// 3. 2 で保存したテキストファイルを読み込んで表示させる
$filename = "kadai1_2.txt";

$fp = @fopen($filename ,"r") or die("ファイルを開けませんでした。");
$str = file_get_contents($filename);
echo $str."<br />";

// while ($fline = fgets($fp,1024)){
//   echo $fline;
// }
fclose( $fp );

?>