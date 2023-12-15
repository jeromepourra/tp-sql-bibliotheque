<header>
    <nav>
        <ul>
            <?php if (!$_SESSION["connected"]) : ?>
                <li><a href="./login.php">Connexion</a></li>
                <li><a href="./register.php">Inscription</a></li>
            <?php else : ?>
                <li><a href="./index.php">Recherche de livres</a></li>
                <?php if ($_SESSION["gestionnaire"] == "gestionnaire") : ?>
                    <li><a href="./subscribers.php">Recherche d'abonnés</a></li>
                <?php endif; ?>
                <li><a href="<?= "./fiche.php?id=" . $_SESSION["abonneId"] ?>">Voir ma fiche</a></li>
                <li><a href="./logout.php">Déconnexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>