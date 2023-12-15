<?php

require("./required/required.php");

if ($_SESSION["connected"]) {
    sessionLogout();
}

?>

<?php require("./parts/head.php"); ?>
<?php require("./parts/header.php"); ?>


<main>
    <div class="max-width-800 margin-y-1">
        <fieldset>
            <legend>Déconnexion</legend>
            <p style="text-align: center;">Vous êtes désormais déconnecté</p>
        </fieldset>
    </div>
</main>


<?php require("./parts/footer.php"); ?>
<?php require("./parts/foot.php"); ?>