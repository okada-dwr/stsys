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

        // レコード列名をキーとして取得させる
        [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    if (isset($_POST['request'])) {

        // $list = ['product_code', 'product_name', 'number', 'unit_price', 'price'];
        // $answer = [];

        //要素分だけpostで受け取る
        // for ($i = 0; $i < count($list); $i++) {
        //     if (isset($_POST[$list[$i]])) {
        //         $answer[$i] = $_POST[$list[$i]];
        //     }
        // }
        $app_name = $_POST['app_name'];
        // $product_name = $answer[1];
        // $number = $answer[2];
        // $unit_price = $answer[3];
        // $price = $answer[4];

        // SQL文をセット
        $stmt = $pdo->prepare('INSERT INTO st_reserve (app_name) 
            VALUES(:app_name)');

        // 値をセット
        $stmt->bindValue(':app_name', $app_name);
        // $stmt->bindValue(':product_name', $product_name);
        // $stmt->bindValue(':number', $number);
        // $stmt->bindValue(':unit_price', $unit_price);
        // $stmt->bindValue(':price', $price);

        // SQL実行
        $stmt->execute();
    } elseif (isset($_POST['no_read'])) {

        //     //伝票NO
        //     $slip_number = $_POST['slip_number'];

        //     // SQL文をセット
        //     $stmt = $pdo->prepare('SELECT indicate FROM sales_details WHERE no=:slip_number');

        //     // 値をセット
        //     $stmt->bindValue(':slip_number', $slip_number);

        //     // SQL実行
        //     $stmt->execute();
        //     $username = $stmt->fetch(PDO::FETCH_ASSOC);

        //     if ($username['indicate'] = 0) {
        //         // SQL文をセット
        //         $stmt = $pdo->prepare('SELECT * FROM sales_details WHERE no=:slip_number');

        //         // 値をセット
        //         $stmt->bindValue(':slip_number', $slip_number);

        //         // SQL実行
        //         $stmt->execute();
        //         $username = $stmt->fetch(PDO::FETCH_ASSOC);

        //         $_SESSION['no'] = $username['no'];
        //         $_SESSION['product_code'] = $username['product_code'];
        //         $_SESSION['product_name'] = $username['product_name'];
        //         $_SESSION['number'] = $username['number'];
        //         $_SESSION['unit_price'] = $username['unit_price'];
        //         $_SESSION['price'] = $username['price'];
        //     } else {
        //         $_SESSION['no'] = 0;
        //         $_SESSION['product_code'] = 0;
        //         $_SESSION['product_name'] = "";
        //         $_SESSION['number'] = 0;
        //         $_SESSION['unit_price'] = 0;
        //         $_SESSION['price'] = 0;
        //     }
        // } elseif (isset($_POST['delete'])) {
        //     $no = $_POST['no'];
        //     $indicate = 1;
        //     // SQL文をセット
        //     $stmt = $pdo->prepare('UPDATE sales_details SET indicate=:indicate WHERE no=:no');

        //     // 値をセット
        //     $stmt->bindValue(':no', $no);
        //     $stmt->bindValue(':indicate', $indicate);

        //     // SQL実行
        //     $stmt->execute();
    }
} catch (PDOException $e) {
    // エラー発生
    echo $e->getMessage();
} finally {
    // DB接続を閉じる
    $pdo = null;
    // header('location:\sales_details.php');
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
            <table border="1">
                <tr>
                    <td>申込書郵送の有無</td>
                    <td><select name="mail_flg">
                            <option value=""></option>
                            <option value="1">有</option>
                            <option value="2">無</option>
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
                        <input type="text" name="post_no" size="10" ime-mode:disabled maxlength="8" placeholder="例）6240951" onKeyUp=" AjaxZip3.zip2addr(this,'','adress','adress');">
                        <!--〒<input type="text" size="8" maxlength="3" placeholder="例）624>-<input type=" text" size="8" maxlength="4" placeholder="例）0951">-->
                    </td>
                </tr>
                <tr>
                    <td>住所1(番地まで)</td>
                    <td>
                        <input type="text" name="address1" size="60" placeholder="例）京都府舞鶴市上福井117番地">
                        <!--〒<input type="text" size="8" placeholder="例）京都府舞鶴市上福井１１７">-->
                    </td>
                </tr>
                <tr>
                    <td>住所2(マンション名)</td>
                    <td>
                        <input type="text" name="address2" size="60" placeholder="例）京都府舞鶴市上福井117番地">
                        <!--〒<input type="text" size="8" placeholder="例）京都府舞鶴市上福井１１７">-->
                    </td>
                </tr>
                <tr>
                    <td>電話番号(会社または個人)</td>
                    <td>
                        <input type="text" name="tel" size="8" placeholder="例）0773750652">
                    </td>
                </tr>
                <tr>
                    <td>FAX番号(会社または個人)</td>
                    <td>
                        <input type="text" name="fax" size="8" placeholder="例）0773755591">
                    </td>
                </tr>
                <tr>
                    <td>mailアドレス</td>
                    <td>
                        <input type="text" name="mail_address" size="8" placeholder="例）xxxx@gmail.com">
                    </td>
                </tr>
            </table>
            <table border="1">
                <tr>
                    <td>助成金の有無</td>
                    <td><select name="sub_flg">
                            <option value=""></option>
                            <option value="1">有</option>
                            <option value="2">無</option>
                        </select>※助成金を受けるかの有無を選択してください。 (有：受ける　無：受けない)
                    </td>
                </tr>
            </table>
            <?php
            //if (isset($_POST(['example']))) {
            //}
            ?>

            <table border="1">
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
                $number_person = 5;
                if (isset($_POST['stsys1_click'])) {

                    $number_person = $_POST['number_person'];

                    for ($i = 0; $i < $number_person; $i++) {
                        echo "<tr>
                <td><input name='students' type=text placeholder=例）志摩太郎></td>
                <td><input name='birthday' type=text placeholder=例）生年月日></td>
                <td><select name='sex'><option value=''></option><option value=1>有</option><option value=2>無</option></select></td>
                <td><input name='kojin_tel' type=text placeholder=例）個人電話番号></td>
                <td><input name='phone_tel' type=text placeholder=例）携帯番号></td>
                <td><input type=text name='post_no' size='10' ime-mode:disabled maxlength='8' placeholder='例）6240951' onKeyUp=' AjaxZip3.zip2addr(this,'','adress','adress');'></td>
                <td><input name='insert_date' type=text placeholder=例）住所></td>
                <td></td>
                <td><select name='sex'><option value=''></option><option value=1>有</option><option value=2>無</option></select></td>
                <td></td>
            </tr>";
                    }
                }
                ?>
            </table>
            <div align="center" class="example-r">
                <button type="submit" name="request">申請</button>
                <!-- <button type="submit"><a href="pdf/sample.pdf" name="request" download="sample.pdf">申請</a></button> -->
                <button><a href="index.php">戻る</a></button>
            </div>
        </form>
        <p>●「受講申込書」および「技能講習のご案内」</p>
        <p>「受講申込書」および「技能講習のご案内」のイメージです。
        <p>●登録ボタン押下後の処理</p>
        <p> ⇒ダウンロードする資料は受講者分ダウンロードされる(5人なら5枚)</p>
        <p>技能講習の場合(上記、入力情報の場合)</p>
        <p>表面</p>
        <img src="img/受講申込書.png" alt="受講申込書">
        <p>特別教育受講申込書の場合</p>
        <p>クレーンの場合</p>
        <img src="img/受講申込書.png" alt="受講申込書">
        <p>裏面</p>
        <img src="img/受講申込書.png" alt="受講申込書">
        <p>各コースのご案内(小型移動式クレーン K1の場合)</p>
        <p>　⇒コースが混在の場合、2枚発行</p>
        <img src="img/受講申込書.png" alt="受講申込書">
    </body>
</body>

</html>
