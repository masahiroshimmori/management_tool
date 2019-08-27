<?php
ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../common_function.php');

// 日付関数(date)を使うのでタイムゾーンの設定
date_default_timezone_set('Asia/Tokyo');

$admin_data = array();
if(true === isset($_SESSION['admin'])){
    $admin_data = $_SESSION['admin'];
}else{
    $login_alert['login_alert'] = true;
    $_SESSION['output_buffer'] = $login_alert;
    header('Location: ../login/login.php');
    exit();
}

// 「パラメタの一覧」を把握
$params = array('sales_date', 'order_id', 'user_code', 'user_name', 'item_code1', 'item_name1', 'item_price1', 'item_mount1','item_tax1', 'item_code2', 'item_name2', 'item_price2', 'item_mount2','item_tax2', 'item_code3', 'item_name3', 'item_price3', 'item_mount3','item_tax3', 'item_code4', 'item_name4', 'item_price4', 'item_mount4', 'item_tax4', 'item_code5', 'item_name5', 'item_price5', 'item_mount5', 'item_tax5', 'total_calc', 'tax8', 'tax10', 'total_sum');
// データを取得する
$user_input_data = array();
foreach($params as $p) {
    $user_input_data[$p] = (string)@$_POST[$p];
    $user_input_data = array_filter($user_input_data, 'strlen');

}
// var_dump($user_input_data);

$dbh = get_dbh();

// //注文情報登録
$sql ='INSERT INTO dat_sales(order_id, sales_date, user_code, user_name, total_calc, tax8, tax10,total_sum, created, updated)VALUES(:order_id, :sales_date, :user_code, :user_name, :total_calc, :tax8, :tax10, :total_sum, :created, :updated);';
$pre = $dbh->prepare($sql);

$pre->bindValue(':order_id', $user_input_data['order_id'], PDO::PARAM_STR);
$pre->bindValue(':sales_date', $user_input_data['sales_date'], PDO::PARAM_STR);
$pre->bindValue(':user_code', $user_input_data['user_code'], PDO::PARAM_STR);
$pre->bindValue(':user_name', $user_input_data['user_name'], PDO::PARAM_STR);
$pre->bindValue(':total_calc', $user_input_data['total_calc'], PDO::PARAM_INT);
$pre->bindValue(':tax8', $user_input_data['tax8'], PDO::PARAM_INT);
$pre->bindValue(':tax10', $user_input_data['tax10'], PDO::PARAM_INT);
$pre->bindValue(':total_sum', $user_input_data['total_sum'], PDO::PARAM_INT);
$pre->bindValue(':created', date(DATE_ATOM), PDO::PARAM_STR);
$pre->bindValue(':updated', date(DATE_ATOM), PDO::PARAM_STR);

$pre->execute();

//注文明細登録
$sql='SELECT LAST_INSERT_ID()';
$pre=$dbh->prepare($sql);
$pre->execute();
$rec=$pre->fetch(PDO::FETCH_ASSOC);
$last_get_id=$rec['LAST_INSERT_ID()'];

for($i = 1; $i <=5; $i++){
    $sql = 'INSERT INTO dat_user_item(user_item_dat, item_code, item_name, item_price, item_mount, item_tax)VALUES(:user_item_dat, :item_code, :item_name, :item_price, :item_mount, :item_tax);';
    $pre = $dbh->prepare($sql);
    if(true === isset($user_input_data["item_code$i"])){
        $pre->bindValue(':user_item_dat', $last_get_id, PDO::PARAM_INT);
        $pre->bindValue(':item_code', $user_input_data["item_code$i"], PDO::PARAM_STR);
        $pre->bindValue(':item_name', $user_input_data["item_name$i"], PDO::PARAM_STR);
        $pre->bindValue(':item_price', $user_input_data["item_price$i"], PDO::PARAM_INT);
        $pre->bindValue(':item_mount', $user_input_data["item_mount$i"], PDO::PARAM_INT);
        $pre->bindValue(':item_tax', $user_input_data["item_tax$i"], PDO::PARAM_INT);
    }
    $pre->execute();
}

// 現状の在庫数を把握
// for($i = 1; $i <=5; $i++){
//     if(true === isset($user_input_data["item_code$i"])){
//         $sql = 'SELECT item_stock FROM item WHERE item_code=:item_code;';
//         $pre = $dbh->prepare($sql);
//         $pre->bindValue(':item_code', $user_input_data["item_code$i"], PDO::PARAM_STR);
//         $r = $pre->execute();
//         $data = $pre->fetch(PDO::FETCH_ASSOC);
//         $item_stock=$data['item_stock'];
//         $datum[] = $item_stock;
//         // var_dump($item_stock);
//     }
// }
// var_dump($datum);
// exit();

//在庫の更新
for($i = 1; $i<=5; $i++){
    if(true === isset($user_input_data["item_code$i"])){
        //sqlの組み立て
        $sql1 = 'UPDATE item SET item_stock = item_stock - ';
        $sql2 = ' WHERE item_code=:item_code;';
        $sql = $sql1 . intval($user_input_data["item_mount$i"]) . $sql2;
        $pre = $dbh->prepare($sql);
        $pre->bindValue(':item_code', $user_input_data["item_code$i"], PDO::PARAM_STR);
        $r = $pre->execute();
    }
}


//登録完了したフラグ
$_SESSION['output_buffer']['sales_register_success'] = true;
header('Location: ./add_sales.php');