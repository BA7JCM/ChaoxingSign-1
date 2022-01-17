<?PHP
require 'connect.php';
require_once "./lib/notice_functions.php";
$sql = "SELECT * FROM list where status = 1";
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
	    echo PHP_EOL;
        echo "<br>尝试为".$name."签到完成".PHP_EOL."<br><hr>";
    }else{
        echo "<br>尝试为".$name."签到失败".PHP_EOL."<br><hr>";
    }
}
?>
