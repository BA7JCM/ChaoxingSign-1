<?PHP
require 'connect.php';
require_once "lib/notice_functions.php";
$sql = "SELECT * FROM list where status = 1";
$list = mysqli_query($connect,$sql);
while($run = mysqli_fetch_assoc($list)){
    $account = $run['tel'];
    $password = $run['password'];
    $name = $run['name'];
    echo "<br>".$name.":".PHP_EOL."<br>";
    $post_data = "account=".$account."&password=".$password;
    $single= send_post('https://'.$_SERVER["HTTP_HOST"].'/main.php', $post_data);
    if($single){
        echo "<pre>$single</pre>";
	    echo PHP_EOL;
        echo "<br>尝试为".$name."签到完成".PHP_EOL."<br><hr>";
    }else{
        echo "<br>尝试为".$name."签到失败".PHP_EOL."<br><hr>";
    }
}
?>
