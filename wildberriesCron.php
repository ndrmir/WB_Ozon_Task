<?php
ini_set('display_errors', 'On');
require_once "Curl.php";
require_once "libraries/ConnectDb.php";

use PDOException as PDOException;
use PDO as PDO;
use Libraries\ConnectDb;
use Ozon\Curl;

$curl = new Curl();

$ObjDb = new ConnectDb('localhost', 'root', 'root', 'wildberries');
$db = $ObjDb->connect();

// Остатки

$data['dateFrom'] = date(DATE_RFC3339, strtotime('2023-02-26'));
$url = 'https://statistics-api.wildberries.ru/api/v1/supplier/stocks' . '?dateFrom=' . $data['dateFrom'];

$strDate = date("Y-m-d H:i:s", time());

$response = $curl->getWB($url, 'statistic');

// Define table name
$tableName = 'stocks_info';

// Prepare insert statement
$insertStatement = $db->prepare("
    INSERT INTO $tableName (
        lastChangeDate,
        supplierArticle,
        techSize,
        barcode,
        quantity,
        isSupply,
        isRealization,
        quantityFull,
        warehouseName,
        nmId,
        subject,
        category,
        daysOnSite,
        brand,
        SCCode,
        Price,
        Discount,
        date
    ) VALUES (
        :lastChangeDate,
        :supplierArticle,
        :techSize,
        :barcode,
        :quantity,
        :isSupply,
        :isRealization,
        :quantityFull,
        :warehouseName,
        :nmId,
        :subject,
        :category,
        :daysOnSite,
        :brand,
        :SCCode,
        :Price,
        :Discount,
        :date
    )
");

// Loop through data and insert records into table
foreach ($response as $row) {
    $row['isSupply'] = $row['isSupply'] ? 1 : 0;
    $row['isRealization'] = $row['isRealization'] ? 1 : 0;
    $row['date'] = $strDate;
    try {
        $insertStatement->execute($row);
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

// Close database connection
$ObjDb->close();
