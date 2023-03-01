<?php
ini_set('display_errors', 'On');
require_once "Curl.php";
require_once "libraries/ConnectDb.php";

use PDOException as PDOException;
use PDO as PDO;
use Libraries\ConnectDb;
use Ozon\Curl;

$curl = new Curl();

// Поставки

$method = '/api/v1/supplier/incomes';
$data['dateFrom'] = date(DATE_RFC3339, strtotime('2023-02-26'));
$url = 'https://statistics-api.wildberries.ru' . $method . '?dateFrom=' . $data['dateFrom'];

$response = $curl->getWB($url, 'statistic');

// Define table name
$tableName = 'supplier_incomes';

$ObjDb = new ConnectDb('localhost', 'root', 'root', 'wildberries');
$db = $ObjDb->connect();

// Prepare insert statement
$insertStatement = $db->prepare("
    INSERT INTO $tableName (
        incomeId,
        number,
        date,
        lastChangeDate,
        supplierArticle,
        techSize,
        barcode,
        quantity,
        totalPrice,
        dateClose,
        warehouseName,
        nmId,
        status
    ) VALUES (
        :incomeId,
        :number,
        :date,
        :lastChangeDate,
        :supplierArticle,
        :techSize,
        :barcode,
        :quantity,
        :totalPrice,
        :dateClose,
        :warehouseName,
        :nmId,
        :status
    )
");

// Loop through data and insert records into table
foreach ($response as $row) {
    try {
        $insertStatement->execute($row);
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}


// Цены

$url = 'https://suppliers-api.wildberries.ru/public/api/v1/info' . '?quantity=0';

$response = $curl->getWB($url, 'supplier');

// Define table name
$tableName = 'price_info';

// Prepare insert statement
$insertStatement = $db->prepare("
    INSERT INTO $tableName (
        nmId,
        price,
        discount,
        promoCode
    ) VALUES (
        :nmId,
        :price,
        :discount,
        :promoCode
    )
");

// Loop through data and insert records into table
foreach ($response as $row) {
    try {
        $insertStatement->execute($row);
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}



// Заказы

$data['dateFrom'] = date(DATE_RFC3339, strtotime('2023-02-26'));
$url = 'https://statistics-api.wildberries.ru/api/v1/supplier/orders' . '?dateFrom=' . $data['dateFrom'];

$response = $curl->getWB($url, 'statistic');

// Define table name
$tableName = 'order_info';

// Prepare insert statement
$insertStatement = $db->prepare("
    INSERT INTO $tableName (
        date,
        lastChangeDate,
        supplierArticle,
        techSize,
        barcode,
        totalPrice,
        discountPercent,
        warehouseName,
        oblast,
        incomeID,
        odid,
        nmId,
        subject,
        category,
        brand,
        isCancel,
        cancel_dt,
        gNumber,
        sticker,
        srid
    ) VALUES (
        :date,
        :lastChangeDate,
        :supplierArticle,
        :techSize,
        :barcode,
        :totalPrice,
        :discountPercent,
        :warehouseName,
        :oblast,
        :incomeID,
        :odid,
        :nmId,
        :subject,
        :category,
        :brand,
        :isCancel,
        :cancel_dt,
        :gNumber,
        :sticker,
        :srid
    )
");

// Loop through data and insert records into table
foreach ($response as $row) {
    $row['isCancel'] = $row['isCancel'] ? 1 : 0;
    try {
        print_r($row);
        $insertStatement->execute($row);
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}


// Отчет о продажах по реализации

$data['dateFrom'] = date(DATE_RFC3339, strtotime('2023-02-26'));
$data['dateTo'] = date(DATE_RFC3339, strtotime('2023-03-01'));
$data['rrdid'] = 0;

$url = 'https://statistics-api.wildberries.ru/api/v1/supplier/reportDetailByPeriod';

// Define table name
$tableName = 'saler_report_by_realisation';

$count = 1;

while ($count) {
    $tempUrl = $url . '?dateFrom=' . $data['dateFrom'] . '&' .
     'dateTo=' . $data['dateTo'] . '&' . 'rrdid=' . $data['rrdid'];

    $response = $curl->getWB($tempUrl, 'statistic');

    if (is_array($response)) {
        $count = count($response);
        $data['rrdid'] = end($response)['rrd_id'];
    } else {
        $count = false;
        break;
    }

    // В ответе четко не регламентировано наличие определенных полей
    // Создаю массив со всеми ключами
    $fullArray = [];

    foreach ($response as $item) {
        foreach ($item as $key => $value) {
            if (!isset($fullArray[$key])) {
                $fullArray[$key] = 1;
            }
        }
    }

    $keys = array_keys($fullArray);
    $columns = implode(', ', $keys);
    $values = ':';
    $values .= implode(', :', $keys);

    // Prepare insert statement
    $insertStatement = $db->prepare("
        INSERT INTO $tableName (
            $columns
        ) VALUES (
            $values
        )
    ");

    // Loop through data and insert records into table

    $fullKeys = array_keys($fullArray);

    foreach ($response as $row) {
        $insertData = [];
        // Подготавлмиваем даты к инсерту
        foreach ($row as $k => $v) {
            if (!is_array($v)) {
                if (substr($k, -3) === '_dt') {
                    // $v = str_replace('T', ' ', $v);
                    $v = str_replace('Z', '', $v);
                    $insertData[$k] = $v;
                } elseif ($k === 'date_from' || $k === 'date_to') {
                    $v = str_replace('Z', '', $v);
                    $insertData[$k] = $v;
                } else {
                    $insertData[$k] = $v;
                }
            }
        }
        // Дополняем отстутствующие поля
        $keys = array_keys($insertData);
        $diff = array_diff($fullKeys, $keys);
        foreach ($diff as $value) {
            $insertData[$value] = null;
        }

        try {
            print_r($insertData);
            $insertStatement->execute($insertData);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }
    }
}



// Продажи

$data['dateFrom'] = date(DATE_RFC3339, strtotime('2023-02-26'));

$url = 'https://statistics-api.wildberries.ru/api/v1/supplier/sales' . '?dateFrom=' . $data['dateFrom'];

// Define table name
$tableName = 'sales_info';

$response = $curl->getWB($url, 'statistic');

$keys = array_keys($response[0]);
$columns = implode(', ', $keys);
$values = ':';
$values .= implode(', :', $keys);

// Prepare insert statement
$insertStatement = $db->prepare("
    INSERT INTO $tableName (
        $columns
    ) VALUES (
        $values
    )
");

// Loop through data and insert records into table
foreach ($response as $row) {
    $row['isSupply'] = $row['isSupply'] ? 1 : 0;
    $row['isRealization'] = $row['isRealization'] ? 1 : 0;
    $row['IsStorno'] = $row['IsStorno'] ? 1 : 0;
    try {
        print_r($row);
        $insertStatement->execute($row);
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

// Close database connection
$ObjDb->close();
