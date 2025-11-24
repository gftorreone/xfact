<?php
// Basic configuration
$DB_PATH = __DIR__ . '/../../data/database.sqlite';
$UPLOAD_DIR = __DIR__ . '/../uploads';
$BASE_URL = '/';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
