<?php
/**
 * Plugin Name: Undangan Core (hariH)
 * Description: Fondasi platform undangan digital hariH — CPT & meta, REST RSVP, privasi REST, aturan WooCommerce, hardening. mu-plugin: selalu aktif, tidak bisa dimatikan tak sengaja.
 * Version: 0.1.0
 * Author: hariH
 */

if (!defined('ABSPATH')) exit;

define('UNDANGAN_CORE_DIR', __DIR__ . '/undangan-core');

require UNDANGAN_CORE_DIR . '/cpt.php';
require UNDANGAN_CORE_DIR . '/rest.php';
require UNDANGAN_CORE_DIR . '/woocommerce.php';
require UNDANGAN_CORE_DIR . '/cetak.php';
require UNDANGAN_CORE_DIR . '/proof.php';
require UNDANGAN_CORE_DIR . '/hardening.php';
require UNDANGAN_CORE_DIR . '/masa-aktif.php';
require UNDANGAN_CORE_DIR . '/og.php';
