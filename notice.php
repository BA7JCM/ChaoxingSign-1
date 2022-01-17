<?php
require_once "./lib/notice_functions.php";
$notice_sql = "SELECT * FROM list where tel = " . $account;
$notice_check = mysqli_query($connect, $notice_sql);
$notice_array = mysqli_fetch_array($notice_check);
echo $operate . "成功<br>";
echo $notice_array["name"] . ",欢迎";
if ($operate == "添加") {
    echo "加入，你的账号“" . $account . "”已成功添加账号到签到列表";
} elseif ($operate == "登录") {
    echo "回来";
    if ($notice_array["last_time"] != null) {
        echo "，上次登录时间：" . date("Y年n月j日 g:i:s A", $notice_array["last_time"]);
    }
}
echo "<br>";
if (!isset($not_notice) or $not_notice != true) {
    $time = time();
    $time_sql = "UPDATE list set last_time = $time WHERE tel = $account";
    $time_query = mysqli_query($connect, $time_sql);
    if (!$time_query) {
        die("更新上次登录时间失败：" . mysqli_error($connect));
    }
}
// 设定推送通知的内容
$title = "学习通自动签到系统登录通知";
$content = '你的账号“' . $notice_array["name"] . '”于' . date("Y年n月j日g:i:s A", time()) . '登录，请确保是你本人操作';
// wxpusher 推送部分
if ($notice_array["push_id"] == null) {
    // 检测到没有wxpusher的uid，生成应用二维码
    $qr_data = '{
        "appToken": "这里填写你自己从wxpusher新建的应用的token",
        "extra": "' . $notice_array["tel"] . '",
        "validTime": 1800
    }';
    $response = post_json('https://wxpusher.zjiecode.com/api/fun/create/qrcode', $qr_data);
    $qr_json = json_decode($response, true);
    // print_r($qr_json);
    echo "<hr>";
    echo "<p>检测到没有用于推送一对一通知的uid，请扫描下面的二维码以便后续接收通知</p>";
    echo "<img src='" . $qr_json["data"]["url"] . "' width='' alt='接收通知的二维码'>";
    $expires = substr($qr_json["data"]["expires"], 0, 10);
    echo "<p>过期时间：" . date("Y年n月j日g:i:s A", $expires) . "</p>";
    // echo "<p>此二维码为动态码，请不要截图或保存此二维码，因为它随时会过期</p>";
} else {
    // 通过uid走wxpusher推送登录通知
    // if (!isset($_SESSION["account".$account]) and !isset($not_notice) or $not_notice != true) {
    //     $status = wxpusher_send_notice($title,$content,$notice_array["push_id"]);
    //     if (strpos($status, "成功")) {
    //         echo "<hr>尝试发送通知成功";
    //     } else {
    //         echo "发送通知可能出错，请<a href='mailto:这里填写你自己用于联系的邮箱'>邮件联系这里填写你自己用于联系的邮箱</a>并将以下消息转告他：" . $status;
    //     }
    // } else {
    //     echo "<hr>已从会话中恢复，不更新上次登录时间，不发送登录通知";
    // }
    // if(isset($_SESSION) and array_key_exists("account".$account,$_SESSION)){
    //     // 存在session信息，说明登录没掉
    //     if(!isset($not_notice) or $not_notice != true) {
            // 判断该账号的通知设置
            $status = wxpusher_send_notice($title,$content,$notice_array["push_id"]);
            if (strpos($status, "成功")) {
                echo "<hr>尝试发送通知成功";
            } else {
                echo "发送通知可能出错，请<a href='mailto:这里填写你自己用于联系的邮箱'>邮件联系这里填写你自己用于联系的邮箱</a>并将以下消息转告他：" . $status;
            }
    //     } else {
    //         echo "<hr>已从会话中恢复，不更新上次登录时间，不发送登录通知";
    //     }
    // }else{
    //     // 没有session信息，说明是新登录的，发送通知
    //     $status = wxpusher_send_notice($title,$content,$notice_array["push_id"]);
    //     if (strpos($status, "成功")) {
    //         echo "<hr>尝试发送通知成功";
    //     } else {
    //         echo "发送通知可能出错，请<a href='mailto:这里填写你自己用于联系的邮箱'>邮件联系这里填写你自己用于联系的邮箱</a>并将以下消息转告他：" . $status;
    //     }
    // }
}
// 邮箱推送部分
if ($notice_array["email"] == null) {
    // 检测到没有设置用于接收邮件的邮箱
    echo "<p>请填写用于接收推送信息的邮箱：</p>";
    ?>
    <form action="" method="post">
        <p><input type="email" name="email"></p>
        <p><input type="submit" value="submit"></p>
    </form>
    <?php
    if(isset($_POST["email"])){
        $email = $_POST["email"];
        $email_sql = "UPDATE list set email = '$email' where tel = '$account'";
        $email_query = mysqli_query($connect,$email_sql);
        if (!$email_query) {
            echo "更新邮件可能出错，请<a href='mailto:这里填写你自己用于联系的邮箱'>邮件联系这里填写你自己用于联系的邮箱</a>并将以下消息转告他：" . mysqli_error($connect);
        } else {
            graph_send_email("学习通自动签到系统添加邮箱通知",'你的账号“'.$notice_array["name"].'”于'.date("Y年n月j日g:i:s A",time()).'成功将此邮箱添加为通知邮箱，请确保是你本人操作',$email);
            echo "已成功提交修改，如果输入框还在请重新加载当前页面（不要直接刷新）";
        }
    }
}else {
//     if (isset($_SESSION) and array_key_exists("account".$account, $_SESSION)) {
//         if (!isset($not_notice) or $not_notice != true) {
            $send_email = graph_send_email($title, $content, $notice_array["email"]);
            if (!isset($send_email) or $send_email != "success") {
                echo "<hr>发送邮件可能出错：";
                var_dump($send_email);
            } else {
                echo "<hr>尝试发送邮件成功";
            }
    //     } else {
    //         echo "，不发送邮件，<a href='?not_notice=enable'>点击这里启用从会话恢复时发送通知</a>";
    //     }
    // }else{
    //     $send_email = graph_send_email($title, $content, $notice_array["email"]);
    //     if (!isset($send_email) or $send_email != "success") {
    //         echo "<hr>发送邮件可能出错：";
    //         var_dump($send_email);
    //     } else {
    //         echo "<hr>尝试发送邮件成功";
    //     }
    // }
}
// if(isset($not_notice) and $not_notice != true and isset($_SESSION) and array_key_exists("account".$account,$_SESSION)){
//     echo "，已设置从会话恢复时发送通知，<a href='?not_notice=disable'>点击这里禁用</a>";
// // }
// if (isset($_GET["not_notice"])) {
//     if($_GET["not_notice"] == "enable"){
//         $sql2 = "UPDATE list SET not_notice = 1 where tel ='$account'";
//     }elseif($_GET["not_notice"] == "disable"){
//         $sql2 = "UPDATE list SET not_notice = 0 where tel = '$account'";
//     }else{
//         die("get out!");
//     }
//     $query2 = mysqli_query($connect,$sql2);
//     if(!$query2){
//         echo "尝试更改出错：".mysqli_error($connect);
//         die();
//     }else {
//         echo "已成功提交修改，如果仍然显示旧的页面请删除地址栏中“?”后面的东西后重新加载当前页面（不要直接刷新）";
//     }
// } 
// 不想弄session了，干脆每次都重新登陆，每次登录都发通知得了
?>