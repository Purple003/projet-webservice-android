<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include_once '../service/EtudiantService.php';
    extract($_POST);
    $es = new EtudiantService();
    $es->create(new Etudiant(null, $nom, $prenom, $ville, $sexe));


    header('Content-Type: application/json');
    echo json_encode($es->findAllApi());
}
?>