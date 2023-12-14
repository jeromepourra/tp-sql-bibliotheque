<?php

require("./required/required.php");
require("./database/Database.php");

$database = new Database();
$database->connect();

$lastname = "";
$firstname = "";
$city = "";
$subscriber = "";
$resultsPerPage = RESULTS_PER_PAGE_DEFAULT;

$results = null;

$currentPage = CURRENT_PAGE_DEFAULT;
$countPage = 0;
$countResults = 0;

if (!empty($_GET)) {

    $lastname = isset($_GET["lastname"]) && (!empty($_GET["lastname"])) ? $_GET["lastname"] : $lastname;
    $firstname = isset($_GET["firstname"]) && (!empty($_GET["firstname"])) ? $_GET["firstname"] : $firstname;
    $city = isset($_GET["city"]) && (!empty($_GET["city"])) ? $_GET["city"] : $city;
    $subscriber = isset($_GET["subscriber"]) && (!empty($_GET["subscriber"])) ? $_GET["subscriber"] : $subscriber;

    $currentPage = isset($_GET["page"]) && (!empty($_GET["page"])) ? $_GET["page"] : $currentPage;
    $resultsPerPage = isset($_GET["count"]) && !empty($_GET["count"]) ? (int) $_GET["count"] : $resultsPerPage;

    // set result per page into a valid value
    if (!in_array($resultsPerPage, RESULTS_PER_PAGE)) {
        $resultsPerPage = RESULTS_PER_PAGE_DEFAULT;
    }

    $countResults = $database->searchSubscriberCount($lastname, $firstname, $city, $subscriber)["total"];
    $countPage = ceil($countResults / $resultsPerPage);

    if ($countPage == 0) {
        $countPage = 1;
    }

    // set current page into a valid value
    if ($currentPage < 1) {
        $currentPage = CURRENT_PAGE_DEFAULT;
    } else if ($currentPage > $countPage) {
        $currentPage = $countPage;
    }

    $results = $database->searchSubscriber($lastname, $firstname, $city, $subscriber, $resultsPerPage, $currentPage);

    unset($_GET["page"]); // remove it to building next & previous page with current $_GET

}

?>

<?php require("./parts/head.php"); ?>
<?php require("./parts/header.php"); ?>


<main>

    <div class="max-width-800 margin-y-1">
        <form action="./subscribers.php" method="GET">
            <fieldset>
                <legend>Recherche d'abonnés</legend>
                <input type="hidden" name="page" value="1">
                <div class="margin-b-05">
                    <label for="lastname">Par nom :</label>
                    <input class="w-100" type="text" id="lastname" name="lastname" value="<?= $lastname ?>">
                </div>
                <div class="margin-b-05">
                    <label for="firstname">Par prénom :</label>
                    <input class="w-100" type="text" id="firstname" name="firstname" value="<?= $firstname ?>">
                </div>
                <div class="margin-b-05">
                    <label for="city">Par ville :</label>
                    <input class="w-100" type="text" id="city" name="city" value="<?= $city ?>">
                </div>
                <div class="margin-b-05">
                    <label for="available">Status :</label>
                    <select class="w-100" name="subscriber" id="subscriber">
                        <option value="" <?= $subscriber == "" ? "selected" : "" ?>>Tous status</option>
                        <option value="true" <?= $subscriber == "true" ? "selected" : "" ?>>Abonné</option>
                        <option value="false" <?= $subscriber == "false" ? "selected" : "" ?>>Expiré</option>
                    </select>
                </div>
                <div>
                    <select name="count">
                        <?php foreach (RESULTS_PER_PAGE as $nb) : ?>
                            <option value="<?= $nb ?>" <?= $resultsPerPage == $nb ? "selected" : "" ?>><?= $nb ?> résultats par page</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Rechercher</button>
                </div>
            </fieldset>
        </form>
    </div>

    <?php if ($results !== null) : ?>
        <div class="max-width-800 margin-y-1">
            <fieldset>
                <legend><?= $countResults ?> résultats</legend>
                <table class="pagination">
                    <tbody>
                        <tr>
                            <td>
                                <?php if ($currentPage > 1) : ?>
                                    <button>
                                        <a href="<?= "./subscribers.php?page=" . $currentPage - 1 . "&" . http_build_query($_GET) ?>">Page précédente</a>
                                    </button>
                                <?php else : ?>
                                    <button disabled>
                                        Page précédente
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                Page <?= $currentPage ?> sur <?= $countPage ?>
                            </td>
                            <td>
                                <?php if ($currentPage < $countPage) : ?>
                                    <button>
                                        <a href="<?= "./subscribers.php?page=" . $currentPage + 1 . "&" . http_build_query($_GET) ?>">Page suivante</a>
                                    </button>
                                <?php else : ?>
                                    <button disabled>
                                        Page suivante
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="results">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Ville</th>
                            <th>Date de naissance</th>
                            <th>Abonné</th>
                            <th>Fiche</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $index => $result) : ?>
                            <tr>
                                <td><?= $result["lastName"] ?></td>
                                <td><?= $result["firstName"] ?></td>
                                <td><?= $result["city"] ?></td>
                                <td><?= $result["birthday"] ?></td>
                                <td><?= ($result["subscriber"] ? "Oui" : "Non") ?></td>
                                <td><button><a class="no-decoration" href="<?= "./fiche.php?id=" . $result["id"] ?>">Y&nbsp;aller</a></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </fieldset>
        </div>
    <?php endif; ?>

    <?php Database::printLoggerMsg(); ?>

</main>


<?php require("./parts/footer.php"); ?>
<?php require("./parts/foot.php"); ?>