<?php
// 共通関数のinclude
require_once('../common_function.php');

// GETメソッドでリクエストした値を取得
$item_code = $_GET['item_code'];

$dbh = get_dbh();

// データ取得用SQL
// 値はバインドさせる
$sql = "SELECT item_name, item_price, item_tax FROM item WHERE item_code = :item_code";
// SQLをセット
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':item_code', $item_code, PDO::PARAM_STR);
// SQLを実行
$stmt->execute();

$itemList = array();

$itemList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ヘッダーを指定することによりjsonの動作を安定させる
header('Content-type: application/json');
// htmlへ渡す配列$itemListをjsonに変換する
echo json_encode($itemList);