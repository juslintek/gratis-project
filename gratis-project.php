<?php
/**
 * Plugin Name: Gratis Project
 * Description: Project management with Kanban boards, tasks, milestones. Replaces Trello/Asana for teams.
 * Version: 1.0.0
 * Requires PHP: 8.1
 * Author: Linas Jusys
 * License: GPL-2.0-or-later
 */
defined('ABSPATH') || exit;
require_once __DIR__ . '/autoload.php';
add_action('init', [\Gratis\Project\ProjectSystem::class, 'register']);
add_action('plugins_loaded', function () {
    if (defined('WP_CLI') && WP_CLI) \WP_CLI::add_command('gratis-project', \Gratis\Project\CLI\ProjectCommand::class);
});
