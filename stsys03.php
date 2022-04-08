<?php
try {
    $course_array = [];

    // DB接続
    $pdo = new PDO(
        'mysql:dbname=heroku_5e78f26ff50403d;host=us-cdbr-east-05.cleardb.net;charset=utf8',
        'b2c2e6853ab5ee',
        '2f35b6a9',

//         'mysql:dbname=stsys;host=localhost;charset=utf8',
//         'root',
//         'shinei4005',

        // レコード列名をキーとして取得させる
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    $sql_course = $pdo->prepare('SELECT * FROM st_course_item_mst WHERE del_flg=0  ORDER BY course_code');
    $sql_course->execute();

    $s = 0; //カウントに使う

    //検定項目登録{couse_code=>day})
    foreach ($sql_course as $sql_course1) {
        $course_array[$s] = [$sql_course1['course_code'], $sql_course1['course_name']];
        $s += 1;
    }

    //検定項目の数だけプルダウン作る
    $course_select = "";
    foreach ($course_array as $course_array1) {
        $course_select  .= '<option value="' . $course_array1[0] . '">' . $course_array1[1] . '</option>';
    }
} catch (PDOException $e) {
    // エラー発生
    echo $e->getMessage();
} finally {
    // DB接続を閉じる
    $pdo = null;
}
?>
<?php
$course_code = "";
$start_date = "";
$end_date = "";

if (isset($_POST['code'])) {
    try {
        // DB接続
        $pdo = new PDO(
            'mysql:dbname=heroku_5e78f26ff50403d;host=us-cdbr-east-05.cleardb.net;charset=utf8',
            'b2c2e6853ab5ee',
            '2f35b6a9',

//             'mysql:dbname=stsys;host=localhost;charset=utf8',
//             'root',
//             'shinei4005',

            // レコード列名をキーとして取得させる
            [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        if ($_POST["code"] === "0") {
            if (isset($_POST['course_code'])) {
                // if (!empty($_POST)) {
                $course_code = $_POST['course_code']; // 送ったデータを受け取る（GETで送った場合は、INPUT_GET）
                // $param = $course_code;
                $start_date = $_POST['start_date'];  // 送ったデータを受け取る（GETで送った場合は、INPUT_GET）
                // $param = $course_code;
                $end_date = $_POST['end_date'];  // 送ったデータを受け取る（GETで送った場合は、INPUT_GET）
                // $param = $course_code;
                // echo json_encode($param); //　echoするとデータを返せる（JSON形式に変換して返す）
                $sql_reserve = $pdo->prepare('SELECT * FROM st_reserve WHERE del_flg=0 AND course_code=:course_code AND start_date BETWEEN :start_date AND :end_date ORDER BY reserve_no');
                $sql_reserve->bindValue(':course_code', $course_code);
                $sql_reserve->bindValue(':start_date', $start_date);
                $sql_reserve->bindValue(':end_date', $end_date);
                $sql_reserve->execute();

                $s = 0; //カウントに使う
                //検定項目登録{couse_code=>day})
                $sql_reserve_array = [];
                foreach ($sql_reserve as $sql_reserve1) {
                    $sql_reserve_array[$s] = [$sql_reserve1['sub_flg_name'], $sql_reserve1['course_sub_name'], $sql_reserve1['mail_flg_name'], $sql_reserve1['students'], $sql_reserve1['kojin_tel'], $sql_reserve1['kojin_address'], $sql_reserve1['start_date'], $sql_reserve1['end_date'], $sql_reserve1['inc_name'], $sql_reserve1['app_name'], $sql_reserve1['address1'] . $sql_reserve1['address2'], $sql_reserve1['tel'], $sql_reserve1['fax'], $sql_reserve1['reserve_no']];
                    $s += 1;
                }
                $reserve_date = "";
                $s = 0;
                foreach ($sql_reserve_array as $sql_reserve_array1) {
                    if ($s === 0) {
                        $reserve_date .=
                            '<tr style="background-color: lightskyblue;">
            <th rowspan="2">
                <input type="checkbox">
            </th>
            <th rowspan="2"> 助成金 </th>
            <th rowspan="2"> コース </th>
            <th rowspan="2"> 郵送希望 </th>
            <th rowspan="2"> 受講者指名 </th>
            <th rowspan="2"> 個人電話番号 </th>
            <th rowspan="2" class="place"> 住所 </th>
            <th rowspan="2"> 講習開始日 </th>
            <th rowspan="2"> 講習終了日 </th>
            <th colspan="6" class="company"> 会社情報 </th>
        </tr>
        <tr>
            <th> 会社名 </th>
            <th> 申請者氏名 </th>
            <th> 住所 </th>
            <th> 電話番号 </th>
            <th> FAX番号 </th>
        </tr>
                <tr></tr>
                <tr><td><input type="checkbox" name="check[]" value=' . $sql_reserve_array1[13] . '></td>' .
                            '<td class="text-center">' . $sql_reserve_array1[0] . '</td>' .
                            '<td class="text-center">' . $sql_reserve_array1[1] . '</td>' .
                            '<td class="text-center">' . $sql_reserve_array1[2] . '</td>' .
                            '<td>' . $sql_reserve_array1[3] . '</td>' .
                            '<td>' . $sql_reserve_array1[4] . '</td>' .
                            '<td>' . $sql_reserve_array1[5] . '</td>' .
                            '<td class="text-center">' . $sql_reserve_array1[6] . '</td>' .
                            '<td class="text-center">' . $sql_reserve_array1[7] . '</td>' .
                            '<td>' . $sql_reserve_array1[8] . '</td>' .
                            '<td>' . $sql_reserve_array1[9] . '</td>' .
                            '<td>' . $sql_reserve_array1[10] . '</td>' .
                            '<td>' . $sql_reserve_array1[11] . '</td>' .
                            '<td>' . $sql_reserve_array1[12] . '</td></tr>';
                        $s += 1;
                    } else {
                        $reserve_date .=
                            '<tr><td><input type="checkbox" name="check[]" value=' . $sql_reserve_array1[13] . '></td>' .
                            '<td class="text-center">' . $sql_reserve_array1[0] . '</td>' .
                            '<td class="text-center">' . $sql_reserve_array1[1] . '</td>' .
                            '<td class="text-center">' . $sql_reserve_array1[2] . '</td>' .
                            '<td>' . $sql_reserve_array1[3] . '</td>' .
                            '<td>' . $sql_reserve_array1[4] . '</td>' .
                            '<td>' . $sql_reserve_array1[5] . '</td>' .
                            '<td class="text-center">' . $sql_reserve_array1[6] . '</td>' .
                            '<td class="text-center">' . $sql_reserve_array1[7] . '</td>' .
                            '<td>' . $sql_reserve_array1[8] . '</td>' .
                            '<td>' . $sql_reserve_array1[9] . '</td>' .
                            '<td>' . $sql_reserve_array1[10] . '</td>' .
                            '<td>' . $sql_reserve_array1[11] . '</td>' .
                            '<td>' . $sql_reserve_array1[12] . '</td></tr>';
                        $s += 1;
                    }
                }
                if ($reserve_date === "") {
                    $reserve_date = "";
                    $reserve_date .=
                        '<tr style="background-color: lightskyblue;">
    <th rowspan="2">
        <input type="checkbox">
    </th>
    <th rowspan="2"> 助成金 </th>
    <th rowspan="2"> コース </th>
    <th rowspan="2"> 郵送希望 </th>
    <th rowspan="2"> 受講者指名 </th>
    <th rowspan="2"> 個人電話番号 </th>
    <th rowspan="2" class="place"> 住所 </th>
    <th rowspan="2"> 講習開始日 </th>
    <th rowspan="2"> 講習終了日 </th>
    <th colspan="6" class="company"> 会社情報 </th>
</tr>
<tr>
    <th> 会社名 </th>
    <th> 申請者氏名 </th>
    <th> 住所 </th>
    <th> 電話番号 </th>
    <th> FAX番号 </th>
</tr>
<tr><td><input type="checkbox" name="check[]" value=0></td><td></td><td></td> <td></td><td></td><td></td><td></td> 
            <td></td><td></td><td></td><td></td><td></td><td></td><td></td>';
                }
            }
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($reserve_date);
            exit;
        } else if ($_POST["code"] === "1") { //削除ボタン
            $checks = [];
            $checks = $_POST["checks"];
            $delete_result = 0;
            foreach ($checks as $checks1) {
                $sql_delete = $pdo->prepare('DELETE FROM st_reserve WHERE reserve_no=:checks');
                $sql_delete->bindValue(':checks', $checks1);
                $sql_delete->execute();
            }
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($delete_result);
            exit;
        }
    } catch (PDOException $e) {
        // エラー発生

        echo $e->getMessage();
    } finally {
        // DB接続を閉じる
        $pdo = null;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset=" UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <link rel="stylesheet" href="/style3.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/5.2.0/js/tableexport.min.js" integrity="sha512-XmZS54be9JGMZjf+zk61JZaLZyjTRgs41JLSmx5QlIP5F+sSGIyzD2eJyxD4K6kGGr7AsVhaitzZ2WTfzpsQzg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body>
    <a href="index.php"> stsys01 </a>
    <a href="stsys02.php"> stsys02 </a>
    <a href="stsys03.php"> stsys03 </a>
    <a href="stsys04.php"> stsys04 </a>

    <?php
    //タイムゾーンを設定
    date_default_timezone_set('Asia/Tokyo');
    $year = date("Y");
    $today_show = date('Ym'); //前月の表示・非表示判断に使う(<<前月マーク)
    $ym = date('Y-m');
    $ymd = date('Y-m') . '-01';
    $timestamp = strtotime($ym . '-01');
    //該当月の日数を取得
    $day_count = '-' . date('t', $timestamp);
    $ymd1 = date('Y-m') . $day_count;

    $sql_reserve_array = [];

    ?>

    <!--検索ボタン押したとき-->
    <script type="text/javascript">
        $(function() {
            $("#search").click(function() {
                var course_code = $("[name=course_code]").val();
                var start_date = $("[name=start_date]").val();
                var end_date = $("[name=end_date]").val();
                //ボタンの数をsession変数に入れる（javaの変数をphpで使うにはajaxを使う）
                $.ajax({
                        type: "POST", //　GETでも可
                        url: "stsys03.php", //　送り先
                        data: {
                            'course_code': course_code,
                            'start_date': start_date,
                            'end_date': end_date,
                            'code': 0, //検索
                        }, //　渡したいデータをオブジェクトで渡す
                        dataType: "json", //　データ形式を指定
                        scriptCharset: 'utf-8' //　文字コードを指定
                    })

                    .done(
                        function(date) { //　paramに処理後のデータが入って戻ってくる
                            $("#return").find("tr").remove()
                            $("#return").append(date);
                        },
                    ).fail(function(XMLHttpRequest, status, e) {
                        alert(e);
                    });
                // function(XMLHttpRequest, textStatus, errorThrown) { //　エラーが起きた時はこちらが実行される
                //     console.log(XMLHttpRequest); //　エラー内容表示
                // });
            })
            $("#cancel").click(function(e) {
                var checks = [];
                $("[name='check[]']:checked").each(function() {
                    checks.push(this.value);
                })
                if (checks.length != 0) {
                    e.preventDefault();
                    if (!window.confirm('本当に削除しますか？')) {
                        // window.alert('キャンセルされました');
                        return false;
                    }

                    //ボタンの数をsession変数に入れる（javaの変数をphpで使うにはajaxを使う）
                    $.ajax({
                            type: "POST", //　GETでも可
                            url: "stsys03.php", //　送り先
                            data: {
                                'checks': checks,
                                'code': 1, //削除
                            }, //　渡したいデータをオブジェクトで渡す
                            dataType: "json", //　データ形式を指定
                            scriptCharset: 'utf-8' //　文字コードを指定
                        })

                        .done(
                            function(date) { //　paramに処理後のデータが入って戻ってくる
                                $("#search").trigger('click');
                                // $("#return").find("tr").remove()
                                // $("#return").append(date);
                            },
                        ).fail(function(XMLHttpRequest, status, e) {
                            alert(e);
                        });
                    // function(XMLHttpRequest, textStatus, errorThrown) { //　エラーが起きた時はこちらが実行される
                    //     console.log(XMLHttpRequest); //　エラー内容表示
                    // });
                }
            })

        })
    </script>
    <div class="main_container">
        <h1>予約情報管理画面</h1>
        <div class="table_content" name="form1">
            <table class="course_name_select" style="border-collapse:collapse;">
                <tr>
                    <td> 講習名 </td>
                    <td>
                        <select name="course_code" id="course_code" style="outline: none;">
                            <?php echo  $course_select ?> </select>
                    </td>
                </tr>
            </table>
            <table class="day_select" style="border-collapse:collapse ;">
                <tr>
                    <td>講習期間<br><span class="small_text">開始日付～終了日付</span></td>
                    <?php echo '<td><input type="date" name="start_date" value= ' . $ymd . '></td>'; ?>
                    <td> ～ </td>
                    <?php echo '<td><input type="date" name="end_date" value= ' . $ymd1 . '></td>'; ?>
                </tr>
            </table>
            <div class="btn_main">
                <button id="search" class="btn btn--orange"> 検索 </button>
                <button id="cancel" class="btn btn--silver">キャンセル</button>
<!--Excel出力------------------------------------------------------------------>
                 <form action="test.php" method="POST">
                    <button id="btnExport" class="btn btn--silver">Excel出力</button>
                </form>
            </div>
        </div>

   
        <!--Excel出力------------------------------------------------------------------>
                  
        <div class="guide_text">
            <p class="guide_text_link">
                <a href="./stsys04.php">期間登録履歴画面へ</a>
            </p>
        </div>
        <div class="stsys3_table_main">
            <table border=" 1" id="return" style="border-collapse:collapse" class="stsys3_table" ;>
                <tr class="table_tlt">
                    <th rowspan="2">
                        <input type="checkbox">
                    </th>
                    <th rowspan="2"> 助成金 </th>
                    <th rowspan="2"> コース </th>
                    <th rowspan="2"> 郵送希望 </th>
                    <th rowspan="2"> 受講者指名 </th>
                    <th rowspan="2"> 個人電話番号 </th>
                    <th rowspan="2" class="place"> 住所 </th>
                    <th rowspan="2" class="day"> 講習開始日 </th>
                    <th rowspan="2" class="day"> 講習終了日 </th>
                    <th colspan="6" class="company"> 会社情報 </th>
                </tr>
                <tr>
                    <th> 会社名 </th>
                    <th> 申請者氏名 </th>
                    <th> 住所 </th>
                    <th> 電話番号 </th>
                    <th> FAX番号 </th>
                </tr>
                <tr class="table_input">
                    <td><input type="checkbox"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

            </table>
        </div>
        <p class="caution_text"> ※「申込書有無」「 入金日」「 金額」 のチェック後の登録は、「 当日受付チェックツール.xlsm」 を使用し登録を行ってください。 </p>
    </div>
</body>

</html>
