<?php

// セッションの開始
ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../common_function.php');

// 日付関数(date)を使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

// 入力情報を保持する配列を準備する
$user_edit_data = array();

// エラー情報を保持する配列を準備する
$error_detail = array();

// 「パラメタの一覧」を把握
$params = array('sales_date', 'user_code', 'user_name', 'order_id', 'user_sales_id1', 'item_code1', 'item_name1', 'item_price1', 'item_mount1', 'item_tax1', 'user_sales_id2', 'item_code2', 'item_name2', 'item_price2', 'item_mount2', 'item_tax2', 'user_sales_id3', 'item_code3', 'item_name3', 'item_price3', 'item_mount3', 'item_tax3', 'user_sales_id4', 'item_code4', 'item_name4', 'item_price4', 'item_mount4', 'item_tax4', 'user_sales_id5', 'item_code5', 'item_name5', 'item_price5', 'item_mount5', 'item_tax5', 'total_calc', 'tax8', 'tax10', 'total_sum');
// データを取得する
foreach($params as $p) {
    $user_edit_data[$p] = (string)@$_POST[$p];
    $user_edit_data = array_filter($user_edit_data, 'strlen');
// 「必須情報の未入力エラー」であることを配列に格納しておく
  if(true === isset($user_edit_data[$p])){
      if ('' === $user_edit_data[$p]) {
          $error_detail['error_must_' . $p] = true;
      }
    }
}

//注文番号は必須ではないためここで値を把握
$user_edit_data['order_id'] = (string)@$_POST['order_id'];

//エラー時、戻し用に注文idを把握しておく。
$sales_id = (int)@$_POST['sales_id'];

// 確認
// var_dump($user_edit_data);
// var_dump($sales_id);

// // 確認
// var_dump($error_detail);
// exit();
// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;

    // 入力値をセッションに入れて持ちまわる
    // XXX 「keyが重複しない」はずなので、加算演算子でOK
    $_SESSION['output_buffer'] += $user_edit_data;

    // 編集ページに遷移する
    header('Location: ./sales_update.php?sales_id=' . rawurlencode($sales_id));
    //var_dump($_SESSION['output_buffer']);
    exit;
}

// DBハンドルの取得
$dbh = get_dbh();

$sql_dat_user_item ='UPDATE dat_user_item SET item_code=:item_code, item_name=:item_name, item_price=:item_price, item_mount=:item_mount, item_tax=:item_tax WHERE user_sales_id=:user_sales_id;';

$pre_dat_user_item = $dbh->prepare($sql_dat_user_item);
// var_dump($pre_dat_user_item);

for($i = 1; $i <=5; $i++){
    
    if(true === isset($user_edit_data["item_code$i"])){
        
        $pre_dat_user_item->bindValue(':user_sales_id', (int)$user_edit_data["user_sales_id$i"], PDO::PARAM_INT);
        $pre_dat_user_item->bindValue(':item_code', $user_edit_data["item_code$i"], PDO::PARAM_STR);
        $pre_dat_user_item->bindValue(':item_name', $user_edit_data["item_name$i"], PDO::PARAM_STR);
        $pre_dat_user_item->bindValue(':item_price', (int)$user_edit_data["item_price$i"], PDO::PARAM_INT);
        $pre_dat_user_item->bindValue(':item_mount', (int)$user_edit_data["item_mount$i"], PDO::PARAM_INT);
        $pre_dat_user_item->bindValue(':item_tax', (int)$user_edit_data["item_tax$i"], PDO::PARAM_INT);
        
    }
    $r_dat_user_item = $pre_dat_user_item->execute();
}


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

$sql_dat_sales ='UPDATE dat_sales SET sales_date=:sales_date, user_code=:user_code, user_name=:user_name, order_id=:order_id, total_calc=:total_calc, tax8=:tax8, tax10=:tax10, total_sum=:total_sum, updated=:updated WHERE sales_id=:sales_id;';
$pre_dat_sales = $dbh->prepare($sql_dat_sales);

$pre_dat_sales->bindValue(':sales_id', $sales_id, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':sales_date', $user_edit_data['sales_date'], PDO::PARAM_STR);
$pre_dat_sales->bindValue(':user_code', $user_edit_data['user_code'], PDO::PARAM_STR);
$pre_dat_sales->bindValue(':user_name', $user_edit_data['user_name'], PDO::PARAM_STR);
$pre_dat_sales->bindValue(':order_id', $user_edit_data['order_id'], PDO::PARAM_STR);
$pre_dat_sales->bindValue(':total_calc', $total_calc, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':tax8', $tax8, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':tax10', $tax10, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':total_sum', $total_sum, PDO::PARAM_INT);
$pre_dat_sales->bindValue(':updated', date(DATE_ATOM), PDO::PARAM_STR);

$r_dat_sales = $pre_dat_sales->execute();


//編集完了したフラグ
$_SESSION['output_buffer']['sales_edit_success'] = true;
header('Location: ./sales_list.php');