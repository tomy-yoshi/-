<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>mission_5-1</title>
</head>
<body>
<?php
// DB接続設定
    $dsn = //データベース名;
    $user = //ユーザ名;
    $password = //パスワード;
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
//テーブル作成
    $sql = "CREATE TABLE IF NOT EXISTS fm_datebase"
    ." ("
    . "id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,"
    . "name VARCHAR(100) NOT NULL,"
    . "comment TEXT NOT NULL,"
    . "date DATETIME NOT NULL,"
    . "pass CHAR(4)"
    .");";
    $stmt = $pdo->query($sql);
    
    if(isset($_POST["sub1"]))   {
        //特殊文字をエンティティに変換する
        $kbn = htmlspecialchars($_POST["sub1"],ENT_QUOTES, "UTF-8");
       //ボタンによって処理を変える
        switch($kbn)  {
          //入力フォーム
            case "入力": 
                //新規投稿の場合
                if($_POST["editNO"] == "")  {
                //データの受け取り
                    if(!empty($_POST["name"]) && !empty($_POST["text"]))    {
                        $name = $_POST["name"];
                        $text = $_POST["text"];
                        $date = date("YmdHis");
                        
                        echo "受け付けました<br>";
                        if(preg_match("/^[0-9]{4}$/", $_POST["PW"]) && !empty($_POST["PW"]))   {
                            $pass = $_POST["PW"];
                            echo "パスワードが設定されました<br>";
                        }else   {
                            echo "パスワードは設定されていません<br>";
                        }
                        //プリペアドステートメントを作成
                        $stmt = $pdo->prepare("
                        INSERT INTO fm_datebase (name, comment, date, pass)
                        VALUES (:name, :comment, :date, :pass)"
                        );
                        //プリペアドステートメントにパラメータを割り当てる
                        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                        $stmt->bindParam(':comment', $text, PDO::PARAM_STR);
                        $stmt->bindParam(':date', $date, PDO::PARAM_INT);
                        $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                        //クエリの実行
                        $stmt->execute();
                    }else   {
                        echo "名前とコメントは入力必須項目です<br>";
                    }
                }
                
                //編集の場合
                if($_POST["editNO"] != "")  {
                    $id = $_POST["editNO"];
                    $name = $_POST["name"];
                    $comment = $_POST["text"];
                    $date = date("YmdHis");
                    //プリぺアドステートメントを作成
                    $stmt = $pdo->prepare("UPDATE fm_datebase SET name=:name,comment=:comment,date=:date WHERE id=:id");
                    //パラメータ割り当て
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':date', $date, PDO::PARAM_INT);
                    //クエリの実行
                    $stmt->execute();
                    echo "編集を受け付けました<br>";
                }
            break;
            
           //削除フォーム
            case "削除":
                //データの受け取り
                if(!empty($_POST["delete"])) {
                    $id = $_POST["delete"];
                }
                if(!empty($_POST["d_PW"]))   {
                    $pass = $_POST["d_PW"];
                }
                //プリペアドステートメントを作成
                $stmt = $pdo->prepare("DELETE FROM fm_datebase WHERE id=:id AND pass=:pass");
                //パラメータ割り当て
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':pass', $pass, PDO::PARAM_INT);
                //クエリの実行
                $stmt->execute();
                //削除した件数を取得
                $cnt = $stmt->rowCount();
                //削除した件数が1以上なら削除成功、0ならパスワードが違い削除されていない又は削除できる番号がない
                if($cnt>=1)  {
                    echo $id . "を削除しました。<br>";
                }else   {
                    echo $id . "のパスワードが違います。もしくは存在しません。<br>";
                }
                
            break;
            
            //編集フォーム
            case "編集" :
                if(!empty($_POST["edit"]))  {
                    $id = $_POST["edit"];
                }    
                if(!empty($_POST["e_PW"]))  {
                    $pass = $_POST["e_PW"];
                }
                //プリペアドステートメントを作成
                $stmt = $pdo->prepare("SELECT * FROM fm_datebase WHERE id=:id AND pass=:pass");
                //パラメータ割り当て
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':pass', $pass, PDO::PARAM_INT);
                //クエリの実行
                $stmt->execute();
                
                if($row = $stmt->fetch(PDO::FETCH_ASSOC))  {
                    $editnumber = $row['id'];
                    $editname = $row['name'];
                    $editcomment = $row['comment'];
                    echo "編集モード";
                }else   {
                    echo "パスワードが違っています<br>";
                }
            break;
        }
        
    }
      
 
?>
        <form action="" method="post">
        <div>
            <input type ="text" name = "name" value = "<?php 
            if(isset($editname))    {
                echo $editname;
            } 
            ?>" placeholder = "名前">
            <input type = "text"  name = "text" value = "<?php
            if(isset($editcomment))     {
                echo $editcomment;
            } 
            ?>" placeholder = "コメント">
            <input type = "text"  name = "PW" value = "<?php
            if(isset($editnumber)) {
                echo "※変更不可";
            }
            ?>"
            placeholder = "パスワード（数字4桁）">
            <input type = "hidden" name ="editNO" value = "<?php
            if(isset($editnumber))  {
                echo $editnumber;   
            } 
            ?>"> 

            <input type = "submit" name ="sub1" value = "入力" >
        </div>
        <div>
            <input type = "number" name = "delete" min = "1" placeholder="削除対象番号">
            <input type = "text" name = "d_PW" placeholder = "パスワード">
            <input type = "submit" name = "sub1" value = "削除">
        </div>
        <div>
            <input type = "number" name = "edit" min = "1" placeholder = "編集対象番号">
            <input type = "text" name = "e_PW" placeholder = "パスワード">
            <input type = "submit" name = "sub1" value = "編集">
        </div>
    </form>
<?php
    // DB接続設定
    $dsn = //データベース名;
    $user = //ユーザ名;
    $password = //パスワード;
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    $sql = 'SELECT * FROM fm_datebase';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        //echo $row['pass'].',';
        echo $row['date'].'<br>';
    echo "<hr>";
    }
?>
</body>
</html>