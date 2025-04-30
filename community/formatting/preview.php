<?php
require_once 'formatting_functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    header('Content-Type: text/html');
    echo render_formatted_text($_POST['content']);
    exit;
}
