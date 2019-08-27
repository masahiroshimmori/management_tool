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

//値を受け取る
$sales_id = (int)@$_POST['sales_id'];

//値がなければ突き返す
if(true === empty($sales_id)){
  header('Location: ./sales_list.php');
  exit();
}
// else
// var_dump($sales_id);

// DBハンドルの取得
$dbh = get_dbh();

// 注文明細の削除
$sql = 'DELETE FROM dat_user_item WHERE user_item_dat = :user_item_dat;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':user_item_dat', $sales_id, PDO::PARAM_INT);

// SQLの実行
$r = $pre->execute();
if (false === $r) {
  // XXX 本当はもう少し丁寧なエラーページを出力する
  echo 'データ削除時にエラーが起きました';
  exit;
}

// 注文情報の削除
$sql = 'DELETE FROM dat_sales WHERE sales_id = :sales_id;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':sales_id', $sales_id, PDO::PARAM_INT);

// SQLの実行
$r = $pre->execute();
if (false === $r) {
    // XXX 本当はもう少し丁寧なエラーページを出力する
    echo 'データ削除時にエラーが起きました';
    exit;
}

//削除完了メッセージを出力するためのフラグを持ち回る
$_SESSION['output_buffer']['sales_delete_success'] = true;
header('Location: ./sales_list.php');
