<?php
function post_json($url,$data,$header ="" ){
    // 加个header参数用于需要在头部带token之类的场景，默认为空
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json',$header],
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}
function send_post($url, $post_data) {
    $data = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $data,
            // 'timeout' => 15 * 60 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
 
    return $result;
}
function graph_send_email($title,$content,$recipient,$sender = "88c925cb-ab5d-473c-907d-65fabea32ab9"){
    // 用微软api发邮件的部分太多东西了，写成函数方便后面调用
    // 现用现从微软那里拿token
    $data = [
        "client_id" => "这里填写你自己在Azure那边新建的应用的id",
        "scope" => "https://graph.microsoft.com/.default",
        "client_secret" => "这里填写你自己在Azure那边新建的应用的secret",
        "grant_type" => "client_credentials"
    ];
    $url = "https://login.microsoftonline.com/这里填写你自己office365组织的id/oauth2/v2.0/token";
    $result = send_post($url,$data);
    $token = json_decode($result,true)["access_token"];
    // 用拿到的token去发邮件
    $header = "Authorization: Bearer ".$token;
    $email_data = '{
        "message": {
            "subject": "'.$title.'",
            "body": {
                "contentType": "Text",
                "content": "'.$content.'"
            },
            "toRecipients": [
                {
                    "emailAddress": {
                        "address": "'.$recipient.'"
                    }
                }
            ]
        }
    }';
    $email_url = "https://graph.microsoft.com/v1.0/users/".$sender."/sendMail";
    $send_email = post_json($email_url,$email_data,$header);
    if(!isset($send_email) or $send_email != ""){
        return "发送邮件可能出错：".$send_email;
    } else {
        return "success";
    }
}
function wxpusher_send_notice($title,$content,$recipient){
    $send_data = '{
        "appToken":"这里填写你自己从wxpusher新建的应用的token",
        "content":"'.$content.'",
        "summary":"'.$title.'",
        "contentType":1,
        "uids":[
            "' . $recipient . '"
        ],
        "url":"https://这里换成你自己的部署地址/login.php"
      }';
        $sended = post_json("https://wxpusher.zjiecode.com/api/send/message", $send_data);
        $status = json_decode($sended, true)["data"][0]["status"];
        return $status;
        // var_dump($sended);
}
?>