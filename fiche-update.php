<?php

require("./required/required.php");
require("./database/Database.php");

$database = new Database();
$database->connect();

$id = "";
$lastname = "";
$firstname = "";
$birthday = "";
$address = "";
$postalCode = "";
$city = "";
$dateInscription = "";
$dateEndSub = "";

$results = null;

if (!empty($_POST)) {

    $id = $_POST["id"];
    $lastname = $_POST["lastname"];
    $firstname = $_POST["firstname"];
    $birthday = $_POST["birthday"];
    $address = $_POST["address"];
    $postalCode = $_POST["postalCode"];
    $city = $_POST["city"];
    $dateInscription = $_POST["dateInscription"];
    $dateEndSub = $_POST["dateEndSub"];

    $database->updateSubscriber($id, $lastname, $firstname, $birthday, $address, $postalCode, $city, $dateInscription, $dateEndSub);

}

?>

<?php require("./parts/head.php"); ?>
<?php require("./parts/header.php"); ?>


<main>

    <div class="max-width-800 margin-y-1">
        <fieldset>
            <legend>Modification de fiche</legend>
            <button>
                <a href="<?= "./fiche.php?id=" . $id ?>">Retour vers la fiche de l'abonné n° <?= $id ?></a>
            </button>
        </fieldset>
    </div>

    <?php Database::printLoggerMsg(); ?>
    
</main>


<?php require("./parts/footer.php"); ?>
<?php require("./parts/foot.php"); ?>