<?php
session_start();
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

        // レコード列名をキーとして取得させる
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    // ***************SQL文をセット（講習名選択）*******************************************
    $sql = ('SELECT * FROM st_course_item_mst WHERE del_flg=0');

    // SQL実行
    $stmt = $pdo->query($sql);

    // foreach文で配列の中身を一行ずつ出力
    $course_code_name = [];
    $i = 0;

    foreach ($stmt as $row) {
        // データベースのフィールド名で出力
        $course_code_name[$i] = [$row['course_code'] => $row['course_name']];
        $i += 1;
    }

    // ***************SQL文をセット（会場選択）*******************************************
    $sql = ('SELECT * FROM st_course_item_mst WHERE del_flg=0');

    // SQL実行
    $stmt = $pdo->query($sql);

    // foreach文で配列の中身を一行ずつ出力
    $course_code_name = [];
    $i = 0;

    foreach ($stmt as $row) {
        // データベースのフィールド名で出力
        $course_code_name[$i] = [$row['course_code'] => $row['course_name']];
        $i += 1;
    }
    // ********************************************************************************
} catch (PDOException $e) {
    // エラー発生
    echo $e->getMessage();
} finally {
    // DB接続を閉じる
    $pdo = null;
}

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <a href="index.php">stsys01</a>
    <a href="stsys02.php">stsys02</a>
    <a href="stsys03.php">stsys03</a>
    <a href="stsys04.php">stsys04</a>

    <!--講習名選択-->
    <table border="1" style="border-collapse:collapse" ;>
        <tr>
            <td>講習名</td>
            <td><select name="" id="">
                    <?php
                    foreach ($course_code_name as $key => $course_name) {
                        foreach ($course_name as $key => $course_name1) {
                            echo '<option value="' . $key . '">' . $course_name1 . '</option>';
                        }
                    }
                    ?>
                </select></td>
        </tr>
    </table>

    <!--会場選択-->
    <table border="1" style="border-collapse:collapse" ;>
        <tr>
            <td>会場</td>
            <td><select name="" id="">
                    <?php
                    foreach ($course_code_name as $key => $course_name) {
                        foreach ($course_name as $key => $course_name1) {
                            echo '<option value="' . $key . '">' . $course_name1 . '</option>';
                        }
                    }
                    ?>
                </select></td>
        </tr>
    </table>

</body>

</html>
