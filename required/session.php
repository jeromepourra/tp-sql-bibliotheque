<?php
session_start();
if (!isset($_SESSION["initialized"])) {
    $_SESSION["initialized"] = true;
}