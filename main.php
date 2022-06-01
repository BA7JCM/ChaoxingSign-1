<?php
error_reporting(1);
include_once __DIR__ . "/lib/Functions.php";
include_once __DIR__ . "/lib/Selector.php";
include_once __DIR__ . "/Config.php";
// 引入连接数据库和定义通知函数的相关文件
include_once "./lib/notice_functions.php";
include_once "./connect.php";
$tryLogin = 0;//尝试登录次数

// 允许签到的时间范围
// ['08:00:00', '23:00:00'] 表示仅能在这两个区间内进行签到, 时间使用24小时制
$enable_time = ['08:00:00', '23:00:00'];
if(!timeInterval(time(), $enable_time)){
    die("仅能在 每天 $enable_time[0] - $enable_time[1] 间 签到。如果你要修改，请修改第 10 行代码".PHP_EOL);
}

if (is_cli() && isset($argv)) {
    $param = getopt('A:P:');
    if(!isset($param['A']) || !isset($param['P'])){
        die("使用方法：php main.php -A 你的账号 -P 你的密码".PHP_EOL.PHP_EOL
            ."例如：php main.php -A 1008611 -P zgyd1008611".PHP_EOL
            ."或者(如果你的账号含有特殊字符，建议使用双引号)：php main.php -A \"1008611\" -P \"zgyd1008611\"".PHP_EOL);
    }
    $account = $param['A'];
    $password = $param['P'];
}else{
    $account = $_REQUEST['account'];
    $password = $_REQUEST['password'];
}
    // 更新最后一次执行签到的时间
    $time = time();
    $update_sql = "UPDATE list set last_single = '$time' where tel = '$account'";
    $update_query = mysqli_query($connect,$update_sql);

$jar_path = __DIR__ . "/cookie/{$account}.cookie";//保存 Cookie 的路径
$signed_path = __DIR__ . "/cookie/{$account}.signed";//保存 已经签到完成 的路径


//如果存在 cookie 文件，先判断 cookie 是否过期
if (file_exists($jar_path)) {
    goto getCourseList;
}else{
    //不存在 cookie 文件，则新建一个
    file_put_contents($jar_path, "#新建一个保存cookie文件");
}
goto takeLogin;//执行登陆




//获取课程列表
getCourseList:
$getCourseListRes = json_decode(curl_get(COURSE_LIST, $jar_path), true);

if(!isset($getCourseListRes['channelList'])){
    if($tryLogin > 1){
        die("[已尝试重新登录2次]获取课程列表失败，请稍后再试。多次出现此问题请前往 https://github.com/PrintNow/ChaoxingSign 提交 Issues".PHP_EOL);
    }else{
        $tryLogin += 1;
        echo "[getCourseList]获取课程列表失败，可能是 cookie 过期，正在尝试第{$tryLogin}次重新登录".PHP_EOL;
        goto takeLogin;//执行登录，更新 cookie
    }
}
// echo "<pre>";var_dump($getCourseListRes['channelList'], true);echo "</pre>";die;

$course_list = [];
foreach ($getCourseListRes['channelList'] as $v){
    //TODO: 后续再考虑字段可能不存在的情况，能用就行
    /*
    $title = '未知课程名';
    $teacherName = '未知教师名';

    if(isset($v['content']['course']['data'][0])){
        if(isset($v['content']['course']['data'][0]['name'])){
            $title = $v['content']['course']['data'][0]['name'];
        }
        if(isset($v['content']['course']['data'][0]['teacherfactor'])){
            $teacherName = $v['content']['course']['data'][0]['teacherfactor'];
        }
    }
    */
    // echo "<pre>";var_dump($v, true);echo "</pre>";die;

    if(!isset($v['content']['course']['data'][0]['id'])) continue;

    $course_list[] = [
        'courseId' => $v['content']['course']['data'][0]['id'],
        'classId' => $v['content']['id'],
        'title' => $v['content']['course']['data'][0]['name'],
        'teacherName' => $v['content']['course']['data'][0]['teacherfactor'],
    ];
}

goto getTaskID;//获取任务 ID



//获取任务ID
getTaskID:
// echo "<pre>";var_dump($course_list, true);echo "</pre>";die;
$taskID = [];
foreach ($course_list as $val) {
    $html = curl_get(sprintf(TASK_ID, $val['courseId'], $val['classId']), $jar_path);
// echo "<pre>";var_dump(json_decode($html, true)["activeList"]);echo "</pre>";die;
$res = json_decode($html, true)["activeList"];
    // 由于同一时间同一门课不会出现多个签到，优化遍历代码
// echo "<pre>";var_dump($res[0]);echo "</pre>";die;
    for ($i = 0; $i <= 3; $i ++){
        if($res[$i]["status"] == 1 && $res[$i]["activeType"] ==2){
            $taskID[] = [
                $val['courseId'],//课程ID
                $val['classId'],//班级ID
                $res[$i]["id"],//签到任务ID
                $val['title'],//课程名
                $val['teacherName'],//教师名
                ];
        }
    }
    // 数组全部遍历
    // foreach ($res as $k => $v) {
    //     if($v["status"] == 1){
    //          $taskID[] = [
    //             $val['courseId'],//课程ID
    //             $val['classId'],//班级ID
    //             $v["id"],//签到任务ID
    //             $val['title'],//课程名
    //             $val['teacherName'],//教师名
    //             ];
    //     }
    // }
}

if (count($taskID) > 0) {

    if (!file_exists($signed_path)) {
        file_put_contents($signed_path, "#新建一个 已经签到完的活动 文件");
    }

    goto doTask;
}

file_put_contents($signed_path, "");//没有签到任务了，将其置为空
echo "[getTaskID]没有待签到的任务".PHP_EOL;
die;


//执行任务
doTask:
$activeBlackList = explode("\n", file_get_contents($signed_path));

$msgTmp = "";
foreach ($taskID as $k => $v) {

    //该签到任务已经签到了，不需要再次重复签到
    if(in_array($v[2], $activeBlackList)) continue;

    echo $_1 = "正在签到：{$v[4]}@{$v[3]}";
    $signRes = trim(curl_get(sprintf(SIGN_API, $v[2]), $jar_path));//签到结果

    echo $_2 = PHP_EOL."[".date("Y-m-d H:i:s")."]";
    if($signRes === "success" || $signRes === "您已签到过了"){
        //该签到加入 签到黑名单，以避免重复签到
        file_put_contents($signed_path, "\n".$v[2], FILE_APPEND);
        echo $_3 = str_replace("success", "签到成功", $signRes).PHP_EOL.PHP_EOL;
    }else{
        echo $_3 = "签到失败，错误原因：{$signRes}";
    }

    $msgTmp .= $_1.$_2.$_3;
}

//检查是否包含签到成功，如果包含签到成功 || 签到失败
//则进行推送
if(strpos($msgTmp,'签到成功') !== false || strpos($msgTmp,'签到失败') !== false){

    //Server酱 微信推送
    //先检查是否开启推送 以及 是否配置了“Server酱”相关信息
    if(SERVER_CHAN_STATE && isset($config['SERVER_CHAN'][strval($account)])){
        //再检查是否开启了推送
        if($config['SERVER_CHAN'][$account]['state']){
            $req = sc_send(
                "超星自动签到成功",
                str_replace("\n", "\n\n", $msgTmp),//因为 Server酱 两个换行才是换行
                $config['SERVER_CHAN'][$account]['SendKey']
            );

            if(!isset($req['errmsg'])){
                die("Server酱 推送失败，可能是你没有配置 SCKEY，请检查".PHP_EOL);
            }

            if($req['errmsg'] === 'success'){
                echo "Server酱 消息推送成功".PHP_EOL;
            }else{
                echo "Server酱 消息推送失败，原因：{$req['errmsg']}".PHP_EOL;
            }
        }
    }else{
        echo "未配置 Server酱，不推送消息".PHP_EOL;
    }

    //Telegram 推送
    //先检查是否开启推送 以及 是否配置了“Telegram BOT”相关信息
    if(TG_STATE && isset($config['Telegram'][strval($account)])){
        if($config['Telegram'][$account]['state']){
            $req = tg_send(
                $config['Telegram'][$account]['TG_CHAT_ID'],
                $msgTmp = "超星自动签到成功\n\n" . $msgTmp ,
                $config['Telegram'][$account]['TG_BOT_TOKEN']
            );
            if($req['ok'] == true){
                echo "Telegram 消息推送成功".PHP_EOL;
            }else{
                echo "Telegram 消息推送失败。".PHP_EOL;
            }
        }
    }else{
        echo "未配置 Telegram BOT，不推送消息".PHP_EOL;
    }
// 从数据库中获取一对一推送信息
$notice_info_sql = "SELECT * FROM list where tel = '$account'";
$notice_info_query = mysqli_query($connect,$notice_info_sql);
$notice_info_array = mysqli_fetch_array($notice_info_query);
// 统一标题和内容
$title = "签到结果推送";
$content = "尝试签到完成，$msgTmp";
// wxpusher推送
if($notice_info_array["push_id"] != null or $notice_info_array["push_id"] != ""){
    // var_dump($content);
    $wxpusher_send = wxpusher_send_notice($title,str_replace("\n", " ", $content),$notice_info_array["push_id"]);
    if (strpos($wxpusher_send, "成功")) {
        echo "<hr>尝试发送通知成功";
    } else {
        echo "发送通知可能出错：" . $wxpusher_send;
    }
    // var_dump($wxpusher_send);
}
// 邮件推送
$send_email = graph_send_email($title, $content, $notice_info_array["email"]);
    if (!isset($send_email) or $send_email != "success") {
        echo "<hr>发送邮件可能出错：";
        var_dump($send_email);
    } else {
        echo "<hr>尝试发送邮件成功";
    }
// 更新最后一次成功签到的时间
$update_time_sql = "UPDATE list set last_success = '$time' where tel = '$account'";
$update_time_query = mysqli_query($connect,$update_time_sql);

    //BARK 推送
    //先检查是否开启推送 以及 是否配置了“BARK”相关信息
    if(BARK_STATE && isset($config['Bark'][strval($account)])){
        if($config['Bark'][$account]['state']){
            $req = bark_send(
                "超星自动签到提醒",
                $msgTmp = "超星自动签到成功\n\n" . $msgTmp,
                $config['Bark'][$account]['BARK_PUSH_API']
            );
            if($req['code'] == 200){
                echo "Bark 消息推送成功".PHP_EOL;
            }else{
                echo "Bark 消息推送失败。".PHP_EOL;
            }
        }
    }else{
        echo "未配置 Bark，不推送消息".PHP_EOL;
    }
}else{
    echo "没有待签到的任务".PHP_EOL;
}
die;


//登陆账号
takeLogin:
$login_data = json_decode(curl_get(sprintf(LOGIN_API, $account, $password), $jar_path), true);

if (!isset($login_data['status'])) {
    die("登陆失败，原因：API 错误，请再次尝试。多次出现此问题请前往 https://github.com/PrintNow/ChaoxingSign 提交 Issues");
}

if($login_data['status'] !== true){
    unlink($jar_path);//删除临时创建的 cookie 文件，避免产生大量的垃圾文件
    die("登陆失败，原因：{$login_data['mes']}".PHP_EOL);
}

echo "登陆成功，正在尝试签到...".PHP_EOL;
goto getCourseList;//获取课程列表