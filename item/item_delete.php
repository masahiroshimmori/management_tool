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
// XXX エラーチェックは get_test_form() 関数側でやっているのでここではオミット
$item_code = (string)@$_POST['item_code'];
// 確認
//var_dump($item_code);

// CSRFチェック
if (false === is_csrf_token_admin()) {
    // 「CSRFトークンエラー」であることをセッションに格納しておく
    $_SESSION['output_buffer']["error_csrf"]  = true;

    // 一覧ページに遷移する
    header('Location: ./item_list.php');
    exit;
}

// DBハンドルの取得
$dbh = get_dbh();

// INSERT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'DELETE FROM item WHERE item_code = :item_code;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':item_code', $item_code, PDO::PARAM_STR);

// SQLの実行
$r = $pre->execute();
if (false === $r) {
    // XXX 本当はもう少し丁寧なエラーページを出力する
    echo 'データ削除時にエラーが起きました';
    exit;
}

//登録したメッセージを出力するためのフラグを持ち回る
$_SESSION['output_buffer']['item_delete_success'] = true;
header('Location: ./item_list.php');