<?php
//タイムゾーンを設定
date_default_timezone_set('Asia/Tokyo');


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

//前月・次月の年月を取得
//strtotime(,基準)
$prev = date('Y-m', strtotime('-1 month', $timestamp));
$next = date('Y-m', strtotime('+1 month', $timestamp));


//該当月の日数を取得
$day_count = date('t', $timestamp);

//１日が何曜日か
$youbi = date('w', $timestamp);

//カレンダー作成の準備
$weeks = [];
$week = '';

//第１週目：空のセルを追加
//str_repeat(文字列, 反復回数)
$week .= str_repeat('<td></td>', $youbi);

for ($day = 1; $day <= $day_count; $day++, $youbi++) {




    $date = $ym . '-' . $day;
    //それぞれの日付をY-m-d形式で表示例：2020-01-23
    //$dayはfor関数のおかげで１日づつ増えていく

    $Holidays_day = display_to_Holidays(date("Y-m-d", strtotime($date)), $Holidays_array);
    //display_to_Holidays($date,$Holidays_array)の$dateに1/1~12/31の日付を入れる
    //比較してあったらdisplay_to_Holidaysメソッドによって$Holidays_array[$date]つまり$holidaysがreturnされる


    $reservation = reservation(date("Y-m-d", strtotime($date)), $reservation_array);




    if ($today == $date) {
        //もしその日が今日なら
        $week .= '<td class="today">' . $day; //今日の場合はclassにtodayをつける
    } elseif (display_to_Holidays(date("Y-m-d", strtotime($date)), $Holidays_array)) {
        //もしその日に祝日が存在していたら
        //その日が祝日の場合は祝日名を追加しclassにholidayを追加する
        $week .= '<td class="holiday">' . $day . $Holidays_day;
    } elseif (reservation(date("Y-m-d", strtotime($date)), $reservation_array)) {
        $week .= '<td>' . $day . $reservation;
    } else {
        //上２つ以外なら
        $week .= '<td>' . $day;
    }
    $week .= '</td>';



    if ($youbi % 7 == 6 || $day == $day_count) { //週終わり、月終わりの場合
        //%は余りを求める、||はまたは
        //土曜日を取得

        if ($day == $day_count) { //月の最終日、空セルを追加
            $week .= str_repeat('<td></td>', 6 - ($youbi % 7));
        }

        $weeks[] = '<tr>' . $week . '</tr>'; //weeks配列にtrと$weekを追加

        $week = ''; //weekをリセット
    }
}


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

//GoogleカレンダーAPIから祝日を取得

$year = date("Y");

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

$Holidays_array = getHolidays($year);



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

?>

<!-----------カレンダープログラム--------------->

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>PHPカレンダー</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
    <style>
        .container {
            font-family: 'Noto Sans', sans-serif;
            margin-top: 80px;
        }

        h3 {
            margin-bottom: 30px;
        }

        th {
            height: 30px;
            text-align: center;
        }

        td {
            height: 100px;
        }

        .today {
            background: orange;
        }

        th:nth-of-type(1),
        td:nth-of-type(1) {
            color: red;
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
    　　　　
    <div class="container">
        <h3><a href="?ym=<?php echo $prev; ?>">&lt;</a><?php echo $html_title; ?><a href="?ym=<?php echo $next; ?>">&gt;</a></h3>
        <table class="table table-bordered">
            <tr>
                <th>日</th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
                <th>土</th>
            </tr>
            <?php
            foreach ($weeks as $week) {
                echo $week;
            }
            ?>
        </table>
    </div>



</body>

</html>
