<?php
/**
 * Logout Handler
 * 
 * Destroys user session and redirects to login page
 */

// Include dependencies
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

// Logout user
logout_user();

// Set logout message
set_message('info', 'You have been logged out successfully.');

// Redirect to login page
redirect('login.php');
?>