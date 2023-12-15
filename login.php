<?php

require("./required/required.php");
require("./database/Database.php");

$database = new Database();
$database->connect();

$identifier = "";
$password = "";

$formResult = [
    "success" => true,
    "messages" => [
        "form" => "",
        "fields" => [
            "identifier" => "",
            "password" => ""
        ]
    ]
];

if (!empty($_POST)) {

    $identifier = $_POST["identifier"];
    $password = $_POST["password"];

    $result = $database->getAccountFromIdentifier($identifier);

    if ($result) {
        if (password_verify($password, $result["password"])) {
            sessionLogin($result["id"],  $result["idAbonne"], $result["role"]);
            $formResult["messages"]["form"] = "Vous êtes connecté en tant que " . "'" . $result["role"] . "'";
        } else {
            $formResult["success"] = false;
            $formResult["messages"]["fields"]["password"] = "Le mot de passe ne correspond pas";
        }
    } else {
        $formResult["success"] = false;
        $formResult["messages"]["fields"]["identifier"] = "Cet identifiant de compte n'existe pas";
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
        <form action="./login.php" method="POST">
            <fieldset>
                <legend>Connexion</legend>
                <?php if (!$formResult["success"]) : ?>
                    <p class="form-error">
                        <?= $formResult["messages"]["form"] ?>
                    </p>
                <?php else : ?>
                    <p class="form-success">
                        <?= $formResult["messages"]["form"] ?>
                    </p>
                <?php endif; ?>
                <input type="hidden" name="page" value="1">
                <div class="margin-b-05">
                    <label for="identifier">Identifiant :</label>
                    <input class="w-100" type="text" id="identifier" name="identifier" value="<?= $identifier ?>">
                    <p class="form-field-error"><?= $formResult["messages"]["fields"]["identifier"] ?></p>
                </div>
                <div class="margin-b-05">
                    <label for="password">Mot de passe :</label>
                    <input class="w-100" type="password" id="password" name="password" value="<?= $password ?>">
                    <p class="form-field-error"><?= $formResult["messages"]["fields"]["password"] ?></p>
                </div>
                <div>
                    <button type="submit">Se connecter</button>
                </div>
            </fieldset>
        </form>
    </div>
</main>


<?php require("./parts/footer.php"); ?>
<?php require("./parts/foot.php"); ?>