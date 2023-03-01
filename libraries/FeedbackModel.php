<?php

namespace Libraries;

require_once "../libraries/ConnectDb.php";

use PDOException;
use PDO;
use Libraries\ConnectDb;

// Класс проверки и сохранения данных формы
class FeedbackModel
{
    protected $user_tablename;
    protected $file_tablename;
    protected $ObjDb;
    protected $uploaddir;
    protected $fileNameArr = array();
    public $data = array();

    public function __construct()
    {
        session_start();
        $this->user_tablename = "users";
        $this->file_tablename = "files";
        $this->uploaddir = "../uploads/";
    }

    public function saveData()
    {
        $this->ObjDb = new ConnectDb();
        $PDO = $this->ObjDb->connect();

        $name = htmlspecialchars($_POST['name']);
        $phone = htmlspecialchars($_POST['phone']);
        $email = mb_strtolower(htmlspecialchars($_POST['email']));
        $password = htmlspecialchars($_POST['password']);

        if (empty($name)) {
            self::errorMessage("Введите свое имя!");
        }

        if (empty($phone)) {
            self::errorMessage("Введите свой номер телефона!");
        }

        if (empty($email)) {
            self::errorMessage("Введите свой e-mail-адрес!");
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
            self::errorMessage("Неверный формат e-mail-адреса!");
        }
        if ($password != $_SESSION['password']) {
            self::errorMessage("Код с изображения не верен!");
        }


        $query = "START TRANSACTION;";

        try {
            $result = $PDO->query($query);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        // Проверяем есть пользователь в базе или нет
        try {
            $stmt = $PDO->prepare("SELECT user_id FROM $this->user_tablename WHERE email = ?");
            $stmt->execute([$email]);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        $query_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $query_data['user_id'] ?? null;
        //Пользователь новый заносим данные в базу
        if (!$user_id) {
            try {
                $stmt = $PDO->prepare("INSERT INTO $this->user_tablename VALUES (NULL, :name, :phone, :email)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }


            try {
                $stmt = $PDO->prepare(
                    "SELECT user_id FROM $this->user_tablename WHERE email = ?"
                );
                $stmt->execute([$email]);
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }

            $query_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $user_id = $query_data['user_id'];
        }

        self::saveFiles($user_id);


        foreach ($this->fileNameArr as $filename) {
            //Проверяем существование файла у пользователя
            try {
                $stmt = $PDO->prepare(
                    "SELECT file_id FROM $this->file_tablename WHERE
                     filename = :filename AND user_id = :user_id"
                );
                $stmt->bindParam(':filename', $filename);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
            } catch (PDOException $exception) {
                echo $exception->getMessage();
                exit;
            }

            $query_data = $stmt->fetch(PDO::FETCH_ASSOC);
            $file_id = $query_data['file_id'] ?? null;
            //Если файл новый у пользователя заносим в базу
            if (!$file_id) {
                try {
                    $stmt = $PDO->prepare(
                        "INSERT INTO $this->file_tablename VALUES
                         (NULL, :user_id, :filename, CURRENT_TIMESTAMP)"
                    );
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':filename', $filename);
                    $stmt->execute();
                } catch (PDOException $exception) {
                    echo $exception->getMessage();
                    exit;
                }
            }
        }

        $query = "COMMIT;";

        try {
            $result = $PDO->query($query);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
            exit;
        }

        $this->data[] = 'Данные пользователя сохранены.';
        $this->ObjDb->close();
    }

    private function saveFiles($user_id)
    {
        // Название <input type="file">
        $input_name = 'file';

        // Разрешенные расширения файлов.
        $allow = array();

        // Запрещенные расширения файлов.
        $deny = array(
                'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp',
                'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html',
                'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'
        );

        if (!isset($_FILES[$input_name])) {
            $error = 'Файлы не загружены.';
            $this->data[] = $error;
        } else {
            // Преобразуем массив $_FILES в удобный вид для перебора в foreach.
            $files = array();
            $diff = count($_FILES[$input_name]) - count($_FILES[$input_name], COUNT_RECURSIVE);
            if ($diff == 0) {
                    $files = array($_FILES[$input_name]);
            } else {
                foreach ($_FILES[$input_name] as $k => $l) {
                    foreach ($l as $i => $v) {
                            $files[$i][$k] = $v;
                    }
                }
            }

            foreach ($files as $file) {
                $error = $success = '';

                // Проверим на ошибки загрузки.
                if (!empty($file['error']) || empty($file['tmp_name'])) {
                        $error = 'Не удалось загрузить файл.';
                } elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name'])) {
                        $error = 'Не удалось загрузить файл.';
                } else {
                    // Оставляем в имени файла только буквы, цифры и некоторые символы.
                    $pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
                    $name = mb_eregi_replace($pattern, '-', $file['name']);
                    $name = mb_ereg_replace('[-]+', '-', $name);
                    //сокращаем длинну имени файла
                    $temp = explode('.', $name);
                    $length = 50 - mb_strlen($temp[1], "UTF-8") - mb_strlen('$user_id', "UTF-8");
                    $length--;
                    $temp[0] = mb_substr($temp[0], 0, $length, "UTF-8");
                    $name = $user_id . "_" . $temp[0] . "." . $temp[1];
                    $parts = pathinfo($name);

                    if (empty($name) || empty($parts['extension'])) {
                        $error = 'Недопустимый тип файла';
                    } elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) {
                        $error = 'Недопустимый тип файла';
                    } elseif (!empty($deny) && in_array(strtolower($parts['extension']), $deny)) {
                        $error = 'Недопустимый тип файла';
                    } else {
                        // Создаем директорию
                        if (!file_exists($this->uploaddir)) {
                            if (!mkdir($this->uploaddir, 0777, true)) {
                                $error = 'Не удалось создать директорию.';
                                $this->errorMessage($error);
                            }
                        }
                        // Перемещаем файл в директорию.
                        if (move_uploaded_file($file['tmp_name'], $this->uploaddir . $name)) {
                                // Далее можно сохранить название файла в БД и т.п.
                                $this->fileNameArr[] = $name;
                                $success = 'Файл «' . $name . '» успешно загружен.';
                        } else {
                                $error = 'Не удалось загрузить файл.';
                        }
                    }
                }

                if (!empty($success)) {
                        $this->data[] = $success;
                }
                if (!empty($error)) {
                        $this->data[] = $error;
                }
            }
        }
    }

    public function getMessage()
    {
        foreach ($this->data as $val) {
            echo $val . '<br/>';
        }
    }

    private function errorMessage($msg)
    {
        echo $msg;
        exit;
    }
}
