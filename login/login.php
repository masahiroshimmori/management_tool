<?php

ob_start();
session_start();

require_once('../common_function.php');

//var_dump($_SESSION);

$view_data = array();

if(true === isset($_SESSION['output_buffer'])){
    $view_data = $_SESSION['output_buffer'];
}

if(true === isset($_SESSION['login_alert'])){
    $login_alert = $_SESSION['login_alert'];
}

//var_dump($view_data);

unset($_SESSION['output_buffer']);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>Document</title>
</head>
<body style="background-color: #87cefa;">
<div class="container">
    <div class="row">

    <form action="login_check.php" method="post" class="mx-auto my-5">

        <?php if(isset($login_alert['login_alert']) && true === $login_alert['login_alert']): ?>
        <span class="text-danger">ログインが必要です。<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_invalid_login']) && true === $view_data['error_invalid_login']): ?>
            <span class="text-danger">管理者IDまたはパスワードに誤りがあります。<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_double_pass']) && true === $view_data['error_invalid_pass']): ?>
            <span class="text-danger">パスワードが一致しません<br></span>
        <?php endif; ?>

        <?php if(isset($view_data['error_must_admin_id']) && true === $view_data['error_must_admin_id']): ?>
            <span class="text-danger">管理者IDが未入力です。<br></span>
        <?php endif; ?>
                <label>管理者ID：</label>
                <input type="text" name="admin_id" class="form-control" placeholder="管理者ID" value="<?php echo h(@$view_data['admin_id']); ?>"><br>
                
        <?php if(isset($view_data['error_must_pass_1']) && true === $view_data['error_must_pass_1']): ?>
            <span class="text-danger">パスワードが未入力です。<br></span>
        <?php endif; ?>
                <label>パスワード：</label>
                <input type="password" name="pass_1" class="form-control" placeholder="パスワード" value=""><br>
        <?php if(isset($view_data['error_must_pass_2']) && true === $view_data['error_must_pass_2']): ?>
            <span class="text-danger">パスワード(再)が未入力です。<br></span>
        <?php endif; ?>
                <label>パスワード(再)：</label>
                <input type="password" name="pass_2" class="form-control" placeholder="パスワード(再)" value=""><br>    
                <br>
                <button type="submit" class="btn btn-lg btn-primary btn-block">ログイン</button>
    </form>
    </div><!--row-->
</div><!--container-->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>