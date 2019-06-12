#!/usr/bin/php -n
<?php
require_once 'vendor/autoload.php';
require_once 'config/db.php';
require_once 'lib/lib.php';

//引数はフィルタ名、プロトコル、サービス名またはポート番号、IPアドレス
if(count($argv) != 5){
    echo "引数が不正です";
    exit(1);
}

$name = $argv[1];
$protocol = $argv[2];
$service_name = $argv[3];
$ip = $argv[4];
$port = "";
$port = get_port_no($service_name,$protocol);
$hostname = gethostname();

if (filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){
    //IPv4なら
    $country_cd = "";
    $netblock = "";
    $country_name = "";
    try{
        $block = "";
        //Select ip
        $sql = "SELECT lst.*,(select c.country_name from country c "
            . "where lst.country = c.country_cd) country_name "
            . "FROM iplist lst "
            . "WHERE "
            . "inet_aton(:ip) between inet_aton(lst.ip) and (inet_aton(lst.ip) + (lst.kosu -1))";
        //DBから指定範囲にあるデータを抜く
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt -> bindParam(':ip',$ip,PDO::PARAM_STR);
        $stmt -> execute();
        while($row = $stmt -> fetch()){
            $country_cd = $row["country"];
            $netblock   = $row["netblock"];
            $netblock = fix_netblock($netblock);
            $country_name = $row["country_name"];
            $block = IPBlock::create($netblock);
            if ($block->contains($ip)){
                //IPアドレスが範囲にあれば処理を抜ける(念のため)
                break;
            }
        }
    } catch (PDOException $e) {
        echo $e -> getMessage();
        exit(1);
    }
    try{
        //DBに登録
        $sql = "INSERT INTO fail2ban set "
        . "hostname = :hostname,"
        . "created = Now(),"
        . "name = :name,"
        . "protocol = :protocol,"
        . "port = :port,"
        . "service_name = :service_name,"
        . "ip = :ip,"
        . "netblock = :netblock,"
        . "country_cd = :country_cd,"
        . "country_name = :country_name";
        $db = getDB();
        $db->beginTransaction();
        $stmt = $db->prepare($sql);
        $stmt -> bindParam(':hostname',$hostname,PDO::PARAM_STR);
        $stmt -> bindParam(':name',$name,PDO::PARAM_STR);
        $stmt -> bindParam(':protocol',$protocol,PDO::PARAM_STR);
        $stmt -> bindParam(':port',$port,PDO::PARAM_STR);
        $stmt -> bindParam(':service_name',$service_name,PDO::PARAM_STR);
        $stmt -> bindParam('ip',$ip,PDO::PARAM_STR);
        $stmt -> bindParam('netblock',$netblock,PDO::PARAM_STR);
        $stmt -> bindParam('country_cd',$country_cd,PDO::PARAM_STR);
        $stmt -> bindParam('country_name',$country_name,PDO::PARAM_STR);
        $stmt -> execute();
        $db -> commit();
    } catch (PDOException $e) {
        $db -> rollback();
        echo $e -> getMessage();
        exit(1);
    }
    exit(0);
}
exit(0);
