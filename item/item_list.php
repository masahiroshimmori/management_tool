<?php

error_reporting(1);

// セッションの開始
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
    $_SESSION['login_alert'] = $login_alert;
    header('Location: ../login/login.php');
    exit();
}

//var_dump($_SESSION);

//設定値の設定：configなどに吐き出した方がよりよい場合が多い
$contents_per_page = 3; //1ページあたりの出力数

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
    'search_item_tax',
    'search_item_price_from',
    'search_item_price_to',
    'search_item_cost_from',
    'search_item_cost_to',    
    'search_like_item_code',
    'search_like_item_name',
    'search_created_from',
    'search_created_to'
);
//データの取得
$search = array();
foreach($search_list as $p){
    if((true === isset($_GET[$p])) && ('' !== $_GET[$p])){
        $search[$p] = $_GET[$p];
    }
}

//確認
//var_dump($search);


// DBハンドルの取得
$dbh = get_dbh();

// SELECT文の作成と発行
// ------------------------------
// 準備された文(プリペアドステートメント)の用意
//countと通常用の２種類のsqlを発行する必要があるのでselectを一旦切り取る
$sql = 'FROM item';

//条件がある場合の検索条件の付与
$bind_array = array();
if(false === empty($search)){
    
    $where_list = array();
    
    //値を把握する
    //完全一致tax
    if(true === isset($search['search_item_tax'])){
        //where句に入れる文言を設定
        $where_list[] = 'item_tax = :item_tax';
        //bindする値を設定
        $bind_array[':item_tax'] = $search['search_item_tax'];
    }

    //範囲指定(から)原価
    if(true === isset($search['search_item_cost_from'])){
        //where句に入れる文言を設定
        $where_list[] = 'item_cost >= :item_cost_from';
        //bindする値を設定
        $bind_array[':item_cost_from'] = $search['search_item_cost_from'];
    }

    //範囲指定(まで)原価
    if(true === isset($search['search_item_cost_to'])){
        //where句に入れる文言を設定
        $where_list[] = 'item_cost <= :item_cost_to';
        //bindoする値を設定
        $bind_array[':item_cost_to'] = $search['search_item_cost_to'];
    }

    //範囲指定(から)売価
    if(true === isset($search['search_item_price_from'])){
        //where句に入れる文言を設定
        $where_list[] = 'item_price >= :item_price_from';
        //bindする値を設定
        $bind_array[':item_price_from'] = $search['search_item_price_from'];
    }

    //範囲指定(まで)売価
    if(true === isset($search['search_item_price_to'])){
        //where句に入れる文言を設定
        $where_list[] = 'item_price <= :item_price_to';
        //bindoする値を設定
        $bind_array[':item_price_to'] = $search['search_item_price_to'];
    }

    //範囲指定(から〜まで)登録日時検索
    if(true === isset($search['search_created_to']) && true === isset($search['search_created_from'])){
        //where句に入れる文言を設定
        $where_list[] = 'created BETWEEN :created_from AND :created_to';
        //日付を整える
        $search['search_created_from'] = date('Y-m-d',strtotime($search['search_created_from']));
        $search['search_created_to'] = date('Y-m-d',strtotime($search['search_created_to']));        
        //bindする値を設定
        $bind_array[':created_from'] = $search['search_created_from'].' 00:00:00';
        $bind_array[':created_to'] = $search['search_created_to'].' 23:59:59';
    }elseif
    //範囲指定(から)登録日時検索
    (true === isset($search['search_created_from'])){
        //where句に入れる文言を設定
        $where_list[] = 'created BETWEEN :created_from AND :created_to';
        //日付を整える
        $search['search_created_from'] = date('Y-m-d',strtotime($search['search_created_from']));
        //bindする値を設定
        $bind_array[':created_from'] = $search['search_created_from'].' 00:00:00';
        $bind_array[':created_to'] = date(DATE_ATOM);
        
    }elseif
    //範囲指定(まで)登録日時検索
    (true === isset($search['search_created_to'])){
        //where句に入れる文言を設定
        $where_list[] = 'created BETWEEN :created_from AND :created_to';
        //日付を整える
        $search['search_created_to'] = date('Y-m-d',strtotime($search['search_created_to']));
        //bindする値を設定
        $bind_array[':created_from'] = '2019-07-01 00:00:00';        
        $bind_array[':created_to'] = $search['search_created_to'].' 23:59:59';
    }   
    
    //like句(商品名)
    if(true === isset($search['search_like_item_name'])){
        //where句に入れる文言を設定
        $where_list[] = 'item_name LIKE :like_item_name';
        //bindする値を設定する
        //$bind_array['like_item_name'] = $search['search_like_item_name'].'%';//前方一致の場合
        //$bind_array['like_item_name'] = '%'. $search['search_like_item_name'].'%';//部分一致の場合
        $bind_array[':like_item_name'] = '%'. like_escape($search['search_like_item_name']).'%';//部分一致の場合、%や_はエスケープ
    }

        //like句(商品コード)
        if(true === isset($search['search_like_item_code'])){
            //where句に入れる文言を設定
            $where_list[] = 'item_code LIKE :like_item_code';
            //bindする値を設定する
            //$bind_array['like_item_code'] = $search['search_like_item_code'].'%';//前方一致の場合
            //$bind_array['like_item_code'] = '%'. $search['search_like_item_code'].'%';//部分一致の場合
            $bind_array[':like_item_code'] = '%'. like_escape($search['search_like_item_code']).'%';//部分一致の場合、%や_はエスケープ
        }
    
    //where句を合成してsqlへつなげる
    $sql = $sql.' WHERE '. implode(' AND ', $where_list);
    
}

//ページング処理
    $sql_limit_string = ' LIMIT :start_page, :contents_per_page';
    $bind_array[':start_page'] = ($page_num -1) * $contents_per_page;//[ページ数 - 1] * 1pageあたりの出力数
    $bind_array[':contents_per_page'] = $contents_per_page;

//count用と通常用の２つのsqlを作成し、sqlを閉じる
$sql_count = 'SELECT count(item_code) ' . $sql . ';';
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
        $pre_main->bindValue($k, $v);//デフォルトのstrとしておく：数値が入る可能性が出てきたらis_int関数を実装
        $pre_count->bindValue($k, $v);//デフォルトのstrとしておく：数値が入る可能性が出てきたらis_int関数を実装
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

// CSRFトークンの取得
$csrf_token = create_csrf_token_admin();

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
    <title>商品登録一覧</title>
</head>
<body>
<div class="container">
  <h1>商品一覧</h1>

    <?php if ( (isset($output_buffer['error_csrf']))&&(true === $output_buffer['error_csrf']) ) : ?>
    <span class="text-danger">CSRFトークンでエラーが起きました。正しい遷移を、30分以内に操作してください。<br></span>
    <?php endif; ?>

    <?php if ( (isset($output_buffer['item_register_success']))&&(true === $output_buffer['item_register_success']) ) : ?>
    <span class="text-danger">商品を登録しました。<br></span>
    <?php endif; ?>

    <?php if ( (isset($output_buffer['item_update_success']))&&(true === $output_buffer['item_update_success']) ) : ?>
    <span class="text-danger">商品情報を更新しました。<br></span>
    <?php endif; ?>

    <?php if ( (isset($output_buffer['item_delete_success']))&&(true === $output_buffer['item_delete_success']) ) : ?>
    <span class="text-danger">商品情報を削除しました。<br></span>
    <?php endif; ?>

  <div class="row">
      <form action="./item_list.php" method="get">
    <div>
        <span class="col-md-6">商品コード（部分一致検索）<input type="text" name="search_like_item_code" value="<?php echo h(@$search['search_like_item_code']); ?>"></span>
        <span class="col-md-6">商品売価<input type="text" name="search_item_price_from" value="<?php echo h(@$search['search_item_price_from']); ?>">～<input type="text" name="search_item_price_to" value="<?php echo h(@$search['search_item_price_to']); ?>"></span>
    </div>
    <div>
        <span class="col-md-6">商品名（部分一致検索）<input type="text" name="search_like_item_name" value="<?php echo h(@$search['search_like_item_name']); ?>"></span>
        <span class="col-md-6">商品原価<input type="text" name="search_item_cost_from" value="<?php echo h(@$search['search_item_cost_from']); ?>">～<input type="text" name="search_item_cost_to" value="<?php echo h(@$search['search_item_cost_to']); ?>"></span>
    </div>
    <div>
    <span class="col-md-6">登録日(YYYY-MM-DD)<input type="text" name="search_created_from" value="<?php echo h(@$search['search_created_from']); ?>">～<input type="text" name="search_created_to" value="<?php echo h(@$search['search_created_to']); ?>"></span>
        <span class="col-md-6">消費税率<input type="text" name="search_item_tax" value="<?php echo h(@$search['search_item_tax']); ?>"></span>
    </div>
    <span class="col-md-6"><button class="btn btn-primary">検索する</button></span>
    <span class="col-md-6"><a class="btn btn-primary" href="./add_item.php">商品の新規登録</a></span>
  </form>
  </div>
    <?php if (false === empty($search)) : ?>
        現在、以下の項目で検索をかけています。<br>
        <?php
            foreach($search as $k => $v) {
                if($k === 'search_like_item_code'){$k = '商品コード';}
                if($k === 'search_like_item_name'){$k = '商品名';}
                if($k === 'search_item_price_from'){$k = '商品売価(以上)';}
                if($k === 'search_item_price_to'){$k = '商品売価(以下)';}
                if($k === 'search_item_cost_from'){$k = '商品原価(以上)';}
                if($k === 'search_item_cost_to'){$k = '商品原価(以下)';}
                if($k === 'search_created_from'){$k = '登録日(から)';}
                if($k === 'search_created_to'){$k = '登録日（まで）';}
                if($k === 'search_item_tax'){$k = '消費税';}
                echo h($k), ': ', h($v), "<br>\n";
            }
        ?>
        <br>
        <a class="btn btn-light" href="./item_list.php">検索項目をクリアする</a>
    <?php endif;?>
        
  <h2>一覧</h2>
  <div class="row">
  <div class="col-6 text-left">全<?php echo $total_contents_num; ?>件</div>
  <div class="col-6 text-right"><?php echo $page_num . '/' . $max_page_num;?>ページを表示中</div>
  </div>
  <table class="table table-hover">
  <tr>
    <th>商品コード</th>
    <th>商品名</th>
    <th>売価</th>
    <th>原価</th>
    <th>消費税率</th>
    <th>登録日</th>
    <th>修正日</th>
    <th></th>
    <th></th>
    <th></th>
  </tr>
  <?php foreach($data as $datum): ?>
  <tr>
    <td><?php echo h($datum['item_code']); ?></td>
    <td><?php echo h($datum['item_name']); ?></td>
    <td><?php echo h($datum['item_price']); ?>円</td>
    <td><?php echo h($datum['item_cost']); ?>円</td>
    <td><?php echo h($datum['item_tax']); ?>%</td>    
    <td><?php echo h($datum['created']); ?></td>
    <td><?php echo h($datum['updated']); ?></td>
    <td><a class="btn btn-light" href="./item_detail.php?item_code=<?php echo rawurlencode($datum['item_code']); ?>">詳細</a></td>
    <td><a class="btn btn-light" href="./item_update.php?item_code=<?php echo rawurlencode($datum['item_code']); ?>">修正</a></td>
    <td><form action="./item_delete.php" method="post">
            <input type="hidden" name="item_code" value="<?php echo h($datum['item_code']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
            <button class="btn btn-danger" onClick="return confirm('本当に削除しますか？');">削除</button>
        </form></td>
  </tr>
  <?php endforeach; ?>
  </table>

      <nav class="my-5">
        <ul class="pagination justify-content-center">

        <?php if (1 !== $page_num): ?>
        <li><a class="btn btn-outline-primary" href="./item_list.php?search_like_item_code=<?php if(isset($search['search_like_item_code'])) echo $search['search_like_item_code'];?>&search_item_price_from=<?php if(isset($search['search_item_price_from'])) echo $search['search_item_price_from'];?>&search_item_price_to=<?php if(isset($search['search_item_price_to'])) echo $search['search_item_price_to'];?>&search_like_item_name=<?php if(isset($search['search_like_item_name'])) echo $search['search_like_item_name'];?>&search_item_cost_from=<?php if(isset($search['search_item_cost_from'])) echo $search['search_item_cost_from'];?>&search_item_cost_to=<?php if(isset($search['search_item_cost_to'])) echo $search['search_item_cost_to'];?>&search_created_from=<?php if(isset($search['search_created_from'])) echo $search['search_created_from'];?>&search_created_to=<?php if(isset($search['search_created_to'])) echo $search['search_created_to'];?>&search_item_tax=<?php if(isset($search['search_item_tax'])) echo $search['search_item_tax'];?>&<?php echo get_url_params($page_num - 1); ?>">&laquo;</a></li>
        <?php endif; ?>

        <?php for($i = 1; $i <= $max_page_num; ++$i): ?>
            <?php if($i === $page_num): ?>
                <li><a class="btn btn-outline-primary active" href="#" role="button"><?php echo $i; ?></a></li>
            <?php else: ?>
            <li><a class="btn btn-outline-primary" href="./item_list.php?search_like_item_code=<?php if(isset($search['search_like_item_code'])) echo $search['search_like_item_code'];?>&search_item_price_from=<?php if(isset($search['search_item_price_from'])) echo $search['search_item_price_from'];?>&search_item_price_to=<?php if(isset($search['search_item_price_to'])) echo $search['search_item_price_to'];?>&search_like_item_name=<?php if(isset($search['search_like_item_name'])) echo $search['search_like_item_name'];?>&search_item_cost_from=<?php if(isset($search['search_item_cost_from'])) echo $search['search_item_cost_from'];?>&search_item_cost_to=<?php if(isset($search['search_item_cost_to'])) echo $search['search_item_cost_to'];?>&search_created_from=<?php if(isset($search['search_created_from'])) echo $search['search_created_from'];?>&search_created_to=<?php if(isset($search['search_created_to'])) echo $search['search_created_to'];?>&search_item_tax=<?php if(isset($search['search_item_tax'])) echo $search['search_item_tax'];?>&<?php echo get_url_params($i); ?>"><?php echo $i; ?></a></li>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($max_page_num > $page_num): ?>
        <li><a class="btn btn-outline-primary" href="./item_list.php?search_like_item_code=<?php if(isset($search['search_like_item_code'])) echo $search['search_like_item_code'];?>&search_item_price_from=<?php if(isset($search['search_item_price_from'])) echo $search['search_item_price_from'];?>&search_item_price_to=<?php if(isset($search['search_item_price_to'])) echo $search['search_item_price_to'];?>&search_like_item_name=<?php if(isset($search['search_like_item_name'])) echo $search['search_like_item_name'];?>&search_item_cost_from=<?php if(isset($search['search_item_cost_from'])) echo $search['search_item_cost_from'];?>&search_item_cost_to=<?php if(isset($search['search_item_cost_to'])) echo $search['search_item_cost_to'];?>&search_created_from=<?php if(isset($search['search_created_from'])) echo $search['search_created_from'];?>&search_created_to=<?php if(isset($search['search_created_to'])) echo $search['search_created_to'];?>&search_item_tax=<?php if(isset($search['search_item_tax'])) echo $search['search_item_tax'];?>&<?php echo get_url_params($page_num + 1); ?>">&raquo;</a></li>
        <?php endif; ?>

        </ul>
      </nav>  
</div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>