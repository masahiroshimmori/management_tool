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

    <form action="add_user_fin.php" method="post" class="mx-auto">
        <?php if( (isset($view_data['error_overlap_user_code'])) && (true === $view_data['error_overlap_user_code']) ) :?>
        <span class="text-danger">そのユーザーコードは既に登録済みです。</span><br>
        <?php endif ;?>

        <?php if( (isset($view_data['error_csrf'])) && (true === $view_data['error_csrf']) ) :?>
        <span class="text-danger">CSRFトークンでエラーが起きました。正しい転移を30分以内操作してください。</span><br>
        <?php endif ;?>

        <input type="hidden" name="csrf_token" class="form-control" value="<?php echo h($csrf_token); ?>"><br>

        <?php if(isset($view_data['error_must_user_code']) && true === $view_data['error_must_user_code']): ?>
            <span class="text-danger">顧客コードが未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_invalid_user_code']) && true === $view_data['error_invalid_user_code']): ?>
            <span class="text-danger">顧客コードは半角英数で入力してください。<br></span>
        <?php endif; ?>
  
                <label>顧客コード：</label>
                <input type="text" name="user_code" class="form-control" placeholder="顧客コード" value="<?php echo h(@$view_data['user_code']); ?>"><br>

        <?php if(isset($view_data['error_must_user_name']) && true === $view_data['error_must_user_name']): ?>
            <span class="text-danger">顧客名が未入力です。<br></span>
        <?php endif; ?>
      
                <label>顧客名：</label>
                <input type="text" name="user_name" class="form-control" placeholder="顧客名" value="<?php echo h(@$view_data['user_name']); ?>"><br>

        <?php if(isset($view_data['error_must_user_post']) && true === $view_data['error_must_user_post']): ?>
            <span class="text-danger">郵便番号が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_invalid_user_post']) && true === $view_data['error_invalid_user_post']): ?>
            <span class="text-danger">郵便番号は半角数値のみで入力してください。<br></span>
        <?php endif; ?>        

                <label>郵便番号(ハイフン不要)：</label>
                <input type="text" name="user_post" class="form-control" placeholder="郵便番号" value="<?php echo h(@$view_data['user_post']); ?>"><br>

        <?php if(isset($view_data['error_must_user_address']) && true === $view_data['error_must_user_address']): ?>
            <span class="text-danger">住所が未入力です。<br></span>
        <?php endif; ?>     

                <label>住所：</label>
                <input type="text" name="user_address" class="form-control" placeholder="住所" value="<?php echo h(@$view_data['user_address']); ?>"><br>

        <?php if(isset($view_data['error_must_user_tel']) && true === $view_data['error_must_user_tel']): ?>
            <span class="text-danger">電話番号が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_invalid_user_tel']) && true === $view_data['error_invalid_user_tel']): ?>
            <span class="text-danger">電話番号は半角数値のみで入力してください。<br></span>
        <?php endif; ?>              
 
                <label>電話番号(ハイフン不要)：</label>
                <input type="text" name="user_tel" class="form-control" placeholder="電話番号" value="<?php echo h(@$view_data['user_tel']); ?>"><br>
        
        <?php if(isset($view_data['error_validate_email']) && true === $view_data['error_validate_email']): ?>
            <span class="text-danger">メールアドレスの値が無効です。<br></span>
        <?php endif; ?>   
 
                <label>email(任意)：</label>
                <input type="text" name="user_email" class="form-control" placeholder="E-MAIL" value="<?php echo h(@$view_data['user_emai;']); ?>"><br>


                <button type="submit" class="btn btn-lg btn-primary btn-block">顧客登録</button>

    </form>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</div>
</div>
</body>
</html>