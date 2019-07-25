<?php

ob_start();
session_start();
session_regenerate_id(true);

require_once('./common_function.php');

//var_dump($_SESSION);

$admin_data = array();

if(true === isset($_SESSION['admin'])){
    $admin_data = $_SESSION['admin'];
}else{
    $login_alert['login_alert'] = true;
    $_SESSION['output_buffer'] = $login_alert;
    header('Location: ./login/login.php');
    exit();
}

//var_dump($admin_data);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>販売管理ツールメインメニュー</title>
</head>
<body>
<div class="container">
<div class="row">
    <div class="col-12 text-center">
    <p>ようこそ<?php echo h(@$admin_data['name']); ?>さん</p>
    </div>
    <div class="col-12 text-center">
    <p>ログイン成功</p>
    </div>
    <div class="col-12 text-center">
    <p><a href="./item/add_item.php">商品登録画面</a></p>
    </div>
    <div class="col-12 text-center">
    <p><a href="./item/item_list.php">商品一覧画面</a></p>
    </div>
    <div class="col-12 text-center">
    <p><a href="./user/add_user.php">顧客登録画面</a></p>
    </div>
    <div class="col-12 text-center">
    <p><a href="./user/user_list.php">顧客一覧画面</a></p>
    </div>
</div>
</div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>