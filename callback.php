<?php
$json = file_get_contents("php://input");
$decoded = json_decode($json,true);
include "./connect.php";
$push_id = $decoded["data"]["uid"];
$account = $decoded["data"]["extra"];
$sql = "UPDATE list set push_id = \"$push_id\" where tel = \"$account\"";
// echo $sql;
$query = mysqli_query($connect,$sql);
include "./lib/notice_functions.php";
$sql2 = "SELECT * FROM list WHERE tel = \"$account\"";
$query2 = mysqli_query($connect,$sql2);
$array = mysqli_fetch_array($query2);
$title = "学习通自动签到系统对接通知";
if($array["push_id"] == $push_id){
    $send_data = '{
        "appToken":"这里填写你自己从wxpusher新建的应用的token",
        "content":"你的账号'.$array["name"].'于'.date("Y年n月j日g:i:s A",time()).'成功对接，请确保是你本人操作",
        "summary":"'.$title.'",
        "contentType":1,
        "uids":[
            "'.$push_id.'"
        ],
        "url":"https://这里换成你自己的部署地址/login.php"
      }';
    $sended = post_json("https://wxpusher.zjiecode.com/api/send/message",$send_data);
}else{
    $send_data = '{
        "appToken":"这里填写你自己从wxpusher新建的应用的token",
        "content":"你的账号'.$array["name"].'于'.date("Y年n月j日g:i:s A",time()).'在对接时可能发生错误，请邮件联系这里填写你自己用于联系的邮箱并把这条通知告诉他",
        "summary":"'.$title.'",
        "contentType":1,
        "uids":[
            "'.$push_id.'"
        ],
        "url":"mailto:这里填写你自己用于联系的邮箱"
      }';
    $sended = post_json("https://wxpusher.zjiecode.com/api/send/message",$send_data);
}
if($array["email"]){
    graph_send_email($title,'你的账号'.$array["name"].'于'.date("Y年n月j日g:i:s A",time()).'尝试添加通知渠道wxpusher，请确保是你本人操作',$array["email"]);
}
?>