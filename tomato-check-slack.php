<?php
date_default_timezone_set("America/New_York");
$tomatoStatus =  json_decode(file_get_contents("https://tomato.bmltenabled.org/rest/v1/rootservers/"), true);

# Sort by last import time.
usort($tomatoStatus, function ($a, $b) {
    return strtotime($a['last_successful_import']) - strtotime($b['last_successful_import']);
});

# get last import time as timestamp. Last item of array.
$lsi = strtotime($tomatoStatus[array_keys($tomatoStatus)[count($tomatoStatus)-1]]['last_successful_import']);

# Slack token
$token = '';
$threeHours = strtotime("-3 hours", $lsi);

foreach($tomatoStatus as $tomato) {
    $lastImport = strtotime($tomato['last_successful_import']);

    if ($lastImport < $threeHours) {
        $attachments = array([
            "fallback" => "Tomato Failed Import.",
            "color" => "#ff6600",
            "title" => "Tomato Failed Import",
            "title_link" => "https://tomato.na-bmlt.org/rest/v1/rootservers/",
            'fields'   => array(
                [
                    "title" => "Root Server",
                    "value" => "https://latest.aws.bmlt.app/main_server/",
                    "short" => false
                ],
                [
                    "title" => "Last Import",
                    "value" => date("M d, o g:iA", "2020-02-16T02:34:54.418391Z"),
                    "short" => false
                ]
            ),
            "footer" => "BMLT-Enabled",
            "footer_icon" => "https://s3-us-west-2.amazonaws.com/slack-files2/avatars/2018-12-26/512035188372_266e0f7e633d3b17af73_132.png",
            "ts" => time()
        ]);
        $data = array(
            'channel'     => 'tomato',
            'username'    => 'tomato-bot',
            'text'        => '',
            'icon_emoji'  => ':tomato:',
            'attachments' => $attachments
        );
        $data_string = json_encode($data);
        $ch = curl_init("https://hooks.slack.com/services/$token");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($ch);
        echo $result;
    }
}
