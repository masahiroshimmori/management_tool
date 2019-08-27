<?php

ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../common_function.php');
require_once('../form_data_validate.php');

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

// パラメタを受け取る
// XXX エラーチェックは get_test_form() 関数側でやっているのでここではオミット
$sales_id = (string)@$_GET['sales_id'];
// 確認
//var_dump($sales_id);

// データの取得
$datum = form_check_sales($sales_id);
if (true === empty($datum)) {
    header('Location: ./sales_list.php');
    exit;
}

list($datum1, $datum2) = $datum;
// var_dump($datumx1);
// var_dump($datum2);

//合計金額計算(税別)
function calc(){
  $total_calc = 0;
  global $datum2;
  foreach($datum2 as $data){
    $calc = (int)$data["item_price"] * (int)$data["item_mount"];
    $total_calc += $calc;
  }
    return $total_calc;
}

//消費税計算(8%)
function tax8(){
  $total_cal = 0;
  $tax_calc8 = 0;
  global $datum2;
  foreach($datum2 as $data){
    if(8 === $data["item_tax"]){
        $calc = (int)$data["item_price"] * (int)$data["item_mount"];
        $total_cal += $calc;
    }  
  }
  $tax_calc8 = floor($total_cal * 0.08);
  return $tax_calc8;
}

//消費税計算(10%)
function tax10(){
  $total_cal = 0;
  $tax_calc10 = 0;
  global $datum2;
  foreach($datum2 as $data){
    if(10 === $data["item_tax"]){
        $calc = (int)$data["item_price"] * (int)$data["item_mount"];
        $total_cal += $calc;
    }  
  }
  $tax_calc10 = floor($total_cal * 0.1);
  return $tax_calc10;
}

//合計
function total_sum(){
  $total_sum = calc() + tax8() + tax10(); 
  return $total_sum;
}

$csrf_token = create_csrf_token();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <title>商品情報明細</title>
</head>
<body>

<?php require_once dirname(__DIR__)."/common_parts/header.php"; ?>
    
    <div class="container">
    <div class="row">

    <h1>注文情報明細</h1>
  <form action="sales_update_fin.php" method="post" class="mx-auto">
  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token;?>">
  <input type="hidden" name="sales_id" value="<?php echo h($datum1['sales_id']);?>">
  <table class="table table-hover">
  <thead class="thead-light">
  <tr>
    <th>登録日</th>
    <th>顧客コード</th>
    <th>顧客名</th>
    <th>注文番号</th>
  </tr>
  </thead>
  <tr>
    <td><input type="text" name="sales_date" placeholder="登録日" value="<?php echo h($datum1['sales_date']); ?>"></td>
    <td><input type="text" name="user_code" placeholder="顧客コード" value="<?php echo h($datum1['user_code']); ?>"></td>
    <td><input type="text" name="user_name" placeholder="顧客名" value="<?php echo h($datum1['user_name']); ?>"></td>
    <td><input type="text" name="order_id" placeholder="注文番号" value="<?php echo h($datum1['order_id']); ?>"></td>
  </tr>
  </table>

  <table class="table table-hover">
  <thead class="thead-light">
  <tr>
    <th>商品コード</th>
    <th>商品名</th>
    <th>単価</th>
    <th>数量</th>
    <th>消費税率</th>
    <th>削除</th>
  </tr>
  </thead>

  <?php foreach($datum2 as $k => $data): ?>
  <tr>
    <input type="hidden" name="user_sales_id<?php echo $k + 1;?>" value="<?php echo h($data['user_sales_id']); ?>">
    
    <td>
    <input type="text" name="item_code<?php echo $k + 1;?>" placeholder="商品コード" value="<?php echo h($data['item_code']); ?>">
    </td>

    <td>
    <input type="text" name="item_name<?php echo $k + 1;?>" placeholder="商品名" value="<?php echo h($data['item_name']); ?>">
    </td>

    <td>
    <input type="text" name="item_price<?php echo $k + 1;?>" placeholder="単価" value="<?php echo h($data['item_price']); ?>">円
    </td>

    <td>
    <input type="text" name="item_mount<?php echo $k + 1;?>" placeholder="数量" value="<?php echo h($data['item_mount']); ?>">
    </td>

    <td>
    <input type="text" name="item_tax<?php echo $k + 1;?>" placeholder="消費税" value="<?php echo h($data['item_tax']); ?>">%
    </td>

    <td>
    <a class="btn btn-light" href="./update_item_delete.php?user_sales_id=<?php echo h($data['user_sales_id']); ?>" onClick="return confirm('本当に削除しますか？');">×</a>
    </td>
  </tr>
  <?php endforeach ; ?>
  </table>
  <table class="table table-hover">
  <tr>
    <th>小計</th>
    <td><input type="text" name="total_calc" placeholder="小計" value="<?php echo calc(); ?>">円</td>
  </tr>
  <tr>
    <th>消費税8％</th>
    <td><input type="text" name="tax8" placeholder="消費税8%" value="<?php echo tax8(); ?>">円</td>
  </tr>
  <tr>
    <th>消費税10％</th>
    <td><input type="text" name="tax10" placeholder="消費税10%" value="<?php echo tax10(); ?>">円</td>
  </tr>
  <tr>
    <th>合計</th>
    <td><input type="text" name="total_sum" placeholder="合計" value="<?php echo total_sum(); ?>">円</td>
  </tr>
  </table>
  <a class="btn btn-secondary btn-block mb-2" href ="./sales_list.php" onclick="history.back(); return false;">戻る</a>
  <button type="submit" class="btn btn-primary btn-block mb-5">登録</button>
  </form>


    <script src="ajax_sales.js"></script>
    <script src="https://code.jquery.com/jquery-3.0.0.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</div>
</div>
</body>
</html>