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
       
       return $error_detail;
}