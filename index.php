<?php
session_start();
//タイムゾーンを設定
date_default_timezone_set('Asia/Tokyo');

//GoogleカレンダーAPIから祝日を取得
$year = date("Y");
$year = 0;
$month = 0;
$Holidays_array = getHolidays($year);

$prev_show = 1; //前月の表示・非表示判断フラグ(<<前月マーク)
$today_show = date('Ym'); //前月の表示・非表示判断に使う(<<前月マーク)
$button_name_count = 0;
$button_name = "";
//前月・次月リンクが選択された場合は、GETパラメーターから年月を取得
if (isset($_GET['ym'])) {
    $ym = $_GET['ym'];
    $year = mb_substr($ym, 0, 4);
    $month = mb_substr($ym, 5, 2);
} else {
    //今月の年月を表示
    $ym = date('Y-m');
    $year = date('Y');
    $month =  date('m');
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

/*$youbi = '<th>' . date('w', $timestamp) . '</th>';*/

//カレンダー作成の準備
$weeks = [];
$week = '';
$week_none = [];  //各検定項目の日付セル作成（ボタンを置く）
$youbi_kana = ''; //曜日作成
$count = 0;
$count_youbi = 0; //カレンダーの舞鶴・福知山等の表示の結合数
$ketugou = [];
$ketugou_first = 0; //同じものの配列を一つにする時これが0ならそれを行う、1ならやらない

//第１週目：空のセルを追加
//カレンダーの日付を1から月末まで表示する
$ccheck = 0;
$start_day_final = []; //最終的に表示で使うもの
$start_day_final_dummy = []; //同じ配列を結合する際に使うもの（for等で使うもの）

try {
    $td_check = []; //配列最後に<td>を入れる際使用する。（これがないとエラー）
    $month_db = $month; //月
    $start_day = [];
    $reserve_day = [];
    $place_day = [];
    $course_day = [];
    $start_day_yobi = "";
    $end_day_yobi = "";

    // DB接続
    $pdo = new PDO(
        'mysql:dbname=heroku_5e78f26ff50403d;host=us-cdbr-east-05.cleardb.net;charset=utf8',
        'b2c2e6853ab5ee',
        '2f35b6a9',

        // 'mysql:dbname=stsys;host=localhost;charset=utf8',
        // 'root',
        // 'shinei4005',

        // レコード列名をキーとして取得させる
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    $ymd_start =  $ym . '-01';
    $ymd_end =   $ym . '-31';

    // 管理者の予約情報
    $sql_course = $pdo->prepare('SELECT * FROM st_course_item_mst WHERE del_flg=0  ORDER BY course_code');
    $sql_course->execute();

    //会場
    $sql_place = $pdo->prepare('SELECT * FROM com_place_mst WHERE del_flg=0  ORDER BY place_code');
    $sql_place->execute();
    $rowCount_anser = $sql_place->rowCount() - 1;

    // 管理者の予約情報
    $sql_period = $pdo->prepare('SELECT * FROM st_period_number WHERE del_flg=0 AND start_date BETWEEN :ymd_start AND :ymd_end ORDER BY place_code,course_code,start_date');
    $sql_period->bindValue(':ymd_start', $ymd_start);
    $sql_period->bindValue(':ymd_end', $ymd_end);
    $sql_period->execute();

    //客の予約情報
    $sql_reserve = $pdo->prepare('SELECT * FROM st_reserve WHERE del_flg=0 AND start_date BETWEEN :ymd_start AND :ymd_end ORDER BY place_code,course_code,start_date');
    $sql_reserve->bindValue(':ymd_start', $ymd_start);
    $sql_reserve->bindValue(':ymd_end', $ymd_end);
    $sql_reserve->execute();

    // foreach文で配列の中身を一行ずつ出力（2次元配列）

    //会場名
    $s = 0; //カウントに使う
    foreach ($sql_place as $row_place) { //予約日登録{couse_code=>day})
        $place_day[$s] = [$row_place['place_code'], $row_place['place_name']];
        $s += 1;
    }

    //検定項目MST分
    $s = 0; //カウントに使う
    foreach ($sql_course as $row_course) { //予約日登録{couse_code=>day})
        $z = 0;
        //[場所C,場所名,コースC,コース名,Sdat,Edat,人数（キー型）]
        for ($z; $z <= $rowCount_anser; $z++) {
            $course_day[$s] = [$place_day[$z][0], $place_day[$z][1], $row_course['course_code'], $row_course['course_name']];
            $s += 1;
        }
    }

    //志摩管理予定分
    $s = 0; //カウントに使う
    foreach ($sql_period as $row_period) { //予約日登録{couse_code=>day})
        $start_day_yobi = mb_substr($row_period['start_date'], 8, 2);
        $end_day_yobi = mb_substr($row_period['end_date'], 8, 2);
        if (mb_substr($start_day_yobi, 0, 1) === "0") {
            $start_day_yobi = mb_substr($start_day_yobi, 1, 1);
        }
        if (mb_substr($end_day_yobi, 0, 1) === "0") {
            $end_day_yobi = mb_substr($end_day_yobi, 1, 1);
        }
        //[場所C,場所名,コースC,コース名,Sdat,Edat,人数（キー型）]
        $start_day[$s] = [$row_period['place_code'], $row_period['place_name'], $row_period['course_code'], $row_period['course_name'],  $start_day_yobi, $end_day_yobi, $row_period['limited_num']];

        //人数を追加 10000+KEY(予約当日日:例3日が20人の場合）10003=>20（後ボタンに人数表示の際１万桁で見る）
        $limited_num_check = 0;
        $limited_num_check = 10000 + (int)$start_day[$s][4];
        $start_day[$s][6] = array($limited_num_check => $start_day[$s][6]);
        $start_day100[$s][6] = array($limited_num_check => $start_day[$s][6]);
        $s += 1;
    }

    //客予約分
    $s = 0; //カウントに使う
    foreach ($sql_reserve as $row_reserve) { //予約日登録{couse_code=>day})
        $start_day_yobi = mb_substr($row_reserve['start_date'], 8, 2);
        $end_day_yobi = mb_substr($row_reserve['end_date'], 8, 2);
        if (mb_substr($start_day_yobi, 0, 1) === "0") {
            $start_day_yobi = mb_substr($start_day_yobi, 1, 1);
        }
        if (mb_substr($end_day_yobi, 0, 1) === "0") {
            $end_day_yobi = mb_substr($end_day_yobi, 1, 1);
        }
        $reserve_day[$s] = [$row_reserve['place_code'], $row_reserve['place_name'], $row_reserve['course_code'], $row_reserve['course_name'],  $start_day_yobi, $end_day_yobi];
        $s += 1;
    }

    //人数を減らす
    $m = 0;
    foreach ($reserve_day as $reserve_day1) {
        foreach ($start_day as $start_day1) {
            if ($reserve_day1[0] === $start_day1[0] and $reserve_day1[2] === $start_day1[2] and $reserve_day1[4] === $start_day1[4]) {
                foreach ($start_day1[6] as $key => $start_day2)
                    $start_day[$m][6] = array($key => (int)$start_day2 - 1);
            }
            $m += 1;
        }
        $m = 0;
    }
    // データベース終わり**********************************************************************

    //$dayはfor関数で１日づつ増えていく
    for ($day = 1; $day <= $day_count; $day++, $youbi++) {
        // if ($day === 19) {
        //     $day = $day;
        // }

        //それぞれの日付をY-m-d形式で表示例：2020-01-23
        $date = $ym . '-' . $day; //"2021-12-1"

        //タイムスタンプを作成(表示例）：1638284400
        $timestamp = strtotime($ym . '-' . $day);

        //曜日を数字で取得 0:日 1:月 2:火 3:水 4:木 5:金 6:土
        $youbi = date('w',  $timestamp);

        //曜日作成
        youbi_create($youbi);

        $Holidays_day = display_to_Holidays(date("Y-m-d", strtotime($date)), $Holidays_array);
        //display_to_Holidays($date,$Holidays_array)の$dateに1/1~12/31の日付を入れる
        //比較してあったらdisplay_to_Holidaysメソッドによって$Holidays_array[$date]つまり$holidaysがreturnされる
        /*$reservation = reservation(date("Y-m-d", strtotime($date)), $reservation_array);*/

        $keisan = 0;
        $key_count = 0;
        $count = 0;
        $higawari = 0;
        $insert_day = 0; //月マタギの際の来月分追加数
        $coun1 = 0;

        //何日結合するか調べる（8日～10日の場合２つ）**********************************************
        for ($i = 0; $i < count($start_day); $i++) {
            $insert_day = 0;
            $count = $start_day[$i][5] - $start_day[$i][4]; //月マタギか判断
            if ($count < 0) { //月マタギなら
                $count2 = 7; //配列の７番目から日付入れる
                $higawari = $day_count - $start_day[$i][4]; //月末か月末の手前か判断
                if ($higawari === 0) { //月末の場合 1月なら31日とか
                    for ($z = 0; $z < $start_day[$i][5]; $z++) { //来月分だけ追加する
                        $coun1 += 1;
                        $start_day[$i][$count2] =  array($start_day[$i][4] => 1 + $coun1 - 1); //結合される部分作成(8日なら9日と10日が結合される
                        $count2 += 1;
                        $insert_day += 1;
                    }
                } else { //月末ではない場合 1月なら30日とか
                    if ($day === 29) {
                        $day = $day;
                    }
                    $count2 = 7; //配列の７番目から日付入れる
                    for ($z = 0; $z < $higawari; $z++) { //来月分と今月最終日まで追加する
                        $coun1 += 1;
                        $start_day[$i][$count2] =   array($start_day[$i][4] => $start_day[$i][4] + $coun1); //結合される部分作成(8日なら9日と10日が結合される
                        $count2 += 1;
                    }
                    $coun1 = 1;
                    for ($z = 0; $z <  $start_day[$i][5]; $z++) {
                        $start_day[$i][$count2] =  array($start_day[$i][4] => 1 + $coun1 - 1); //結合される部分作成(8日なら9日と10日が結合される
                        $count2 += 1;
                        $coun1 += 1;
                        $insert_day += 1;
                    }
                }
            } else { //月マタギではない
                $count2 = 7; //配列の７番目から結合する日付を入れる（startday=3,end=5なら4,5を追加）
                for ($z = 0; $z < $count; $z++) {
                    $coun1 += 1;
                    $start_day[$i][$count2] =  array($start_day[$i][4] => $start_day[$i][4] + $coun1); //結合される部分作成(8日なら9日と10日が結合される
                    $count2 += 1;
                }
            }
            $z = 0;
            $coun1 = 0;
        }
        //結合終わり********************************************************************************

        $skip_check = 0;
        $colspan_count = 0;
        $finish_key_count = 0;

        //無日付作成**************************************************************************
        $p = 0;
        $place_code_befor = 0;
        $course_code_befor = 0;
        $place_code_after = 0;
        $course_code_after = 0;
        $count5 = 0;

        //会場コードと検定コードが両方同じものの結合日付を１行にまとめる
        if ($ketugou_first === 0) {
            foreach ($start_day as $start_day1) {
                $ketugou_first = 1;
                $place_code_after = $start_day1[0];
                $course_code_after = $start_day1[2];
                if ($place_code_befor === 0 and $course_code_befor === 0) {
                    $start_day_final[$count5] = $start_day1;
                    $start_day_final_dummy[$count5] = $start_day1;
                    $place_code_befor =  $place_code_after;
                    $course_code_befor = $course_code_after;
                } else {
                    if ($place_code_after === $place_code_befor and $course_code_after === $course_code_befor) {
                        for ($k = 7; $k < count($start_day1); $k++) {
                            $start_day_final[$count5][count($start_day_final[$count5])] = $start_day1[$k];
                            $start_day_final_dummy[$count5][count($start_day_final_dummy[$count5])] = $start_day1[$k];
                        }
                        //人数を追加 10000+KEY(予約当日日:例3日が20人の場合）10003=>20（後ボタンに人数表示の際１万桁で見る）
                        // $limited_num_check = 10000 + (int)$start_day1[5];
                        $start_day_final[$count5][count($start_day_final[$count5])] = $start_day1[6];
                        $start_day_final_dummy[$count5][count($start_day_final_dummy[$count5])] = $start_day1[6];
                    } else { //コードが違うとき、次のインデックスに追加する
                        $count5 += 1;
                        $start_day_final[$count5] = $start_day1;
                        $start_day_final_dummy[$count5] = $start_day1;
                    }
                    $place_code_befor =  $place_code_after;
                    $course_code_befor = $course_code_after;
                }
            }
        }

        $limited_num_final = 0;

        if ($ccheck === 0) {
            for ($n = 0; $n < count($start_day_final_dummy); $n++) {
                $td_check[$n] = 0;
                $ccheck = 1;
            }
        }

        $final_count = 0;
        //$dayが結合される日付か確認する(0:飛ばさない 1:飛ばす)
        foreach ($start_day_final_dummy as $start_day1) {
            $ketugou_count = count($start_day1); //結合数を数える
            $skip_check = 0;    //スキップに使う変数の初期化(0:飛ばさない 1:飛ばす)
            $slice_array = array_slice($start_day1, 7);
            foreach ($slice_array as $start_day2) { //7=>3:4
                foreach ($start_day2 as $Key => $start_day3) { //7=>3:4
                    if ($day === $start_day3) { //dayと結合日を順番に照らし合わせる
                        if ($start_day3 - $Key > -1) { //月マタギの日付ではないなら作らない（1-31=-30)
                            $skip_check = 1; //次の月の日付なら1追加
                        }
                    }
                }
            }
            $reset = 0;

            //結合の日ではなかったらセル作成
            if ($skip_check === 0) { //結合される日付は飛ばす(0:飛ばさない 1:飛ばす)
                $yoyakubi_ari = 0;
                //この段階では予約のない日か予約日の先頭日付かどちらかになる
                $slice_array = array_slice($start_day1, 7);
                foreach ($slice_array as $start_day2) { //7=>3:4
                    $key_name = 0;
                    if ($yoyakubi_ari === 0) {
                        foreach ($start_day2 as $Key => $start_day3) { //7=>3:4
                            if ($day === $Key) { //予約日先頭かそうでないか判断する
                                $yoyakubi_ari = 1; //予約日先頭なら1を入れる
                                //キーの同じものの数をカウント $key=3なら3=>4と3=>5をとる
                                if ($day === 10) {
                                    $day = $day;
                                }
                                //結合日を取得
                                $hairetu_count = array_column($start_day1, $Key);
                                $finish_key_count = count($hairetu_count); //結合数を数える

                                // 2021/11/3～2021/11/5		
                                $month1 = $month;
                                $year1 = $year;
                                if (substr($month1, 0, 1) === "0") {
                                    $month1 = substr($month1, 1, 1);
                                }
                                $stsys2_start_day = $year . '/' . $month1 . '/' .  $Key;

                                //end_dayが月マタギなら来月にする、12月末ならyearを来年にする
                                if ($hairetu_count[count($hairetu_count) - 1] - $Key < 0) {
                                    //12月なら1月にして、来年にする
                                    if ($month === "12") {
                                        $month1 = 1;
                                        $year1 = $year + 1;
                                        $stsys2_end_day = $year1 . '/' .  $month1  . '/' . $hairetu_count[count($hairetu_count) - 1];

                                        //12月以外の月マタギは月を来月にする
                                    } else {
                                        $month1 += 1;
                                        $stsys2_end_day = $year1 . '/' .  $month1  . '/' . $hairetu_count[count($hairetu_count) - 1];
                                    }
                                } else {
                                    $stsys2_end_day = $year1 . '/' .  $month1  . '/' . (int)$Key + $finish_key_count;
                                }

                                $stsys2_day = $stsys2_start_day . '~' . $stsys2_end_day;

                                //人数表示する。
                                $reset = 1; //予約日先頭なら1を入れる
                                $slice_array1 = array_slice($start_day1, 6);
                                foreach ($slice_array1 as $Key => $start_day4) {
                                    foreach ($start_day4 as $Key => $start_day5) {
                                        if (strlen($Key) === 5) { //$KEYが10000桁なら人数である
                                            $enzan = $Key - 10000;
                                            if ($enzan === $day) { //予約日先頭かそうでないか判断する

                                                $limited_num_final = $start_day5;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // $button_name_count = 0;
            // $button_name = "";
            if ($reset === 1) { //予約日先頭ならセル結合を入れる
                // if ($day === 29) {
                //     $day = $day;
                // }

                //
                $button_value = "";
                $button_name = 'stsys1_click' . $button_name_count;
                $button_name_count += 1;
                $course_code_button = $start_day_final[$final_count][2]; //コースコード
                $course_name_button = $start_day_final[$final_count][3]; //コース名

                if ($button_name_count === 4) {
                    $day = $day;
                }
                if ((int)$limited_num_final === 0) { //登録人数が０ならボタン押せなくする
                    $button_value = '<button type="submit" onclick="ckBtn(this)" name="' . $button_name . '" value="0",' . $course_code_button . ',' . $course_name_button . ' disabled>x</button>';
                } else {
                    $button_value = '<button type="submit" onclick="ckBtn(this)" name="' . $button_name . '" value="' . $limited_num_final . ',' . $course_code_button . ',' . $course_name_button . ',' . $stsys2_day . '">' . $limited_num_final . '</button>';
                }
                //ボタン作成
                if ($td_check[$final_count] === 0) {
                    $start_day_final[$final_count][count($start_day_final[$final_count])] = '<td class="course_type" colspan="' . $finish_key_count + 1 . '">' . $button_value;
                } else {
                    $start_day_final[$final_count][count($start_day_final[$final_count]) - 1] .= '<td class="course_type" colspan="' . $finish_key_count + 1 . '">' . $button_value;
                }
                $td_check[$final_count]  = 1;
            } elseif ($reset === 0 and $skip_check === 0) { //予約日先頭でないから普通に作成
                // if ($day === 2) {
                //     $day = $day;
                // }
                if ($td_check[$final_count]  === 0) {
                    $start_day_final[$final_count][count($start_day_final[$final_count])] = '<td></td>';
                } else {
                    $start_day_final[$final_count][count($start_day_final[$final_count]) - 1] .= '<td></td>';
                }

                $td_check[$final_count] = 1;
            }
            $p += 1;
            $final_count += 1;
        }

        // 日付作成**********************************************************************************
        $week .= '<td>' . $day;
        $week .= '</td>';
        // *************************************************************************************************

    }

    //来月の日付がある際（１月なら２月１日とか）、曜日作成の必要があるため、一番多い日付を
    $youbi_raigetu = 0;
    $youbi_raigetu_backup = 0;
    foreach ($start_day_final as $start_day_final10) {
        $slice_array1 = array_slice($start_day_final10, 7);

        //<td>が含まれるとforeachエラーになるので最後の行はとらないように処理する
        $hairetu_count1 = count($slice_array1) - 1;
        $slice_array = array_slice($slice_array1, 0, $hairetu_count1);

        foreach ($slice_array as $key => $slice_array11) {
            foreach ($slice_array11 as $key => $slice_array12) {
                if (strlen($key) <> 5) { //$KEYが10000桁なら人数である
                    if ($slice_array12 - $key < 0) {
                        $youbi_raigetu += 1;
                    }
                }
            }
            //もし前の来月日付の数より大きければ$youbi_raigetu_backupに入れる（最終的に使うやつ）
            if ($youbi_raigetu > $youbi_raigetu_backup) {
                $youbi_raigetu_backup = $youbi_raigetu;
            }
        }
        $youbi_raigetu = 0;
    }
    for ($i = 1; $i <= $youbi_raigetu_backup; $i++) {
        //それぞれの日付をY-m-d形式で表示例：2020-01-23
        //タイムスタンプを作成(表示例）：1638284400
        if ($month === "12") {
            $year += 1;
            $month = 0;
        }
        $timestamp = strtotime($year . '-' . $month + 1 . '-' .  $i);
        //曜日を数字で取得 0:日 1:月 2:火 3:水 4:木 5:金 6:土
        $youbi = date('w',  $timestamp);

        youbi_create($youbi);

        $week .= '<td>' . $i;
        $week .= '</td>';
    }
    $youbi_raigetu = 0;
} catch (PDOException $e) {
    // エラー発生
    echo $e->getMessage();
} finally {
    // DB接続を閉じる
    $pdo = null;
    // header('location:\sales_details.php');
}

//志摩管理予定があるものだけ詳細情報を配列に入れる
$d = 0;
foreach ($course_day as  $course_day1) {
    foreach ($start_day_final as $start_day_final1) {
        // if ($d === 3) {
        //     $d = $d;
        // }
        if ($course_day1[0] === $start_day_final1[0] and $course_day1[2] === $start_day_final1[2]) {
            $course_day[$d] = $start_day_final1;
            break;
        }
    }
    $d += 1;
}

$td_ad = 0;
$td_already = 0;
//来月の日付がある際（１月なら２月１日とか）、無い検定項目のセルも追加する必要がある（空白になるため）
foreach ($course_day as $course_day2) {
    if (count($course_day2) > 4) {
        $slice_array1 = array_slice($course_day2, 7);
        //<td>が含まれるとforeachエラーになるので最後の行はとらないように処理する
        $hairetu_count1 = count($slice_array1) - 1;
        $slice_array = array_slice($slice_array1, 0, $hairetu_count1);

        foreach ($slice_array as $key => $slice_array11) {
            foreach ($slice_array11 as $key => $slice_array12) {
                if (strlen($key) <> 5) { //$KEYが10000桁なら人数であるので飛ばす
                    if ($slice_array12 - $key < 0) {
                        $youbi_raigetu += 1;
                    }
                }
            }
        }
        if ($youbi_raigetu < $youbi_raigetu_backup) {
            for ($i = 0; $i < $youbi_raigetu_backup - $youbi_raigetu; $i++) {
                $course_day[$td_ad][count($course_day[$td_ad]) - 1] .= '<td></td>';
            }
            $youbi_raigetu = 0;
        }
    }
    $youbi_raigetu = 0;
    $td_ad += 1;
}

function youbi_create($youbi)
{
    global $youbi_kana;
    global $count_youbi;
    switch ($youbi) {
        case 0:
            $youbi_kana .= '<th style="color:red";>日</th>';
            $count_youbi = $count_youbi + 1;
            break;
        case 1:
            $youbi_kana .= '<th>月</th>';
            $count_youbi = $count_youbi + 1;
            break;
        case 2:
            $youbi_kana .= '<th>火</th>';
            $count_youbi = $count_youbi + 1;
            break;
        case 3:
            $youbi_kana .= '<th>水</th>';
            $count_youbi = $count_youbi + 1;
            break;
        case 4:
            $youbi_kana .= '<th>木</th>';
            $count_youbi = $count_youbi + 1;
            break;
        case 5:
            $youbi_kana .= '<th>金</th>';
            $count_youbi = $count_youbi + 1;
            break;
        case 6:
            $youbi_kana .= '<th style="color:blue";>土</th>';
            $count_youbi = $count_youbi + 1;
            break;
        default:
            echo '曜日エラーです';
    }
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
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
    <a href="index.php">stsys01</a>
    <a href="stsys02.php">stsys02</a>
    <a href="stsys03.php">stsys03</a>
    <a href="stsys04.php">stsys04</a>


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
    <?php
    $f = 1;

    // echo '"stsys1_click' . $f . '"';
    ?>
    <!--曜日表示^---------------------------------------------------------------------------------------------------------------------------------------------------->
    <div class="container">
        <form method="post" action="stsys02.php">
            <script type="text/javascript">
                function ckBtn(button) {

                    //押されたボタンの予約可能人数を取得
                    var name = button.getAttribute('name');
                    var button_person = document.getElementsByName(name);
                    var button_person_list = [];
                    for (var i = 0; i < button_person.length; i++) {
                        button_person_list[i] = (button_person[i].value);
                    }
                    button_person_list[0] = button_person_list[0].split(','); // , 区切りで

                    //ユーザー選択人数を取得
                    var number_person = document.getElementsByName("number_person");
                    var number_person_list = [];
                    for (var i = 0; i < number_person.length; i++) {
                        number_person_list[i] = (number_person[i].value);
                    }

                    //人数チェック
                    if (button_person_list[0][0] - number_person_list[0] < 0) {
                        alert("登録可能人数を超えています");
                        event.preventDefault();
                    } else {

                        //ボタンの数をsession変数に入れる（javaの変数をphpで使うにはajaxを使う）
                        $.ajax({
                                type: "POST", //　GETでも可
                                url: "index.php", //　送り先
                                data: {
                                    'データ': '<?= $button_name_count - 1 ?>',
                                    // 'データ1': button_person_list[0][1],
                                    // 'データ2': button_person_list[0][2],
                                }, //　渡したいデータをオブジェクトで渡す
                                dataType: "json", //　データ形式を指定
                                scriptCharset: 'utf-8' //　文字コードを指定
                            })
                            .then(
                                function(param) { //　paramに処理後のデータが入って戻ってくる
                                    // console.log(param); //　帰ってきたら実行する処理
                                },
                                function(XMLHttpRequest, textStatus, errorThrown) { //　エラーが起きた時はこちらが実行される
                                    // console.log(XMLHttpRequest); //　エラー内容表示
                                });

                        <?php
                        $data = filter_input(INPUT_POST, 'データ'); // 送ったデータを受け取る（GETで送った場合は、INPUT_GET）
                        $_SESSION["button_person"] = $data;

                        // $data1 = filter_input(INPUT_POST, 'データ1'); // 送ったデータを受け取る（GETで送った場合は、INPUT_GET）
                        // $_SESSION["course_code"] = $data1;

                        // $data2 = filter_input(INPUT_POST, 'データ2'); // 送ったデータを受け取る（GETで送った場合は、INPUT_GET）
                        // $_SESSION["course_name"] = $data2;

                        $param = $data;
                        echo json_encode($param); //　echoするとデータを返せる（JSON形式に変換して返す）

                        ?>
                    }
                }
            </script>

            <table class="table table-bordered">
                <tr>
                    <!-- 人数を押されたら人数、コース名、期間をstsys２.phpに送る -->
                    <td>受講人数（※10名まで）</td>
                    <td><select name="number_person">
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

                <!-- 日付表示 -->
                <tr>
                    <td rowspan="2" colspan="2">
                        <?php
                        echo $prev_show;
                        ?>
                    </td>
                    <?php
                    echo $week;
                    ?>
                </tr>

                <!-- 曜日表示 -->
                <tr>
                    <?php
                    echo $youbi_kana;
                    ?>
                </tr>

                <!-- 会場表示 -->

                <?php
                $test_after = 0;
                $test_before = 0;

                //配列を会場コード順に並び替え
                $sort_key_team = array_column($course_day, 0);
                $sort_key_team1 = array_column($course_day, 2);
                //並べ替えのキー（ソートキー）とする項目を配列として取り出す

                //次の通りソートキーを指定して二次元配列を並び替える。
                //第一ソートキー：team（文字列の降順で並び替え）
                //第二ソートキー：age （数値の昇順で並び替え）
                //第三ソートキー：id  （文字列の昇順で並び替え）
                array_multisort(
                    $sort_key_team,
                    SORT_ASC,
                    SORT_STRING,
                    $sort_key_team1,
                    SORT_ASC,
                    SORT_STRING,
                    $course_day
                );

                //カレンダー表示
                foreach ($course_day as $course_day3) {
                    //予約ないやつは非表示
                    if (count($course_day3) < 5) {
                    } else {
                        $test_after = $course_day3[0];
                        if ($test_before === 0) {
                            $test_after = $course_day3[0];
                            $test_name = $course_day3[1];

                            //会場表示
                            echo '  <tr class="area-tr"><td colspan="' .  $count_youbi + 2 . '">' . $test_name  . '</td> </tr>';
                            $test_before = $test_after;
                            //検定名表示
                            echo '<tr> <td colspan="2"><h3 style="font-size: 1.0rem;">' . $course_day3[3] . '</h3> </td>';
                            //日付表示
                            echo $course_day3[count($course_day3) - 1] . '</tr>';
                        } elseif ($test_after <> $test_before) {
                            $test_name = $course_day3[1];
                            //会場表示
                            echo '  <tr class="area-tr"><td colspan="' .  $count_youbi + 2 . '">' . $test_name  . '</td> </tr>';
                            $test_before = $test_after;
                            //検定名表示
                            echo '<tr> <td colspan="2"><h3 style="font-size: 1.0rem;">' . $course_day3[3] . '</h3> </td>';
                            //日付表示
                            echo $course_day3[count($course_day3) - 1] . '</tr>';
                        } else {
                            if ($test_after <> $test_before) { //最初ではなくて、違う場合
                                //会場表示
                                echo '  <tr class="area-tr"><td colspan="' .  $count_youbi + 2 . '">' . $test_name  . '</td> </tr>';
                                $test_before = $test_after;
                                //検定名表示
                                echo '<tr> <td colspan="2"><h3 style="font-size: 1.0rem;">' . $course_day3[3] . '</h3> </td>';
                                //日付表示
                                echo $course_day3[count($course_day3) - 1] . '</tr>';
                            } else { //最初ではなくて、一緒の場合
                                //検定名表示
                                echo '<tr> <td colspan="2"><h3 style="font-size: 1.0rem;">' . $course_day3[3] . '</h3> </td>';
                                //日付表示
                                echo $course_day3[count($course_day3) - 1] . '</tr>';
                                $test_before = $test_after;
                            }
                        }
                    }
                }
                ?>
            </table>
        </form>
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
