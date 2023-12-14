<?php

use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;

require("./sql-formatter/src/Cursor.php");
require("./sql-formatter/src/Token.php");
require("./sql-formatter/src/Tokenizer.php");
require("./sql-formatter/src/Highlighter.php");
require("./sql-formatter/src/NullHighlighter.php");
require("./sql-formatter/src/SqlFormatter.php");

class Database
{

    private static $LOGGER_MSG = [];

    private string $dbHost = "127.0.0.1";
    private string $dbName = "mds_bibliotheque";
    private string $username = "root";
    private string $userpass = "";

    private PDO $db;

    public function connect()
    {
        $this->db = new PDO("mysql:dbname=" . $this->dbName . ";host=" . $this->dbHost, $this->username, $this->userpass);
    }

    public function searchBookCount(string $title, string $author, string $editor, string $available)
    {

        $query = "
            SELECT
                COUNT(*) as total
            FROM
                livre
            INNER JOIN auteur ON livre.id_auteur = auteur.id
            INNER JOIN editeur ON livre.id_editeur = editeur.id";

        $conditionsList = [];
        $valuesList = [];

        if (!empty($available)) {
            $valuesList += [":available" => [($available == "true" ? 0 : 1), PDO::PARAM_INT]];
            array_push(
                $conditionsList,
                "
                (
                    SELECT
                        COUNT(*) > 0
                    FROM
                        emprunt
                    WHERE
                        livre.id = emprunt.id_livre AND emprunt.date_retour IS NULL
                ) = :available"
            );
        }

        if (!empty($title)) {
            $valuesList += [":title" => ["%" . strtolower($title) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(livre.titre) LIKE " . ":title");
        }
        if (!empty($author)) {
            $valuesList += [":author" => ["%" . strtolower($author) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(auteur.nom) LIKE " . ":author");
        }
        if (!empty($editor)) {
            $valuesList += [":editor" => ["%" . strtolower($editor) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(editeur.nom) LIKE " . ":editor");
        }

        if (!empty($conditionsList)) {
            $query .= "
            WHERE
                " . join(" AND ", $conditionsList);
        }

        $query .= ";";

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function searchBook(string $title, string $author, string $editor, string $available, int $limit = 20, int $page = 1)
    {

        $query = "
            SELECT
                livre.titre AS livreName,
                auteur.nom AS authorName,
                editeur.nom AS editorName,
                (
                    SELECT
                        COUNT(*) = 0
                    FROM
                        emprunt
                    WHERE
                        livre.id = emprunt.id_livre AND emprunt.date_retour IS NULL
                ) AS available,
                MAX(emprunt.date_emprunt) AS borrowDate
            FROM
                livre
            INNER JOIN auteur ON livre.id_auteur = auteur.id
            INNER JOIN editeur ON livre.id_editeur = editeur.id
            LEFT OUTER JOIN emprunt ON livre.id = emprunt.id_livre";

        $conditionsList = [];
        $valuesList = [];

        if (!empty($available)) {
            $valuesList += [":available" => [($available == "true" ? 0 : 1), PDO::PARAM_INT]];
            array_push($conditionsList, "
                (
                    SELECT
                        COUNT(*) > 0
                    FROM
                        emprunt
                    WHERE
                        livre.id = emprunt.id_livre AND emprunt.date_retour IS NULL
                ) = :available");
        }

        if (!empty($title)) {
            $valuesList += [":title" => ["%" . strtolower($title) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(livre.titre) LIKE " . ":title");
        }
        if (!empty($author)) {
            $valuesList += [":author" => ["%" . strtolower($author) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(auteur.nom) LIKE " . ":author");
        }
        if (!empty($editor)) {
            $valuesList += [":editor" => ["%" . strtolower($editor) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(editeur.nom) LIKE " . ":editor");
        }

        if (!empty($conditionsList)) {
            $query .= "
            WHERE
                " . join(" AND ", $conditionsList);
        }

        $query .= "
            GROUP BY
                emprunt.id_livre
            ORDER BY 
                livreName ASC, authorName ASC, editorName ASC
            LIMIT " . $limit . " OFFSET " . ($page - 1) * $limit . ";";

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function searchSubscriberCount(string $lastname, string $firstname, string $city, string $subscriber)
    {

        $query = "
            SELECT
                COUNT(*) as total
            FROM
                abonne";

        $conditionsList = [];
        $valuesList = [];

        if (!empty($lastname)) {
            $valuesList += [":lastname" => ["%" . strtolower($lastname) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(abonne.nom) LIKE " . ":lastname");
        }
        if (!empty($firstname)) {
            $valuesList += [":firstname" => ["%" . strtolower($firstname) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(abonne.prenom) LIKE " . ":firstname");
        }
        if (!empty($city)) {
            $valuesList += [":city" => ["%" . strtolower($city) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(abonne.ville) LIKE " . ":city");
        }
        if (!empty($subscriber)) {
            if ($subscriber == "true") {
                array_push($conditionsList, "abonne.date_fin_abo >= CURRENT_DATE()");
            } else {
                array_push($conditionsList, "abonne.date_fin_abo < CURRENT_DATE()");
            }
        }

        if (!empty($conditionsList)) {
            $query .= "
            WHERE
                " . join(" AND ", $conditionsList);
        }

        $query .= ";";

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function searchSubscriber(string $lastname, string $firstname, string $city, string $subscriber, int $limit = 20, int $page = 1)
    {

        $query = "
            SELECT
                abonne.id AS id,
                abonne.nom AS lastName,
                abonne.prenom AS firstName,
                abonne.ville AS city,
                abonne.date_naissance AS birthday,
                abonne.date_fin_abo >= CURRENT_DATE() AS subscriber
            FROM
                abonne";

        $conditionsList = [];
        $valuesList = [];

        if (!empty($lastname)) {
            $valuesList += [":lastname" => ["%" . strtolower($lastname) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(abonne.nom) LIKE " . ":lastname");
        }
        if (!empty($firstname)) {
            $valuesList += [":firstname" => ["%" . strtolower($firstname) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(abonne.prenom) LIKE " . ":firstname");
        }
        if (!empty($city)) {
            $valuesList += [":city" => ["%" . strtolower($city) . "%", PDO::PARAM_STR]];
            array_push($conditionsList, "LOWER(abonne.ville) LIKE " . ":city");
        }
        if (!empty($subscriber)) {
            if ($subscriber == "true") {
                array_push($conditionsList, "abonne.date_fin_abo >= CURRENT_DATE()");
            } else {
                array_push($conditionsList, "abonne.date_fin_abo < CURRENT_DATE()");
            }
        }

        if (!empty($conditionsList)) {
            $query .= "
            WHERE
                " . join(" AND ", $conditionsList);
        }

        $query .= "
            ORDER BY
                lastName ASC, firstName ASC, city ASC
            LIMIT " . $limit . " OFFSET " . ($page - 1) * $limit . ";";

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSubscriber(string $id)
    {

        $query = "
            SELECT
                abonne.id as id,
                abonne.prenom AS firstName,
                abonne.nom AS lastName,
                abonne.date_naissance AS birthday,
                abonne.adresse AS address,
                abonne.code_postal AS postalCode,
                abonne.ville AS city,
                abonne.date_inscription AS dateInscription,
                abonne.date_fin_abo AS dateEndSub
            FROM
                abonne
            WHERE
                abonne.id = :id;";

        $valuesList = [];
        $valuesList += [":id" => [$id, PDO::PARAM_INT]];

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function updateSubscriber(string $id, string $lastname, string $firstname, string $birthday, string $address, string $postalCode, string $city, string $dateInscription, string $dateEndSub)
    {

        $valuesList = [];
        $valuesList += [":id" => [$id, PDO::PARAM_INT]];
        $valuesList += [":firstname" => [$firstname, PDO::PARAM_STR]];
        $valuesList += [":lastname" => [$lastname, PDO::PARAM_STR]];
        $valuesList += [":birthday" => [$birthday, PDO::PARAM_STR]];
        $valuesList += [":address" => [$address, PDO::PARAM_STR]];
        $valuesList += [":postalCode" => [$postalCode, PDO::PARAM_STR]];
        $valuesList += [":city" => [$city, PDO::PARAM_STR]];
        $valuesList += [":dateInscription" => [$dateInscription, PDO::PARAM_STR]];
        $valuesList += [":dateEndSub" => [$dateEndSub, PDO::PARAM_STR]];

        $query = "
            UPDATE
                abonne
            SET
                abonne.prenom = :firstname, abonne.nom = :lastname, abonne.date_naissance = :birthday, abonne.adresse = :address, abonne.code_postal = :postalCode, abonne.ville = :city, abonne.date_inscription = :dateInscription, abonne.date_fin_abo = :dateEndSub
            WHERE
                id = :id";

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();
    }

    public function getBorroweds(string $id)
    {

        $query = "
            SELECT
                livre.titre AS livreName,
                emprunt.date_emprunt AS borrowDate,
                emprunt.date_retour AS returnDate
            FROM
                emprunt
            INNER JOIN livre ON emprunt.id_livre = livre.id
            WHERE
                emprunt.id_abonne = :id
            ORDER BY
                emprunt.date_emprunt DESC,
                emprunt.date_retour DESC;";

        $valuesList = [];
        $valuesList += [":id" => [$id, PDO::PARAM_INT]];

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function createFuncGetMaxBorrowedCategory()
    {
        $query = "
                CREATE FUNCTION IF NOT EXISTS getMaxBorrowedCategory(subscriberId INTEGER)
                RETURNS VARCHAR(255)
                BEGIN
                    RETURN (
                        SELECT
                            livre.categorie AS category
                        FROM
                            emprunt
                        INNER JOIN livre ON emprunt.id_livre = livre.id
                        WHERE
                            emprunt.id_abonne = subscriberId AND livre.categorie IS NOT NULL AND LENGTH(livre.categorie) > 0
                        GROUP BY
                            livre.categorie
                        ORDER BY
                            COUNT(*) DESC
                        LIMIT 1
                    );
                END;";

        $this->addDebugMsg(__METHOD__, $query);
        $statement = $this->db->prepare($query);
        $statement->execute();
    }

    public function getMaxBorrowedCategories(string $id)
    {
        $query = "
            SELECT
                livre.categorie AS categoryName,
                COUNT(*) AS total
            FROM
                emprunt
            INNER JOIN livre ON emprunt.id_livre = livre.id
            WHERE
                emprunt.id_abonne = :id AND livre.categorie IS NOT NULL AND LENGTH(livre.categorie) > 0
            GROUP BY
                livre.categorie
            ORDER BY
                total DESC";

        $valuesList = [];
        $valuesList += [":id" => [$id, PDO::PARAM_INT]];

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getMaxBorrowedCategory(string $id)
    {
        $query = "SELECT getMaxBorrowedCategory(:id) AS categoryName";

        $valuesList = [];
        $valuesList += [":id" => [$id, PDO::PARAM_INT]];

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function getSuggest(string $id)
    {
        $query = "
            SELECT
                livre.titre AS livreName,
                livre.categorie AS categoryName,
                genre.nom_genre AS genreName,
                auteur.nom AS authorName,
                editeur.nom AS editorName,
                COUNT(*) AS total
            FROM
                emprunt
            INNER JOIN livre ON emprunt.id_livre = livre.id
            INNER JOIN genre ON genre.id = livre.id_genre
            INNER JOIN auteur ON auteur.id = livre.id_auteur
            INNER JOIN editeur ON editeur.id = livre.id_editeur
            WHERE
                livre.categorie = (SELECT getMaxBorrowedCategory(:id)) AND emprunt.date_emprunt >= CURRENT_DATE - INTERVAL 1 YEAR AND
                (
                    SELECT
                        COUNT(*) = 0
                    FROM
                        emprunt
                    WHERE
                        livre.id = emprunt.id_livre AND emprunt.date_retour IS NULL
                ) = 1
            GROUP BY
                emprunt.id_livre
            ORDER BY
                total DESC,
                livreName ASC
            LIMIT 5;";

        $valuesList = [];
        $valuesList += [":id" => [$id, PDO::PARAM_INT]];

        $this->addDebugMsg(__METHOD__, $query, $valuesList);

        $statement = $this->db->prepare($query);

        foreach ($valuesList as $key => $value) {
            $statement->bindParam($key, $value[0], $value[1]);
        }

        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    private function addDebugMsg(string $from, string $query, array $values = [])
    {
        foreach ($values as $key => $value) {
            switch ($value[1]) {
                case PDO::PARAM_STR:
                    $query = str_replace($key, "'" . $value[0] . "'", $query);
                    break;
                default:
                    $query = str_replace($key, $value[0], $query);
            }
        }
        array_push(self::$LOGGER_MSG, [$from . "()", "<pre>" . (new SqlFormatter(new NullHighlighter()))->format($query) . "</pre>"]);
    }

    public static function printLoggerMsg()
    {
        if (count(self::$LOGGER_MSG) > 0) {
?>
            <div class="margin-y-1 logger-container">
                <fieldset>
                    <legend>Database queries</legend>
                    <table class="logger">
                        <tbody>
                            <tr>
                                <th>Method</th>
                                <th>Query</th>
                            </tr>
                        </tbody>
                        <tbody>
                            <?php foreach (self::$LOGGER_MSG as $msg) : ?>
                                <tr>
                                    <td><?= $msg[0] ?></td>
                                    <td><?= $msg[1] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </fieldset>
            </div>
<?php
        }
    }
}
