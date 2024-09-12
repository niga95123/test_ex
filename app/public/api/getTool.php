<?php

require __DIR__ . '/../../src/DataBaseTool.php';

use App\DataBaseTool;

if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    $g = $_GET['methode'] ?? null;

    if ($g != null) {
        $dbt = new DataBaseTool();
        $methodeEndName = 'Api';
        $methodeName = $g . $methodeEndName;
        echo json_encode(['ans' => method_exists($dbt, $methodeName) ? $dbt->$methodeName() : $dbt->getError()]);
    }
}