<?php
try {
    // DB接続
    $pdo = new PDO(
        // 'mysql:dbname=heroku_5e78f26ff50403d;host=us-cdbr-east-05.cleardb.net;charset=utf8',
        // 'b2c2e6853ab5ee',
        // '2f35b6a9',

        'mysql:dbname=stsys;host=localhost;charset=utf8',
        'root',
        'shinei4005',

        // レコード列名をキーとして取得させる
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    if (isset($_POST['search'])) {

        $period_stmt = $pdo->prepare('INSERT INTO st_period_number (course_code,course_name,place_code,place_name,start_date,end_date,limited_num,biko,del_flg)' .
            'VALUES(:course_code,:course_name,:place_code,:place_name,:start_date,:end_date,:limited_num,:biko,0)');

        $course = $_POST['course'];
        $course = explode(",", $course);
        $course_code = $course[0];
        $course_name = $course[1];
        $place = $_POST['place'];
        $place = explode(",", $place);
        $place_code = $place[0];
        $place_name =  $place[1];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $limited_num = $_POST['limited_num'];
        $biko = $_POST['biko'];

        $period_stmt->bindValue(':course_code', $course_code);
        $period_stmt->bindValue(':course_name', $course_name);
        $period_stmt->bindValue(':place_code', $place_code);
        $period_stmt->bindValue(':place_name', $place_name);
        $period_stmt->bindValue(':start_date', $start_date);
        $period_stmt->bindValue(':end_date', $end_date);
        $period_stmt->bindValue(':limited_num', $limited_num);
        $period_stmt->bindValue(':biko', $biko);

        // SQL実行
        $period_stmt->execute();
        header("Location: ./stsys04.php");
    }
    $sql_reserve = $pdo->prepare('SELECT * FROM st_period_number WHERE del_flg=0 ORDER BY start_date,course_code');
    $sql_reserve->execute();

    $s = 0; //カウントに使う
    //検定項目登録{couse_code=>day})
    $sql_reserve_array = [];
    foreach ($sql_reserve as $sql_reserve1) {
        $sql_reserve_array[$s] = [$sql_reserve1['course_name'], $sql_reserve1['place_name'], $sql_reserve1['start_date'], $sql_reserve1['end_date'], $sql_reserve1['limited_num'], $sql_reserve1['biko']];
        $s += 1;
    }
    $reserve_date = "";
    $s = 0;
    $count = 1;
    foreach ($sql_reserve_array as $sql_reserve_array1) {
        if ($s === 0) {
            $reserve_date .=
                '<tr style="background-color: lightskyblue;"></tr>' .
                '<td class="text_center">' . $count . '</td>' .
                '<td>' . $sql_reserve_array1[0] . '</td>' .
                '<td>' . $sql_reserve_array1[1] . '</td>' .
                '<td class="text_center">' . $sql_reserve_array1[2] . '</td>' .
                '<td class="text_center">' . $sql_reserve_array1[3] . '</td>' .
                '<td class="text_center">' . $sql_reserve_array1[4] . '</td>' .
                '<td>' . $sql_reserve_array1[5] . '</td></tr>';
            $s += 1;
        } else {
            $reserve_date .=
                '<td  class="text_center">' . $count . '</td>' .
                '<td>' . $sql_reserve_array1[0] . '</td>' .
                '<td>' . $sql_reserve_array1[1] . '</td>' .
                '<td class="text_center">' . $sql_reserve_array1[2] . '</td>' .
                '<td class="text_center">' . $sql_reserve_array1[3] . '</td>' .
                '<td class="text_center">' . $sql_reserve_array1[4] . '</td>' .
                '<td>' . $sql_reserve_array1[5] . '</td></tr>';
            $s += 1;
        }
        $count += 1;
    }

    // ***************SQL文をセット（講習名選択）*******************************************
    $course_sql = ('SELECT * FROM st_course_item_mst WHERE del_flg=0');

    // SQL実行
    $course_stmt = $pdo->query($course_sql);

    // foreach文で配列の中身を一行ずつ出力
    $course_code_name = [];
    $i = 0;

    foreach ($course_stmt as $course_row) {
        // データベースのフィールド名で出力
        $course_code_name[$i] = [$course_row['course_code'] => $course_row['course_name']];
        $i += 1;
    }

    // ***************SQL文をセット（会場選択）*******************************************
    $place_sql = ('SELECT * FROM com_place_mst WHERE del_flg=0');

    // SQL実行
    $place_stmt = $pdo->query($place_sql);

    // foreach文で配列の中身を一行ずつ出力
    $place_code_name = [];
    $i = 0;

    foreach ($place_stmt as $place_row) {
        // データベースのフィールド名で出力
        $place_code_name[$i] = [$place_row['place_code'] => $place_row['place_name']];
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

<!--講習期間-->
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

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/5.2.0/js/tableexport.min.js" integrity="sha512-XmZS54be9JGMZjf+zk61JZaLZyjTRgs41JLSmx5QlIP5F+sSGIyzD2eJyxD4K6kGGr7AsVhaitzZ2WTfzpsQzg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="/style4.css">
</head>

<body>
    <a href="index.php">stsys01</a>
    <a href="stsys02.php">stsys02</a>
    <a href="stsys03.php">stsys03</a>
    <a href="stsys04.php">stsys04</a>
    <div class="main_container">
        <h1>期間登録・編集画面</h1>
    
        <form action="stsys04.php" method="POST">
            <div class="flex">
                <!--講習名選択-->
                <table class="course_table" style="border-collapse:collapse" ;>
                    <tr>
                        <td>講習名</td>
                        <td><select name="course" class="border_no course_tr">
                                <?php
                                foreach ($course_code_name as $key => $course_name) {
                                    foreach ($course_name as $key => $course_name1) {
                                        echo '<option value="' . $key . "," . $course_name1 . '">' . $course_name1 . '</option>';
                                    }
                                }
                                ?>
                            </select></td>
                    </tr>
                </table>
    
                <!--会場選択-->
                <table class="place_table" style="border-collapse:collapse" ;>
                    <tr>
                        <td>会場</td>
                        <td><select name="place" class="border_no place_tr">
                                <?php
                                foreach ($place_code_name as $key => $place_name) {
                                    foreach ($place_name as $key => $place_name1) {
                                        echo '<option value="' . $key . "," . $place_name1 . '">' . $place_name1 . '</option>';
                                    }
                                }
                                ?>
                            </select></td>
                    </tr>
                </table>
                <!-- 講習期間 -->
                <table class="day_table" style="border-collapse:collapse">
                    <tr>
                        <td>講習期間<br><span class="small_text">開始日付～終了日付</span></td>
                        <?php echo '<td><input type="date" class="border_no day_tr" name="start_date" value= ' . $ymd . '></td>'; ?>
                        <td> ～ </td>
                        <?php echo '<td><input type="date" class="border_no day_tr" name="end_date" value= ' . $ymd1 . '></td>'; ?>
                    </tr>
                    <!-- <tr>
                        <td colspan="3"> ※半角8桁のyyyymmdd形式で入力してください</td>
                    </tr> -->
                </table>
    
                <!--制限人数-->
                <table class="limited_table" style="border-collapse:collapse" ;>
                    <tr>
                        <td>制限人数</td>
                        <td><select name="limited_num" class="border_no limited_tr">
                                <?php
                                for ($i = 1; $i < 31; $i++) {
                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                }
                                ?>
                            </select></td>
                    </tr>
                </table>
            </div>
            <div class="flex1">
                <!--備考-->
                <table class="biko_table" style="border-collapse:collapse" ;>
                    <tr>
                        <td>備考</td>
                        <td><input type="text" name="biko" class="biko biko_tr"></td>
                    </tr>
                </table>
                <button id="search" name="search" type="submit" class="btn btn--orange"> 登録 </button>
            </div>
        </form>
        <div class="guide_text">
            <p class="guide_text_tlt">・講習期間一覧</p>
            <p class="guide_text_link">
                <a href="">登録情報管理画面へ</a>
            </p>
        </div>
        <div class="stsys4_table_main">
            <table border=" 1" id="return" class="stsys4_table" style="border-collapse:collapse;">
                <tr style="background-color: lightskyblue;">
                    <th class="stsys4_th" rowspan="2"> No </th>
                    <th class="stsys4_th" rowspan="2"> 講習名 </th>
                    <th class="stsys4_th" rowspan="2"> 会場 </th>
                    <th class="stsys4_th" rowspan="2"> 講習開始日 </th>
                    <th class="stsys4_th" rowspan="2"> 講習終了日 </th>
                    <th class="stsys4_th" rowspan="2"> 制限人数 </th>
                    <th class="stsys4_th" rowspan="2"> 備考 </th>
                    <?php
                    echo $reserve_date;
                    ?>
                </tr>
            </table>
        </div>
    
        <p class="caution_text">※必ず、<span class="color_red">月までに9月～12月、12月までに1月～4月、4月までに5月～8月</span>の講習詳細情報を登録してください。<br>
        ※1度登録した講習情報を削除・変更したい場合は、デジタルワークレートまでご連絡お願いいたします。
        </p>
    </div>
</body>

</html>
