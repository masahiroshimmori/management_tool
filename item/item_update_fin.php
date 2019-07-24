<?php

// セッションの開始
ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../form_data_validate.php');

// 日付関数(date)を使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// ユーザ入力情報を保持する配列を準備する
$item_edit_data = array();

// エラー情報を保持する配列を準備する
$error_detail = array();

// 「パラメタの一覧」を把握
$params = array('item_name', 'item_price', 'item_cost', 'item_tax');
// データを取得する
foreach($params as $p) {
    $item_edit_data[$p] = (string)@$_POST[$p];
// 「必須情報の未入力エラー」であることを配列に格納しておく
    if ('' === $item_edit_data[$p]) {
        $error_detail['error_must_' . $p] = true;
    }
}
//エラー時、戻し用に商品コードを把握しておく。
$item_code = (string)@$_POST['item_code'];

// 確認
//var_dump($item_edit_data);
//var_dump($item_code);

// 基本のエラーチェック(原価と売価は数値であること、商品コードは英数字であること)
$error_detail += validate_item_form_update($item_edit_data);


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
    $_SESSION['output_buffer'] += $item_edit_data;

    // 編集ページに遷移する
    header('Location: ./item_update.php?item_code=' . rawurlencode($item_code));
    //var_dump($_SESSION['output_buffer']);
    exit;
}

// DBハンドルの取得
$dbh = get_dbh();

// INSERT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
$sql = 'UPDATE item SET item_name=:item_name, item_price=:item_price, item_cost=:item_cost, item_tax=:item_tax, updated=:updated WHERE item_code = :item_code;';
$pre = $dbh->prepare($sql);

// 値のバインド
$pre->bindValue(':item_code', $item_code, PDO::PARAM_STR);
$pre->bindValue(':item_name', $item_edit_data['item_name'], PDO::PARAM_STR);
$pre->bindValue(':item_price', $item_edit_data['item_price'], PDO::PARAM_INT);
$pre->bindValue(':item_cost', $item_edit_data['item_cost'], PDO::PARAM_INT);
$pre->bindValue(':item_tax', $item_edit_data['item_tax'], PDO::PARAM_INT);
$pre->bindValue(':updated', date(DATE_ATOM), PDO::PARAM_STR);

// SQLの実行
$r = $pre->execute();
if(false === $r){
        echo 'データ更新時にエラーが起きました。';
        exit();
}

//var_dump($item_edit_data);

//登録したメッセージを出力するためのフラグを持ち回る
$_SESSION['output_buffer']['item_update_success'] = true;
header('Location: ./item_list.php');