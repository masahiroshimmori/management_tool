<?php

/*
 * (管理画面想定)１件のform情報の詳細
 */

// HTTP responseヘッダを出力する可能性があるので、バッファリングしておく
ob_start();
session_start();
session_regenerate_id(true);

$admin_data = array();
if(true === isset($_SESSION['admin'])){
    $admin_data = $_SESSION['admin'];
}else{
    $login_alert['login_alert'] = true;
    $_SESSION['output_buffer'] = $login_alert;
    header('Location: ../login/login.php');
    exit();
}

// 共通関数のinclude
require_once('../common_function.php');
require_once('../form_data_validate.php');


// パラメタを受け取る
// XXX エラーチェックは get_test_form() 関数側でやっているのでここではオミット
$item_code = (string)@$_GET['item_code'];
// 確認
//var_dump($item_code);

// データの取得
$datum = form_check($item_code);
if (true === empty($datum)) {
    header('Location: ./item_list.php');
    exit;
}
//var_dump($datum);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>商品登録明細</title>
</head>
<body>

<?php require_once dirname(__DIR__)."/common_parts/header.php"; ?>

<div class="container">
<h1>フォーム内容詳細</h1>
  <table class="table table-hover">
  <tr>
    <td>商品コード</td>
    <td><?php echo h($datum['item_code']); ?></td>
  </td>
  <tr>
    <td>商品名</td>
    <td><?php echo h($datum['item_name']); ?></td>
  </tr>
  <tr>
    <td>売価</td>
    <td><?php echo h($datum['item_price']); ?>円</td>
  </tr>
  <tr>
    <td>原価</td>
    <td><?php echo h($datum['item_cost']); ?>円</td>
  </tr>
  <tr>
    <td>消費税</td>
    <td><?php echo h($datum['item_tax']); ?>%</td>
  </tr>
  <tr>
    <td>在庫数</td>
    <td><?php echo h($datum['item_stock']); ?></td>
  </tr>
  <tr>
    <td>作成日時</td>
    <td><?php echo h($datum['created']); ?></td>
  </tr>
  <tr>
    <td>修正日時</td>
    <td><?php echo h($datum['updated']); ?></td>
  </tr>
  </table>
  <a class="btn btn-light" href ="./item_list.php" onclick="history.back(); return false;">戻る</a>
</div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>