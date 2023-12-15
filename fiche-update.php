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

    $id = isset($_POST["id"]) ? $_POST["id"] : null;
    $lastname = isset($_POST["lastname"]) ? $_POST["lastname"] : null;
    $firstname = isset($_POST["firstname"]) ? $_POST["firstname"] : null;
    $birthday = isset($_POST["birthday"]) ? $_POST["birthday"] : null;
    $address = isset($_POST["address"]) ? $_POST["address"] : null;
    $postalCode = isset($_POST["postalCode"]) ? $_POST["postalCode"] : null;
    $city = isset($_POST["city"]) ? $_POST["city"] : null;
    $dateInscription = isset($_POST["dateInscription"]) ? $_POST["dateInscription"] : null;
    $dateEndSub = isset($_POST["dateEndSub"]) ? $_POST["dateEndSub"] : null;

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