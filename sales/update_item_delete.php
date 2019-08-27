<?php

// セッションの開始
ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../common_function.php');

$admin_data = array();
if(true === isset($_SESSION['admin'])){
    $admin_data = $_SESSION['admin'];
}else{
    $login_alert['login_alert'] = true;
    $_SESSION['login_alert'] = $login_alert;
    header('Location: ../login/login.php');
    exit();
}

// パラメタを受け取る
$user_sales_id = (int)@$_GET['user_sales_id'];
// 確認
// var_dump($user_sales_id);

// 空の確認
if(true === empty($user_sales_id)){
  $_SESSION['output_buffer']["error_must_user_sales_id"]  = true;
  header('Location: ./sales_update.php');
  exit();
}

// DBハンドルの取得
$dbh = get_dbh();

//削除前に数量を一旦0とする
$sql = 'UPDATE dat_user_item SET item_mount=:item_mount WHERE user_sales_id=:user_sales_id;';
$pre = $dbh->prepare($sql);
$pre->bindValue(':item_mount', 0, PDO::PARAM_INT);
$pre->bindValue(':user_sales_id', $user_sales_id, PDO::PARAM_INT);
$pre->execute();

//user_sales_idからuser_item_dat(通し番号＝sales_id)取得
$get_sql_dat_sales_id ='SELECT * FROM dat_user_item WHERE user_sales_id = :user_sales_id;';
$get_pre_dat_sales_id = $dbh->prepare($get_sql_dat_sales_id);
$get_pre_dat_sales_id->bindValue(':user_sales_id', $user_sales_id, PDO::PARAM_INT);
$get_pre_dat_sales_id->execute();

$data = $get_pre_dat_sales_id->fetch(PDO::FETCH_ASSOC);
// var_dump($data);

$sales_id = $data['user_item_dat'];

//計算用に値の取得
$get_sql_dat_user_item ='SELECT * FROM dat_user_item WHERE user_item_dat = :user_item_dat;';
$get_pre_dat_user_item = $dbh->prepare($get_sql_dat_user_item);
$get_pre_dat_user_item->bindValue(':user_item_dat', $sales_id, PDO::PARAM_INT);
$get_pre_dat_user_item->execute();

$datum = $get_pre_dat_user_item->fetchAll(PDO::FETCH_ASSOC);
// var_dump($datum);
// exit();

//小計
$total_calc = 0;
foreach($datum as $data){
    $total_by = $data["item_price"] * $data["item_mount"];
    $total_calc += $total_by;
}

//消費税8％
$total_calc8 = 0;
$tax8 = 0;
foreach($datum as $data){
    if(8 === $data['item_tax']){
    $total_by8 = $data["item_price"] * $data["item_mount"];
    $total_calc8 += $total_by8;
    }
    $tax8 = floor($total_calc8 * 0.08);
}

//消費税10％
$total_calc10 = 0;
$tax10 = 0;
foreach($datum as $data){
    if(10 === $data['item_tax']){
    $total_by10 = $data["item_price"] * $data["item_mount"];
    $total_calc10 += $total_by10;
    }
    $tax10 = floor($total_calc10 * 0.1);
}

//合計
$total_sum = $total_calc + $tax8 + $tax10;

// var_dump($total_calc);
// var_dump($tax8);
// var_dump($tax10);
// var_dump($total_sum);
// exit();

$sql_dat_sales ='UPDATE dat_sales SET total_calc=:total_calc, tax8=:tax8, tax10=:tax10, total_sum=:total_sum, updated=:updated WHERE sales_id=:sales_id;';
$pre_dat_sales = $dbh->prepare($sql_dat_sales);

$pre_dat_sales->bindValue(':sales_id', $sales_id, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':total_calc', $total_calc, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':tax8', $tax8, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':tax10', $tax10, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':total_sum', $total_sum, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':updated', date(DATE_ATOM), PDO::PARAM_STR);

$r_dat_sales = $pre_dat_sales->execute();


// DELETE文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'DELETE FROM dat_user_item WHERE user_sales_id = :user_sales_id;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':user_sales_id', $user_sales_id, PDO::PARAM_INT);

// SQLの実行
$r = $pre->execute();
if (false === $r) {
    // XXX 本当はもう少し丁寧なエラーページを出力する
    echo 'データ削除時にエラーが起きました';
    exit;
}

//登録したメッセージを出力するためのフラグを持ち回る
$_SESSION['output_buffer']['update_item_delete_success'] = true;
header('Location: ./sales_update.php');
exit();