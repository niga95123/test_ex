<?php

namespace App;

require __DIR__ . '/../vendor/autoload.php';


use PDO;

class DataBaseTool {

    /**
     * Функция для заполнения "фейк данными БД"
     */
    public function getFakeDataForCategory()
    {
        $dbc = new DataBaseConf();

        $sql = "INSERT INTO Category (title) VALUES
            ('Общие'),
            ('Ковид'),
            ('Шутки'),
            ('Новости'),
            ('Спорт'),
            ('Технологии'),
            ('Музыка'),
            ('Фильмы'),
            ('Путешествия'),
            ('Еда');";

        $dbc->makeRequest($sql);
    }

    public function getError(): string
    {
        return 'bad answer check func!';
    }

    public function getCategoryAllDataApi()
    {
        try {
            $dbc = new DataBaseConf();

            $pdo = new PDO($dbc->getDsn(), $dbc->getUsername(), $dbc->getPassword());
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Получение категорий
            $stmt = $pdo->query("SELECT * FROM Category");

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            /*
             * Return array format =>
              [
                0 => [
                   'id' => '1',
                    'title' => 'test'
                    ]
              ]
             */
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getTweetsAllDataApi()
    {
        try {
            $dbc = new DataBaseConf();

            $pdo = new PDO($dbc->getDsn(), $dbc->getUsername(), $dbc->getPassword());
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Получение твитов
            $stmt = $pdo->query("SELECT Twits.*, Category.title AS category_title FROM Twits JOIN Category ON Twits.CategoryId = Category.id ORDER BY CreatedAt ASC");

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            /*
             * Return array format =>
               [
                 0 => [
                    'id' => '4',
                    'CategoryId' => '1',
                    'Username' => '123',
                    'Content' => '1',
                    'CreatedAt' => '1',
                    'category_title' => '1'
                   ]
                  ...
                ]
             */
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getCheckFullnessDataBaseApi()
    {
        $responseCode = 'error';
        try {
            $dbc = new DataBaseConf();

            $pdo = new PDO($dbc->getDsn(), $dbc->getUsername(), $dbc->getPassword());
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->query("CREATE TABLE IF NOT EXISTS Category (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    title VARCHAR(255) NOT NULL)");
            $CategoryTable = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt = $pdo->query("CREATE TABLE IF NOT EXISTS Twits (
                                     id INT AUTO_INCREMENT PRIMARY KEY,
                                     CategoryId INT,
                                     Username VARCHAR(255) NOT NULL,
                                     Content TEXT NOT NULL,
                                     CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                     FOREIGN KEY (CategoryId) REFERENCES Category(id))");
            $TwitsTable = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->query("SELECT * FROM Category");
            $CategoriesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$CategoriesData) {
                $this->getFakeDataForCategory();
            }
            if (!$CategoryTable && !$TwitsTable) {
                $responseCode = 'success';
            }
            return ['status' => $responseCode];
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}