<?php

ob_start();
session_start();
session_regenerate_id(true);

require_once('../common_function.php');

//var_dump($_SESSION);

$view_data = array();
if(true === isset($_SESSION['output_buffer'])){
    $view_data = $_SESSION['output_buffer'];
}

if(true === isset($_SESSION['admin'])){
    $admin_data = $_SESSION['admin'];
}else{
    $login_alert['login_alert'] = true;
    $_SESSION['login_alert'] = $login_alert;
    header('Location: ../login/login.php');
    exit();
}
//var_dump($view_data);
//var_dump($admin_data);

unset($_SESSION['output_buffer']);

//CSRF
$csrf_token = create_csrf_token_admin();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>商品登録画面</title>
</head>
<body>
<div class="container">
<div class="row">

    <form action="add_item_fin.php" method="post" class="mx-auto">
        <?php if( (isset($view_data['error_overlap_item_code'])) && (true === $view_data['error_overlap_item_code']) ) :?>
        <span class="text-danger">その商品コードは既に登録済みです。</span><br>
        <?php endif ;?>

        <?php if( (isset($view_data['error_csrf'])) && (true === $view_data['error_csrf']) ) :?>
        <span class="text-danger">CSRFトークンでエラーが起きました。正しい転移を30分以内操作してください。</span><br>
        <?php endif ;?>

        <input type="hidden" name="csrf_token" class="form-control" value="<?php echo h($csrf_token); ?>"><br>

        <?php if(isset($view_data['error_must_item_code']) && true === $view_data['error_must_item_code']): ?>
            <span class="text-danger">商品コードが未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_invalid_item_code']) && true === $view_data['error_invalid_item_code']): ?>
            <span class="text-danger">商品コードは半角英数で入力してください。<br></span>
        <?php endif; ?>
  
                <label>商品コード：</label>
                <input type="text" name="item_code" class="form-control" placeholder="商品コード" value="<?php echo h(@$view_data['item_code']); ?>"><br>

        <?php if(isset($view_data['error_must_item_name']) && true === $view_data['error_must_item_name']): ?>
            <span class="text-danger">商品名が未入力です。<br></span>
        <?php endif; ?>
      
                <label>商品名：</label>
                <input type="text" name="item_name" class="form-control" placeholder="商品名" value="<?php echo h(@$view_data['item_name']); ?>"><br>

        <?php if(isset($view_data['error_must_item_price']) && true === $view_data['error_must_item_price']): ?>
            <span class="text-danger">売価が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_invalid_item_price']) && true === $view_data['error_invalid_item_price']): ?>
            <span class="text-danger">売価は半角数値で入力してください。<br></span>
        <?php endif; ?>        

                <label>売価（税別）：</label>
                <input type="text" name="item_price" class="form-control" placeholder="売価(税別)" value="<?php echo h(@$view_data['item_price']); ?>"><br>

        <?php if(isset($view_data['error_must_item_cost']) && true === $view_data['error_must_item_cost']): ?>
            <span class="text-danger">原価が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_invalid_item_cost']) && true === $view_data['error_invalid_item_cost']): ?>
            <span class="text-danger">原価は半角数値で入力してください。<br></span>
        <?php endif; ?>              
 
                <label>原価（税別）：</label>
                <input type="text" name="item_cost" class="form-control" placeholder="原価(税別)" value="<?php echo h(@$view_data['item_cost']); ?>"><br>

        <?php if(isset($view_data['error_must_item_tax']) && true === $view_data['error_must_item_tax']): ?>
            <span class="text-danger">消費税区分が未入力です。<br></span>
        <?php endif; ?>

                <label>消費税率：</label>
                <input type="radio" name="item_tax" class="form-control" value="8"><p>8%</p><br>
                <input type="radio" name="item_tax" class="form-control" value="10"><p>10%</p><br>

                <button type="submit" class="btn btn-lg btn-primary btn-block">商品登録</button>

    </form>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</div>
</div>
</body>
</html>