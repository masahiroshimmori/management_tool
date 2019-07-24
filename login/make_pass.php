<?php

//管理者の初期登録時のSQL作成用のミニマムコード
$pass = 'pass';
echo password_hash($pass, PASSWORD_DEFAULT);