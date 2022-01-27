<?php
session_start();
?>

<?php
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
    for ($f = 0; $f < $button_name_count + 1; $f++) {
        if (isset($_POST["stsys1_click" . $f])) {
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

            //助成金作成
            $s = 0;
            foreach ($sql_money as $sql_money1) {
                $money[$s] = [$sql_money1["Flg_code"], $sql_money1["Flg_name"]];
                $s += 1;
            }
            //助成金の数だけプルダウン作る
            $_SESSION["money_final"] = "";
            foreach ($money as $money1) {
                $_SESSION["money_final"] .= '<option value="' . $money1[0] . '">' . $money1[1] . '</option>';
            }


            //申込書作成
            $s = 0;
            foreach ($sql_paper as $sql_paper1) {
                $paper[$s] = [$sql_paper1["Flg_code"], $sql_paper1["Flg_name"]];
                $s += 1;
            }

            //申込書の数だけプルダウン作る
            $_SESSION["paper_final"] = "";
            foreach ($paper as $paper1) {
                $_SESSION["paper_final"] .= '<option value="' . $paper1[0] . '">' . $paper1[1] . '</option>';
            }


            //性別作成
            $s = 0;
            foreach ($sql_sex as $sql_sex1) {
                $sex[$s] = [$sql_sex1["Flg_code"], $sql_sex1["Flg_name"]];
                $s += 1;
            }
            //性別の数だけプルダウン作る
            $_SESSION["sex_final"] = "";
            foreach ($sex as $sex1) {
                $_SESSION["sex_final"] .= '<option value="' . $sex1[0] . '">' . $sex1[1] . '</option>';
            }


            //検定コードサブ取得
            $sql_course = $pdo->prepare('SELECT course_sub_code,course_sub_name FROM st_course_mst WHERE course_code=:course_code AND del_flg=0 ORDER BY course_sub_code');
            $sql_course->bindValue(':course_code', $_SESSION["button_value"][1]);
            $sql_course->execute();

            //検定コードの種類作成 K1,K2等
            $s = 0;
            foreach ($sql_course as $sql_course1) {
                $course_sub_code[$s] = [$sql_course1["course_sub_code"], $sql_course1["course_sub_name"]];
                $s += 1;
            }

            //検定コードの種類の数だけプルダウン作る
            $_SESSION["course_sub_code_final"] = "";
            foreach ($course_sub_code as $course_sub_code1) {
                $_SESSION["course_sub_code_final"] .= '<option value="' . $course_sub_code1[0] . '">' . $course_sub_code1[1] . '</option>';
            }
        }
    }
    $date = [];
    //データベース書き込み
    try {
        if (isset($_POST['request'])) {

            $mail_flg = $_POST['mail_flg'];
            $inc_name = $_POST['inc_name'];
            $app_name = $_POST['app_name'];
            $app_name_kana = $_POST['app_name_kana'];
            $post_no = $_POST['post_no'];
            $address1 = $_POST['address1'];
            $address2 = $_POST['address2'];
            $tel = $_POST['tel'];
            $fax = $_POST['fax'];
            $mail_address = $_POST['mail_address'];
            $sub_flg = $_POST['sub_flg'];
            $students = $_POST['students'];
            $birthday = $_POST['birthday'];
            $sex = $_POST['sex'];
            $kojin_tel = $_POST['kojin_tel'];
            $phone_tel = $_POST['phone_tel'];
            $kojin_post_no = $_POST['kojin_post_no'];
            $kojin_address = $_POST['kojin_address'];
            $course_code = $_SESSION["button_value"][1];
            $course_name = $_SESSION["button_value"][2];
            $place_code = $_SESSION["button_value"][4];
            $place_name = $_SESSION["button_value"][5];
            $date = explode("~", $_SESSION["button_value"][3]);
            $start_date = $date[0];
            $end_date = $date[1];

            // SQL文をセット
            $stmt = $pdo->prepare('INSERT INTO st_reserve (mail_flg,inc_name,app_name,app_name_kana,' .
                'post_no,address1,address2,tel,fax,mail_address,sub_flg,students,birthday,sex,' .
                'kojin_tel,phone_tel,kojin_post_no,kojin_address,course_code,course_name,' .
                'place_code,place_name,start_date,end_date)' .
                'VALUES(:mail_flg,:inc_name,:app_name,:app_name_kana,:post_no,:address1,:address2,' .
                ':tel,:fax,:mail_address,:sub_flg,:students,:birthday,:sex,:kojin_tel,:phone_tel,' .
                ':kojin_post_no,:kojin_address,:course_code,:course_name,:place_code,:place_name,' .
                ':start_date,:end_date)');
            $stmt->bindValue(':mail_flg', $mail_flg);
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
            $stmt->bindValue(':students', $students);
            $stmt->bindValue(':birthday', $birthday);
            $stmt->bindValue(':sex', $sex);
            $stmt->bindValue(':kojin_tel', $kojin_tel);
            $stmt->bindValue(':phone_tel', $phone_tel);
            $stmt->bindValue(':kojin_post_no', $kojin_post_no);
            $stmt->bindValue(':kojin_address', $kojin_address);
            $stmt->bindValue(':course_code', $course_code);
            $stmt->bindValue(':course_name', $course_name);
            $stmt->bindValue(':place_code', $place_code);
            $stmt->bindValue(':place_name', $place_name);
            $stmt->bindValue(':start_date', $start_date);
            $stmt->bindValue(':end_date', $end_date);

            // SQL実行
            $stmt->execute();
            $_SESSION["mode"]  = "send";
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
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <a href="index.php">stsys01</a>
    <a href="stsys02.php">stsys02</a>
    <a href="stsys03.php">stsys03</a>
    <a href="stsys04.php">stsys04</a>

    <body id=""> 　　　　
        　　　　<div class="title1">
            <h1 style="text-align: center;text-decoration:underline;">技能講習予約申込画面</h1>
            <div class="explanation" style="margin-left: 80px;margin-right: 80px;border:1px solid;padding-left:5px;">
                <p>下記は必ず一読お願いいたします。</p>
                <p>➀&nbsp;下記<span>赤字の必須項目</span>を入力または選択し、「登録」ボタンを押下してください。</p>
                <p>➁&nbsp;自動で入力されている項目「希望コース」「受講日」は、<span>ご確認だけお願いします。</span></p>
                <p>➂「申込書郵送の有無」項目について、「有」の場合、受講者人数分以下の<span>「受講申込書」および「技能講習のご案内」</span>用紙をこちらから郵送します。<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;到着しましたら、各項目に記入していただき、「郵送」か「FAX」をお願いいたします。当日持参いただくようお願いいたします。<br>
                    &nbsp;&nbsp;「無」の場合、④を参照してください。</p>
                <p>➃「登録」ボタン押下後、<span>「受講申込書」と「技能講習のご案内(各コース)」</span>がPDF形式でダウンロードされます。<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;ダウンロードした用紙を<span>印刷</span>していただき<span>空いている項目を入力後</span>、証明写真を貼り付けたうえ「郵送」か「FAX」をお願いいたします。<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;原本については、当日持参いただくようお願いいたします。</p>
                </p>
            </div>
        </div>

        <form action="stsys02.php" method="POST">
            <table class="table_01">
                <tr>
                    <th>申込書郵送の有無</th>
                    <td><select name="mail_flg">
                            <?php echo '<option value=""></option>' . $_SESSION["paper_final"] ?>
                        </select>※受講申込書の郵送を希望される方は、有を選択。(スマホしかない…など、印刷環境がない方用です)
                    </td>
                </tr>
                <tr>
                    <td>会社名または個人情報
                        ※【会社名】または「個人名」を入力してください</td>
                    <td><input type="text" name="inc_name" placeholder="例） 志摩機械 株式会社">
                    </td>
                </tr>
                <tr>
                    <td>申込者氏名</td>
                    <td><input type="text" name="app_name" placeholder="例）志摩太郎">
                    </td>
                </tr>
                <tr>
                    <td>申込者氏名フリガナ</td>
                    <td><input type="text" name="app_name_kana" placeholder="例）シマタロウ">
                    </td>
                </tr>
                <tr>
                    <td>郵便番号(会社または個人)</td>
                    <td>
                        <input type="text" name="post_no" ime-mode:disabled maxlength="8" placeholder="例）6240951" onKeyUp=" AjaxZip3.zip2addr(this,'','adress','adress');">
                        <!--〒<input type="text" size="8" maxlength="3" placeholder="例）624>-<input type=" text" size="8" maxlength="4" placeholder="例）0951">-->
                    </td>
                </tr>
                <tr>
                    <td>住所1(番地まで)</td>
                    <td>
                        <input type="text" name="address1" placeholder="例）京都府舞鶴市上福井117番地">
                        <!--〒<input type="text" size="8" placeholder="例）京都府舞鶴市上福井１１７">-->
                    </td>
                </tr>
                <tr>
                    <td>住所2(マンション名)</td>
                    <td>
                        <input type="text" name="address2" placeholder="例）京都府舞鶴市上福井117番地">
                        <!--〒<input type="text" size="8" placeholder="例）京都府舞鶴市上福井１１７">-->
                    </td>
                </tr>
                <tr>
                    <td>電話番号(会社または個人)</td>
                    <td>
                        <input type="text" name="tel" placeholder="例）0773750652">
                    </td>
                </tr>
                <tr>
                    <td>FAX番号(会社または個人)</td>
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
            <table class="table_01">
                <tr>
                    <td>助成金の有無</td>
                    <td><select name="sub_flg">
                            <?php echo '<option value=""></option>' . $_SESSION["money_final"] ?>
                        </select>※助成金を受けるかの有無を選択してください。 (有：受ける　無：受けない)
                    </td>
                </tr>
            </table>

            <table class="table_01">
                <tr>
                    <th>受講者指名</th>
                    <th>生年月日</th>
                    <th>性別</th>
                    <th>個人電話番号</th>
                    <th>携帯番号</th>
                    <th colspan="2">住所</th>
                    <th>講習内容</th>
                    <th>希望コース</th>
                    <th>受講期間</th>
                </tr>
                <?php
                for ($i = 0; $i < $_SESSION["number_person"]; $i++) {
                    echo '<tr>
                    <td><input name="students" type=text placeholder=例）志摩太郎></td>
                    <td><input name="birthday" type=text placeholder=例）生年月日></td>
                    <td><select name="sex"><option value=""></option>' . $_SESSION["sex_final"] . '
                    <td><input name="kojin_tel" type=text placeholder=例）個人電話番号></td>
                    <td><input name="phone_tel" type=text placeholder=例）携帯番号></td>
                    <td><input type=text name="kojin_post_no" size="10" ime-mode:disabled maxlength="8" placeholder="例）6240951" onKeyUp=" AjaxZip3.zip2addr(this,"","adress","adress");></td>
                    <td><input name="kojin_address" type=text placeholder=例）住所></td>
                    <td>' . $_SESSION["button_value"][2] . '</td> 
                    <td><select name="course_sub_code"><option value=""></option>' . $_SESSION["course_sub_code_final"] . '
                    <td>' . $_SESSION["button_value"][3] . '</td>
                </tr>';
                }
                ?>
            </table>
            <div align="center" class="example-r">
                <button type="submit" name="request">申請</button>

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
        <p>技能講習の場合(上記、入力情報の場合)</p>
        <p>表面</p>
        <img src="img/stsys2_1.PNG" alt="受講申込書">
        <p>特別教育受講申込書の場合</p>
        <p>クレーンの場合</p>
        <img src="img/stsys2_2.PNG" alt="特別教育受講申込書">
        <p>裏面</p>
        <img src="img/stsys2_3.PNG" alt="特別教育実施証明書">
        <p>各コースのご案内(小型移動式クレーン K1の場合)</p>
        <p>　⇒コースが混在の場合、2枚発行</p>
        <img src="img/stsys2_4.PNG" alt="小型移動式クレーン運転技能講習のご案内（K1コース）">
    </body>
</body>

</html>
