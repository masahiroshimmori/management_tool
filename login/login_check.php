<?php

// セッションの開始
ob_start();
session_start();

// 共通関数のinclude
require_once('../common_function.php');
require_once('../common_auth.php');

// 日付関数(date)を(後で)使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// ユーザ入力情報を保持する配列を準備する
$user_input_data = array();
// エラー情報を保持する配列を準備する
$error_detail = array();

// 「パラメタの一覧」を把握
$params = array('admin_id', 'pass_1', 'pass_2');
// データを取得する ＋ 必須入力のvalidate
foreach($params as $p) {
    $user_input_data[$p] = (string)@$_POST[$p];
    if ('' === $user_input_data[$p]) {
        $error_detail['error_must_' . $p] = true;
    }
}
//パスワードの二重チェク
    if ($user_input_data['pass_1'] !== $user_input_data['pass_2']) {
        $error_detail['error_invalid_pass'] = true;
    }
// 確認
//var_dump($user_input_data);

// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;
    // メアドは保持する
    $_SESSION['output_buffer']['admin_id'] = $user_input_data['admin_id'];

    // 入力ページに遷移する
    header('Location: ./login.php');
    exit;
}

// 比較用のパスワード情報取得 ＆ パスワード比較
// DBハンドルの取得
$dbh = get_dbh();

// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'SELECT * FROM admin_users WHERE admin_id=:admin_id;';
$pre = $dbh->prepare($sql);
// 値のバインド
$pre->bindValue(':admin_id', $user_input_data['admin_id'], PDO::PARAM_STR);
// SQLの実行
$r = $pre->execute();
if (false === $r) {

    echo 'SQLでエラーが起きました';
    exit;
}
// SELECTした内容の取得

$datum = $pre->fetch(PDO::FETCH_ASSOC);
//var_dump($datum);

// ログイン処理(共通化)
$login_flg = login($user_input_data['pass_1'], $datum, 'admin_user_login_lock');

//var_dump($login_flg);

// 最終的に「ログイン情報に不備がある」場合は、エラーとして突き返す

// エラーが出たら入力ページに遷移する
if (false === $login_flg) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer']['error_invalid_login'] = true;
    // IDは保持する
    $_SESSION['output_buffer']['admin_id'] = $user_input_data['admin_id'];

    // 入力ページに遷移する
    header('Location: ./login.php');
    exit;
}

// ここまで来たら「適切な情報でログインができている」

// セッションIDを張り替える：
session_regenerate_id(true);
// 「ログインできている」という情報をセッション内に格納する
$_SESSION['admin']['admin_id'] = $datum['admin_id'];
$_SESSION['admin']['name'] = $datum['name'];
$_SESSION['admin']['role'] = $datum['role'];


// TopPage(認証後トップページ)に遷移させる
header('Location: ../top.php');