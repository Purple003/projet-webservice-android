# TP 8 — Consommer un Web Service PHP 8 depuis une application Android (Volley + Gson)

**Cours :** Programmation Mobile — Android avec Java

---

## Présentation

Ce dépôt contient deux parties du TP :

* **backend-php/** : service Web en PHP 8 (MySQL + PDO) qui expose des endpoints pour ajouter et lister des étudiants (JSON).
* **android-app/** : application Android (Java) qui consomme le service via **Volley** et désérialise le JSON avec **Gson**.

Le but : apprendre à créer un WebService simple en PHP et à le consommer depuis Android.

---

## Structure recommandée du dépôt

```
mon-projet-ws/
├─ projet/        # Projet PHP (à déposer dans C:\xampp\htdocs\projet ou sur votre serveur)
│  ├─ classes/
│  │   └─ Etudiant.php
│  ├─ connexion/
│  │   └─ Connexion.php
│  ├─ dao/
│  │   └─ IDao.php
│  ├─ service/
│  │   └─ EtudiantService.php
│  └─ ws/
│      ├─ createEtudiant.php
│      └─ loadEtudiant.php
└─ projetws/        # Projet Android Studio (module app)
   ├─ app/src/main/java/.. (code Java)
   └─ app/src/main/res/..  (layouts, xml)
```

---

## Contenu du README

Ce README explique :

1. comment préparer la base MySQL,
2. le rôle des fichiers PHP (explication du code),
3. comment configurer et lancer l'application Android,
4. où insérer les captures d'écran / démo.

---

## 1) Base de données MySQL (XAMPP)

Démarrer XAMPP (Apache + MySQL) puis ouvrir `http://localhost/phpmyadmin`.

**SQL pour créer la base et la table :**

```sql
CREATE DATABASE school1;

CREATE TABLE Etudiant (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50),
  prenom VARCHAR(50),
  ville VARCHAR(50),
  sexe VARCHAR(10)
);

INSERT INTO Etudiant (nom, prenom, ville, sexe)
VALUES ('Lachgar', 'Mohamed', 'Rabat', 'homme'),
       ('Safi', 'Amine', 'Marrakech', 'homme');
```

---

## 2) Backend PHP — explication des fichiers importants

Tous les fichiers PHP doivent être placés dans `C:\xampp\htdocs\projet` (ou l'équivalent sur votre serveur).

### `connexion/Connexion.php`

* **Rôle :** encapsule la connexion PDO à MySQL.
* **Points clés :** utilisation de `charset=utf8`, gestion des exceptions et `PDO::ERRMODE_EXCEPTION`.

```php
// Connexion.php
class Connexion {
    private $connexion;
    public function __construct() {
        try {
            $this->connexion = new PDO("mysql:host=localhost;dbname=school1;charset=utf8", "root", "");
            $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function getConnexion() { return $this->connexion; }
}
```

### `classes/Etudiant.php`

* **Rôle :** modèle simple représentant un étudiant (id, nom, prenom, ville, sexe).

### `dao/IDao.php`

* **Rôle :** interface décrivant les opérations CRUD (create, delete, update, findAll, findById).

### `service/EtudiantService.php`

* **Rôle :** implémente `IDao` et contient la logique d'accès à la base (utilise `Connexion`).
* **Méthodes importantes :**

  * `create($o)` : prépare et exécute une insertion avec paramètres nommés pour éviter les injections.
  * `findAllApi()` : effectue `SELECT * FROM Etudiant` et retourne un tableau associatif prêt à être encodé en JSON.

### `ws/createEtudiant.php` (endpoint POST)

* **Rôle :** reçoit des paramètres `nom`, `prenom`, `ville`, `sexe` via POST, crée l'étudiant, puis renvoie la liste complète en JSON.
* **Exemple d'utilisation :** via Postman ou Advanced REST Client (ARC) en `x-www-form-urlencoded`.

### `ws/loadEtudiant.php` (endpoint GET)

* **Rôle :** renvoie la liste complète des étudiants en JSON.

---

## 3) Tester les Web Services (ARC / Postman)

* **POST** `http://localhost/projet/ws/createEtudiant.php` (body x-www-form-urlencoded) avec : `nom`, `prenom`, `ville`, `sexe`.
* **GET** `http://localhost/projet/ws/loadEtudiant.php` pour récupérer la liste.

Exemple de réponse JSON :

```json
[
  {"id":1,"nom":"Lachgar","prenom":"Mohamed","ville":"Rabat","sexe":"homme"},
  {"id":2,"nom":"Dupont","prenom":"Sara","ville":"Casablanca","sexe":"femme"}
]
```

---

## 4) Application Android — configuration & explications

### Dépendances (module app/build.gradle)

```gradle
implementation 'com.android.volley:volley:1.2.1'
implementation 'com.google.code.gson:gson:2.10.1'
```

### Permission Internet

Ajoutez dans `AndroidManifest.xml` :

```xml
<uses-permission android:name="android.permission.INTERNET" />
```

### Network security (Android 9+)

Pour autoriser HTTP local (émulateur) :

* `res/xml/network_security_config.xml`

```xml
<network-security-config>
    <domain-config cleartextTrafficPermitted="true">
        <domain includeSubdomains="true">10.0.2.2</domain>
    </domain-config>
</network-security-config>
```

* Déclarez-le dans `AndroidManifest.xml` :

```xml
<application
    android:usesCleartextTraffic="true"
    android:networkSecurityConfig="@xml/network_security_config"
    ...>
```

> **Remarque :** sur l'émulateur Android, utilisez l'URL `http://10.0.2.2/projet/ws/createEtudiant.php` pour atteindre `localhost` sur la machine hôte.

### Activité `AddEtudiant` — logique principale

* **But :** collecter les champs (nom, prenom, ville, sexe) et envoyer une requête POST via Volley.
* **Parser la réponse :** utiliser `Gson` pour convertir le JSON en liste d'objets `Etudiant`.

```java
StringRequest request = new StringRequest(Request.Method.POST, insertUrl,
    response -> {
        Type type = new TypeToken<Collection<Etudiant>>(){}.getType();
        Collection<Etudiant> etudiants = new Gson().fromJson(response, type);
        // traiter la liste (Log, affichage...)
    },
    error -> Log.e("VOLLEY", "Erreur : " + error.getMessage())) {
    @Override
    protected Map<String, String> getParams() {
        Map<String, String> params = new HashMap<>();
        params.put("nom", nom.getText().toString());
        params.put("prenom", prenom.getText().toString());
        params.put("ville", ville.getSelectedItem().toString());
        params.put("sexe", m.isChecked() ? "homme" : "femme");
        return params;
    }
};
requestQueue.add(request);
```

---

## 5) Fichiers à personnaliser / points d'attention

* **Base de données :** si vous changez le nom d'utilisateur/mot de passe MySQL, mettez à jour `connexion/Connexion.php`.
* **URLs :** dans l'app Android (`insertUrl`, `loadUrl`) modifier selon l'environnement (émulateur ou appareil réel).
* **Encodage/locale :** gérer les accents et majuscules si nécessaire (ex : `utf8`/`utf8mb4`).

---

## 6) Tests et debug

* Vérifiez d'abord que `http://localhost/projet/ws/loadEtudiant.php` renvoie bien du JSON dans le navigateur.
* Testez les POSTs via Postman/ARC.
* Sur Android, surveillez **Logcat** et `D/RESPONSE` pour la réponse serveur.

---

### Screenshot - Backend (phpMyAdmin)
<img width="689" height="534" alt="image" src="https://github.com/user-attachments/assets/7c9455b0-6ff0-4389-94ed-882e023291d6" />


### App Android 
<img width="1080" height="2340" alt="Screenshot_20251130_135108" src="https://github.com/user-attachments/assets/cf643504-86f3-467c-b9bf-5fd6d553ee05" />

### Démo vidéo
https://github.com/user-attachments/assets/6d90933e-1eb6-4b44-bb78-2cc3e6ebacce

---

## Fait par:
```
Arroche aya
```
