<?php

namespace App;

use PDO;

class DataBaseTool {

    /**
     * Функция для заполнения "фейк данными БД"
     */
    public function getFakeDataForTable()
    {
        $dbc = new DataBaseConf();

        $sql = "INSERT INTO Category (title) VALUES
            ('Electronics'),
            ('Books'),
            ('Clothing'),
            ('Home & Kitchen'),
            ('Sports & Outdoors'),
            ('Beauty & Personal Care'),
            ('Toys & Games'),
            ('Automotive'),
            ('Health & Wellness'),
            ('Office Supplies');";

        $dbc->makeRequest($sql);
    }
}