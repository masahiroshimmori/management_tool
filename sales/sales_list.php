<?php

error_reporting(1);

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

//設定値の設定：configなどに吐き出した方がよりよい場合が多い
$contents_per_page = 5; //1ページあたりの出力数

//ページの取得
if(false === isset($_GET['p'])){
    $page_num = 1;
}else{
    $page_num = intval($_GET['p']);//もし文字が入ってきたら０になる。
    //１より小さいページ数が指定されたら１に揃える
    if(1 > $page_num){
        $page_num = 1;
    }
}
//確認
//var_dump($page_num);

//検索パラメータの取得
//ホワイトリストの準備
$search_list = array(
    'search_like_order_id',
    'search_like_user_name',
    'search_sales_date_from',
    'search_sales_date_to'
);
//データの取得
$search = array();
foreach($search_list as $p){
    if((true === isset($_GET[$p])) && ('' !== $_GET[$p])){
        $search[$p] = $_GET[$p];
    }
}
//確認
// var_dump($search);

// DBハンドルの取得
$dbh = get_dbh();

// SELECT文の作成と発行
// 準備された文(プリペアドステートメント)の用意
//countと通常用の２種類のsqlを発行する必要があるのでselectを一旦切り取る
$sql = 'FROM dat_sales';

//条件がある場合の検索条件の付与
$bind_array = array();
if(false === empty($search)){

    $where_list = array();
    
    //値を把握する
    //like句(注文番号)
    if(true === isset($search['search_like_order_id'])){
        $where_list[] = 'order_id LIKE :like_order_id';
        $bind_array[':like_order_id'] = '%'. like_escape($search['search_like_order_id']).'%';
    }

    //like句(顧客名)
    if(true === isset($search['search_like_user_name'])){
        $where_list[] = 'user_name LIKE :like_user_name';
        $bind_array[':like_user_name'] = '%'. like_escape($search['search_like_user_name']).'%';
    }


    //範囲指定(から〜まで)登録日時検索
    if(true === isset($search['search_sales_date_from']) && true === isset($search['search_sales_date_to'])){
        $where_list[] = 'sales_date BETWEEN :sales_date_from AND :sales_date_to';
        $search['search_sales_date_from'] = date('Y-m-d',strtotime($search['search_sales_date_from']));
        $search['search_sales_date_to'] = date('Y-m-d',strtotime($search['search_sales_date_to']));
        $bind_array[':sales_date_from'] = $search['search_sales_date_from'].' 00:00:00';
        $bind_array[':sales_date_to'] = $search['search_sales_date_to'].' 23:59:59';
    }elseif
    //範囲指定(から)登録日時検索
    (true === isset($search['search_sales_date_from'])){
        $where_list[] = 'sales_date BETWEEN :sales_date_from AND :sales_date_to';
        $search['search_sales_date_from'] = date('Y-m-d',strtotime($search['search_sales_date_from']));
        $bind_array[':sales_date_from'] = $search['search_sales_date_from'].' 00:00:00';
        $bind_array[':sales_date_to'] = date(DATE_ATOM);
        
    }elseif
    //範囲指定(まで)登録日時検索
    (true === isset($search['search_sales_date_to'])){
        $where_list[] = 'sales_date BETWEEN :sales_date_from AND :sales_date_to';
        $search['search_sales_date_to'] = date('Y-m-d',strtotime($search['search_sales_date_to']));
        $bind_array[':sales_date_from'] = '2019-07-01 00:00:00';        
        $bind_array[':sales_date_to'] = $search['search_sales_date_to'].' 23:59:59';
    }   
    
    
    //where句を合成してsqlへつなげる
    $sql = $sql.' WHERE '. implode(' AND ', $where_list);
    
}

//ページング処理
    $sql_limit_string = ' LIMIT :start_page, :contents_per_page';
    $bind_array[':start_page'] = ($page_num -1) * $contents_per_page;//[ページ数 - 1] * 1pageあたりの出力数
    $bind_array[':contents_per_page'] = $contents_per_page;

//count用と通常用の２つのsqlを作成し、sqlを閉じる
$sql_count = 'SELECT count(sales_id) ' . $sql . ';';
//SELECT count(item_code) FROM item WHERE $where_list;//例えばitem_tax = :item_tax
$sql_main = 'SELECT * ' . $sql . $sql_limit_string . ';';
//確認
//var_dump($sql_count);
//var_dump($sql_main);

//プリペアドステートメントを作成する
$pre_count = $dbh->prepare($sql_count);
$pre_main = $dbh->prepare($sql_main);

//exit();
// 値のバインド
if(false === empty($bind_array)){
    foreach($bind_array as $k => $v){
        $pre_count->bindValue($k, $v);
        $pre_main->bindValue($k, $v);
    }
}

//値の確認
//var_dump($bind_array);

//count側のsql実行
$r = $pre_count->execute();
if (false === $r) {
        // XXX 本当はもう少し丁寧なエラーページを出力する
        echo 'システムでエラーが起きました';
        exit;
}
// データを取得
$data = $pre_count->fetch();
//確認
//var_dump($data);
//var_dump($data[0]);
//全件数取得
$total_contents_num = $data[0];
//var_dump($total_contents_num);

//最大ページ数を把握
//ceil(全体n件÷１ページあたりm件)
$max_page_num = (int) ceil($total_contents_num / $contents_per_page);
//var_dump($max_page_num);

//指定されたpageが最大ページを超える場合は最大ページとする
if($page_num > $max_page_num){
    $page_num = $max_page_num;
    //値をバインドし直す
    //$pre_count->bindValue(':start_page', ($page_num -1) * $contents_per_page, PDO::PARAM_INT);
}
//var_dump($page_num);
//
//main側のsql実行
$r = $pre_main->execute();
if (false === $r) {
        // XXX 本当はもう少し丁寧なエラーページを出力する
        echo 'システムでエラーが起きました';
        exit;
}

//データをまとめて取得
$data = $pre_main->fetchAll(PDO::FETCH_ASSOC);
//var_dump($data);

// $_SESSION['output_buffer']にデータがある場合は、情報を取得する
if (true === isset($_SESSION['output_buffer'])) {
    $output_buffer = $_SESSION['output_buffer'];
} else {
    $output_buffer = array();
}

//var_dump($output_buffer);
// (二重に出力しないように)セッション内の「出力用情報」を削除する
unset($_SESSION['output_buffer']);

//URLパラメータを作成する共通関数
//sort条件は同一なのでglobal変数領域から、page数は状況によって異なるので引数から取得
function get_url_params($page_num){
    $params = array();
    $params['p'] = $page_num;

    return http_build_query($params);
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
    <title>売上検索画面</title>
</head>
<body>

<?php require_once dirname(__DIR__)."/common_parts/header.php"; ?>
    
    <div class="container">
    <div class="row">

        <?php if(true === isset($output_buffer['sales_delete_success'])): ?>
            <div class="container my-2">
                <div class="alert alert-danger alert-dismissible fade show">
                    <p>登録情報を削除しました。</p>
                    <button class="close" data-dismiss="alert">&times;</button>
                </div>
            </div>
        <?php endif ; ?>

        <form action="sales_list.php" method="get" class="mx-auto my-5">
            <div class="form-row mb-3">
                <div class="col">
                        <input type="text" name="search_like_order_id" class="form-control" value="<?php echo h(@$search['search_like_order_id']); ?>" placeholder="注文番号">
                </div>
                <div class="col">
                        <input type="text" name="search_like_user_name" class="form-control" value="<?php echo h(@$search['search_like_user_name']); ?>" placeholder="顧客名">
                </div>
            </div>
            <div class="form-row mb-3">
                <div class="col">
                        <input type="text" name="search_sales_date_from" class="form-control" value="<?php echo h(@$search['search_sales_date_from']); ?>" placeholder="登録日(から)">
                </div>
                <div class="col">
                        <input type="text" name="search_sales_date_to" class="form-control" value="<?php echo h(@$search['search_sales_date_to']); ?>" placeholder="登録日(まで)">
                </div>
            </div>
            <button type="submit" class="btn btn-block btn-primary">検索</button>
        </form>
    </div>
    </div>

    <div class="container mb-5">
    <div class="row">
        <?php if (false === empty($search)) : ?>
            現在、以下の項目で検索をかけています。<br>
            <?php
                foreach($search as $k => $v) {
                    if($k === 'search_like_order_id'){$k = '注文番号';}
                    if($k === 'search_like_user_name'){$k = '顧客名';}
                    if($k === 'search_sales_date_from'){$k = '登録日(から)';}
                    if($k === 'search_sales_date_to'){$k = '登録日(まで)';}
                    echo h($k), ': ', h($v), "<br>\n";
                }
                ?>

            <a class="btn btn-light" href="./sales_list.php">検索項目をクリアする</a>
        <?php endif;?>
    </div>
    </div>
        
    <div class="container">
    <div class="row">
    <h2>売上一覧</h2>
    </div>
    </div>
    <div class="container">
    <div class="row">
    <div class="col-6"><p class="text-left">全<?php echo $total_contents_num; ?>件</p></div>
    <div class="col-6"><p class="text-right"><?php echo $page_num . '/' . $max_page_num;?>ページを表示中</p></div>

    <table class="table table-hover">
    <tr>
        <th>注文番号</th>
        <th>顧客名</th>
        <th>購入金額合計</th>
        <th>登録日</th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <?php foreach($data as $datum): ?>
    <tr>
        <td><?php echo h($datum['order_id']); ?></td>
        <td><?php echo h($datum['user_name']); ?></td>
        <td><?php echo number_format($datum['total_sum']); ?></td>
        <td><?php echo h($datum['sales_date']); ?></td>
        <td><a class="btn btn-light" href="./sales_detail.php?sales_id=<?php echo rawurlencode($datum['sales_id']); ?>">詳細</a></td>
        <td><a class="btn btn-light" href="./sales_update.php?sales_id=<?php echo rawurlencode($datum['sales_id']); ?>">修正</a></td>
        <td><form action="./sales_delete.php" method="post">
                <input type="hidden" name="sales_id" value="<?php echo h($datum['sales_id']); ?>">
                <button class="btn btn-danger" onClick="return confirm('本当に削除しますか？');">削除</button>
            </form></td>
    </tr>
    <?php endforeach; ?>
    </table>

      <nav class="my-5">
        <ul class="pagination justify-content-center">

        <?php if (1 !== $page_num): ?>
        <li><a class="btn btn-outline-primary" href="./sales_list.php?search_like_order_id=<?php if(isset($search['search_like_order_id'])) echo $search['search_like_order_id'];?>&search_like_user_name=<?php if(isset($search['search_like_user_name'])) echo $search['search_like_user_name'];?>&search_sales_date_from=<?php if(isset($search['search_sales_date_from'])) echo $search['search_sales_date_from'];?>&search_sales_date_to=<?php if(isset($search['search_sales_date_to'])) echo $search['search_sales_date_to'];?>$<?php echo get_url_params($page_num - 1); ?>">&laquo;</a></li>
        <?php endif; ?>

        <?php for($i = 1; $i <= $max_page_num; ++$i): ?>
            <?php if($i === $page_num): ?>
                <li><a class="btn btn-outline-primary active" href="#" role="button"><?php echo $i; ?></a></li>
            <?php else: ?>
            <li><a class="btn btn-outline-primary" href="./sales_list.php?search_like_order_id=<?php if(isset($search['search_like_order_id'])) echo $search['search_like_order_id'];?>&search_like_user_name=<?php if(isset($search['search_like_user_name'])) echo $search['search_like_user_name'];?>&search_sales_date_from=<?php if(isset($search['search_sales_date_from'])) echo $search['search_sales_date_from'];?>&search_sales_date_to=<?php if(isset($search['search_sales_date_to'])) echo $search['search_sales_date_to'];?>&<?php echo get_url_params($i); ?>"><?php echo $i; ?></a></li>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($max_page_num > $page_num): ?>
        <li><a class="btn btn-outline-primary" href="./sales_list.php?search_like_order_id=<?php if(isset($search['search_like_order_id'])) echo $search['search_like_order_id'];?>&search_like_user_name=<?php if(isset($search['search_like_user_name'])) echo $search['search_like_user_name'];?>&search_sales_date_from=<?php if(isset($search['search_sales_date_from'])) echo $search['search_sales_date_from'];?>&search_sales_date_to=<?php if(isset($search['search_sales_date_to'])) echo $search['search_sales_date_to'];?>&<?php echo get_url_params($page_num + 1); ?>">&raquo;</a></li>
        <?php endif; ?>

        </ul>
      </nav>  


    <script src="https://code.jquery.com/jquery-3.0.0.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</div>
</div>
</body>
</html>