<?php
ini_set('display_errors', 'On');
require_once "Curl.php";
require_once "libraries/ConnectDb.php";

use PDOException as PDOException;
use PDO as PDO;
use Libraries\ConnectDb;
use Ozon\Curl;

$curl = new Curl();

// Список отправлений

$strDate = date("Y-m-d\TH:i:s\Z", time());

$data = '{
    "dir": "ASC",
    "filter": {
    "since": "1970-01-01T10:44:12.828Z",
    "status": "",
    "to": "' . $strDate . '"
    },
    "limit": 5,
    "offset": 0,
    "translit": true,
    "with": {
    "analytics_data": true,
    "financial_data": true
    }
}';

$method = "/v2/posting/fbo/list";

$ObjDb = new ConnectDb();
$PDO = $ObjDb->connect();

$fboPostingListTablename = 'fbo_posting_list';
$fboPostingListProductTablename = 'fbo_posting_list_product';
$fboPostingListAnalyticsData = 'fbo_posting_list_analytics_data';
$fboPostingListFinancialData = 'fbo_posting_list_financial_data';
$fboPostingListFinancialDataProduct = 'fbo_posting_list_financial_data_product';
$fboPostingListFinancialDataProductItemServices = 'fbo_posting_list_financial_data_product_item_services';
$fboPostingListFinancialDataPostingServices = 'fbo_posting_list_financial_data_posting_services';

$count = 1;
$offset = 5 - 1;
while ($count) {
    $response = $curl->postOzon($data, $method);
    $items = $response['result'];
    $count = count($items);
    if (!$count) {
        break;
    }

    $data = json_decode($data, true);
    $data['offset'] = $offset;
    $data = json_encode($data);


    foreach ($items as $key => $value) {
        // Проверяем есть ли заказ в таблице fbo_posting_list
        try {
            $stmtStock = $PDO->prepare("SELECT id FROM $fboPostingListTablename WHERE order_id = ?");
            $stmtStock->execute([$value['order_id']]);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        $insertData = [];
        foreach ($value as $k => $v) {
            if (!is_array($v)) {
                if (substr($k, -3) === '_at') {
                    $str = str_replace('T', ' ', $v);
                    $str = str_replace('Z', '', $str);
                    $insertData[$k] = $str;
                } elseif (substr($k, 0, 3) === 'is_') {
                    $v = $v ? 1 : 0;
                    $insertData[$k] = $v;
                } else {
                    $insertData[$k] = $v;
                }
            } elseif ($k === 'additional_data') {
                $insertData[$k] = json_encode($v);
            }
        }

        $query_data = $stmtStock->fetch(PDO::FETCH_ASSOC);
        $id = $query_data['id'] ?? null;
        
        if (!$id) {
            // заказ новый заносим данные в таблицу fbo_posting_list
            echo 'заказ новый заносим данные в таблицу fbo_posting_list' . PHP_EOL;
            try {
                $stmt = $PDO->prepare("INSERT INTO $fboPostingListTablename VALUES (
                    NULL,
                    :order_id,
                    :order_number,
                    :posting_number,
                    :status,
                    :cancel_reason_id,
                    :created_at,
                    :in_process_at,
                    :additional_data
                )");
                $stmt->execute($insertData);
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }
            $order_id = $PDO->lastInsertId();
            echo 'order_id=' . $order_id . PHP_EOL;

            $insertData = [];
            $insertData['order_id'] = $order_id;

            foreach ($value['products'] as $item) {
                foreach ($item as $k => $v) {
                    if (!is_array($v)) {
                        if (substr($k, -3) === '_at') {
                            $str = str_replace('T', ' ', $v);
                            $str = str_replace('Z', '', $str);
                            $insertData[$k] = $str;
                        } elseif (substr($k, 0, 3) === 'is_') {
                            $v = $v ? 1 : 0;
                            $insertData[$k] = $v;
                        } else {
                            $insertData[$k] = $v;
                        }
                    } elseif ($k === 'digital_codes') {
                        $insertData[$k] = json_encode($v);
                    }
                }
                // заносим данные в таблицу fbo_posting_list_product
                echo 'заказ новый заносим данные в таблицу fbo_posting_list_product' . PHP_EOL;

                try {
                    $stmt = $PDO->prepare("INSERT INTO $fboPostingListProductTablename VALUES (
                        NULL,
                        :order_id,
                        :sku,
                        :name,
                        :quantity,
                        :offer_id,
                        :price,
                        :digital_codes,
                        :currency_code
                    )");
                    $stmt->execute($insertData);
                } catch (PDOException $exception) {
                    echo $exception->getMessage();
                    exit;
                }
            }            

            $insertData = [];
            foreach ($value['analytics_data'] as $k => $v) {
                if (!is_array($v)) {
                    if (substr($k, -3) === '_at') {
                        $str = str_replace('T', ' ', $v);
                        $str = str_replace('Z', '', $str);
                        $insertData[$k] = $str;
                    } elseif (substr($k, 0, 3) === 'is_') {
                        $v = $v ? 1 : 0;
                        $insertData[$k] = $v;
                    } else {
                        $insertData[$k] = $v;
                    }
                }
            }
            $insertData['order_id'] = $order_id;
            // заносим данные в таблицу fbo_posting_list_analytics_data
            echo 'заказ новый заносим данные в таблицу fbo_posting_list_analytics_data' . PHP_EOL;
            try {
                $stmt = $PDO->prepare("INSERT INTO $fboPostingListAnalyticsData VALUES (
                    NULL,
                    :order_id,
                    :region,
                    :city,
                    :delivery_type,
                    :is_premium,
                    :payment_type_group_name,
                    :warehouse_id,
                    :warehouse_name,
                    :is_legal
                )");
                $stmt->execute($insertData);
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }

            $insertData = [];
            $insertData['order_id'] = $order_id;

            foreach ($value['financial_data'] as $k => $v) {
                if (!is_array($v)) {
                    if ($k === 'posting_services') {
                        continue;
                    }
                    if (substr($k, -3) === '_at') {
                        $str = str_replace('T', ' ', $v);
                        $str = str_replace('Z', '', $str);
                        $insertData[$k] = $str;
                    } elseif (substr($k, 0, 3) === 'is_') {
                        $v = $v ? 1 : 0;
                        $insertData[$k] = $v;
                    } elseif (substr($k, -6) === '_price') {
                        $v = (int)$v;
                        $insertData[$k] = $v;
                    } else {
                        $insertData[$k] = $v;
                    }
                }
            }

            // заносим данные в таблицу fbo_posting_list_financial_data
            echo 'заказ новый заносим данные в таблицу fbo_posting_list_financial_data' . PHP_EOL;
            try {
                $stmt = $PDO->prepare("INSERT INTO $fboPostingListFinancialData VALUES (
                    NULL,
                    :order_id,
                    :cluster_from,
                    :cluster_to
                )");
                $stmt->execute($insertData);
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }

            // заносим данные в таблицу fbo_posting_list_financial_data_product
            echo 'заказ новый заносим данные в таблицу fbo_posting_list_financial_data_product' . PHP_EOL;

            $insertData = [];
            $financial_data_id = $PDO->lastInsertId();
            $insertData['financial_data_id'] = $financial_data_id;

            foreach ($value['financial_data']['products'] as $item) {
                foreach ($item as $k => $v) {
                    if (!is_array($v)) {
                        if (substr($k, -3) === '_at') {
                            $str = str_replace('T', ' ', $v);
                            $str = str_replace('Z', '', $str);
                            $insertData[$k] = $str;
                        } elseif (substr($k, 0, 3) === 'is_') {
                            $v = $v ? 1 : 0;
                            $insertData[$k] = $v;
                        } elseif (substr($k, -6) === '_price') {
                            $v = (int)$v;
                            $insertData[$k] = $v;
                        } else {
                            $insertData[$k] = $v;
                        }
                    } elseif ($k === 'actions') {
                        $insertData[$k] = json_encode($v);
                    }
                }

                try {
                    $stmt = $PDO->prepare("INSERT INTO $fboPostingListFinancialDataProduct VALUES (
                        NULL,
                        :financial_data_id,
                        :commission_amount,
                        :commission_percent,
                        :payout,
                        :product_id,
                        :old_price,
                        :price,
                        :total_discount_value,
                        :total_discount_percent,
                        :actions,
                        :picking,
                        :quantity,
                        :client_price,
                        :currency_code
                    )");
                    $stmt->execute($insertData);
                } catch (PDOException $exception) {
                    echo $exception->getMessage();
                    exit;
                }
    
                // заносим данные в таблицу fbo_posting_list_analytics_data_product_item_services
                echo 'заказ новый заносим данные в таблицу fbo_posting_list_analytics_data_product_item_services' . PHP_EOL;
                foreach ($value['financial_data']['products'] as $item) {
                    $insertData = [];
                    $insertData = $item['item_services'];
                    $financial_product_id = $PDO->lastInsertId();
                    $insertData['product_id'] = $financial_product_id;
        
                    try {
                        $stmt = $PDO->prepare("INSERT INTO $fboPostingListFinancialDataProductItemServices VALUES (
                            NULL,
                            :product_id,
                            :marketplace_service_item_fulfillment,
                            :marketplace_service_item_pickup,
                            :marketplace_service_item_dropoff_pvz,
                            :marketplace_service_item_dropoff_sc,
                            :marketplace_service_item_dropoff_ff,
                            :marketplace_service_item_direct_flow_trans,
                            :marketplace_service_item_return_flow_trans,
                            :marketplace_service_item_deliv_to_customer,
                            :marketplace_service_item_return_not_deliv_to_customer,
                            :marketplace_service_item_return_part_goods_customer,
                            :marketplace_service_item_return_after_deliv_to_customer
                        )");
                        $stmt->execute($insertData);
                    } catch (PDOException $exception) {
                        echo $exception->getMessage();
                        exit;
                    }
                }
            }


            // заносим данные в таблицу fbo_posting_list_financial_data_posting_services
            echo 'заказ новый заносим данные в таблицу fbo_posting_list_financial_data_posting_services' . PHP_EOL;
            $insertData = [];
            $insertData = $value['financial_data']['posting_services'];
            if (is_array($insertData)) {
                $insertData['financial_data_id'] = $financial_data_id;

                try {
                    $stmt = $PDO->prepare("INSERT INTO $fboPostingListFinancialDataPostingServices VALUES (
                        NULL,
                        :financial_data_id,
                        :marketplace_service_item_fulfillment,
                        :marketplace_service_item_pickup,
                        :marketplace_service_item_dropoff_pvz,
                        :marketplace_service_item_dropoff_sc,
                        :marketplace_service_item_dropoff_ff,
                        :marketplace_service_item_direct_flow_trans,
                        :marketplace_service_item_return_flow_trans,
                        :marketplace_service_item_deliv_to_customer,
                        :marketplace_service_item_return_not_deliv_to_customer,
                        :marketplace_service_item_return_part_goods_customer,
                        :marketplace_service_item_return_after_deliv_to_customer
                    )");
                    $stmt->execute($insertData);
                } catch (PDOException $exception) {
                    echo $exception->getMessage();
                    exit;
                }
            }
        }
    }
    $offset += 5;
}

$ObjDb->close();

// Список отправлений V3 - данных не вернул
// Сервер отвечает, но массив данных пуст.

$strDate = date("Y-m-d\TH:i:s\Z", time());

$data = '{
    "dir": "ASC",
    "filter": {
    "since": "2023-02-26T10:44:12.828Z",
    "status": "",
    "to": "' . $strDate . '"
    },
    "limit": 10,
    "offset": 0,
    "translit": true,
    "with": {
    "analytics_data": true,
    "financial_data": true
    }
}';

$method = "/v3/posting/fbs/list";

// $ObjDb = new ConnectDb();
// $PDO = $ObjDb->connect();

$response = $curl->postOzon($data, $method);
print_r($response);


// $ObjDb->close();
