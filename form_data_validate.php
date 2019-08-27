<?php

 require_once ('common_function.php');
 
 function form_check($item_code){
     //存在しない場合は空配列を返す
     if('' === $item_code){
         return array();
     }
     
     //else
     $dbh = get_dbh();
     
     $sql = 'select * from item where item_code = :item_code;';
     $pre= $dbh->prepare($sql);
     
     $pre->bindValue(':item_code', $item_code, PDO::PARAM_STR);
     $r = $pre->execute();
     
     if(false === $r){
         echo "システムエラーがおきました。";
         exit();
     }
     //データの取得
     $data = $pre->fetchAll(PDO::FETCH_ASSOC);
     //var_dump($data);
     
     if(true === empty($data)){
         return array();
     }
     //else
     $datum = $data[0];
     //var_dump($datum);
     
     return $datum;
     
 }
 
 function form_check_sales($sales_id){
     //存在しない場合は空配列を返す
     if('' === $sales_id){
         return array();
     }
     
     //else
     $dbh = get_dbh();
     
     $sql_dat_sales = 'select * from dat_sales where sales_id = :sales_id;';
     $pre= $dbh->prepare($sql_dat_sales);
     
     $pre->bindValue(':sales_id', $sales_id, PDO::PARAM_INT);
     $r = $pre->execute();
     
     if(false === $r){
         echo "システムエラーがおきました。";
         exit();
     }
     //データの取得
     $data_dat_sales = $pre->fetchAll(PDO::FETCH_ASSOC);
     //var_dump($data);
     
     if(true === empty($data_dat_sales)){
         return array();
     }
     //else
     $datum1 = $data_dat_sales[0];
     //var_dump($datum1);
     
     $sales_id = $datum1['sales_id'];
     $sql_dat_user_item = 'SELECT user_sales_id, item_code, item_name, item_price, item_mount, item_tax from dat_user_item where user_item_dat = :user_item_dat;';
     $pre= $dbh->prepare($sql_dat_user_item);
     
     $pre->bindValue(':user_item_dat', $sales_id, PDO::PARAM_INT);
     $r = $pre->execute();

     if(false === $r){
        echo "システムエラーがおきました。";
        exit();
    }
    //データの取得
    $data_dat_user_item = $pre->fetchAll(PDO::FETCH_ASSOC);
    //var_dump($data_dat_user_item);
    
    if(true === empty($data_dat_user_item)){
        return array();
    }
    //else
        $datum2 = $data_dat_user_item;

    $datum = array($datum1, $datum2);
    // var_dump($datum);
    return $datum;
     
 }

 function user_form_check($user_code){
    //存在しない場合は空配列を返す
    if('' === $user_code){
        return array();
    }
    
    //else
    $dbh = get_dbh();
    
    $sql = 'select * from user where user_code = :user_code;';
    $pre= $dbh->prepare($sql);
    
    $pre->bindValue(':user_code', $user_code, PDO::PARAM_STR);
    $r = $pre->execute();
    
    if(false === $r){
        echo "システムエラーがおきました。";
        exit();
    }
    //データの取得
    $data = $pre->fetchAll(PDO::FETCH_ASSOC);
    //var_dump($data);
    
    if(true === empty($data)){
        return array();
    }
    //else
    $datum = $data[0];
    //var_dump($datum);
    
    return $datum;
    
}

 //validate
 //validateが全てokなら空配列、NG項目がある場合はerror_detailに値が入った配列を返す
 
 function validate_item_form($datum){
     $error_detail = array(); 

        // 型チェックを実装
        if (1 !== preg_match('/^[0-9]+$/', $datum['item_price'])) {
            $error_detail["error_invalid_item_price"] = true;
        }

        if (1 !== preg_match('/^[0-9]+$/', $datum['item_cost'])) {
            $error_detail["error_invalid_item_cost"] = true;
        }

        if (1 !== preg_match('/^[a-zA-Z0-9]+$/', $datum['item_code'])) {
            $error_detail["error_invalid_item_code"] = true;
        }        
        
        return $error_detail;
}

function validate_item_form_update($datum){
    $error_detail = array(); 

       // 型チェックを実装
       if (1 !== preg_match('/^[0-9]+$/', $datum['item_price'])) {
           $error_detail["error_invalid_item_price"] = true;
       }

       if (1 !== preg_match('/^[0-9]+$/', $datum['item_cost'])) {
           $error_detail["error_invalid_item_cost"] = true;
       }     
       
       if (1 !== preg_match('/^[0-9]+$/', $datum['item_stock'])) {
           $error_detail["error_invalid_item_stock"] = true;
       }
       
       return $error_detail;
}

function validate_user_form($datum){
    $error_detail = array(); 

       // 型チェックを実装
       if (1 !== preg_match('/^[0-9]+$/', $datum['user_post'])) {
           $error_detail["error_invalid_user_post"] = true;
       }

       if (1 !== preg_match('/^[0-9]+$/', $datum['user_tel'])) {
        $error_detail["error_invalid_user_tel"] = true;
        }

       if (1 !== preg_match('/^[a-zA-Z0-9]+$/', $datum['user_code'])) {
           $error_detail["error_invalid_user_post"] = true;
       }        
       
       return $error_detail;
}

function validate_user_form_update($datum){
    $error_detail = array(); 

       // 型チェックを実装
       if (1 !== preg_match('/^[0-9]+$/', $datum['user_post'])) {
           $error_detail["error_invalid_user_post"] = true;
       }

       if (1 !== preg_match('/^[0-9]+$/', $datum['user_tel'])) {
           $error_detail["error_invalid_user_tel"] = true;
       }     
       
       return $error_detail;
}