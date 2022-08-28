<?php
/**
 * Plugin Name: Layanan Google Books
 * Plugin URI: https://github.com/drajathasan/slims-google-books
 * Description: Ambil data buku dari Google Books
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://t.me/drajathasan
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering menus or hook
$plugin->registerMenu("bibliography", "Layanan Google Books", __DIR__ . "/index.php");