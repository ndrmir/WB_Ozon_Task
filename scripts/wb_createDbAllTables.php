<?php

ini_set('display_errors', 'On');

require_once "../libraries/ConnectDb.php";

use Libraries\ConnectDb;

/**
 * Создание базы данных и всех таблиц для работы сайта
 *
 *
 */
$dbname = 'wildberries';
$dbhost = 'localhost';
$dbusername = 'root';
$dbuserpassword = 'root';

// function db_connect($dbhost, $dbusername, $dbuserpassword)
// {
//     $config = [
//         'dns' => 'mysql:host=' . $dbhost . ';charset=utf8',
//         'username' => $dbusername,
//         'password' => $dbuserpassword,
//     ];
//     try {
//         $db = new PDO($config['dns'], $config['username'], $config['password']);
//         $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
//         return $db;
//     } catch (PDOException $PDOException) {
//         echo $PDOException->getMessage();
//     }
//     return false;
// }
// function db_close($PDO)
// {
//         $PDO = null;
// }

// $PDO = db_connect($dbhost, $dbusername, $dbuserpassword);

// if (!$PDO) {
//     die("Не удалось подключиться к хосту $dbhost");
// }

// try {
//     $PDO->query('CREATE DATABASE IF NOT EXISTS `' . $dbname . '`');
// } catch (PDOException $exception) {
//     echo $exception->getMessage() . "Ошибка создания базы данных" . $dbname;
//     exit;
// }

// echo "База данных $dbname успешно создана.<br>";

// db_close($PDO);



$ObjDb = new ConnectDb('localhost', 'root', 'root', 'wildberries');
$db = $ObjDb->connect();
if (!$db) {
    die('Ошибка подключения к базе данных');
}

// Поставки
# создание таблицы supplier_incomes 

$tablename = 'supplier_incomes';

// Define table schema based on data structure
$tableSchema = "
    CREATE TABLE $tablename (
        id INT(11) NOT NULL AUTO_INCREMENT,
        incomeId INT(11) NOT NULL,
        number VARCHAR(255),
        date DATETIME,
        lastChangeDate DATETIME,
        supplierArticle VARCHAR(255),
        techSize INT(11),
        barcode VARCHAR(255),
        quantity INT(11),
        totalPrice DECIMAL(10,2),
        dateClose DATETIME,
        warehouseName VARCHAR(255),
        nmId INT(11),
        status VARCHAR(255),
        PRIMARY KEY (id)
    )
";

try {
    // Execute table creation query
    $db->exec($tableSchema);
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $tablename;
    exit;
}

echo "Таблица $tablename успешно создана.<br />";


// Цены
# создание таблицы price_info 

$tablename = 'price_info';

// Define table schema based on data structure
// Создаем таблицу
$tableSchema = "CREATE TABLE IF NOT EXISTS $tablename (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nmId INT(11) NOT NULL,
    price INT(11) NOT NULL,
    discount INT(11) NOT NULL,
    promoCode INT(11) NOT NULL
)";

try {
    // Execute table creation query
    $db->exec($tableSchema);
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $tablename;
    exit;
}

echo "Таблица $tablename успешно создана.<br />";


// Заказы
# создание таблицы order_info 

$tablename = 'order_info';

// Define table schema based on data structure
// Создаем таблицу
$tableSchema = "CREATE TABLE IF NOT EXISTS $tablename (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATETIME,
    lastChangeDate DATETIME,
    supplierArticle VARCHAR(255),
    techSize INT,
    barcode BIGINT,
    totalPrice DECIMAL(10,2),
    discountPercent INT,
    warehouseName VARCHAR(255),
    oblast VARCHAR(255),
    incomeID BIGINT,
    odid BIGINT,
    nmId BIGINT,
    subject VARCHAR(255),
    category VARCHAR(255),
    brand VARCHAR(255),
    isCancel BOOLEAN,
    cancel_dt DATETIME,
    /* значение не помещается в bigint */
    gNumber VARCHAR(255),

    sticker VARCHAR(255),
    srid VARCHAR(255)
)";

try {
    // Execute table creation query
    $db->exec($tableSchema);
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $tablename;
    exit;
}

echo "Таблица $tablename успешно создана.<br />";


// Отстатки
# создание таблицы stocks_info 

$tablename = 'stocks_info';

// Define table schema based on data structure
// Создаем таблицу
$tableSchema = "CREATE TABLE IF NOT EXISTS $tablename (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lastChangeDate DATETIME,
    supplierArticle VARCHAR(255),
    techSize INT,
    barcode VARCHAR(255),
    quantity INT,
    isSupply BOOLEAN,
    isRealization BOOLEAN,
    quantityFull INT,
    warehouseName VARCHAR(255),
    nmId INT,
    subject VARCHAR(255),
    category VARCHAR(255),
    daysOnSite INT,
    brand VARCHAR(255),
    SCCode VARCHAR(255),
    Price DECIMAL(10,2),
    Discount DECIMAL(10,2),
    date DATETIME
)";

try {
    // Execute table creation query
    $db->exec($tableSchema);
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $tablename;
    exit;
}

echo "Таблица $tablename успешно создана.<br />";


// Отчет о продажах по реализации
# создание таблицы saler_report_realisation 

$tablename = 'saler_report_by_realisation';

// Define table schema
$tableSchema = "CREATE TABLE IF NOT EXISTS $tablename (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    realizationreport_id INT(11) NOT NULL,
    date_from DATETIME NOT NULL,
    date_to DATETIME NOT NULL,
    create_dt DATETIME NOT NULL,
    suppliercontract_code VARCHAR(255),
    rrd_id BIGINT(20) NOT NULL,
    gi_id INT(11) NOT NULL,
    subject_name VARCHAR(255) NOT NULL,
    nm_id INT(11) NOT NULL,
    brand_name VARCHAR(255) NOT NULL,
    sa_name VARCHAR(255) NOT NULL,
    ts_name VARCHAR(255) NOT NULL,
    barcode VARCHAR(255) NOT NULL,
    doc_type_name VARCHAR(255) NOT NULL,
    quantity INT(11) NOT NULL,
    retail_price FLOAT NOT NULL,
    retail_amount FLOAT NOT NULL,
    sale_percent FLOAT NOT NULL,
    commission_percent FLOAT NOT NULL,
    office_name VARCHAR(255) NOT NULL,
    supplier_oper_name VARCHAR(255) NOT NULL,
    order_dt DATETIME NOT NULL,
    sale_dt DATETIME NOT NULL,
    rr_dt DATETIME NOT NULL,
    shk_id BIGINT(20) NOT NULL,
    retail_price_withdisc_rub FLOAT NOT NULL,
    delivery_amount INT(11) NOT NULL,
    return_amount INT(11) NOT NULL,
    delivery_rub FLOAT NOT NULL,
    gi_box_type_name VARCHAR(255) NOT NULL,
    product_discount_for_report FLOAT NOT NULL,
    supplier_promo FLOAT NOT NULL,
    rid BIGINT(20) NOT NULL,
    ppvz_spp_prc FLOAT NOT NULL,
    ppvz_kvw_prc_base FLOAT NOT NULL,
    ppvz_kvw_prc FLOAT NOT NULL,
    ppvz_sales_commission FLOAT NOT NULL,
    ppvz_for_pay FLOAT NOT NULL,
    ppvz_reward FLOAT NOT NULL,
    acquiring_fee FLOAT NOT NULL,
    acquiring_bank VARCHAR(255),
    ppvz_vw FLOAT NOT NULL,
    ppvz_vw_nds FLOAT NOT NULL,
    ppvz_office_id INT(11) NOT NULL,
    ppvz_office_name VARCHAR(255),
    ppvz_supplier_id INT(11) NOT NULL,
    ppvz_supplier_name VARCHAR(255),
    ppvz_inn VARCHAR(255),
    declaration_number VARCHAR(255),
    bonus_type_name VARCHAR(255),
    sticker_id VARCHAR(255),
    site_country VARCHAR(255) NOT NULL,
    penalty FLOAT NOT NULL,
    additional_payment FLOAT NOT NULL,
    srid VARCHAR(255) NOT NULL
)";


try {
    // Execute table creation query
    $db->exec($tableSchema);
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $tablename;
    exit;
}

echo "Таблица $tablename успешно создана.<br />";


// Продажи
# создание таблицы sales_info

$tablename = 'sales_info';

// Define table schema
$tableSchema = "CREATE TABLE IF NOT EXISTS $tablename (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    date DATETIME,
    lastChangeDate DATETIME,
    supplierArticle VARCHAR(255),
    techSize INT,
    barcode BIGINT(20),
    totalPrice DECIMAL(10,2),
    discountPercent INT,
    isSupply BOOLEAN,
    isRealization BOOLEAN,
    promoCodeDiscount DECIMAL(10,2),
    warehouseName VARCHAR(255),
    countryName VARCHAR(255),
    oblastOkrugName VARCHAR(255),
    regionName VARCHAR(255),
    incomeID BIGINT(20),
    saleID VARCHAR(255),
    odid BIGINT(20),
    spp INT,
    forPay DECIMAL(10,2),
    finishedPrice DECIMAL(10,2),
    priceWithDisc DECIMAL(10,2),
    nmId BIGINT(20),
    subject VARCHAR(255),
    category VARCHAR(255),
    brand VARCHAR(255),
    IsStorno BOOLEAN,
    gNumber VARCHAR(255),
    sticker VARCHAR(255),
    srid VARCHAR(255)
)";


try {
    // Execute table creation query
    $db->exec($tableSchema);
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $tablename;
    exit;
}

echo "Таблица $tablename успешно создана.<br />";
$ObjDb->close();
