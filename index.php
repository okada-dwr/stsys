<?php


try {
   

    // DB接続
    $pdo = new PDO(
        // ホスト名、データベース名
        'mysql:host=us-cdbr-east-05.cleardb.net;dbname=heroku_5e78f26ff50403d;',
        // ユーザー名
        'b2c2e6853ab5ee',
        // パスワード
        '2f35b6a9',

        // // ホスト名、データベース名
        // 'mysql:host=localhost;dbname=stsys;',
        // // ユーザー名
        // 'root',
        // // パスワード
        // 'shinei4005',
        // レコード列名をキーとして取得させる
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
  

    // 管理者の予約情報
    $sql_course = $pdo->prepare('SELECT * FROM st_course_item_mst WHERE del_flg=0  ORDER BY course_code');
    $sql_course->execute();

    //会場名
    $s = 0; //カウントに使う
    foreach ($sql_place as $row_place) { //予約日登録{couse_code=>day})
        echo [$row_place['place_code'], $row_place['place_name']];
    }

    
