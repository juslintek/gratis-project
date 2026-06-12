<?php
/**
 * Plugin Name: Gratis Project
 * Plugin URI: https://github.com/juslintek/gratis-project
 * Description: Project management with Kanban boards, tasks, and milestones. Replaces Trello/Asana for teams.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Linas Jusys
 * Author URI: https://github.com/juslintek
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gratis-project
 */

defined('ABSPATH') || exit;

define('GRATIS_PROJECT_VERSION', '1.0.0');
define('GRATIS_PROJECT_FILE', __FILE__);
define('GRATIS_PROJECT_DIR', plugin_dir_path(__FILE__));
define('GRATIS_PROJECT_URL', plugin_dir_url(__FILE__));

require_once __DIR__ . '/autoload.php';

add_action('init', [\Gratis\Project\ProjectSystem::class, 'register']);
add_action('init', [\Gratis\Project\Board::class, 'register_shortcode']);

// Task editing meta box.
add_action('add_meta_boxes', [\Gratis\Project\MetaBox::class, 'register']);
add_action('save_post_gratis_task', [\Gratis\Project\MetaBox::class, 'save']);

// Admin settings page.
add_action('admin_menu', [\Gratis\Project\Settings::class, 'register_menu']);
add_action('admin_init', [\Gratis\Project\Settings::class, 'register_settings']);

add_action('plugins_loaded', function () {
    if (defined('WP_CLI') && WP_CLI) {
        \WP_CLI::add_command('gratis-project', \Gratis\Project\CLI\ProjectCommand::class);
    }
});
