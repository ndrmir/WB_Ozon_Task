<?php

ini_set('display_errors', 'On');

require_once "../libraries/ConnectDb.php";

use Libraries\ConnectDb;

/**
 * Создание базы данных и всех таблиц для работы сайта
 *
 *
 */
$dbname = 'ozon';
$dbhost = 'localhost';
$dbusername = 'root';
$dbuserpassword = 'root';

function db_connect($dbhost, $dbusername, $dbuserpassword)
{
    $config = [
        'dns' => 'mysql:host=' . $dbhost . ';charset=utf8',
        'username' => $dbusername,
        'password' => $dbuserpassword,
    ];
    try {
        $db = new PDO($config['dns'], $config['username'], $config['password']);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $PDOException) {
        echo $PDOException->getMessage();
    }
    return false;
}
function db_close($PDO)
{
        $PDO = null;
}

$PDO = db_connect($dbhost, $dbusername, $dbuserpassword);

if (!$PDO) {
    die("Не удалось подключиться к хосту $dbhost");
}

try {
    $PDO->query('CREATE DATABASE IF NOT EXISTS `' . $dbname . '`');
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания базы данных" . $dbname;
    exit;
}

echo "База данных $dbname успешно создана.<br>";

db_close($PDO);



$ObjDb = new ConnectDb();
$PDO = $ObjDb->connect();
if (!$PDO) {
    die('Ошибка подключения к базе данных');
}

# создание таблицы остатков
$infoStoksTablename = 'info_stoks';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "product_id INT NOT NULL,";
$table_def .= "offer_id VARCHAR(30) BINARY NOT NULL,";
$table_def .= "fbs_present INT NOT NULL,";
$table_def .= "fbs_reserved INT NOT NULL,";
$table_def .= "fbo_present INT NOT NULL,";
$table_def .= "fbo_reserved INT NOT NULL,";
$table_def .= "date TIMESTAMP,";
$table_def .= "PRIMARY KEY (id)";


try {
    $PDO->query("CREATE TABLE $infoStoksTablename ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных " . $infoStoksTablename;
    exit;
}

echo "Таблица $infoStoksTablename успешно создана.<br />";


# создание таблицы отправлнеий
$fboPostingListTablename = 'fbo_posting_list';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "order_id INT NOT NULL,";
$table_def .= "order_number VARCHAR(30) BINARY NOT NULL,";
$table_def .= "posting_number VARCHAR(30) BINARY NOT NULL,";
$table_def .= "status VARCHAR(30) BINARY NOT NULL,";
$table_def .= "cancel_reason_id INT NOT NULL,";
$table_def .= "created_at TIMESTAMP,";
$table_def .= "in_process_at TIMESTAMP,";
$table_def .= "additional_data JSON DEFAULT NULL,";
$table_def .= "PRIMARY KEY (id)";

try {
    $PDO->query("CREATE TABLE $fboPostingListTablename ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных" . $fboPostingListTablename;
    exit;
}

echo "Таблица $fboPostingListTablename успешно создана.<br />";

# создание таблицы продуктов отправлений
$fboPostingListProductTablename = 'fbo_posting_list_product';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "order_id INT NOT NULL,";
$table_def .= "sku INT NOT NULL,";
$table_def .= "name VARCHAR(2000) BINARY NOT NULL,";
$table_def .= "quantity INT NOT NULL,";
$table_def .= "offer_id VARCHAR(200) BINARY NOT NULL,";
$table_def .= "price FLOAT(7,2) UNSIGNED NOT NULL,";
$table_def .= "digital_codes JSON DEFAULT NULL,";
$table_def .= "currency_code VARCHAR(20) BINARY NOT NULL,";
$table_def .= "PRIMARY KEY (id),";
$table_def .= "FOREIGN KEY (order_id) REFERENCES fbo_posting_list(id) ON UPDATE CASCADE ON DELETE CASCADE";

try {
    $PDO->query("CREATE TABLE $fboPostingListProductTablename ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных" . $fboPostingListProductTablename;
    exit;
}

echo "Таблица $fboPostingListProductTablename успешно создана.<br />";

# создание таблицы аналитических данных отправлений
$fboPostingListAnalyticsData = 'fbo_posting_list_analytics_data';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "order_id INT NOT NULL,";
$table_def .= "region VARCHAR(50) BINARY NOT NULL,";
$table_def .= "city VARCHAR(50) BINARY NOT NULL,";
$table_def .= "delivery_type VARCHAR(20) BINARY NOT NULL,";
$table_def .= "is_premium BOOLEAN NOT NULL DEFAULT 0,";
$table_def .= "payment_type_group_name VARCHAR(50) BINARY NOT NULL,";
$table_def .= "warehouse_id BIGINT NOT NULL,";
$table_def .= "warehouse_name VARCHAR(50) BINARY NOT NULL,";
$table_def .= "is_legal BOOLEAN NOT NULL DEFAULT 0,";
$table_def .= "PRIMARY KEY (id),";
$table_def .= "FOREIGN KEY (order_id) REFERENCES fbo_posting_list(id) ON UPDATE CASCADE ON DELETE CASCADE";

try {
    $PDO->query("CREATE TABLE $fboPostingListAnalyticsData ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных" . $fboPostingListAnalyticsData;
    exit;
}

echo "Таблица $fboPostingListAnalyticsData успешно создана.<br />";

# создание таблицы финансовых данных отправлений
$fboPostingListFinancialData = 'fbo_posting_list_financial_data';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "order_id INT NOT NULL,";
$table_def .= "cluster_from VARCHAR(50) BINARY NOT NULL,";
$table_def .= "cluster_to VARCHAR(50) BINARY NOT NULL,";
$table_def .= "PRIMARY KEY (id),";
$table_def .= "FOREIGN KEY (order_id) REFERENCES fbo_posting_list(id) ON UPDATE CASCADE ON DELETE CASCADE";

try {
    $PDO->query("CREATE TABLE $fboPostingListFinancialData ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных" . $fboPostingListFinancialData;
    exit;
}

echo "Таблица $fboPostingListFinancialData успешно создана.<br />";


# создание таблицы финансовых данных продуктов из отправлений
$fboPostingListFinancialDataProduct = 'fbo_posting_list_financial_data_product';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "financial_data_id INT NOT NULL,";
$table_def .= "commission_amount INT NOT NULL,";
$table_def .= "commission_percent INT NOT NULL,";
$table_def .= "payout INT NOT NULL,";
$table_def .= "product_id INT NOT NULL,";
$table_def .= "old_price INT NOT NULL,";
$table_def .= "price INT NOT NULL,";
$table_def .= "total_discount_value INT NOT NULL,";
$table_def .= "total_discount_percent INT NOT NULL,";
$table_def .= "actions JSON DEFAULT NULL,";
$table_def .= "picking VARCHAR(50) BINARY,";
$table_def .= "quantity INT NOT NULL,";
$table_def .= "client_price INT,";
$table_def .= "currency_code VARCHAR(20) BINARY NOT NULL,";
$table_def .= "PRIMARY KEY (id),";
$table_def .= "FOREIGN KEY (financial_data_id) REFERENCES fbo_posting_list_financial_data(id) ON UPDATE CASCADE ON DELETE CASCADE";

try {
    $PDO->query("CREATE TABLE $fboPostingListFinancialDataProduct ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных" . $fboPostingListFinancialDataProduct;
    exit;
}

echo "Таблица $fboPostingListFinancialDataProduct успешно создана.<br />";


# создание таблицы финансовых данных item_services продуктов из отправлений
$fboPostingListFinancialDataProductItemServices = 'fbo_posting_list_financial_data_product_item_services';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "product_id INT NOT NULL,";
$table_def .= "marketplace_service_item_fulfillment INT NOT NULL,";
$table_def .= "marketplace_service_item_pickup INT NOT NULL,";

$table_def .= "marketplace_service_item_dropoff_pvz INT NOT NULL,";
$table_def .= "marketplace_service_item_dropoff_sc INT NOT NULL,";
$table_def .= "marketplace_service_item_dropoff_ff INT NOT NULL,";

$table_def .= "marketplace_service_item_direct_flow_trans INT NOT NULL,";
$table_def .= "marketplace_service_item_return_flow_trans INT NOT NULL,";
$table_def .= "marketplace_service_item_deliv_to_customer INT NOT NULL,";

$table_def .= "marketplace_service_item_return_not_deliv_to_customer INT NOT NULL,";
$table_def .= "marketplace_service_item_return_part_goods_customer INT NOT NULL,";
$table_def .= "marketplace_service_item_return_after_deliv_to_customer INT NOT NULL,";

$table_def .= "PRIMARY KEY (id),";
$table_def .= "FOREIGN KEY (product_id) REFERENCES fbo_posting_list_financial_data_product(id) ON UPDATE CASCADE ON DELETE CASCADE";

try {
    $PDO->query("CREATE TABLE $fboPostingListFinancialDataProductItemServices ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных" . $fboPostingListFinancialDataProductItemServices;
    exit;
}

echo "Таблица $fboPostingListFinancialDataProductItemServices успешно создана.<br />";


# создание таблицы финансовых данных posting_services из отправлений
$fboPostingListFinancialDataPostingServices = 'fbo_posting_list_financial_data_posting_services';

$table_def = "id INT NOT NULL AUTO_INCREMENT,";
$table_def .= "financial_data_id INT NOT NULL,";
$table_def .= "marketplace_service_item_fulfillment INT NOT NULL,";
$table_def .= "marketplace_service_item_pickup INT NOT NULL,";

$table_def .= "marketplace_service_item_dropoff_pvz INT NOT NULL,";
$table_def .= "marketplace_service_item_dropoff_sc INT NOT NULL,";
$table_def .= "marketplace_service_item_dropoff_ff INT NOT NULL,";

$table_def .= "marketplace_service_item_direct_flow_trans INT NOT NULL,";
$table_def .= "marketplace_service_item_return_flow_trans INT NOT NULL,";
$table_def .= "marketplace_service_item_deliv_to_customer INT NOT NULL,";

$table_def .= "marketplace_service_item_return_not_deliv_to_customer INT NOT NULL,";
$table_def .= "marketplace_service_item_return_part_goods_customer INT NOT NULL,";
$table_def .= "marketplace_service_item_return_after_deliv_to_customer INT NOT NULL,";

$table_def .= "PRIMARY KEY (id),";
$table_def .= "FOREIGN KEY (financial_data_id) REFERENCES fbo_posting_list_financial_data(id) ON UPDATE CASCADE ON DELETE CASCADE";

try {
    $PDO->query("CREATE TABLE $fboPostingListFinancialDataPostingServices ($table_def) ENGINE=InnoDB;");
} catch (PDOException $exception) {
    echo $exception->getMessage() . "Ошибка создания таблицы данных" . $fboPostingListFinancialDataPostingServices;
    exit;
}

echo "Таблица $fboPostingListFinancialDataPostingServices успешно создана.<br />";


$ObjDb->close();
