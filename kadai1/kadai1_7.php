<?php
// 7. 6 で保存したファイルを読み込み、配列にし、表示させる。
// (※1行ずつの内容がそれぞれ配列の要素となるように)

$filename = "kadai1_5.txt";
$file_contents_array = [];

$fp = @fopen($filename ,"r") or die("ファイルを開けませんでした。");

while ($fline = fgets($fp, 1024)){
  $file_contents_array[] = $fline;
}
echo var_dump($file_contents_array);

fclose( $fp );

?>