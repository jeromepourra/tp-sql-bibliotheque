<?php

require("./required/required.php");
require("./database/Database.php");

$database = new Database();
$database->connect();

$results = null;
$borrows = null;
$maxCategory = null;
$suggests = null;

if (isset($_GET["id"]) && !empty($_GET["id"]) && is_numeric($_GET["id"])) {
    $results = $database->getSubscriber($_GET["id"]);
    if (!$results) {
        $results = null;
    } else {

        $database->createFuncGetMaxBorrowedCategory();

        $borrows = $database->getBorroweds($_GET["id"]);
        $maxCategories = $database->getMaxBorrowedCategories($_GET["id"]);
        $maxCategory = $database->getMaxBorrowedCategory($_GET["id"]);
        $suggests = $database->getSuggest($_GET["id"]);

    }
}

?>

<?php require("./parts/head.php"); ?>
<?php require("./parts/header.php"); ?>


<main>

    <?php if ($results !== null) : ?>

        <div class="max-width-800 margin-y-1">
            <form action="./fiche-update.php" method="POST">
                <fieldset>
                    <legend>Fiche de l'abonne n° <?= $results["id"] ?></legend>
                    <input type="hidden" name="id" value="<?= $results["id"] ?>">
                    <div class="margin-b-05">
                        <label for="lastname">Nom :</label>
                        <input class="w-100" type="text" id="lastname" name="lastname" value="<?= $results["lastName"] ?>">
                    </div>
                    <div class="margin-b-05">
                        <label for="firstname">Prénom :</label>
                        <input class="w-100" type="text" id="firstname" name="firstname" value="<?= $results["firstName"] ?>">
                    </div>
                    <div class="margin-b-05">
                        <label for="birthday">Date de naissance :</label>
                        <input class="w-100" type="date" id="birthday" name="birthday" value="<?= $results["birthday"] ?>">
                    </div>
                    <div class="margin-b-05">
                        <label for="address">Adresse :</label>
                        <input class="w-100" type="text" id="address" name="address" value="<?= $results["address"] ?>">
                    </div>
                    <div class="margin-b-05">
                        <label for="postalCode">Code postal :</label>
                        <input class="w-100" type="text" id="postalCode" name="postalCode" value="<?= $results["postalCode"] ?>">
                    </div>
                    <div class="margin-b-05">
                        <label for="city">Ville :</label>
                        <input class="w-100" type="text" id="city" name="city" value="<?= $results["city"] ?>">
                    </div>
                    <div class="margin-b-05">
                        <label for="dateInscription">Date d'inscription :</label>
                        <input class="w-100" type="date" id="dateInscription" name="dateInscription" value="<?= $results["dateInscription"] ?>">
                    </div>
                    <div class="margin-b-05">
                        <label for="dateEndSub">Date fin abonnement :</label>
                        <input class="w-100" type="date" id="dateEndSub" name="dateEndSub" value="<?= $results["dateEndSub"] ?>">
                    </div>
                    <div>
                        <button type="submit">Modifier</button>
                    </div>
                </fieldset>
            </form>
        </div>

        <div class="max-width-800 margin-y-1">
            <fieldset>
                <legend>Liste des emprunts</legend>
                <table class="results">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Date emprunt</th>
                            <th>Date retour</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($borrows as $value) : ?>
                            <tr>
                                <td><?= $value["livreName"] ?></td>
                                <td><?= $value["borrowDate"] ?></td>
                                <td><?= ($value["returnDate"] == null ? "-" : $value["returnDate"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </fieldset>
        </div>

        <div class="max-width-800 margin-y-1">
            <fieldset>
                <legend>Categories les plus lus</legend>
                <table class="results">
                    <thead>
                        <tr>
                            <th>Categorie</th>
                            <th>Nombre de fois emprunté</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($maxCategories as $value) : ?>
                            <tr>
                                <td><?= $value["categoryName"] ?></td>
                                <td><?= $value["total"] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </fieldset>
        </div>

        <div class="max-width-800 margin-y-1">
            <fieldset>
                <legend>Suggestions de livres pour la catégorie "<?= $maxCategory["categoryName"] ?>"</legend>
                <table class="results">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Categorie</th>
                            <th>Genre</th>
                            <th>Auteur</th>
                            <th>Éditeur</th>
                            <th>Tot. emprunt cette année</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suggests as $value) : ?>
                            <tr>
                                <td><?= $value["livreName"] ?></td>
                                <td><?= $value["categoryName"] ?></td>
                                <td><?= $value["genreName"] ?></td>
                                <td><?= $value["authorName"] ?></td>
                                <td><?= $value["editorName"] ?></td>
                                <td><?= $value["total"] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </fieldset>
        </div>

    <?php else : ?>
        <p class="text-center">Aucun abonné trouvé...</p>
    <?php endif; ?>

    <?php Database::printLoggerMsg(); ?>

</main>


<?php require("./parts/footer.php"); ?>
<?php require("./parts/foot.php"); ?>