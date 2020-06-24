<?php

function broker_input() {
        global $broker_key;

        $request_json = file_get_contents('php://input');
        $request = json_decode($request_json, true);

        if(isset($request['key'])) $user_key = $request['key'];
        else $user_key = '';

        if($user_key != $broker_key) {
                echo broker_fail("Invalid key");
                die();
        }
        return $request['message'];
}

function broker_success($reply) {
        global $broker_key;

        return json_encode(["result" => "ok", "key" => $broker_key, "reply" => $reply]);

}
function broker_delete($reply = '') {
        global $broker_key;

        return json_encode(["result" => "ok", "key" => $broker_key, "delete" => true, "reply" => $reply]);
}

function broker_fail($reason) {
        return json_encode(["result" => "fail", "reason" => $reason]);
}

function broker_add($destination, $request) {
        global $broker_url;
        global $broker_key;
        global $broker_project_name;

        $ch = curl_init($broker_url);
        $body = json_encode(["method" => "add", "key" => $broker_key, "source" => $broker_project_name, "destination" => $destination, "request" => $request]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $result_json = curl_exec($ch);
        curl_close($ch);        
        $result = json_decode($result_json, true);
        return $result;
}
