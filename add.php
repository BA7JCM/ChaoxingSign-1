<?PHP
// 获取从HTML传过来的数据
$name = $_REQUEST['name'];
$account = $_REQUEST['account'];
$password = $_REQUEST['password'];
$email = $_REQUEST["email"];
$operate = "添加";
if ($account == null || $password == null){
    die("<script>alert('账号或密码不能为空！');location='add.html'</script>");
}
include 'connect.php';
// 查询自动签到列表中是否已存在该账号
$find = "SELECT * FROM list WHERE tel = '$account'";
$find1 = mysqli_query($connect,$find);
if(!$find1){
    die('查询失败' . mysqli_error($connect));
}
$find2 = mysqli_num_rows($find1);
if ($find2 == 0){
    echo "正在添加账号信息到自动签到列表中。。。。。。<br>";
    if (!$name OR $name == NULL){
        die("<script>alert('回去给个名字用于备注吧');location='index.html'</script>");
    }
    echo "正在验证账号密码是否正确.....<br>";
    $check_with_cx = json_decode(file_get_contents("https://passport2-api.chaoxing.com/v11/loginregister?uname=".$account."&code=".$password),true);
    if($check_with_cx['status'] !== true){
        die("登陆失败，原因：".$check_with_cx['mes'].PHP_EOL);
    }else{
        echo "账号密码验证正确，正在添加。。。。。<br>";
    }
    $add = "INSERT INTO list(name,tel,password,email) values ('$name','$account','$password','$email')";
    $add1 = mysqli_query($connect,$add);
    if(!$add1){
        die('添加账号到列表失败'. mysqli_error($connect));
    }else{
        // die("<script>alert('".$name."，你的账号".$account."已成功添加账号到签到列表，请返回手动执行登录');location='login.html'</script>");
        // echo $name."，你的账号".$account."已成功添加账号到签到列表";
        include "./notice.php";
    }
}else{
    if (!$name OR $name == NULL){
        $find3 = mysqli_fetch_assoc($find1);
        $name = $find3['name'];
    }
    // echo $find1['name'];
    die("<script>alert('".$name."，你的账号".$account."已经在签到列表了，正在跳转到登录页面');location='login.html'</script>");
}

?>