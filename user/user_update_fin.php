<?php

// セッションの開始
ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../form_data_validate.php');

// 日付関数(date)を使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// 顧客入力情報を保持する配列を準備する
$user_edit_data = array();

// エラー情報を保持する配列を準備する
$error_detail = array();

// 「パラメタの一覧」を把握
$params = array('user_name', 'user_post', 'user_address', 'user_tel');
// データを取得する
foreach($params as $p) {
    $user_edit_data[$p] = (string)@$_POST[$p];
// 「必須情報の未入力エラー」であることを配列に格納しておく
    if ('' === $user_edit_data[$p]) {
        $error_detail['error_must_' . $p] = true;
    }
}
//メールアドレスは必須ではないためここで値を把握
$user_edit_data['user_email'] = (string)@$_POST['user_email'];

//エラー時、戻し用に顧客コードを把握しておく。
$user_code = (string)@$_POST['user_code'];

// 確認
//var_dump($user_edit_data);
//var_dump($user_code);

// 基本のエラーチェック(原価と売価は数値であること、商品コードは英数字であること)
$error_detail += validate_user_form_update($user_edit_data);


// CSRFチェック
if (false === is_csrf_token_admin()) {
    // 「CSRFトークンエラー」であることを配列に格納しておく
    $error_detail["error_csrf"] = true;
}

// 確認
//var_dump($error_detail);

// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;

    // 入力値をセッションに入れて持ちまわる
    // XXX 「keyが重複しない」はずなので、加算演算子でOK
    $_SESSION['output_buffer'] += $user_edit_data;

    // 編集ページに遷移する
    header('Location: ./user_update.php?user_code=' . rawurlencode($user_code));
    //var_dump($_SESSION['output_buffer']);
    exit;
}

// DBハンドルの取得
$dbh = get_dbh();

// INSERT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'UPDATE user SET user_name=:user_name, user_post=:user_post, user_address=:user_address, user_tel=:user_tel, user_email=:user_email, updated=:updated WHERE user_code = :user_code;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':user_code', $user_code, PDO::PARAM_STR);
$pre->bindValue(':user_name', $user_edit_data['user_name'], PDO::PARAM_STR);
$pre->bindValue(':user_post', $user_edit_data['user_post'], PDO::PARAM_STR);
$pre->bindValue(':user_address', $user_edit_data['user_address'], PDO::PARAM_STR);
$pre->bindValue(':user_tel', $user_edit_data['user_tel'], PDO::PARAM_STR);
$pre->bindValue(':user_email', $user_edit_data['user_email'], PDO::PARAM_STR);
$pre->bindValue(':updated', date(DATE_ATOM), PDO::PARAM_STR);

// SQLの実行
$r = $pre->execute();
if(false === $r){
        echo 'データ更新時にエラーが起きました。';
        exit();
}

//var_dump($user_edit_data);

//登録したメッセージを出力するためのフラグを持ち回る
$_SESSION['output_buffer']['user_update_success'] = true;
header('Location: ./user_list.php');