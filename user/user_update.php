<?php

ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../common_function.php');
require_once('../form_data_validate.php');

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
$user_code = (string)@$_GET['user_code'];
// 確認
//var_dump($user_code);

// データの取得
$datum = user_form_check($user_code);
if (true === empty($datum)) {
    header('Location: ./user_list.php');
    exit;
}

// $_SESSION['output_buffer']にデータがある場合は、情報を上書きする
// 配列の「加算演算子による結合」では先に出したほうが優先されるので、セッション情報を先に書く
if (true === isset($_SESSION['output_buffer'])) {
    $datum = $_SESSION['output_buffer'] + $datum;
}
//var_dump($datum);

// (二重に出力しないように)セッション内の「出力用情報」を削除する
unset($_SESSION['output_buffer']);

// CSRFトークンの取得
$csrf_token = create_csrf_token_admin();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>商品修正画面</title>
</head>
<body>
<div class="container">
<h1>フォーム内容修正</h1>

        <?php if( (isset($datum['error_csrf'])) && (true === $datum['error_csrf']) ) :?>
        <span class="text-danger">CSRFトークンでエラーが起きました。正しい転移を30分以内操作してください。</span><br>
        <?php endif ;?>

        <?php if(isset($datum['error_must_user_name']) && true === $datum['error_must_user_name']): ?>
            <span class="text-danger">顧客名が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($datum['error_must_user_post']) && true === $datum['error_must_user_post']): ?>
            <span class="text-danger">郵便番号が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($datum['error_invalid_user_post']) && true === $datum['error_invalid_user_post']): ?>
            <span class="text-danger">郵便番号は半角数値で入力してください。<br></span>
        <?php endif; ?>        

        <?php if(isset($datum['error_must_user_address']) && true === $datum['error_must_user_address']): ?>
            <span class="text-danger">住所が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($datum['error_must_user_tel']) && true === $datum['error_must_user_tel']): ?>
            <span class="text-danger">電話番号が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($datum['error_invalid_user_tel']) && true === $datum['error_invalid_user_tel']): ?>
            <span class="text-danger">電話番号は半角数値で入力してください。<br></span>
        <?php endif; ?> 

    <form action="user_update_fin.php" method="post" class="mx-auto">
        <input type="hidden" name="user_code" value="<?php echo h($datum['user_code']); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">

        <table class="table table-hover">
        <tr>
            <td>顧客コード</td>
            <td><?php echo h($datum['user_code']); ?></td>
        </tr>    
        <tr>
            <td>顧客名</td>
            <td><input name="user_name" value="<?php echo h($datum['user_name']); ?>"></td>
        </tr>
        <tr>
            <td>郵便番号</td>
            <td><input name="user_post" value="<?php echo h($datum['user_post']); ?>"></td>
        </tr>
        <tr>
            <td>住所</td>
            <td><input name="user_address" value="<?php echo h($datum['user_address']); ?>"></td>
        </tr>
        <tr>
            <td>電話番号</td>
            <td><input name="user_tel" value="<?php echo h($datum['user_tel']); ?>"></td>          
        </tr>
        <tr>
            <td>メールアドレス</td>
            <td><input name="user_email" value="<?php echo h($datum['user_email']); ?>"></td>          
        </tr>
        </table>
        <a class="btn btn-light" href ="./user_list.php">戻る</a>
        <button type="submit" class="btn btn-primary">修正</button>

    </form>
</div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>







