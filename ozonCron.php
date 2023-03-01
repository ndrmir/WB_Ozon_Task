<?php
ini_set('display_errors', 'On');
require_once "Curl.php";
require_once "libraries/ConnectDb.php";

use PDOException as PDOException;
use PDO as PDO;
use Libraries\ConnectDb;
use Ozon\Curl;

$curl = new Curl();

// Получаем остатки

$data = '{
    "filter": {
        "visibility": "ALL"
        },
    "last_id": "",
    "limit": 10
}';

$method = "/v3/product/info/stocks";

$ObjDb = new ConnectDb();
$PDO = $ObjDb->connect();

$infoStoksTablename = 'info_stoks';
$strDate = date("Y-m-d H:i:s", time());

$total = 1;

while ($total) {
    $response = $curl->postOzon($data, $method);

    $result = $response['result'];
    $lastId = $result['last_id'];
    $total = $result['total'];

    $data = json_decode($data, true);
    $data['last_id'] = $lastId;
    $data = json_encode($data);

    if (!$total) {
        break;
    }

    $items = $result['items'];

    foreach ($items as $key => $value) {
        foreach ($value['stocks'] as $k => $v) {
            if ($v['type'] === 'fbs') {
                $fbs_present = $v['present'];
                $fbs_reserved = $v['reserved'];
            }
            if ($v['type'] === 'fbo') {
                $fbo_present = $v['present'];
                $fbo_reserved = $v['reserved'];
            }
        }

        try {
            $stmtStock = $PDO->prepare("INSERT INTO $infoStoksTablename VALUES (
                NULL,
                :product_id,
                :offer_id,
                :fbs_present,
                :fbs_reserved,
                :fbo_present,
                :fbo_reserved,
                :date
            )");
            $stmtStock->bindParam(':product_id', $value['product_id']);
            $stmtStock->bindParam(':offer_id', $value['offer_id']);
            $stmtStock->bindParam(':fbs_present', $fbs_present);
            $stmtStock->bindParam(':fbs_reserved', $fbs_reserved);
            $stmtStock->bindParam(':fbo_present', $fbo_present);
            $stmtStock->bindParam(':fbo_reserved', $fbo_reserved);
            $stmtStock->bindParam(':date', $strDate);
            $stmtStock->execute();
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }
        $id = $PDO->lastInsertId();
        echo 'id=' . $id . PHP_EOL;
    }
}
$ObjDb->close();
