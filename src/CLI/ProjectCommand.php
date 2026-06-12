<?php declare(strict_types=1);

namespace Gratis\Project\CLI;

use Gratis\Project\Settings;

defined('ABSPATH') || exit;

/**
 * WP-CLI commands for Gratis Project.
 */
final class ProjectCommand {

    /**
     * List tasks.
     *
     * [--project=<id>]
     * : Filter by project ID.
     *
     * [--status=<slug>]
     * : Filter by status slug.
     *
     * @when after_wp_load
     */
    public function tasks($args, $assoc_args): void {
        unset($args);
        $query = ['post_type' => 'gratis_task', 'numberposts' => 50];
        if (! empty($assoc_args['project'])) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- intentional CLI filter.
            $query['meta_query'] = [['key' => '_project_id', 'value' => (int) $assoc_args['project']]];
        }
        if (! empty($assoc_args['status'])) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- intentional CLI filter.
            $query['tax_query'] = [['taxonomy' => 'task_status', 'field' => 'slug', 'terms' => sanitize_key((string) $assoc_args['status'])]];
        }

        $tasks = get_posts($query);
        if (empty($tasks)) {
            \WP_CLI::log('No tasks.');
            return;
        }
        $rows = array_map(static fn($t) => [
            'ID'       => $t->ID,
            'Title'    => $t->post_title,
            'Status'   => implode(',', wp_get_object_terms($t->ID, 'task_status', ['fields' => 'slugs'])),
            'Priority' => get_post_meta($t->ID, '_priority', true) ?: 'normal',
            'Due'      => get_post_meta($t->ID, '_due_date', true) ?: '-',
        ], $tasks);
        \WP_CLI\Utils\format_items('table', $rows, ['ID', 'Title', 'Status', 'Priority', 'Due']);
    }

    /**
     * Create a task.
     *
     * <title>
     * : Task title.
     *
     * [--project=<id>]
     * : Project ID.
     *
     * [--priority=<p>]
     * : low, normal, high, urgent.
     *
     * [--due=<date>]
     * : Due date (YYYY-MM-DD).
     *
     * @when after_wp_load
     */
    public function add($args, $assoc_args): void {
        $priority = sanitize_key((string) ($assoc_args['priority'] ?? Settings::defaultPriority()));
        $id       = wp_insert_post([
            'post_type'   => 'gratis_task',
            'post_title'  => sanitize_text_field((string) $args[0]),
            'post_status' => 'publish',
            'meta_input'  => [
                '_project_id' => (int) ($assoc_args['project'] ?? 0),
                '_priority'   => $priority,
                '_due_date'   => sanitize_text_field((string) ($assoc_args['due'] ?? '')),
            ],
        ]);
        if (is_wp_error($id)) {
            \WP_CLI::error('Failed to create task.');
        }
        wp_set_object_terms($id, ['todo'], 'task_status');
        \WP_CLI::success("Task #{$id} created.");
    }
}
