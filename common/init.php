<?php
// FILE: common/init.php
// This is the new central starting point for every page.

// 1. Start the session
// This MUST be the very first thing to run.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Include the configuration and database connection
// Use require_once to prevent multiple inclusions.
require_once __DIR__ . '/config.php';

?>