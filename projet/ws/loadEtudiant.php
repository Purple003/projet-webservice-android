<?php
include_once __DIR__ . '/../service/EtudiantService.php';
$es = new EtudiantService();
header('Content-Type: application/json');
echo json_encode($es->findAllApi());
?>