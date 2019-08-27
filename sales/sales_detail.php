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
//var_dump($item_code);

// データの取得
$datum = form_check_sales($sales_id);
if (true === empty($datum)) {
    header('Location: ./sales_list.php');
    exit;
}
list($datum1, $datum2) = $datum;
// var_dump($datum1);
// var_dump($datum2);

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
  <table class="table table-hover">
  <thead class="thead-light">
  <tr>
    <th>登録日</th>
    <th>顧客コード</th>
    <th>顧客名</th>
    <th>注文番号</th>
    <th>作成日時</th>
    <th>修正日時</th>
  </tr>
  </thead>
  <tr>
    <td><?php echo h($datum1['sales_date']); ?></td>
    <td><?php echo h($datum1['user_code']); ?></td>
    <td><?php echo h($datum1['user_name']); ?></td>
    <td><?php echo h($datum1['order_id']); ?></td>
    <td><?php echo h($datum1['created']); ?></td>
    <td><?php echo h($datum1['updated']); ?></td>
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
  </tr>
  </thead>
  <?php foreach($datum2 as $data): ?>
  <tr>
    <td><?php echo h($data['item_code']); ?></td>
    <td><?php echo h($data['item_name']); ?></td>
    <td><?php echo number_format($data['item_price']); ?>円</td>
    <td><?php echo h($data['item_mount']); ?></td>
    <td><?php echo h($data['item_tax']); ?>%</td>
  </tr>
  <?php endforeach ; ?>
  </table>
  <table class="table table-hover">
  <tr>
    <th>小計</th>
    <td><?php echo number_format($datum1['total_calc']); ?>円</td>
  </tr>
  <tr>
    <th>消費税8％</th>
    <td><?php echo number_format($datum1['tax8']); ?>円</td>
  </tr>
  <tr>
    <th>消費税10％</th>
    <td><?php echo number_format($datum1['tax10']); ?>円</td>
  </tr>
  <tr>
    <th>合計</th>
    <td><?php echo number_format($datum1['total_sum']); ?>円</td>
  </tr>
  </table>
  <a class="btn btn-light" href ="./sales_list.php" onclick="history.back(); return false;">戻る</a>


    <script src="https://code.jquery.com/jquery-3.0.0.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</div>
</div>
</body>
</html>