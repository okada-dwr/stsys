<?php
//タイムゾーンを設定
date_default_timezone_set('Asia/Tokyo');

//GoogleカレンダーAPIから祝日を取得
$year = date("Y");

$Holidays_array = getHolidays($year);

$prev_show = 1; //前月の表示・非表示判断フラグ
$today_show = date('Ym');; //前月の表示・非表示判断に使う
//前月・次月リンクが選択された場合は、GETパラメーターから年月を取得
if (isset($_GET['ym'])) {
    $ym = $_GET['ym'];
} else {
    //今月の年月を表示
    $ym = date('Y-m');
}

//タイムスタンプ（どの時刻を基準にするか）を作成し、フォーマットをチェックする
//strtotime('Y-m-01')
$timestamp = strtotime($ym . '-01');
if ($timestamp === false) { //エラー対策として形式チェックを追加
    //falseが返ってきた時は、現在の年月・タイムスタンプを取得
    $ym = date('Y-m');
    $timestamp = strtotime($ym . '-01');
}

//今月の日付　フォーマット　例）2020-10-2
$today = date('Y-m-j');

//カレンダーのタイトルを作成　例）2020年10月
$html_title = date('Y年n月', $timestamp); //date(表示する内容,基準)


//strtotime(,基準)
$prev = date('Y-m', strtotime('-1 month', $timestamp)); //前月の年月を取得
$next = date('Y-m', strtotime('+1 month', $timestamp)); //来月の年月を取得

//前月・次月リンクが選択された場合は、GETパラメーターから年月を取得
if (isset($_GET['ym'])) {
    $ym = $_GET['ym'];
    $ym_show = $ym;
    $ym_show = str_replace('-', '', $ym_show);

    //echo $today_show, '<', $ym_show;

    if ($today_show < $ym_show) { //ユーザー選択日付が今日の日付より大きい場合、前月遷移を表示する。
        $prev_show = '<h3 style="font-size: 1.0rem;"><a href="?ym=' . $prev . '">&lt;&lt;前月 </a>' . $html_title . '<a href="?ym=' . $next . '"> 来月&gt;&gt;</a></h3>';
    } else {   //ユーザー選択日付が今日の日付と同じ場合、前月遷移を表示しない。
        $prev_show =  '<h3 style="font-size: 1.0rem;">' . $html_title . '<a href="?ym=' . $next . '"> 来月&gt;&gt;</a></h3>';
    }
} else { //ユーザー選択日付が今日の日付と同じ場合、前月遷移を表示しない。
    //今月の年月を表示
    $ym = date('Y-m');
    $prev_show = '<h3 style="font-size: 1.0rem;">' . $html_title . '<a href="?ym=' . $next . '"> 来月&gt;&gt;</a></h3>';
}

//該当月の日数を取得
$day_count = date('t', $timestamp);

//１日が何曜日か 0:日 1:月 2:火 3:水 4:木 5:金 6:土
/*$youbi = '<th>' . date('w', $timestamp) . '</th>';*/

//カレンダー作成の準備
$weeks = [];
$week = '';
$week_none = '';
$youbi_kana = '';
$count = 0;
$ketugou = [];
//第１週目：空のセルを追加
//str_repeat(文字列, 反復回数)
/*$week .= str_repeat('<td></td>', $youbi);*/

for ($day = 1; $day <= $day_count; $day++, $youbi++) {

    $date = $ym . '-' . $day;
    //それぞれの日付をY-m-d形式で表示例：2020-01-23
    //$dayはfor関数のおかげで１日づつ増えていく
    $timestamp = strtotime($ym . '-' . $day);
    $youbi = date('w',  $timestamp);

    switch ($youbi) {
        case 0:
            $youbi_kana .= '<th>日</th>';
            $count = $count + 1;
            break;
        case 1:
            $youbi_kana .= '<th>月</th>';
            $count = $count + 1;
            break;
        case 2:
            $youbi_kana .= '<th>火</th>';
            $count = $count + 1;
            break;
        case 3:
            $youbi_kana .= '<th>水</th>';
            $count = $count + 1;
            break;
        case 4:
            $youbi_kana .= '<th>木</th>';
            $count = $count + 1;
            break;
        case 5:
            $youbi_kana .= '<th>金</th>';
            $count = $count + 1;
            break;
        case 6:
            $youbi_kana .= '<th>土</th>';
            $count = $count + 1;
            break;
        default:
            echo '曜日エラーです';
    }




    $Holidays_day = display_to_Holidays(date("Y-m-d", strtotime($date)), $Holidays_array);
    //display_to_Holidays($date,$Holidays_array)の$dateに1/1~12/31の日付を入れる
    //比較してあったらdisplay_to_Holidaysメソッドによって$Holidays_array[$date]つまり$holidaysがreturnされる


    /*$reservation = reservation(date("Y-m-d", strtotime($date)), $reservation_array);*/

    $month_db = 1; //月
    $start_day = [2, 8, 13, 30];
    $end_day = [2, 10, 14, 2];
    $keisan = 0;

    $key_count = 0;
    $count = 0;
    $higawari = 0;

    for ($i = 0; $i < count($start_day); $i++) { //何日結合するか調べる（8日～10日の場合２つ）
        //echo $count . '=' . $end_day[$i] . '-' . $start_day[$i] . 'finish';
        $count = $end_day[$i] - $start_day[$i];
        if ($count < 0) {
            switch ($month_db) {

                case 1 or 3 or 5 or 7 or 8 or 10 or 12:
                    $higawari = 31 - $start_day[$i];
                    if ($higawari === 0) {
                        for ($z = 0; $z < $end_day[$i]; $z++) {
                            $coun1 += 1;
                            $ketugou[$key_count] = array($start_day[$i] => 1 + $coun1 - 1); //結合される部分作成(8日なら9日と10日が結合される
                            $key_count += 1;
                        }
                    } else {
                        for ($z = 0; $z < $higawari; $z++) {
                            $coun1 += 1;
                            $ketugou[$key_count] = array($start_day[$i] => $start_day[$i] + $coun1); //結合される部分作成(8日なら9日と10日が結合される
                            $key_count += 1;
                        }
                        for ($z = 0; $z < $end_day[$i]; $z++) {

                            $ketugou[$key_count] = array($start_day[$i] => 1 + $coun1 - 1); //結合される部分作成(8日なら9日と10日が結合される
                            $key_count += 1;
                            $coun1 += 1;
                        }
                    }
                    break;

                case 2:
                    break;
                case 4 or 6 or 9 or 11:
                    break;
                default:
            }
        } else {
            for ($z = 0; $z < $count; $z++) {
                $coun1 += 1;
                //echo $start_day[$z] . '+' . $coun1 . '=';

                //予約スタート日が1桁か2桁か調べる。
                //後のforeach等の処理で$ketugouを使う際、[.2桁]を切り取り消すのでその都合。
                // if (strlen($start_day[$i]) === 1) {
                //     $group_day = "0";
                // } else {
                //      $group_day = "";
                //  }

                //｛0:8=>9.08,0:8=>10.08｝後ろの.08は8日から10日までのグループであることを示す。
                $ketugou[$key_count] = array($start_day[$i] => $start_day[$i] + $coun1); //結合される部分作成(8日なら9日と10日が結合される
                //$ketugou[$key_count] = array($start_day[$i] => $start_day[$i] + $coun1 . $group_day . $start_day[$i]); //結合される部分作成(8日なら9日と10日が結合される
                //$ketugou[$key_count] = array($start_day[$i] => $start_day[$i] + $coun1); //結合される部分作成(8日なら9日と10日が結合される
                $key_count += 1;
            }
        }

        $z = 0;
        $coun1 = 0;
    }

    $skip_check = 0;
    $colspan_count = 0;
    $finish_key_count = 0;

    if ($today == $date) {
        //もしその日が今日なら
        $week .= '<td class="today">' . $day; //今日の場合はclassにtodayをつける
        $week_none .= '<td class="today">'; //今日の場合はclassにtodayをつける
    } elseif (display_to_Holidays(date("Y-m-d", strtotime($date)), $Holidays_array)) {
        //もしその日に祝日が存在していたら
        //その日が祝日の場合は祝日名を追加しclassにholidayを追加する
        $week .= '<td class="holiday">' . $day;
        $none .= '<td class="holiday">';
        /*$week .= '<td class="holiday">' . $day . $Holidays_day;*/
        /*} elseif (reservation(date("Y-m-d", strtotime($date)), $reservation_array)) {
        $week .= '<td>' . $day . $reservation;*/
    } else {
        $skip_check = 0;
        //上２つ以外なら
        //foreach ($ketugou as $ketugou1) { //$dayが結合される日付か確認する(0:飛ばさない 1:飛ばす)
        foreach ($ketugou as $key => $ketugou1) { //予約日のキーを探して結合数を数える
            foreach ($ketugou1 as $key => $ketugou3) { //予約日のキーを探して結合数を数える
                if ($day === $ketugou3) {
                    $skip_check = 1;
                    break;
                }
            }
            //$str = $ketugou1; //文字列
            //$cut = 2; //カットしたい文字数
            //$ketugou1 = substr($str, 0, strlen($str) - $cut); // 808の場合右から08を切り取り8にする
        }

        //$i = [1 => "a", 1 => "b", 2 => "c"];

        if ($skip_check === 0) { //結合される日付は飛ばす(0:飛ばさない 1:飛ばす)
            $yoyakubi_ari = 0;
            //この段階では予約のない日か予約日の先頭日付かどちらかになる
            foreach ($start_day as $start_day) {
                $key_name = 0;
                if ($yoyakubi_ari === 0) {
                    if ($day === $start_day) { //予約日かそうでないか判断する
                        $yoyakubi_ari = 1;
                        $key_name = $start_day;
                        foreach ($ketugou as $key => $ketugou2) { //予約日のキーを探して結合数を数える
                            foreach ($ketugou2 as $key => $ketugou2) { //予約日のキーを探して結合数を数える
                                //$key_count = array_keys($ketugou2);
                                if ($day === $key) {
                                    $finish_key_count += 1;
                                }
                            }
                            //$key_count = array_keys($ketugou2);
                            //if ($day === $key) {
                            //    $finish_key_count += 1;
                            //}
                        }
                        if ($colspan_count <> 0) { //結合する数が０ではなかったら
                            $week .= '<td>' . $day;
                            $week_none .= '<td class="course_type" colspan="' . $finish_key_count + 1 . '">';
                        } else { //結合する数が０なら普通に表示する
                            $week .= '<td>' . $day;
                            $week_none .= '<td class="course_type" colspan="' . $finish_key_count + 1 . '">';
                        }
                        //} else { //予約日でないから普通に表示する
                        //$week .= '<td>' . $day;
                        //$week_none .= '<td>';
                    }
                }
            }
            if ($yoyakubi_ari === 0) {
                $week .= '<td>' . $day;
                $week_none .= '<td>';
            }
            //  if ($day === $start_day) { //予約日の始まりの日なら
            // foreach ($ketugou as $ketugou) { //予約日のキーを探して結合数を数える
            // $key_count = array_keys($ketugou);
            //}
            // if ($colspan_count <> 0) { //結合する数が０ではなかったら
            //$week .= '<td>' . $day;
            //  $week_none .= '<td class="course_type" colspan="' . $ketugou + 1 . '">';
            //  } else { //結合する数が０なら普通に表示する
            //    $week .= '<td>' . $day;
            //    $week_none .= '<td class="course_type" colspan="' . $ketugou + 1 . '">';
            //  }
            //  } else {
            //     $week .= '<td>' . $day;
            //     $week_none .= '<td>';
            // }
            $week .= '</td>';
            $week_none .= '</td>';
        } else {
            $week .= '<td>' . $day;
            $week .= '</td>';
        }
    }



    /*if ($youbi % 7 == 6 || $day == $day_count) { //週終わり、月終わりの場合
        //%は余りを求める、||はまたは
        //土曜日を取得

        if ($day == $day_count) { //月の最終日、空セルを追加
            $week .= str_repeat('<td></td>', 6 - ($youbi % 7));
        }

        $weeks[] = '<tr>' . $week . '</tr>'; //weeks配列にtrと$weekを追加

        $week = ''; //weekをリセット
    }*/
}

function getHolidays($year)
{ //その年の祝日を全て取得する関数を作成

    $api_key = 'AIzaSyABYdtuhv0ax0cmuL2GcPFFnbcpdq6Kan4'; //取得したAPIを入れる
    $holidays = array(); //祝日を入れる配列の箱を用意しておく
    $holidays_id = 'japanese__ja@holiday.calendar.google.com';
    $url = sprintf(
        //sprintf関数を使用しURLを設定
        //このURLはGoogleカレンダー独自のURL
        //Googleカレンダーから祝日を調べるURL
        'https://www.googleapis.com/calendar/v3/calendars/%s/events?' .
            'key=%s&timeMin=%s&timeMax=%s&maxResults=%d&orderBy=startTime&singleEvents=true',
        $holidays_id,
        $api_key,
        $year . '-01-01T00:00:00Z', // 取得開始日
        $year . '-12-31T00:00:00Z', // 取得終了日
        150 // 最大取得数
    );

    if ($results = file_get_contents($url, true)) {
        //file_get_contents関数を使用
        //URLの中に情報が入っていれば（trueなら）以下を実行する
        $results = json_decode($results);
        //JSON形式で取得した情報を配列に格納
        foreach ($results->items as $item) {
            $date = strtotime((string) $item->start->date);
            $title = (string) $item->summary;
            $holidays[date('Y-m-d', $date)] = $title;
            //年月日をキー、祝日名を配列に格納
        }
        ksort($holidays);
        //祝日の配列を並び替え
        //ksort関数で配列をキーで逆順に（１月からの順番にした）
    }
    return $holidays;
}

//その日の祝日名を取得
function display_to_Holidays($date, $Holidays_array)
{
    //※引数1は日付"Y-m-d"型、引数に2は祝日の配列データ
    //display_to_Holidays("Y-m-d","Y-m-d") →引数1の日付と引数2の日付が一致すればその日の祝日名を取得する

    if (array_key_exists($date, $Holidays_array)) {
        //array_key_exists関数を使用
        //$dateが$Holidays_arrayに存在するか確認
        //各日付と祝日の配列データを照らし合わせる

        $holidays = "<br/>" . $Holidays_array[$date];
        //祝日が見つかれば祝日名を$holidaysに入れておく
        return $holidays;
    }
}
//その日の祝日名を取得

//-----------予約フォームからDBに書込み---------------//

/*if (isset($_POST['name'])) {
    //名前が送信されたら以下の処理を行う
    //この部分は変更してもいい

    //「予約フォーム」からの情報をそれぞれ変数に格納しておく↓

    $name = htmlspecialchars($_POST["name"], ENT_QUOTES);
    $number = htmlspecialchars($_POST["number"], ENT_QUOTES);
    $member = htmlspecialchars($_POST["member"], ENT_QUOTES);
    $day = htmlspecialchars($_POST["day"], ENT_QUOTES);

    //「予約フォーム」からの情報をそれぞれ変数に格納しておく↑


    $dsn = "mysql:host=ホスト名;dbname=データベース名;charset=utf8";
    $user = "ユーザー名";
    $pass = "パスワード";


    try {

        $db = new PDO($dsn, $user, $pass);
        $db->query("INSERT INTO テーブル名 (ban,name,number,member,day)
            VALUES (NULL,'$name','$number','$member','$day')");
    } catch (Exception $e) {
        echo $e->getMessage() . PHP_EOL;
    }

    header("Location: reservation_form.php");
    //"reservation_form.php（予約フォームがあったページ）"に戻る
    exit;
}

//-----------予約された日の予約人数を取得する関数---------------//
function getreservation()
{

    $dsn = "mysql:host=ホスト名;dbname=データベース名;charset=utf8";
    $user = "ユーザー名";
    $pass = "パスワード";
    $db = new PDO($dsn, $user, $pass);
    $ps = $db->query("SELECT * FROM テーブル名");
    $reservation_member = array();

    foreach ($ps as $out) {

        $day_out = strtotime((string) $out['day']);

        $member_out = (string) $out['member'];

        $reservation_member[date('Y-m-d', $day_out)] = $member_out;
    }
    ksort($reservation_member);
    return $reservation_member;
}


//-----------予約人数を表示させる関数---------------//
$reservation_array = getreservation();
//getreservation関数を$reservation_arrayに代入しておく

function reservation($date, $reservation_array)
{
    //カレンダーの日付と予約された日付を照合する関数

    if (array_key_exists($date, $reservation_array)) {
        //もし"カレンダーの日付"と"予約された日"が一致すれば以下を実行する

        if ($reservation_array[$date] >= 10) {
            //予約人数が１０人以上の場合は以下を実行する

            $reservation_member = "<br/>" . "<span class='green'>" . "予約できません" . "</span>";
            return $reservation_member;
        } else {
            //予約人数が１０人より少なければ以下を実行する

            $reservation_member = "<br/>" . "<span class='green'>" . $reservation_array[$date] . "人" . "</span>";
            //例：echo $reservation_member; → ３人
            //色を変えるためにspanでclassをつけた

            return $reservation_member;
        }
    }
}*/





?>

<!-----------カレンダープログラム--------------->

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>PHPカレンダー</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <style>
        .container {
            font-family: 'Noto Sans', sans-serif;
            margin-top: 80px;
        }

        h3 {
            margin-bottom: 30px;
        }

        .table-bordered th {
            height: 50px;
            text-align: center;
        }

        .table-bordered td {
            height: 30px;
            text-align: center;
        }

        .today {
            background: orange;
        }

        th:nth-of-type(1),
        td:nth-of-type(1) {
            color: black;
        }

        th:nth-of-type(7),
        td:nth-of-type(7) {
            color: blue;
        }

        .holiday {
            color: red;
        }

        .green {
            color: green;
        }
    </style>
</head>

<body id=""> 　　　　
    <a href="stsys02.php">a</a>
    　　　　<div class="title1">
        <h1 style="text-align: center;text-decoration:underline;">予約表示画面</h1>
        <div class="explanation" style="margin-left: 130px;margin-right: 130px;border:1px solid;padding-left:5px;">
            <p>カレンダーには、「予約枠の数」を表示しております。「ｘ」は満席。<br>受講人数項目に人数を指定し、予約日を選択してください。
                <br><br>※<span>予約したい予定日</span>の【X席】を選択してください。
                <br>※<span>複数の講習を受講する場面</span>は、お手数をおかけしますが<span>1講習申込をした後、再度こちらの画面から予約していただきますよう</span>
                、よろしくお願いいたします。<br>※<span>キャンセルする場合</span>は、お手数おかけし申し訳ありませんが<span>「0773-76-0652」　高岸　</span>までご連絡ください。
            </p>
        </div>
    </div>

    <!--曜日表示^---------------------------------------------------------------------------------------------------------------------------------------------------->
    <div class="container">

        <table class="table table-bordered">
            <tr>
                <td>受講人数（※10名まで）</td>
                <td><select name="example">
                        <option value="1">1人</option>
                        <option value="2">2人</option>
                        <option value="3">3人</option>
                        <option value="4">4人</option>
                        <option value="5">5人</option>
                        <option value="6">6人</option>
                        <option value="7">7人</option>
                        <option value="8">8人</option>
                        <option value="9">9人</option>
                        <option value="10">10人</option>
                    </select></td>
            </tr>
            <tr>
                <td rowspan="2">
                    <?php
                    echo $prev_show;
                    ?>
                </td>
                <?php
                echo $week;
                ?>
            </tr>
            <tr>
                <?php
                echo $youbi_kana;
                ?>
            </tr>
            <tr class="area-tr">
                <?php
                echo '<td colspan="' . $count + 1 . '">舞鶴</td>'
                ?>
            </tr>
            <tr>
                <?php
                echo '<td class="course_type" colspan="' . $count + 1 . '">技能講習</td>'
                ?>
            </tr>
            <tr>
                <td>
                    <h3 style="font-size: 1.0rem;">小型移動式クレーン K1、K2</h3>
                </td>
                <?php
                echo $week_none;
                ?>
            </tr>
            <tr>
                <td>
                    <h3 style="font-size: 1.0rem;">車両系建設機械(整地等) S2</h3>
                </td>
                <?php
                echo $week_none;
                ?>
            </tr>
            <tr>
                <?php
                echo '<td colspan="' . $count + 1 . '"></td>'
                ?>
            </tr>
            <tr>
                <?php
                echo '<td class="course_type" colspan="' . $count + 1 . '">特別教育</td>'
                ?>
            </tr>
            <tr>
                <td>
                    <h3 style="font-size: 1.0rem;">クレーン</h3>
                </td>
                <?php
                echo $week_none;
                ?>
            </tr>
            <tr>
                <?php
                echo '<td class="course_type" colspan="' . $count + 1 . '">安全教育</td>'
                ?>
            </tr>
            <tr>
                <td>
                    <h3 style="font-size: 1.0rem;">刈払機</h3>
                </td>
                <?php
                echo $week_none;
                ?>
            </tr>
            <tr class="area-tr">
                <?php
                echo '<td colspan="' . $count + 1 . '">福知山</td>'
                ?>
            </tr>
            <tr>
                <?php
                echo '<td class="course_type" colspan="' . $count + 1 . '">技能講習</td>'
                ?>
            </tr>
            <tr>
                <td rowspan="2">
                    <h3 style="font-size: 1.0rem;">小型移動式クレーン K1、K2</h3>
                </td>
                <?php
                echo $week_none;
                ?>
            </tr>
        </table>
    </div>
    <!---------------------------------------------------------------------------------------------------------------------------------------------------------------->

    <div id="Exam_details">
        <div id="Exam_details_left">
            <div class="crane">
                <p>●小型式移動式クレーン</p>
                <table class="crane_child" border="1">
                    <tr>
                        <th>コース名</th>
                        <th>受講資格</th>
                        <th>料金</th>
                    </tr>
                    <tr>
                        <td class="Exam_details_center">K1</td>
                        <td class="Exam_details_center">20h</td>
                        <td>全科目</td>
                        <td class="Exam_details_center">35,000</td>
                    </tr>
                    <tr>
                        <td class="Exam_details_center">K2</td>
                        <td class="Exam_details_center">16h</td>
                        <td>●クレーン等の運転免許所有者<br>●床上操作式クレーン運転技術講習終了者<br>玉掛け技術講習終了者</td>
                        <td class="Exam_details_center">30,000</td>
                    </tr>
                </table>
            </div>
            <br>
            <div class="crane">
                <p>●車両系建設機械（整地等）</p>
                <table class="crane_child" border="1">
                    <tr>
                        <td class="Exam_details_center">S2</td>
                        <td class="Exam_details_center">14h</td>
                        <td>●大型特殊自動車免許保持者<br>●不整地運搬車運転技術講習修了者<br>●普通・準中型・中型・大型免許所有者で、3t未満の<br>
                            車両系建設機械(整地等)特別教育終了者で且つ、<br>運転経験3ヶ月以上の方<br>(事業主の照明が必要)</td>
                        <td class="Exam_details_center">35,000</td>
                    </tr>
                </table>
            </div>
            <br>
            <div class="crane">
                <p>●不整地運搬車</p>
                <table class="crane_child" border="1">
                    <tr>
                        <td class="Exam_details_center">F1</td>
                        <td class="Exam_details_center">11h</td>
                        <td>●大型特殊自動車免許保持者<br>●車両系建設機械(整地等・解体用)運転技術講習修了者<br>●普通・準中型・中型・大型免許所有者で、3t未満の<br>
                            車両系建設機械(整地等)特別教育終了者で且つ、<br>運転経験3ヶ月以上の方<br>(事業主の照明が必要)
                        </td>
                        <td class="Exam_details_center">35,000</td>
                    </tr>
                </table>
            </div>
            <br>
            <div class="crane">
                <p>●玉掛</p>
                <table class="crane_child" border="1">
                    <tr>
                        <td class="Exam_details_center">T1</td>
                        <td class="Exam_details_center">19h</td>
                        <td>●未経験者</td>
                        <td class="Exam_details_center">23,000</td>
                    </tr>
                    <tr>
                        <td class="Exam_details_center">T2</td>
                        <td class="Exam_details_center">15h</td>
                        <td>●クレーン等の運転免許所有者<br>●床上操作式クレーン運転技術講習終了者<br>●小型移動式クレーン技術講習終了者</td>
                        <td class="Exam_details_center">21,000</td>
                    </tr>
                </table>
            </div>
        </div>

        <div id="Exam_details_right">
            <div class="crane">
                <p>●フォークリフト</p>
                <table class="crane_child" border="1">
                    <tr>
                        <th>コース名</th>
                        <th>受講資格</th>
                        <th>料金</th>
                    </tr>
                    <tr>
                        <td class="Exam_details_center">R3</td>
                        <td class="Exam_details_center">31h</td>
                        <td>●大型特殊自動者免許(ｶﾀﾋﾟﾗ付限定)又は、普通準中型、<br>中型、大型自動車免許所持者</td>
                        <td class="Exam_details_center">41,000</td>
                    </tr>
                    <tr>
                        <td class="Exam_details_center">R4</td>
                        <td class="Exam_details_center">11h</td>
                        <td>●大型特殊自動者免許所持者(ｶﾀﾋﾟﾗ付限定は資格不可)</td>
                        <td class="Exam_details_center">22,000</td>
                    </tr>
                </table>
            </div>
            <br>
            <div class="crane">
                <p>●高所作業車</p>
                <table class="crane_child" border="1">
                    <tr>
                        <td class="Exam_details_center">H1</td>
                        <td class="Exam_details_center">14h</td>
                        <td>●大型特殊自動者免許、普通準中型、中型、大型自動車免許
                            　所有者<br>●ﾌｫｰｸﾘﾌﾄ運転技能講習修了者<br>●ｼｮﾍﾞﾙﾛｰﾀﾞｰ等運転技能講習修了者<br>
                            ●車両系建設機械(整地等)(基礎)(解体)のいずれかの運転技
                            　能講習修了者<br>●不整地運搬車運転技能講習修了者</td>
                        <td class="Exam_details_center">43,000</td>
                    </tr>
                    <tr>
                        <td class="Exam_details_center">H2</td>
                        <td class="Exam_details_center">12h</td>
                        <td>●大型特殊自動者免許所持者(ｶﾀﾋﾟﾗ付限定は資格不可)</td>
                        <td class="Exam_details_center">41,000</td>
                    </tr>
                </table>
            </div>
            <br>
            <div class="crane">
                <p>●フォークリフト</p>
                <table class="crane_child" border="1">
                    <tr>
                        <td class="Exam_details_center">G</td>
                        <td class="Exam_details_center">13h</td>
                        <td>●ガス溶接装置を用いて、溶接・溶断または加熱の作業を</td>
                        <td class="Exam_details_center">17,000</td>
                    </tr>
                </table>
            </div>
            <br>
            <div class="crane">
                <p>●フォークリフト</p>
                <table class="crane_child" border="1">
                    <tr>
                        <td class="Exam_details_center">A</td>
                        <td class="Exam_details_center">5h</td>
                        <td>●ガス溶接装置を用いて、溶接・溶断または加熱の作業をする方</td>
                        <td class="Exam_details_center">21,000</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div id="pay_explanation">
        <div class="pay_explanation_left" style="font-size: 2rem;">
            <b>受講料の支払について</b>
            <p>●受講料は受講日までに、こちらから送付する「運転技能講習のご案内」に記載されている、<br>口座へ振込みをお願いいたします。なお、手数料はお客様負担になります。</p>
            <p>●現金の場合は、初日に持参いただきますよう、よろしくお願いいたします。<br>※講習開始後の受講料は返金できませんので、ご注意ください。</p>
            <br>
            <b>受講資格</b>
            <p>●満18歳以上の男女(個人の方でも受講できます。学歴は不問です。)</p>
            <p>●各コースの受講資格欄に該当される方</p>

        </div>
        <div class="pay_explanation_right" style="font-size: 2rem;">
            <b>その他</b>
            <p>●定員がありますので、ご注意ください。</p>
            <p>●受講開始後の返金はできません。</p>
            <p>●日程等、予告なく変更する場合があります。必ず画面上のカレンダーを確認してください。</p>
            <p>※料金には、テキスト代・消費税を含みます。</p>
            <b>申請は、受講日の2日前までに申請してください。</b>
        </div>
    </div>
    <p class=form_explanation style="font-size:3rem;">何かご不明点や、わからないことがありましたら問い合わせお願いいたします。（Tel：0773-75-0652）

</body>

</html>
