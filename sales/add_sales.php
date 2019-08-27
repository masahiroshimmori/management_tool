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

$view_data = array();
if(true === isset($_SESSION['output_buffer'])){
    $view_data = $_SESSION['output_buffer'];
}
// var_dump($view_data);
unset($_SESSION['output_buffer']);

//今日の日付を返す
function today(){
    return date('Ymd');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <title>商品登録画面</title>
</head>
<body>

<?php require_once dirname(__DIR__)."/common_parts/header.php"; ?>

    <div class="container">
    <div class="row">

    <?php if(true === isset($view_data['sales_register_success'])): ?>
        <div class="container my-2">
            <div class="alert alert-primary alert-dismissible fade show">
                <p>登録が完了しました。</p>
                <button class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    <?php endif ; ?>
    <?php if(true === isset($view_data['error_must_sales_date'])): ?>
        <div class="container my-2">
            <div class="alert alert-danger alert-dismissible fade show">
                <p>登録日が未入力です。</p>
                <button class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    <?php endif ; ?>
    <?php if(true === isset($view_data['error_must_order_id'])): ?>
        <div class="container my-2">
            <div class="alert alert-danger alert-dismissible fade show">
                <p>注文番号が未入力です。</p>
                <button class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    <?php endif ; ?>
    <?php if(true === isset($view_data['error_must_user_code'])): ?>
        <div class="container my-2">
            <div class="alert alert-danger alert-dismissible fade show">
                <p>顧客コードが未入力です。</p>
                <button class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    <?php endif ; ?>
    <?php if(true === isset($view_data['error_must_user_name'])): ?>
        <div class="container my-2">
            <div class="alert alert-danger alert-dismissible fade show">
                <p>顧客名が未入力です。</p>
                <button class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    <?php endif ; ?>
    <?php if(true === isset($view_data['error_must_item'])): ?>
        <div class="container my-2">
            <div class="alert alert-danger alert-dismissible fade show">
                <p>商品登録情報が不足しています。</p>
                <button class="close" data-dismiss="alert">&times;</button>
            </div>
        </div>
    <?php endif ; ?>

    <form action="add_sales_check.php" method="post" class="mx-auto">

        <div class="form-row align-items-center mb-3">
            <div class="col">
                <span class="badge badge-danger">必須</span>
                <input type="text" id="sales_date" name="sales_date" class="form-control" placeholder="登録日" value="<?php echo today(); ?>">
            </div>
            <div class="col">
                <span class="badge badge-danger">必須</span>
                <input type="text" id="order_id" name="order_id" class="form-control" value="<?php echo h(@$view_data['order_id']);?>" placeholder="注文番号">
            </div>
        </div>
    <hr>
        <div class="form-row align-items-center mb-3">
            <div class="col">
            <span class="badge badge-danger">必須</span>
            <div class="input-group">
                <input type="text" id="user_code" name="user_code" class="form-control" value="<?php echo h(@$view_data['user_code']);?>" placeholder="顧客コード">
                <span class="input-group-btn">
                <button type="submit" class="page-link text-dark d-inline-block" id="ajax_user">検索</button>
                </span>
            </div>
            </div>
            <div class="col">
            <span class="badge badge-danger">必須</span>
            <div class="input-group">
                <input type="text" name="user_name" id="result_user_name" class="form-control" value="<?php echo h(@$view_data['user_name']);?>" placeholder="顧客名">
                <span class="input-group-btn">
                <a class="page-link text-dark d-inline-block" href="#" role="button"onclick="window.open('../user/user_list.php','_blank','width=600,height=400,resizable=no'); return false;">顧客名検索</a>
                </span>
            </div>
            </div>
        </div>
    <hr>
    <?php for($i = 1; $i <=5; $i++): ?>
        <div class="form-row align-items-start mb-3">
            <div class="col-2">
            <div class="input-group">
                    <input type="text" id="item_code<?php echo $i; ?>" name="item_code<?php echo $i; ?>" class="form-control" value="<?php echo h(@$view_data["item_code$i"]);?>" placeholder="商品コード">
                <span class="input-group-btn">
                    <button type="submit" class="page-link text-dark d-inline-block" id="ajax_item<?php echo $i; ?>">検索</button>
                </span>
            </div>
            </div>
            <div class="col-6">
            <div class="input-group">
                    <input type="text" name="item_name<?php echo $i; ?>" id="result_item_name<?php echo $i; ?>" class="form-control" value="<?php echo h(@$view_data["item_name$i"]);?>" placeholder="商品名">
                <span class="input-group-btn">
                    <a class="page-link text-dark d-inline-block" href="#" role="button"onclick="window.open('../item/item_list.php','_blank','width=600,height=400,resizable=no'); return false;">商品名検索</a>
                </span>
            </div>
            </div>
            <div class="col-2">
                    <input type="text" name="item_price<?php echo $i; ?>" id="result_item_price<?php echo $i; ?>" class="form-control" value="<?php echo h(@$view_data["item_price$i"]);?>" placeholder="単価">
            </div>
            <div class="col-1">
                    <input type="text" name="item_mount<?php echo $i; ?>" class="form-control" value="<?php echo h(@$view_data["item_mount$i"]);?>" placeholder="数量">
            </div>
            <div class="col-1">
                    <input type="text" name="item_tax<?php echo $i; ?>" id="result_item_tax<?php echo $i; ?>" class="form-control" value="<?php echo h(@$view_data["item_tax$i"]);?>" placeholder="消費税" readonly>
            </div>
        </div>
        <hr>
    <?php endfor ; ?>

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