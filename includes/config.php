<?php
/**
 * Centralized configuration for the application.
 * Reads settings from environment variables.
 */

// SendGrid API key
define('SENDGRID_API_KEY', getenv('SENDGRID_API_KEY'));

// Recipient email and name
define('CONTACT_EMAIL', getenv('CONTACT_EMAIL') ?: 'info@epsomAutoRepairs.com');
define('CONTACT_NAME', getenv('CONTACT_NAME') ?: 'Epsom Auto Repairs');

// Google reCAPTCHA v2 keys
define('RECAPTCHA_SITE_KEY', getenv('RECAPTCHA_SITE_KEY'));
define('RECAPTCHA_SECRET_KEY', getenv('RECAPTCHA_SECRET_KEY'));
