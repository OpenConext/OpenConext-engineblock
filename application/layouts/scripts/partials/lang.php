<?php

// Determine the selected language, default to english
$lang = 'en';
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
} elseif (isset($_POST['lang'])) {
    $lang = $_POST['lang'];
} elseif (isset($_COOKIE['lang'])) {
    $lang = $_COOKIE['lang'];
}
if (!in_array($lang, array('nl','en'))) {
    $lang = 'en';
}