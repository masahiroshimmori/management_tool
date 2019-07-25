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
    'search_like_user_code',
    'search_like_user_name',
    'search_like_user_post',
    'search_like_user_address',
    'search_like_user_tel',
    'search_like_user_email'
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
$sql = 'FROM user';

//条件がある場合の検索条件の付与
$bind_array = array();
if(false === empty($search)){
    
    $where_list = array();
    
    //値を把握する 
    //like句(顧客コード)
    if(true === isset($search['search_like_user_code'])){
        //where句に入れる文言を設定
        $where_list[] = 'user_code LIKE :like_user_code';
        //bindする値を設定する
        $bind_array[':like_user_code'] = '%'. like_escape($search['search_like_user_code']).'%';
    }

    //like句(顧客名)
    if(true === isset($search['search_like_user_name'])){
        //where句に入れる文言を設定
        $where_list[] = 'user_name LIKE :like_user_name';
        //bindする値を設定する
        $bind_array[':like_user_name'] = '%'. like_escape($search['search_like_user_name']).'%';
    }

    //like句(郵便番号)
    if(true === isset($search['search_like_user_post'])){
        //where句に入れる文言を設定
        $where_list[] = 'user_post LIKE :like_user_post';
        //bindする値を設定する
        $bind_array[':like_user_post'] = '%'. like_escape($search['search_like_user_post']).'%';
    }
    
    //like句(住所)    
    if(true === isset($search['search_like_user_address'])){
        //where句に入れる文言を設定
        $where_list[] = 'user_address LIKE :like_user_address';
        //bindする値を設定する
        $bind_array[':like_user_address'] = '%'. like_escape($search['search_like_user_address']).'%';
    }

    //like句(電話番号)    
    if(true === isset($search['search_like_user_tel'])){
        //where句に入れる文言を設定
        $where_list[] = 'user_tel LIKE :like_user_tel';
        //bindする値を設定する
        $bind_array[':like_user_tel'] = '%'. like_escape($search['search_like_user_tel']).'%';
    }    

    //like句(email)    
    if(true === isset($search['search_like_user_email'])){
        //where句に入れる文言を設定
        $where_list[] = 'user_email LIKE :like_user_email';
        //bindする値を設定する
        $bind_array[':like_user_email'] = '%'. like_escape($search['search_like_user_email']).'%';
    }

    //where句を合成してsqlへつなげる
    $sql = $sql.' WHERE '. implode(' AND ', $where_list);
    
}

//ページング処理
    $sql_limit_string = ' LIMIT :start_page, :contents_per_page';
    $bind_array[':start_page'] = ($page_num -1) * $contents_per_page;//[ページ数 - 1] * 1pageあたりの出力数
    $bind_array[':contents_per_page'] = $contents_per_page;

//count用と通常用の２つのsqlを作成し、sqlを閉じる
$sql_count = 'SELECT count(user_code) ' . $sql . ';';
//SELECT count(user_code) FROM user WHERE $where_list;//例えばuser_email LIKE :like_user_email
$sql_main = 'SELECT * ' . $sql . $sql_limit_string . ';';
//確認
//var_dump($sql_count);
//var_dump($sql_main);

//プリペアドステートメントを作成する
$pre_count = $dbh->prepare($sql_count);
$pre_main = $dbh->prepare($sql_main);

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
    <title>顧客登録一覧</title>
</head>
<body>
<div class="container">
  <h1>顧客一覧</h1>

    <?php if ( (isset($output_buffer['error_csrf']))&&(true === $output_buffer['error_csrf']) ) : ?>
    <span class="text-danger">CSRFトークンでエラーが起きました。正しい遷移を、30分以内に操作してください。<br></span>
    <?php endif; ?>

    <?php if ( (isset($output_buffer['user_register_success']))&&(true === $output_buffer['user_register_success']) ) : ?>
    <span class="text-danger">顧客を登録しました。<br></span>
    <?php endif; ?>

    <?php if ( (isset($output_buffer['user_update_success']))&&(true === $output_buffer['user_update_success']) ) : ?>
    <span class="text-danger">顧客情報を更新しました。<br></span>
    <?php endif; ?>

    <?php if ( (isset($output_buffer['user_delete_success']))&&(true === $output_buffer['user_delete_success']) ) : ?>
    <span class="text-danger">顧客情報を削除しました。<br></span>
    <?php endif; ?>

  <div class="row">
    <form action="./user_list.php" method="get">
    <div>
        <span class="col-md-6">顧客コード（部分一致検索）<input type="text" name="search_like_user_code" value="<?php echo h(@$search['search_like_user_code']); ?>"></span>
        <span class="col-md-6">顧客名（部分一致検索）<input type="text" name="search_like_user_name" value="<?php echo h(@$search['search_like_user_name']); ?>"></span>
    </div>
    <div>
        <span class="col-md-6">郵便番号（部分一致検索）<input type="text" name="search_like_user_post" value="<?php echo h(@$search['search_like_user_post']); ?>"></span>
        <span class="col-md-6">住所（部分一致検索）<input type="text" name="search_like_user_address" value="<?php echo h(@$search['search_like_user_address']); ?>"></span>
    </div>
    <div>
        <span class="col-md-6">電話番号（部分一致検索）<input type="text" name="search_like_user_tel" value="<?php echo h(@$search['search_like_user_tel']); ?>"></span>
        <span class="col-md-6">メールアドレス（部分一致検索）<input type="text" name="search_like_user_email" value="<?php echo h(@$search['search_like_user_email']); ?>"></span>
    </div>
    <div>
        <span class="col-md-6"><button class="btn btn-primary">検索する</button></span>
        <span class="col-md-6"><a class="btn btn-primary" href="./add_user.php">顧客の新規登録</a></span>
    </div>
    </form>
  </div>
    <?php if (false === empty($search)) : ?>
        現在、以下の項目で検索をかけています。<br>
        <?php
            foreach($search as $k => $v) {
                if($k === 'search_like_user_code'){$k = '顧客コード';}
                if($k === 'search_like_user_name'){$k = '顧客名';}
                if($k === 'search_like_user_post'){$k = '郵便番号';}
                if($k === 'search_like_user_address'){$k = '住所';}
                if($k === 'search_like_user_tel'){$k = '電話番号';}
                if($k === 'search_like_user_email'){$k = 'メールアドレス';}
                echo h($k), ' : ', h($v), "<br>\n";
            }
        ?>
        <br>
        <a class="btn btn-light" href="./user_list.php">検索項目をクリアする</a>
    <?php endif;?>
        
  <h2>一覧</h2>
  <div class="row">
  <div class="col-6 text-left">全<?php echo $total_contents_num; ?>件</div>
  <div class="col-6 text-right"><?php echo $page_num . '/' . $max_page_num;?>ページを表示中</div>
  </div>
  <table class="table table-hover">
  <tr>
    <th>顧客コード</th>
    <th>顧客名</th>
    <th>郵便番号</th>
    <th>住所</th>
    <th>電話番号</th>
    <th>メールアドレス</th>
    <th></th>
    <th></th>
    <th></th>
  </tr>
  <?php foreach($data as $datum): ?>
  <tr>
    <td><?php echo h($datum['user_code']); ?></td>
    <td><?php echo h($datum['user_name']); ?></td>
    <td><?php echo h($datum['user_post']); ?></td>
    <td><?php echo h($datum['user_address']); ?></td>
    <td><?php echo h($datum['user_tel']); ?></td>    
    <td><?php echo h($datum['user_email']); ?></td>
    <td><a class="btn btn-light" href="./user_detail.php?user_code=<?php echo rawurlencode($datum['user_code']); ?>">詳細</a></td>
    <td><a class="btn btn-light" href="./user_update.php?user_code=<?php echo rawurlencode($datum['user_code']); ?>">修正</a></td>
    <td><form action="./user_delete.php" method="post">
            <input type="hidden" name="user_code" value="<?php echo h($datum['user_code']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo h($csrf_token); ?>">
            <button class="btn btn-danger" onClick="return confirm('本当に削除しますか？');">削除</button>
        </form></td>
  </tr>
  <?php endforeach; ?>
  </table>

      <nav class="my-5">
        <ul class="pagination justify-content-center">

        <?php if (1 !== $page_num): ?>
        <li><a class="btn btn-outline-primary" href="./user_list.php?search_like_user_code=<?php if(isset($search['search_like_user_code'])) echo $search['search_like_user_code'];?>&search_like_user_name=<?php if(isset($search['search_like_user_name'])) echo $search['search_like_user_name'];?>&search_like_user_post=<?php if(isset($search['search_like_user_post'])) echo $search['search_like_user_post'];?>&search_like_user_address=<?php if(isset($search['search_like_user_address'])) echo $search['search_like_user_address'];?>&search_like_user_tel=<?php if(isset($search['search_like_user_tel'])) echo $search['search_like_user_tel'];?>&search_like_user_email=<?php if(isset($search['search_like_user_email'])) echo $search['search_like_user_email'];?>&<?php echo get_url_params($page_num - 1); ?>">&laquo;</a></li>
        <?php endif; ?>

        <?php for($i = 1; $i <= $max_page_num; ++$i): ?>
            <?php if($i === $page_num): ?>
                <li><a class="btn btn-outline-primary active" href="#" role="button"><?php echo $i; ?></a></li>
            <?php else: ?>
            <li><a class="btn btn-outline-primary" href="./user_list.php?search_like_user_code=<?php if(isset($search['search_like_user_code'])) echo $search['search_like_user_code'];?>&search_like_user_name=<?php if(isset($search['search_like_user_name'])) echo $search['search_like_user_name'];?>&search_like_user_post=<?php if(isset($search['search_like_user_post'])) echo $search['search_like_user_post'];?>&search_like_user_address=<?php if(isset($search['search_like_user_address'])) echo $search['search_like_user_address'];?>&search_like_user_tel=<?php if(isset($search['search_like_user_tel'])) echo $search['search_like_user_tel'];?>&search_like_user_email=<?php if(isset($search['search_like_user_email'])) echo $search['search_like_user_email'];?>&<?php echo get_url_params($i); ?>"><?php echo $i; ?></a></li>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($max_page_num > $page_num): ?>
        <li><a class="btn btn-outline-primary" href="./user_list.php?search_like_user_code=<?php if(isset($search['search_like_user_code'])) echo $search['search_like_user_code'];?>&search_like_user_name=<?php if(isset($search['search_like_user_name'])) echo $search['search_like_user_name'];?>&search_like_user_post=<?php if(isset($search['search_like_user_post'])) echo $search['search_like_user_post'];?>&search_like_user_address=<?php if(isset($search['search_like_user_address'])) echo $search['search_like_user_address'];?>&search_like_user_tel=<?php if(isset($search['search_like_user_tel'])) echo $search['search_like_user_tel'];?>&search_like_user_email=<?php if(isset($search['search_like_user_email'])) echo $search['search_like_user_email'];?>&<?php echo get_url_params($page_num + 1); ?>">&raquo;</a></li>
        <?php endif; ?>

        </ul>
      </nav>  
</div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>