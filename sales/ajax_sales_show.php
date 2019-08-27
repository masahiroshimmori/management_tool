<?php
// GETメソッドでリクエストした値を取得
$user_code = $_GET['user_code'];

// 共通関数のinclude
require_once('../common_function.php');

$dbh = get_dbh();

// データ取得用SQL
// 値はバインドさせる
$sql = "SELECT user_name FROM user WHERE user_code = :user_code";
// SQLをセット
$stmt = $dbh->prepare($sql);
$stmt->bindValue(':user_code', $user_code, PDO::PARAM_STR);
// SQLを実行
$stmt->execute();

$userList = array();

$userList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ヘッダーを指定することによりjsonの動作を安定させる
header('Content-type: application/json');
// htmlへ渡す配列$userListをjsonに変換する
echo json_encode($userList);