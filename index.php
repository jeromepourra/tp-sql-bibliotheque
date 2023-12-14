<?php

require("./required/required.php");
require("./database/Database.php");

$database = new Database();
$database->connect();

$title = "";
$author = "";
$editor = "";
$available = "";
$resultsPerPage = RESULTS_PER_PAGE_DEFAULT;

$results = null;

$currentPage = CURRENT_PAGE_DEFAULT;
$countPage = 0;
$countResults = 0;

if (!empty($_GET)) {

    $title = isset($_GET["title"]) && !empty($_GET["title"]) ? $_GET["title"] : $title;
    $author = isset($_GET["author"]) && !empty($_GET["author"]) ? $_GET["author"] : $author;
    $editor = isset($_GET["editor"]) && !empty($_GET["editor"]) ? $_GET["editor"] : $editor;
    $available = isset($_GET["available"]) && !empty($_GET["available"]) ? $_GET["available"] : $available;

    $currentPage = isset($_GET["page"]) ? (int) $_GET["page"] : $currentPage;
    $resultsPerPage = isset($_GET["count"]) ? (int) $_GET["count"] : $resultsPerPage;

    // set result per page into a valid value
    if (!in_array($resultsPerPage, RESULTS_PER_PAGE)) {
        $resultsPerPage = RESULTS_PER_PAGE_DEFAULT;
    }

    $countResults = $database->searchBookCount($title, $author, $editor, $available)["total"];
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

    $results = $database->searchBook($title, $author, $editor, $available, $resultsPerPage, $currentPage);

    unset($_GET["page"]); // remove it to building next & previous page with current $_GET

}

?>

<?php require("./parts/head.php"); ?>
<?php require("./parts/header.php"); ?>


<main>

    <div class="max-width-800 margin-y-1">
        <form action="./index.php" method="GET">
            <fieldset>
                <legend>Recherche de livre</legend>
                <input type="hidden" name="page" value="1">
                <div class="margin-b-05">
                    <label for="title">Par titre :</label>
                    <input class="w-100" type="text" id="title" name="title" value="<?= $title ?>">
                </div>
                <div class="margin-b-05">
                    <label for="author">Par auteur :</label>
                    <input class="w-100" type="text" id="author" name="author" value="<?= $author ?>">
                </div>
                <div class="margin-b-05">
                    <label for="editor">Par éditeur :</label>
                    <input class="w-100" type="text" id="editor" name="editor" value="<?= $editor ?>">
                </div>
                <div class="margin-b-05">
                    <label for="available">Disponibilité :</label>
                    <select class="w-100" name="available" id="available">
                        <option value="" <?= $available == "" ? "selected" : "" ?>>Toutes disponibilités</option>
                        <option value="true" <?= $available == "true" ? "selected" : "" ?>>Disponible</option>
                        <option value="false" <?= $available == "false" ? "selected" : "" ?>>Emprunté</option>
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
                                        <a href="<?= "./index.php?page=" . $currentPage - 1 . "&" . http_build_query($_GET) ?>">Page précédente</a>
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
                                        <a href="<?= "./index.php?page=" . $currentPage + 1 . "&" . http_build_query($_GET) ?>">Page suivante</a>
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
                            <th>Titre</th>
                            <th>Auteur</th>
                            <th>Éditeur</th>
                            <th>Disponible</th>
                            <th>Date dernier emprunt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $index => $result) : ?>
                            <tr>
                                <td><?= $result["livreName"] ?></td>
                                <td><?= $result["authorName"] ?></td>
                                <td><?= $result["editorName"] ?></td>
                                <td><?= ($result["available"] ? "Oui" : "Non") ?></td>
                                <td><?= ($result["borrowDate"] == null ? "-" : $result["borrowDate"]) ?></td>
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