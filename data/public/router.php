<?php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|woff|woff2|ttf)$/', $_SERVER["REQUEST_URI"])) {
    return false;
} else {
    require 'index.php';
}
