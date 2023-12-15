<?php

require("./required/required.php");
require("./database/Database.php");

$database = new Database();
$database->connect();

$identifier = "";
$password = "";
$passwordConfirm = "";
$lastname = "";
$firstname = "";
$birthday = "";
$address = "";
$postalCode = "";
$city = "";

$formResult = [
    "success" => true,
    "messages" => [
        "form" => "",
        "fields" => [
            "identifier" => "",
            "passwordConfirm" => ""
        ]
    ]
];

if (!empty($_POST)) {

    $identifier = $_POST["identifier"];
    $password = $_POST["password"];
    $passwordConfirm = $_POST["passwordConfirm"];
    $lastname = $_POST["lastname"];
    $firstname = $_POST["firstname"];
    $birthday = $_POST["birthday"];
    $address = $_POST["address"];
    $postalCode = $_POST["postalCode"];
    $city = $_POST["city"];

    if ($password !== $passwordConfirm) {
        $formResult["success"] = false;
        $formResult["messages"]["fields"]["passwordConfirm"] = "Les mots de passe ne correspondent pas";
    }

    if ($formResult["success"]) {

        $result = $database->existingSubscriber($lastname, $firstname);

        if ($result["existing"]) {

            $idAbonne = $result["id"];
            $haveAccount = $database->haveAccount($idAbonne);

            if ($haveAccount) {
                $formResult["success"] = false;
                $formResult["messages"]["form"] = "Un compte est déjà associé à cet abonné, veuillez vous connecter";
            } else {
                if ($database->uniqueIdenfierAccount($identifier)) {
                    $database->createAccount($identifier, $password, $idAbonne);
                    $formResult["messages"]["form"] = "Votre compte a bien été créé";
                } else {
                    $formResult["success"] = false;
                    $formResult["messages"]["fields"]["identifier"] = "Cet identifiant de compte existe déjà";
                }
            }
        } else {
            if ($database->uniqueIdenfierAccount($identifier)) {
                $idAbonne = $database->createSubscriber($lastname, $firstname, $birthday, $address, $postalCode, $city);
                $database->createAccount($identifier, $password, $idAbonne);
                $formResult["messages"]["form"] = "Votre compte a bien été créé";
            } else {
                $formResult["success"] = false;
                $formResult["messages"]["fields"]["identifier"] = "Cet identifiant de compte existe déjà";
            }
        }
    }

    if (!$formResult["success"] && empty($formResult["messages"]["form"])) {
        $formResult["messages"]["form"] = "Une erreur est survenue lors de la validation du formulaire, veuillez vérifier les champs";
    }
}

?>

<?php require("./parts/head.php"); ?>
<?php require("./parts/header.php"); ?>


<main>

    <div class="max-width-800 margin-y-1">
        <form action="./register.php" method="POST">
            <fieldset>
                <legend>Inscription</legend>
                <?php if (!$formResult["success"]) : ?>
                    <p class="form-error">
                        <?= $formResult["messages"]["form"] ?>
                    </p>
                <?php else : ?>
                    <p class="form-success">
                        <?= $formResult["messages"]["form"] ?>
                    </p>
                <?php endif; ?>
                <div class="margin-b-05">
                    <label for="identifier">Identifiant :</label>
                    <input class="w-100" type="text" id="identifier" name="identifier" value="<?= $identifier ?>" required>
                    <p class="form-field-error"><?= $formResult["messages"]["fields"]["identifier"] ?></p>
                </div>
                <div class="margin-b-05">
                    <label for="password">Mot de passe :</label>
                    <input class="w-100" type="password" id="password" name="password" value="<?= $password ?>" required>
                </div>
                <div class="margin-b-05">
                    <label for="passwordConfirm">Confirmation du mot de passe :</label>
                    <input class="w-100" type="password" id="passwordConfirm" name="passwordConfirm" value="<?= $passwordConfirm ?>" required>
                    <p class="form-field-error"><?= $formResult["messages"]["fields"]["passwordConfirm"] ?></p>
                </div>
                <div class="margin-b-05">
                    <label for="lastname">Nom :</label>
                    <input class="w-100" type="text" id="lastname" name="lastname" value="<?= $lastname ?>" required>
                </div>
                <div class="margin-b-05">
                    <label for="firstname">Prénom :</label>
                    <input class="w-100" type="text" id="firstname" name="firstname" value="<?= $firstname ?>" required>
                </div>
                <div class="margin-b-05">
                    <label for="birthday">Date de naissance :</label>
                    <input class="w-100" type="date" id="birthday" name="birthday" value="<?= $birthday ?>">
                </div>
                <div class="margin-b-05">
                    <label for="address">Adresse :</label>
                    <input class="w-100" type="text" id="address" name="address" value="<?= $address ?>">
                </div>
                <div class="margin-b-05">
                    <label for="postalCode">Code postal :</label>
                    <input class="w-100" type="text" id="postalCode" name="postalCode" value="<?= $postalCode ?>">
                </div>
                <div class="margin-b-05">
                    <label for="city">Ville :</label>
                    <input class="w-100" type="text" id="city" name="city" value="<?= $city ?>">
                </div>

                <div>
                    <button type="submit">S'inscrire</button>
                </div>
            </fieldset>
        </form>
    </div>

    <?php Database::printLoggerMsg(); ?>

</main>


<?php require("./parts/footer.php"); ?>
<?php require("./parts/foot.php"); ?>