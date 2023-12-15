<?php
session_start();
if (!isset($_SESSION["initialized"])) {
    $_SESSION["initialized"] = true;
    $_SESSION["connected"] = false;
    $_SESSION["abonneId"] = -1;
    $_SESSION["accountId"] = -1;
    $_SESSION["gestionnaire"] = false;
}

function sessionLogin(string $accountId, string $abonneId, string $role) {
    $_SESSION["connected"] = true;
    $_SESSION["abonneId"] = (int) $abonneId;
    $_SESSION["accountId"] = (int) $accountId;
    $_SESSION["gestionnaire"] = $role == "gestionnaire";
}

function sessionLogout() {
    $_SESSION["connected"] = false;
    $_SESSION["abonneId"] = -1;
    $_SESSION["accountId"] = -1;
    $_SESSION["gestionnaire"] = false;
}