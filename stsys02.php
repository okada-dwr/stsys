<?php
session_start();
?>

<?php
//直リンクされた場合index.phpにリダイレクト
// if ($_SERVER["REQUEST_METHOD"] != "POST") {
//     header("Location: index.php");
//     exit();
// }
$button_name_count = 0;
$course_sub_code = [];
$button_name_count = $_SESSION["button_person"];
$money = [];
$paper = [];
$sex = [];
$money_final = 0;
$paper_final = 0;
$sex_final = 0;
$button_value = [];
$course_sub_code_check = "";
$_SESSION["mode"]  = "input";
try {
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
    $paper_final = "";
    $money_final = "";
    $sex_final = "";
    $course_sub_code_final = "";

    //$bu~countはbuttonの数、fが押されたボタンのNoになればそのstsys1_click〇のデータを読み取り開始
    for ($f = 0; $f < $button_name_count + 1; $f++) {
        if (isset($_POST["stsys1_click" . $f])) {
            $_SESSION["error"] = ""; //ボタンが押されて遷移した時はエラーを消す
            $_SESSION["mode"]  = "";
            $_SESSION["number_person"] = $_POST["number_person"]; //確認メッセージに対する答えを取得

            //[0]受講可能人数、[1]コースコード、[2]コース名、[3]日付、[4]場所コード、[5]場所名（$button_value）
            $_SESSION["button_value"] = explode(",", $_POST["stsys1_click" . $f]); // , 区切りで

            //助成金
            $sql_money = $pdo->prepare("SELECT * FROM com_code_mst WHERE project_name='stsys' AND del_flg=0 AND Flg_section=0 ORDER BY no,flg_section,Flg_code");
            $sql_money->execute();

            //申込書の取得
            $sql_paper = $pdo->prepare("SELECT * FROM com_code_mst WHERE project_name='stsys' AND del_flg=0 AND Flg_section=1 ORDER BY no,flg_section,Flg_code");
            $sql_paper->execute();

            //性別
            $sql_sex = $pdo->prepare("SELECT * FROM com_code_mst WHERE project_name='stsys' AND del_flg=0 AND Flg_section=2 ORDER BY no,flg_section,Flg_code");
            $sql_sex->execute();

            //検定コードサブ取得
            $sql_course = $pdo->prepare('SELECT course_sub_code,course_sub_name FROM st_course_mst WHERE course_code=:course_code AND del_flg=0 ORDER BY course_sub_code');
            $sql_course->bindValue(':course_code', $_SESSION["button_value"][1]);
            $sql_course->execute();

            //申込書作成
            $s = 0;
            foreach ($sql_paper as $sql_paper1) {
                $paper[$s] = [$sql_paper1["Flg_code"], $sql_paper1["Flg_name"]];
                $s += 1;
            }

            //申込書の数だけプルダウン作る
            // $_SESSION["paper_final"] = "";
            $paper_final = "";
            foreach ($paper as $paper1) {
                // $_SESSION["paper_final"] .= '<option value="' . $paper1[0] .  "," . $paper1[1] . '">' . $paper1[1] . '</option>';
                $paper_final .= '<option value="' . $paper1[0] .  "," . $paper1[1] . '">' . $paper1[1] . '</option>';
            }

            //助成金作成
            $s = 0;
            foreach ($sql_money as $sql_money1) {
                $money[$s] = [$sql_money1["Flg_code"], $sql_money1["Flg_name"]];
                $s += 1;
            }
            //助成金の数だけプルダウン作る
            // $_SESSION["money_final"] = "";
            $money_final = "";
            foreach ($money as $money1) {
                // $_SESSION["money_final"] .= '<option value="' . $money1[0] . "," . $money1[1] . '">' . $money1[1] . '</option>';
                $money_final .= '<option value="' . $money1[0] . "," . $money1[1] . '">' . $money1[1] . '</option>';
            }

            //性別作成
            $s = 0;
            foreach ($sql_sex as $sql_sex1) {
                $sex[$s] = [$sql_sex1["Flg_code"], $sql_sex1["Flg_name"]];
                $s += 1;
            }
            //性別の数だけプルダウン作る
            // $_SESSION["sex_final"] = "";
            $sex_final = "";
            foreach ($sex as $sex1) {
                // $_SESSION["sex_final"] .= '<option value="' . $sex1[0] . '">' . $sex1[1] . '</option>';
                $sex_final .= '<option value="' . $sex1[0] . "," . $sex1[1] . '">' . $sex1[1] . '</option>';
            }


            //検定コードの種類作成 K1,K2等
            $s = 0;
            foreach ($sql_course as $sql_course1) {
                $course_sub_code[$s] = [$sql_course1["course_sub_code"], $sql_course1["course_sub_name"]];
                $s += 1;
            }

            //検定コードの種類の数だけプルダウン作る
            // $_SESSION["course_sub_code_final"] = "";
            $course_sub_code_final = "";
            foreach ($course_sub_code as $course_sub_code1) {
                // $_SESSION["course_sub_code_final"] .= '<option value="' . $course_sub_code1[0] . "," . $course_sub_code1[1] . '">' . $course_sub_code1[1] . '</option>';
                $course_sub_code_final .= '<option value="' . $course_sub_code1[0] . "," . $course_sub_code1[1] . '">' . $course_sub_code1[1] . '</option>';
            }
            $course_sub_code_check = $course_sub_code[0][0];
        }
    }
    $date = [];
    //データベース書き込み
    //申請ボタン押下時
    try {
        if (isset($_POST['request'])) {

            //データベース書き込みを人数分作成（６人なら６行登録）
            $mail_flg_array = $_POST['mail_flg']; //申請書
            $mail_flg_array = explode(",", $mail_flg_array);
            $mail_flg = $mail_flg_array[0];
            if ($mail_flg === "") {
                header("Location: ./stsys02.php");
                $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※申込書郵送の有無を入力してください</span>";
            } else {
                $mail_flg_name = $mail_flg_array[1];
                $inc_name = $_POST['inc_name'];
                if ($inc_name === "") {
                    header("Location: ./stsys02.php");
                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※会社名または個人情報を入力してください</span>";
                } else {
                    $app_name = $_POST['app_name'];
                    if ($app_name === "") {
                        header("Location: ./stsys02.php");
                        $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※申込者氏名を入力してください</span>";
                    } else {
                        $app_name_kana = $_POST['app_name_kana'];
                        if ($app_name_kana === "") {
                            header("Location: ./stsys02.php");
                            $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※申込者氏名フリガナを入力してください</span>";
                        } else {
                            $post_no = $_POST['post_no'];
                            if ($post_no === "") {
                                header("Location: ./stsys02.php");
                                $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※郵便番号を入力してください</span>";
                            } else {
                                $address1 = $_POST['address1'];
                                if ($address1 === "") {
                                    header("Location: ./stsys02.php");
                                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※住所1(番地まで)を入力してください</span>";
                                } else {
                                    $address2 = $_POST['address2'];
                                    $tel = $_POST['tel'];
                                    if ($tel === "") {
                                        header("Location: ./stsys02.php");
                                        $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※電話番号(会社または個人)を入力してください</span>";
                                    } else {
                                        $fax = $_POST['fax'];
                                        $mail_address = $_POST['mail_address'];
                                        $sub_flg_array = $_POST['sub_flg']; //助成金
                                        $sub_flg_array = explode(",", $sub_flg_array);
                                        $sub_flg = $sub_flg_array[0];
                                        if ($sub_flg === "") {
                                            header("Location: ./stsys02.php");
                                            $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※助成金の有無を入力してください</span>";
                                        } else {
                                            $sub_flg_name = $sub_flg_array[1];
                                            $course_code = $_SESSION["button_value"][1];
                                            $course_name = $_SESSION["button_value"][2];
                                            $place_code = $_SESSION["button_value"][4];
                                            $place_name = $_SESSION["button_value"][5];
                                            $date = explode("~", $_SESSION["button_value"][3]);
                                            $start_date = $date[0];
                                            $end_date = $date[1];

                                            // SQL文をセット
                                            //データベース書き込みを人数分作成（６人なら６行登録）
                                            for ($i = 0; $i < $_SESSION["number_person"]; $i++) {
                                                $stmt = $pdo->prepare('INSERT INTO st_reserve (mail_flg,mail_flg_name,inc_name,app_name,app_name_kana,' .
                                                    'post_no,address1,address2,tel,fax,mail_address,sub_flg,sub_flg_name,students,birthday,sex,sex_name,' .
                                                    'kojin_tel,phone_tel,kojin_post_no,kojin_address,course_code,course_name,' .
                                                    'course_sub_code,course_sub_name,place_code,place_name,start_date,end_date)' .
                                                    'VALUES(:mail_flg,:mail_flg_name,:inc_name,:app_name,:app_name_kana,:post_no,:address1,:address2,' .
                                                    ':tel,:fax,:mail_address,:sub_flg,:sub_flg_name,:students,:birthday,:sex,:sex_name,:kojin_tel,:phone_tel,' .
                                                    ':kojin_post_no,:kojin_address,:course_code,:course_name,:course_sub_code,:course_sub_name,' .
                                                    ':place_code,:place_name,' .
                                                    ':start_date,:end_date)');

                                                $students = $_POST['students' . $i];
                                                if ($students === "") {
                                                    header("Location: ./stsys02.php");
                                                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※" . ($i + 1) . "行目の受講者氏名を入力してください</span>";
                                                    break;
                                                }
                                                $birthday = $_POST['birthday' . $i];
                                                if ($birthday === "") {
                                                    header("Location: ./stsys02.php");
                                                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※" . ($i + 1) . "行目の生年月日を入力してください</span>";
                                                    break;
                                                }
                                                $sex_array = $_POST['sex' . $i];
                                                $sex_array = explode(",", $sex_array);
                                                $sex = $sex_array[0];
                                                if ($sex === "") {
                                                    header("Location: ./stsys02.php");
                                                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※" . ($i + 1) . "行目の性別を入力してください</span>";
                                                    break;
                                                }
                                                $sex_name = $sex_array[1];
                                                $kojin_tel = $_POST['kojin_tel' . $i];
                                                if ($kojin_tel === "") {
                                                    header("Location: ./stsys02.php");
                                                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※" . ($i + 1) . "行目の個人電話番号を入力してください</span>";
                                                    break;
                                                }
                                                $phone_tel = $_POST['phone_tel' . $i];
                                                $kojin_post_no = $_POST['kojin_post_no' . $i];
                                                if ($kojin_post_no === "") {
                                                    header("Location: ./stsys02.php");
                                                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※" . ($i + 1) . "行目の住所を入力してください</span>";
                                                    break;
                                                }
                                                $kojin_address = $_POST['kojin_address' . $i];
                                                if ($kojin_address  === "") {
                                                    header("Location: ./stsys02.php");
                                                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※" . ($i + 1) . "行目の住所を入力してください</span>";
                                                    break;
                                                }

                                                $course_sub_code_array = $_POST['course_sub_code' . $i];
                                                //希望コースが選択されている場合


                                                if ($course_sub_code_array <> "") {
                                                    $course_sub_code_array = explode(",", $course_sub_code_array);
                                                    $course_sub_code_insert = $course_sub_code_array[0];
                                                    if (count($course_sub_code_array) === 2) {
                                                        $course_sub_name = $course_sub_code_array[1];
                                                    } else {
                                                        $course_sub_name = "";
                                                    }
                                                } else { //99（安全教育）の場合
                                                    header("Location: ./stsys02.php");
                                                    $_SESSION["error"] = "エラーメッセージスペース　：　<span class=color_red>※" . ($i + 1) . "行目の希望コースを入力してください</span>";
                                                    break;
                                                }

                                                $stmt->bindValue(':mail_flg', $mail_flg);
                                                $stmt->bindValue(':mail_flg_name', $mail_flg_name);
                                                $stmt->bindValue(':inc_name', $inc_name);
                                                $stmt->bindValue(':app_name', $app_name);
                                                $stmt->bindValue(':app_name_kana', $app_name_kana);
                                                $stmt->bindValue(':post_no', $post_no);
                                                $stmt->bindValue(':address1', $address1);
                                                $stmt->bindValue(':address2', $address2);
                                                $stmt->bindValue(':tel', $tel);
                                                $stmt->bindValue(':fax', $fax);
                                                $stmt->bindValue(':mail_address', $mail_address);
                                                $stmt->bindValue(':sub_flg', $sub_flg);
                                                $stmt->bindValue(':sub_flg_name', $sub_flg_name);
                                                $stmt->bindValue(':students', $students);
                                                $stmt->bindValue(':birthday', $birthday);
                                                $stmt->bindValue(':sex', $sex);
                                                $stmt->bindValue(':sex_name', $sex_name);
                                                $stmt->bindValue(':kojin_tel', $kojin_tel);
                                                $stmt->bindValue(':phone_tel', $phone_tel);
                                                $stmt->bindValue(':kojin_post_no', $kojin_post_no);
                                                $stmt->bindValue(':kojin_address', $kojin_address);
                                                $stmt->bindValue(':course_code', $course_code);
                                                $stmt->bindValue(':course_name', $course_name);
                                                $stmt->bindValue(':course_sub_code', $course_sub_code_insert);
                                                $stmt->bindValue(':course_sub_name', $course_sub_name);
                                                $stmt->bindValue(':place_code', $place_code);
                                                $stmt->bindValue(':place_name', $place_name);
                                                $stmt->bindValue(':start_date', $start_date);
                                                $stmt->bindValue(':end_date', $end_date);

                                                // SQL実行
                                                $stmt->execute();
                                                $_SESSION["mode"]  = "send";
                                                header("Location: ./stsys02.php");
                                                $course_sub_code_check = "99";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    //insertデータベースエラー
    catch (PDOException $e) {
        // エラー発生
        echo $e->getMessage();
    } finally {
        // DB接続を閉じる
        $pdo = null;
    }
    //readデータベースエラー
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
    <meta charset=" UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
    <link rel="stylesheet" href="/style2.css">
</head>

<body>
    <a href="index.php">stsys01</a>
    <a href="stsys02.php">stsys02</a>
    <a href="stsys03.php">stsys03</a>
    <a href="stsys04.php">stsys04</a>

    <div class="main_container">
        <div class="title1">
            <h1 style="text-align: center;text-decoration:underline;">技能講習予約申込画面</h1>
            <div class="explanation">
                <p class="explanation_text">下記は必ず一読お願いいたします。</p>
                <p>➀&nbsp;<span class="underline_black">下記</span><span class="underline_red">赤字の必須項目</span>を入力または選択し、<span class="color_black">「登録」ボタン</span>を押下してください。</p>
                <p>➁&nbsp;<span class="underline_black">自動で入力されている項目「希望コース」「受講日」</span>は、<span class="color_red">ご確認だけお願いします。</span></p>
                <p>➂<span class="color_black">「申込書郵送の有無」項目</span>について、<span class="color_black">「有」の場合</span>、受講者人数分以下の<span class="underline_red">「受講申込書」および「技能講習のご案内」</span>用紙をこちらから郵送します。<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;到着しましたら、各項目に記入していただき、「郵送」か「FAX」をお願いいたします。<span class="color_black">当日持参いただくようお願いいたします。</span><br>
                    &nbsp;&nbsp;<span class="color_black">「無」の場合、④を参照してください。</span></p>
                <p>➃<span class="color_black">「登録」ボタン押下後、</span><span class="underline_red">「受講申込書」</span><span class="color_black">と</span><span class="color_red">「技能講習のご案内(各コース)」</span><span class="color_black">がPDF形式でダウンロードされます。</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;ダウンロードした用紙を<span class="underline_red">印刷</span>していただき<span class="underline_red">空いている項目を入力後</span>、<span class="color_black">証明写真を貼り付けたうえ</span><span class="underline_black">「郵送」</span>か<span class="underline_black">「FAX」</span><span class="color_black">をお願いいたします。</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;<span class="underline_black">原本については、当日持参いただくようお願いいたします。</span></p>
                </p>
            </div>
        </div>

        <!-- エラーメッセージがなければ非表示 -->
        <?php
        if (!empty($_SESSION["error"]))
            echo "<div id=error_message>" .
                $_SESSION["error"] . "</div>";
        ?>

        <form action="stsys02.php" method="POST">
            <!-- 会社情報入力 -->
            <table class="table_01">
                <tr>
                    <th><span class="color_red">申込書郵送の有無</span></th>
                    <td><select name="mail_flg">
                            <?php echo '<option value=""></option>' . $paper_final ?>
                        </select>※受講申込書の郵送を希望される方は、有を選択。(スマホしかない…など、印刷環境がない方用です)
                    </td>
                </tr>
                <tr>
                    <td><span class="color_red">会社名または個人情報<br>
                            ※【会社名】または「個人名」を入力してください</span></td>
                    <td><input type="text" name="inc_name" placeholder="例） 志摩機械 株式会社">
                    </td>
                </tr>
                <tr>
                    <td><span class="color_red">申込者氏名</span></td>
                    <td><input type="text" name="app_name" placeholder="例）志摩太郎">
                    </td>
                </tr>
                <tr>
                    <td><span class="color_red">申込者氏名<br class="view_sp">フリガナ</span></td>
                    <td><input type="text" name="app_name_kana" placeholder="例）シマタロウ">
                    </td>
                </tr>
                <tr>
                    <td><span class="color_red">郵便番号<br class="view_sp">(会社または個人)</span></td>
                    <td>
                        <input type="text" name="post_no" ime-mode:disabled maxlength="8" placeholder="例）6240951" onKeyUp=" AjaxZip3.zip2addr(this,'','adress','adress');">
                        <!--〒<input type="text" size="8" maxlength="3" placeholder="例）624>-<input type=" text" size="8" maxlength="4" placeholder="例）0951">-->
                    </td>
                </tr>
                <tr>
                    <td><span class="color_red">住所1<br class="view_sp">(番地まで)</span></td>
                    <td>
                        <input type="text" name="address1" placeholder="例）京都府舞鶴市上福井117番地">
                        <!--〒<input type="text" size="8" placeholder="例）京都府舞鶴市上福井１１７">-->
                    </td>
                </tr>
                <tr>
                    <td>住所2<br class="view_sp">(マンション名)</td>
                    <td>
                        <input type="text" name="address2" placeholder="例）京都府舞鶴市上福井117番地">
                        <!--〒<input type="text" size="8" placeholder="例）京都府舞鶴市上福井１１７">-->
                    </td>
                </tr>
                <tr>
                    <td><span class="color_red">電話番号<br class="view_sp">(会社または個人)</span></td>
                    <td>
                        <input type="text" name="tel" placeholder="例）0773750652">
                    </td>
                </tr>
                <tr>
                    <td>FAX番号<br class="view_sp">(会社または個人)</td>
                    <td>
                        <input type="text" name="fax" placeholder="例）0773755591">
                    </td>
                </tr>
                <tr class="last">
                    <td>mailアドレス</td>
                    <td>
                        <input type="text" name="mail_address" placeholder="例）xxxx@gmail.com">
                    </td>
                </tr>
            </table>
            <table class="table_01 table_subsidy">
                <tr>
                    <td><span class="color_red">助成金の有無</span></td>
                    <td><select name="sub_flg">
                            <?php echo '<option value=""></option>' . $money_final ?>
                        </select>※助成金を受けるかの有無を選択してください。<br class="view_sp">(有：受ける　無：受けない)
                    </td>
                </tr>
            </table>
            <!-- 受講者入力 -->
            <div class="table_02_main">
                <table class="table_02">
                    <tr>
                        <th><span class="color_red">受講者氏名</span></th>
                        <th><span class="color_red">生年月日</span></th>
                        <th><span class="color_red">性別</span></th>
                        <th><span class="color_red">個人電話番号</span></th>
                        <th>携帯番号</th>
                        <th colspan="2"><span class="color_red">住所</span></th>
                        <th>講習内容</th>
                        <th><span class="color_red">希望コース</span></th>
                        <th>受講期間</th>
                    </tr>
                    <?php
                    //人数分テキストボックスを表示する。

                    if ($course_sub_code_check === "99") { //サブコードが９９ならセレクト出さない
                        for ($i = 0; $i < $_SESSION["number_person"]; $i++) {
                            echo '<tr>
                            <td><input name="students' . $i . '" type=text placeholder=例）志摩太郎></td>
                            <td><input name="birthday' . $i . '" type=text placeholder=例）生年月日></td>
                            <td><select name="sex' . $i . '"><option value=""></option>' . $sex_final . '
                            <td><input name="kojin_tel' . $i . '" type=text placeholder=例）個人電話番号></td>
                            <td><input name="phone_tel' . $i . '" type=text placeholder=例）携帯番号></td>
                            <td><input type=text name="kojin_post_no' . $i . '" size="10" ime-mode:disabled maxlength="8" placeholder="例）6240951" onKeyUp=" AjaxZip3.zip2addr(this,"","adress","adress");></td>
                            <td><input name="kojin_address' . $i . '" type=text placeholder=例）住所></td>
                            <td>' . $_SESSION["button_value"][2] . '</td> 
                            <td><select  id="course_sub_code" disabled="disabled"  name="course_sub_code' . $i . '"><option value="99"></option>
                            <td>' . $_SESSION["button_value"][3] . '</td>
                        </tr>';
                        }
                    } else {
                        for ($i = 0; $i < $_SESSION["number_person"]; $i++) {
                            echo '<tr>
                        <td><input name="students' . $i . '" type=text placeholder=例）志摩太郎></td>
                        <td><input name="birthday' . $i . '" type=text placeholder=例）生年月日></td>
                        <td><select name="sex' . $i . '"><option value=""></option>' . $sex_final . '
                        <td><input name="kojin_tel' . $i . '" type=text placeholder=例）個人電話番号></td>
                        <td><input name="phone_tel' . $i . '" type=text placeholder=例）携帯番号></td>
                        <td><input type=text name="kojin_post_no' . $i . '" size="10" ime-mode:disabled maxlength="8" placeholder="例）6240951" onKeyUp=" AjaxZip3.zip2addr(this,"","adress","adress");></td>
                        <td><input name="kojin_address' . $i . '" type=text placeholder=例）住所></td>
                        <td>' . $_SESSION["button_value"][2] . '</td> 
                        <td><select id="course_sub_code" name="course_sub_code' . $i . '"><option value=""></option>' . $course_sub_code_final . '
                        <td>' . $_SESSION["button_value"][3] . '</td>
                    </tr>';
                        }
                    }
                    ?>
                </table>
            </div>
            <div class="example-r">
                <button type="submit" name="request" onclick="javascript:undisabled();">申請</button>

                <script type="text/javascript">
                    function undisabled() {
                        document.getElementById("course_sub_code").disabled = false;
                        return true;
                    }
                </script>

                <!-- <button type="submit"><a href="pdf/sample.pdf" name="request" download="sample.pdf">申請</a></button> -->
                <button><a href="index.php">戻る</a></button>
            </div>
            <?php
            if ($_SESSION["mode"] === "send") {
                echo '<p class=message_box>送信しました。お問い合わせありがとうございます。</p>';
            }
            ?>
        </form>
        <p>●「受講申込書」および「技能講習のご案内」</p>
        <p>「受講申込書」および「技能講習のご案内」のイメージです。
        <p>●登録ボタン押下後の処理</p>
        <p> ⇒ダウンロードする資料は受講者分ダウンロードされる(5人なら5枚)</p>
        <div class="explanation_image">
            <div class="explanation_image_child">
                <div class="explanation_image_text">
                    <p><span class="color_red">技能講習の場合<br class="view_sp">(上記、入力情報の場合)</span><br>
                        表面
                    </p>
                </div>
                <img src="img/stsys2_1.PNG" alt="受講申込書">
            </div>
            <div class="explanation_image_child">
                <div class="explanation_image_text">
                    <p><span class="color_red">特別教育受講申込書の場合</span><br>
                        クレーンの場合
                    </p>
                </div>
                <img src="img/stsys2_2.PNG" alt="特別教育受講申込書">
            </div>
            <div class="explanation_image_child">
                <div class="explanation_image_text">
                    <p>裏面</p>
                </div>
                <img src="img/stsys2_3.PNG" alt="特別教育実施証明書">
            </div>
            <div class="explanation_image_child">
                <div class="explanation_image_text">
                    <p><span class="color_red">各コースのご案内(小型移動式クレーン K1の場合)<br>
                            ⇒コースが混在の場合、2枚発行</spna>
                    </p>
                </div>
                <img src="img/stsys2_4.PNG" alt="小型移動式クレーン運転技能講習のご案内（K1コース）">
            </div>
        </div>
    </div>
</body>

