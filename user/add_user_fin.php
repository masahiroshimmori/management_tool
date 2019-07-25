<?php

ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../common_function.php');
require_once('../form_data_validate.php');

// 日付関数(date)を使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// ユーザ入力情報を保持する配列を準備する
$user_input_data = array();
// エラー情報を保持する配列を準備する
$error_detail = array();

// 「パラメタの一覧」を把握
$params = array('user_code', 'user_name', 'user_post', 'user_address', 'user_tel');
// データを取得する
foreach($params as $p) {
    $user_input_data[$p] = (string)@$_POST[$p];
    //必須チェック
    if ('' === $user_input_data[$p]) {
        $error_detail['error_must_' . $p] = true;
    }
}
    $user_input_data['user_email'] = (string)@$_POST['user_email'];

// 確認
//var_dump($user_input_data);

// 基本のエラーチェック(原価と売価は数値であること、商品コードは英数字であること)
$error_detail += validate_user_form($user_input_data);

// 確認
//var_dump($error_detail);


// CSRFチェック
if (false === is_csrf_token_admin()) {
    // 「CSRFトークンエラー」であることを配列に格納しておく
    $error_detail["error_csrf"] = true;
}

// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;

    // 入力値をセッションに入れて持ちまわる
    // XXX 「keyが重複しない」はずなので、加算演算子でOK
    $_SESSION['output_buffer'] += $user_input_data;

    // 入力ページに遷移する
    header('Location: ./add_user.php');
    exit;
}

// DBハンドルの取得
$dbh = get_dbh();

// INSERT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'INSERT INTO user(user_code, user_name, user_post, user_address, user_tel, user_email, created, updated)
             VALUES (:user_code, :user_name, :user_post, :user_address, :user_tel, :user_email, :created, :updated);';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':user_code', $user_input_data['user_code'], PDO::PARAM_STR);
$pre->bindValue(':user_name', $user_input_data['user_name'], PDO::PARAM_STR);
$pre->bindValue(':user_post', $user_input_data['user_post'], PDO::PARAM_STR);
$pre->bindValue(':user_address', $user_input_data['user_address'], PDO::PARAM_STR);
$pre->bindValue(':user_tel', $user_input_data['user_tel'], PDO::PARAM_STR);
$pre->bindValue(':user_email', $user_input_data['user_email'], PDO::PARAM_STR);
$pre->bindValue(':created', date(DATE_ATOM), PDO::PARAM_STR);
$pre->bindValue(':updated', date(DATE_ATOM), PDO::PARAM_STR);

// SQLの実行
$r = $pre->execute();
if(false === $r){
    //Duplicate entry 'user_code' for key 'PRIMART'なら入力画面に突き返す。普通に起きうるエラーなので
    $e = $pre->errorInfo();
    //var_dump($e);
    if(0 === strncmp($e[2], 'Duplicate entry', strlen('Duplicate entry'))){
        $_SESSION['output_buffer']['error_overlap_user_code'] = true;
        $_SESSION['output_buffer'] += $user_input_data;
        header('Location: ./add_user.php');
        exit();
    }
    //else
    echo 'システムでエラーが起きました。';
    exit();

}

//登録したメッセージを出力するためのフラグを持ち回る
$_SESSION['output_buffer']['user_register_success'] = true;
header('Location: ./user_list.php');
