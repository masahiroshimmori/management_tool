$(function(){
    // #ajax_userがクリックされた時の処理
    // 指定したidのレコードを取得する
    $('#ajax_user').on('click',function(){
        $.ajax({
            // リクエスト方法
            type: "GET",
            // 送信先ファイル名
            url: "ajax_sales_show.php",
            // 受け取りデータの種類
            datatype: "json",
            // 送信データ
            data:{
                // #user_codeのvalueをセット
                "user_code" : $('#user_code').val()
            },
            // 通信が成功した時
            success: function(data) {
                // 取得件数が1件のため、取得した情報を#result_user_name内にそのまま追加する
                $('#result_user_name').val(data[0].user_name);
    
                console.log("通信成功");
                console.log(data);
            },
    
            // 通信が失敗した時
            error: function(data) {

                console.log("通信失敗");
                console.log(data);
            }
        });
    
        return false;
    });
    
    // #ajax_itemがクリックされた時の処理
    // 指定したidのレコードを取得する
    $('#ajax_item1').on('click',function(){
        $.ajax({
            // リクエスト方法
            type: "GET",
            // 送信先ファイル名
            url: "ajax_sales_item_show.php",
            // 受け取りデータの種類
            datatype: "json",
            // 送信データ
            data:{
                // #item_codeのvalueをセット
                "item_code" : $('#item_code1').val()
            },
            // 通信が成功した時
            success: function(data) {
                // 取得件数が1件のため、取得した情報を#result_item_name#result_item_price内にそのまま追加する
                $('#result_item_name1').val(data[0].item_name);
                $('#result_item_price1').val(data[0].item_price);
                $('#result_item_tax1').val(data[0].item_tax);
    
                console.log("通信成功");
                console.log(data);
            },
    
            // 通信が失敗した時
            error: function(data) {

                console.log("通信失敗");
                console.log(data);
            }
        });
    
        return false;
    });
    
    // #ajax_itemがクリックされた時の処理
    // 指定したidのレコードを取得する
    $('#ajax_item2').on('click',function(){
        $.ajax({
            // リクエスト方法
            type: "GET",
            // 送信先ファイル名
            url: "ajax_sales_item_show.php",
            // 受け取りデータの種類
            datatype: "json",
            // 送信データ
            data:{
                // #item_codeのvalueをセット
                "item_code" : $('#item_code2').val()
            },
            // 通信が成功した時
            success: function(data) {
                // 取得件数が1件のため、取得した情報を#result_item_name#result_item_price内にそのまま追加する
                $('#result_item_name2').val(data[0].item_name);
                $('#result_item_price2').val(data[0].item_price);
                $('#result_item_tax2').val(data[0].item_tax);
    
                console.log("通信成功");
                console.log(data);
            },
    
            // 通信が失敗した時
            error: function(data) {

                console.log("通信失敗");
                console.log(data);
            }
        });
    
        return false;
    });
    
    // #ajax_itemがクリックされた時の処理
    // 指定したidのレコードを取得する
    $('#ajax_item3').on('click',function(){
        $.ajax({
            // リクエスト方法
            type: "GET",
            // 送信先ファイル名
            url: "ajax_sales_item_show.php",
            // 受け取りデータの種類
            datatype: "json",
            // 送信データ
            data:{
                // #item_codeのvalueをセット
                "item_code" : $('#item_code3').val()
            },
            // 通信が成功した時
            success: function(data) {
                // 取得件数が1件のため、取得した情報を#result_item_name#result_item_price内にそのまま追加する
                $('#result_item_name3').val(data[0].item_name);
                $('#result_item_price3').val(data[0].item_price);
                $('#result_item_tax3').val(data[0].item_tax);
    
                console.log("通信成功");
                console.log(data);
            },
    
            // 通信が失敗した時
            error: function(data) {

                console.log("通信失敗");
                console.log(data);
            }
        });
    
        return false;
    });
    
    // #ajax_itemがクリックされた時の処理
    // 指定したidのレコードを取得する
    $('#ajax_item4').on('click',function(){
        $.ajax({
            // リクエスト方法
            type: "GET",
            // 送信先ファイル名
            url: "ajax_sales_item_show.php",
            // 受け取りデータの種類
            datatype: "json",
            // 送信データ
            data:{
                // #item_codeのvalueをセット
                "item_code" : $('#item_code4').val()
            },
            // 通信が成功した時
            success: function(data) {
                // 取得件数が1件のため、取得した情報を#result_item_name#result_item_price内にそのまま追加する
                $('#result_item_name4').val(data[0].item_name);
                $('#result_item_price4').val(data[0].item_price);
                $('#result_item_tax4').val(data[0].item_tax);
    
                console.log("通信成功");
                console.log(data);
            },
    
            // 通信が失敗した時
            error: function(data) {

                console.log("通信失敗");
                console.log(data);
            }
        });
    
        return false;
    });
    
    // #ajax_itemがクリックされた時の処理
    // 指定したidのレコードを取得する
    $('#ajax_item5').on('click',function(){
        $.ajax({
            // リクエスト方法
            type: "GET",
            // 送信先ファイル名
            url: "ajax_sales_item_show.php",
            // 受け取りデータの種類
            datatype: "json",
            // 送信データ
            data:{
                // #item_codeのvalueをセット
                "item_code" : $('#item_code5').val()
            },
            // 通信が成功した時
            success: function(data) {
                // 取得件数が1件のため、取得した情報を#result_item_name#result_item_price内にそのまま追加する
                $('#result_item_name5').val(data[0].item_name);
                $('#result_item_price5').val(data[0].item_price);
                $('#result_item_tax5').val(data[0].item_tax);
    
                console.log("通信成功");
                console.log(data);
            },
    
            // 通信が失敗した時
            error: function(data) {

                console.log("通信失敗");
                console.log(data);
            }
        });
    
        return false;
    });


});
