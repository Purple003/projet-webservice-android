<?php
include_once __DIR__ . '/../classes/Etudiant.php';
include_once __DIR__ . '/../connexion/Connexion.php';
include_once __DIR__ . '/../dao/IDao.php';


class EtudiantService implements IDao {
    private $connexion;

    function __construct() { $this->connexion = new Connexion(); }

    public function create($o) {
        $query = "INSERT INTO Etudiant (nom, prenom, ville, sexe)
                  VALUES (:nom, :prenom, :ville, :sexe)";
        $stmt = $this->connexion->getConnexion()->prepare($query);
        $stmt->execute([
            ':nom' => $o->getNom(),
            ':prenom' => $o->getPrenom(),
            ':ville' => $o->getVille(),
            ':sexe' => $o->getSexe()
        ]);
    }

    public function findAllApi() {
        $req = $this->connexion->getConnexion()->query("SELECT * FROM Etudiant");
        return $req->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>