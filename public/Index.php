<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/database/Conexao.php';
require_once __DIR__ . '/../src/controller/Controller.php';

$pdo = Conexao::getInstance();

$controller = new Controller($pdo);
$controller->handleRequest();