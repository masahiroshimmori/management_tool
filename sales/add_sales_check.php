<?php
ob_start();
session_start();
session_regenerate_id(true);

// 共通関数のinclude
require_once('../common_function.php');

$admin_data = array();
if(true === isset($_SESSION['admin'])){
    $admin_data = $_SESSION['admin'];
}else{
    $login_alert['login_alert'] = true;
    $_SESSION['output_buffer'] = $login_alert;
    header('Location: ../login/login.php');
    exit();
}

// 「パラメタの一覧」を把握
$params = array('sales_date', 'order_id', 'user_code', 'user_name', 'item_code1', 'item_name1', 'item_price1', 'item_mount1','item_tax1', 'item_code2', 'item_name2', 'item_price2', 'item_mount2','item_tax2', 'item_code3', 'item_name3', 'item_price3', 'item_mount3','item_tax3', 'item_code4', 'item_name4', 'item_price4', 'item_mount4', 'item_tax4', 'item_code5', 'item_name5', 'item_price5', 'item_mount5', 'item_tax5');
// データを取得する
$user_input_data = array();
foreach($params as $p) {
    $user_input_data[$p] = (string)@$_POST[$p];
    $user_input_data = array_filter($user_input_data, 'strlen');

}
//  var_dump($user_input_data);

//バリデーション
$error_detail = array();
//登録日の空チェック
if(true === empty($user_input_data['sales_date'])){
    $error_detail['error_must_sales_date'] = true;
}

//注文番号の空チェック
if(true === empty($user_input_data['order_id'])){
    $error_detail['error_must_order_id'] = true;
}

//顧客コードの空チェック
if(true === empty($user_input_data['user_code'])){
    $error_detail['error_must_user_code'] = true;
}

//顧客名の空チェック
if(true === empty($user_input_data['user_name'])){
    $error_detail['error_must_user_name'] = true;
}

//登録商品の空チェック(商品コード主体)
for($i = 1; $i <=5; $i++){
    if(true === isset($user_input_data["item_code$i"])){
        if(true === empty($user_input_data["item_name$i"]) || true === empty($user_input_data["item_price$i"]) || true === empty($user_input_data["item_mount$i"])){
            $error_detail['error_must_item'] = true;
        }
    }
}
//登録商品の空チェック(商品名主体)
for($i = 1; $i <=5; $i++){
    if(true === isset($user_input_data["item_name$i"])){
        if(true === empty($user_input_data["item_code$i"]) || true === empty($user_input_data["item_price$i"]) || true === empty($user_input_data["item_mount$i"])){
            $error_detail['error_must_item'] = true;
        }
    }
}
//登録商品の空チェック(価格主体)
for($i = 1; $i <=5; $i++){
    if(true === isset($user_input_data["item_price$i"])){
        if(true === empty($user_input_data["item_code$i"]) || true === empty($user_input_data["item_name$i"]) || true === empty($user_input_data["item_mount$i"])){
            $error_detail['error_must_item'] = true;
        }
    }
}
//登録商品の空チェック(数量主体)
for($i = 1; $i <=5; $i++){
    if(true === isset($user_input_data["item_mount$i"])){
        if(true === empty($user_input_data["item_code$i"]) || true === empty($user_input_data["item_name$i"]) || true === empty($user_input_data["item_code$i"])){
            $error_detail['error_must_item'] = true;
        }
    }
}

// エラーが出たら入力ページに遷移する
if (false === empty($error_detail)) {
    // エラー情報をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] = $error_detail;

    // 入力値をセッションに入れて持ちまわる
    $_SESSION['output_buffer'] += $user_input_data;

    // 入力ページに遷移する
    header('Location: ./add_sales.php');
    exit;
}


//合計金額計算(税別)
function calc(){
    $total_calc = 0;
    for($i = 1; $i <= 5; $i++){
        global $user_input_data;
            if(true === isset($user_input_data["item_price$i"]) && true === isset($user_input_data["item_mount$i"])){
            $calc = (int)$user_input_data["item_price$i"] * (int)$user_input_data["item_mount$i"];
            $total_calc += $calc;
            // var_dump($calc);
            }
    }
    return $total_calc;
}

//消費税計算(8%)
function tax8(){
    $total_cal = 0;
    $tax_calc8 = 0;
    for($i = 1; $i <= 5; $i++){
        global $user_input_data;
            if(true === isset($user_input_data["item_price$i"]) && true === isset($user_input_data["item_tax$i"])){
                if('8' === $user_input_data["item_tax$i"]){
                    $calc = (int)$user_input_data["item_price$i"] * (int)$user_input_data["item_mount$i"];
                    $total_cal += $calc;
                }
            }
    }
    $tax_calc8 = floor($total_cal * 0.08);
    return $tax_calc8;
}

//消費税計算(10%)
function tax10(){
    $total_cal = 0;
    $tax_calc10 = 0;
    for($i = 1; $i <= 5; $i++){
        global $user_input_data;
            if(true === isset($user_input_data["item_price$i"]) && true === isset($user_input_data["item_tax$i"])){
                if('10' === $user_input_data["item_tax$i"]){
                    $calc = (int)$user_input_data["item_price$i"] * (int)$user_input_data["item_mount$i"];
                    $total_cal += $calc;
                }
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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <title>売上登録確認画面</title>
</head>
<body>

<?php require_once dirname(__DIR__)."/common_parts/header.php"; ?>
    
<div class="container">
    <div class="row">
        <form action="add_sales_fin.php" method="post" class="mx-auto">
        <h1>売上登録情報確認</h1>
        <p class="text-danger">下記の内容で登録しますか？</p>
            <table class="table table-hover">
            <tr>
                <th>登録日</th>
            <?php if(true === isset($user_input_data['order_id'])) : ?> 
                <th>注文番号</th>
            <?php endif ; ?>
                <th>顧客コード</th>
                <th>顧客名</th>
            </tr>
            <tr>
                <td><input type="hidden" name="sales_date" value="<?php echo h($user_input_data['sales_date']); ?>">
                <p><?php echo h($user_input_data['sales_date']); ?></p></td>
            <?php if(true === isset($user_input_data['order_id'])) : ?> 
                <td><input type="hidden" name="order_id" value="<?php echo h($user_input_data['order_id']); ?>">
                <p><?php echo h($user_input_data['order_id']); ?></p></td>               
            <?php endif ; ?>
                <td><input type="hidden" name="user_code" value="<?php echo h($user_input_data['user_code']); ?>">
                <p><?php echo h($user_input_data['user_code']); ?></p></td>               
                <td><input type="hidden" name="user_name" value="<?php echo h($user_input_data['user_name']); ?>">
                <p><?php echo h($user_input_data['user_name']); ?></p></td>                               
            </tr>

            <tr>
                <th>商品コード</th>
                <th>商品名</th>
                <th>単価</th>
                <th>数量</th>
                <th>消費税率</th>
            </tr>
            <tr>
        <?php for($i = 1; $i <=5; $i++): ?>
            <?php if(true === isset($user_input_data["item_code$i"])):?>
                <td><input type="hidden" name="item_code<?php echo $i; ?>" value='<?php echo h($user_input_data["item_code$i"]); ?>'>
                <p><?php echo h($user_input_data["item_code$i"]); ?></p></td>                               
            <?php endif; ?>
            <?php if(true === isset($user_input_data["item_name$i"])):?>
                <td><input type="hidden" name="item_name<?php echo $i; ?>" value='<?php echo h($user_input_data["item_name$i"]); ?>'>
                <p><?php echo h($user_input_data["item_name$i"]); ?></p></td>                               
            <?php endif; ?>
            <?php if(true === isset($user_input_data["item_price$i"])):?>
                <td><input type="hidden" name="item_price<?php echo $i; ?>" value='<?php echo h($user_input_data["item_price$i"]); ?>'>
                <p><?php echo h($user_input_data["item_price$i"]); ?></p></td>                               
            <?php endif; ?>
            <?php if(true === isset($user_input_data["item_mount$i"])):?>
                <td><input type="hidden" name="item_mount<?php echo $i; ?>" value='<?php echo h($user_input_data["item_mount$i"]); ?>'>
                <p><?php echo h($user_input_data["item_mount$i"]); ?></p></td>                               
            <?php endif; ?>
            <?php if(true === isset($user_input_data["item_tax$i"])):?>
                <td><input type="hidden" name="item_tax<?php echo $i; ?>" value='<?php echo h($user_input_data["item_tax$i"]); ?>'>
                <p><?php echo h($user_input_data["item_tax$i"]); ?>%</p></td>                               
            </tr>
            <?php endif; ?>
        <?php endfor ; ?>
            </table>
        <hr>
        <p class="text-right">小計:<input type="hidden" name="total_calc" value="<?php echo calc(); ?>"><?php echo number_format(calc()); ?>円</p>
        <p class="text-right">消費税(8%):<input type="hidden" name="tax8" value="<?php echo tax8(); ?>"><?php echo number_format(tax8()); ?>円</p>
        <p class="text-right">消費税(10%)<input type="hidden" name="tax10" value="<?php echo tax10(); ?>">:<?php echo number_format(tax10()); ?>円</p>
        <p class="text-right">合計:<input type="hidden" name="total_sum" value="<?php echo total_sum(); ?>"><?php echo number_format(total_sum()); ?>円</p>
        <div class="col">
            <a class="btn btn-light btn-lg btn-block" href ="./add_sales.php" onclick="history.back(); return false;">戻る</a>
            <button type="submit" class="btn btn-primary btn-lg btn-block mb-5">登録</button>
        </div>

        </form>

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    </div>
</div>
</body>
</html>