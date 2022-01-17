<?php
session_start();
if(isset($_GET["logout"])){
    if($_GET["logout"] == "true"){
        // unset($_SESSION["login"]);
        // unset($_SESSION["password"]);
        unset($_SESSION["account".$account]);
        die("<script>alert('已成功退出登录');location='login.html'</script>");
    }
}
require "./connect.php";
// if(isset($_SESSION) and array_key_exists("account".$account,$_SESSION)){
//     // var_dump($_SESSION["account".$account]);
//     // die;
//     $account = $_SESSION["account".$account]["account"];
//     $password = $_SESSION["account".$account]["password"];
//     // $account = $_POST["account"];
//     // $password = $_POST["password"];
//     // if ($account == null || $password == null){
//     //     die("<script>alert('账号或密码不能为空！');location='login.html'</script>");
//     // }
//     // // 登录的时候先销毁session
//     // if(isset($_SESSION)){
//     //     session_destroy();
//     // }
// }else{
    // $account = $_SESSION["login"];
    // $password = $_SESSION["password"];
    $account = $_POST["account"];
    $password = $_POST["password"];
    if ($account == null || $password == null){
        die("<script>alert('账号或密码不能为空！');location='login.html'</script>");
    }
    // 登录的时候先销毁session
//     if(isset($_SESSION)){
//         session_destroy();
//     }
//     // $not_notice = true;
// }
$operate = "登录";
$sql = "SELECT * FROM list where tel = '".$account."'";
$query = mysqli_query($connect,$sql);
$array = mysqli_fetch_array($query);
if ($array["password"] == $password) {
    // echo "登陆成功<br><hr>";
    // if(isset($_SESSION) and array_key_exists("account".$account,$_SESSION)){
    //     if($array["not_notice"] == 0){
    //         $not_notice = true;
    //     }else{
    //         $not_notice = false;
    //     }
    // }
    include "./notice.php";
    // 在执行完通知之后再设置session，因为通知的时候需要通过session判断是新登录还是从会话中恢复
    // session_start();
    // // $_SESSION["login"] = "$account";
    // // $_SESSION["password"] = "$password";
    // $_SESSION["account".$account] = [
    //     "account" => $account,
    //     "password" => $password
    // ];
    // echo "<P>已设置session</P>";
    // 让使用者自行决定是否将账号加入自动签到列表
    echo "<hr><P>自动签到开关：</P>";
        if (isset($_GET["status"])) {
        if($_GET["status"] == "disable"){
            $sql2 = "UPDATE list SET status = 0 where tel ='$account'";
        }elseif($_GET["status"] == "enable"){
            $sql2 = "UPDATE list SET status = 1 where tel = '$account'";
        }else{
            die("get out!");
        }
        $query2 = mysqli_query($connect,$sql2);
        if(!$query2){
            echo "尝试更改出错：".mysqli_error($connect);
            die();
        }else {
            echo "已成功提交修改，如果仍然显示旧的页面请删除地址栏中“?”后面的东西后重新加载当前页面（不要直接刷新）";
        }
    } 
    
        if ($array["status"] == 1) {
            echo "<p>账号当前状态：已在自动签到列表中</p>";
            echo "<a href='?status=disable'><input type='button' value='将此账号排除在自动签到列表'></a>";
        } else {
            echo "<p>账号当前状态：已停止自动签到</p>";
            echo "<a href='?status=enable'><input type='button' value='将此账号添加进自动签到列表'></a>";
        }
    echo "<p><a href='?logout=true'><input type='button' value='退出登录'></a></p>";
    echo "<p>上次尝试签到：".date("Y年n月j日g:i:s A",$array["last_single"])."<br>上次成功签到：".date("Y年n月j日g:i:s A",$array["last_success"])."</p>";
    
}
?>
