<?PHP
include 'connect.php';
function send_post($url, $post_data) {
    $data = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            // 'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $data,
            // 'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
 
    return $result;
}
$sql = "SELECT * FROM list";
$list = mysqli_query($connect,$sql);
while($run = mysqli_fetch_assoc($list)){
    $account = $run['tel'];
    $password = $run['password'];
    $name = $run['name'];
    // $url = "https://这里换成你自己的部署地址/main.php?account=$account&password=$password";
    // $single = file_get_contents($url);
    $post_data = array(
        'account' => $account,
        'password' => $password
    );
    $single= send_post('https://这里换成你自己的部署地址/main.php', $post_data);
    if($single){
        echo $single;
        echo "<br>";
        echo $name."尝试签到完成<br><hr>";
    }else{
        echo $name."尝试签到失败<br><hr>";
    }
}
?>