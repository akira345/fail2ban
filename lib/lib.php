<?php

function fix_netblock($in_str){
    //ipcountの戻り値が1.0.0と最後の.0が抜ける場合があるので修正する
    $tmp_count = substr_count($in_str,".");
    if ($tmp_count != 3){
    $tmp_length = strlen($in_str);
    $tmp_sra_point = strpos($in_str,"/");
    $tmp_str = substr($in_str,0,$tmp_sra_point);
    for ($i=$tmp_count;$i<3;$i++){
        $tmp_str .= ".0";
    }
    $tmp_str .= substr($in_str,$tmp_sra_point,$tmp_length - $tmp_sra_point);
    return $tmp_str;
    }else{
        return $in_str;
    }
}

function get_port_no($service_name,$protocol){
    $port = "";
    if (preg_match('/^\d{1,5}$/', $service_name)){
        //数値ならポート番号とする。
        return $service_name;
    }else{
        //サービス名、プロトコルからポート番号を割り出す
        $port = getservbyname($service_name, $protocol);
        if (!$port){
            //見つからなかった
            $port = "";
        }
        return $port;
    }
}