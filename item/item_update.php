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
$item_code = (string)@$_GET['item_code'];
// 確認
//var_dump($item_code);

// データの取得
$datum = form_check($item_code);
if (true === empty($datum)) {
    header('Location: ./item_list.php');
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

        <?php if(isset($datum['error_must_item_name']) && true === $datum['error_must_item_name']): ?>
            <span class="text-danger">商品名が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($datum['error_must_item_price']) && true === $datum['error_must_item_price']): ?>
            <span class="text-danger">売価が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($datum['error_invalid_item_price']) && true === $datum['error_invalid_item_price']): ?>
            <span class="text-danger">売価は半角数値で入力してください。<br></span>
        <?php endif; ?>        

        <?php if(isset($datum['error_must_item_cost']) && true === $datum['error_must_item_cost']): ?>
            <span class="text-danger">原価が未入力です。<br></span>
        <?php endif; ?>

        <?php if(isset($datum['error_invalid_item_cost']) && true === $datum['error_invalid_item_cost']): ?>
            <span class="text-danger">原価は半角数値で入力してください。<br></span>
        <?php endif; ?>              

        <?php if(isset($datum['error_must_item_tax']) && true === $datum['error_must_item_tax']): ?>
            <span class="text-danger">消費税区分が未入力です。<br></span>
        <?php endif; ?>

    <form action="item_update_fin.php" method="post" class="mx-auto">
        <input type="hidden" name="item_code" value="<?php echo h($datum['item_code']); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">

        <table class="table table-hover">
        <tr>
            <td>商品コード</td>
            <td><?php echo h($datum['item_code']); ?></td>
        </tr>    
        <tr>
            <td>商品名</td>
            <td><input name="item_name" value="<?php echo h($datum['item_name']); ?>"></td>
        </tr>
        <tr>
            <td>売価</td>
            <td><input name="item_price" value="<?php echo h($datum['item_price']); ?>">円</td>
        </tr>
        <tr>
            <td>原価</td>
            <td><input name="item_cost" value="<?php echo h($datum['item_cost']); ?>">円</td>
        </tr>
        <tr>
            <td>消費税率</td>
            <td>
            <input type="radio" name="item_tax" value="8"<?php if((int)$datum['item_tax'] === 8) echo ' checked';?>><p>8%</p><br>
            <input type="radio" name="item_tax" value="10"<?php if((int)$datum['item_tax'] === 10) echo ' checked';?>><p>10%</p><br>
            </td>          
        </tr>
        </table>
        <a class="btn btn-light" href ="./item_list.php">戻る</a>
        <button type="submit" class="btn btn-primary">修正</button>

    </form>
</div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>







