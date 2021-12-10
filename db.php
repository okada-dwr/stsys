   <?php
    session_start();
    try {

        // DB接続
        $pdo = new PDO(
            // ホスト名、データベース名
            //'mysql:host=us-cdbr-east-04.cleardb.com;dbname=heroku_ab9a84ac854b6bc;',
            // ユーザー名
            //'b6133783b4692d',
            // パスワード
            //'16eee8bc',

            // ホスト名、データベース名
            'mysql:host=localhost;dbname=hanbai;',
            // ユーザー名
            'root',
            // パスワード
            'shinei4005',
            // レコード列名をキーとして取得させる

            // レコード列名をキーとして取得させる
            [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );

        if (isset($_POST['register'])) {

            $list = ['product_code', 'product_name', 'number', 'unit_price', 'price'];
            $answer = [];

            //要素分だけpostで受け取る
            for ($i = 0; $i < count($list); $i++) {
                if (isset($_POST[$list[$i]])) {
                    $answer[$i] = $_POST[$list[$i]];
                }
            }
            $product_code = $answer[0];
            $product_name = $answer[1];
            $number = $answer[2];
            $unit_price = $answer[3];
            $price = $answer[4];

            // SQL文をセット
            $stmt = $pdo->prepare('INSERT INTO sales_details (product_code,product_name,number,unit_price,price) 
            VALUES(:product_code,:product_name,:number,:unit_price,:price)');

            // 値をセット
            $stmt->bindValue(':product_code', $product_code);
            $stmt->bindValue(':product_name', $product_name);
            $stmt->bindValue(':number', $number);
            $stmt->bindValue(':unit_price', $unit_price);
            $stmt->bindValue(':price', $price);

            // SQL実行
            $stmt->execute();
        } elseif (isset($_POST['no_read'])) {

            //伝票NO
            $slip_number = $_POST['slip_number'];

            // SQL文をセット
            $stmt = $pdo->prepare('SELECT indicate FROM sales_details WHERE no=:slip_number');

            // 値をセット
            $stmt->bindValue(':slip_number', $slip_number);

            // SQL実行
            $stmt->execute();
            $username = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($username['indicate'] = 0) {
                // SQL文をセット
                $stmt = $pdo->prepare('SELECT * FROM sales_details WHERE no=:slip_number');

                // 値をセット
                $stmt->bindValue(':slip_number', $slip_number);

                // SQL実行
                $stmt->execute();
                $username = $stmt->fetch(PDO::FETCH_ASSOC);

                $_SESSION['no'] = $username['no'];
                $_SESSION['product_code'] = $username['product_code'];
                $_SESSION['product_name'] = $username['product_name'];
                $_SESSION['number'] = $username['number'];
                $_SESSION['unit_price'] = $username['unit_price'];
                $_SESSION['price'] = $username['price'];
            } else {
                $_SESSION['no'] = 0;
                $_SESSION['product_code'] = 0;
                $_SESSION['product_name'] = "";
                $_SESSION['number'] = 0;
                $_SESSION['unit_price'] = 0;
                $_SESSION['price'] = 0;
            }
        } elseif (isset($_POST['delete'])) {
            $no = $_POST['no'];
            $indicate = 1;
            // SQL文をセット
            $stmt = $pdo->prepare('UPDATE sales_details SET indicate=:indicate WHERE no=:no');

            // 値をセット
            $stmt->bindValue(':no', $no);
            $stmt->bindValue(':indicate', $indicate);

            // SQL実行
            $stmt->execute();
        }
    } catch (PDOException $e) {
        // エラー発生
        echo $e->getMessage();
    } finally {
        // DB接続を閉じる
        $pdo = null;
        header('location:\sales_details.php');
    }
    ?>
