<?php declare(strict_types=1);
namespace Gratis\Project\CLI;

final class ProjectCommand
{
    /** @when after_wp_load */
    public function tasks($args, $assoc_args): void
    {
        $query = ['post_type' => 'gratis_task', 'numberposts' => 50];
        if (!empty($assoc_args['project'])) $query['meta_query'] = [['key' => '_project_id', 'value' => (int) $assoc_args['project']]];
        if (!empty($assoc_args['status'])) $query['tax_query'] = [['taxonomy' => 'task_status', 'field' => 'slug', 'terms' => $assoc_args['status']]];

        $tasks = get_posts($query);
        if (empty($tasks)) { \WP_CLI::log("No tasks."); return; }
        $rows = array_map(fn($t) => [
            'ID' => $t->ID, 'Title' => $t->post_title,
            'Status' => implode(',', wp_get_object_terms($t->ID, 'task_status', ['fields' => 'slugs'])),
            'Priority' => get_post_meta($t->ID, '_priority', true) ?: 'normal',
            'Due' => get_post_meta($t->ID, '_due_date', true) ?: '-',
        ], $tasks);
        \WP_CLI\Utils\format_items('table', $rows, ['ID', 'Title', 'Status', 'Priority', 'Due']);
    }

    /**
     * Create a task.
     * <title> : Task title.
     * [--project=<id>] : Project ID.
     * [--priority=<p>] : low, normal, high, urgent.
     * [--due=<date>] : Due date (YYYY-MM-DD).
     * @when after_wp_load
     */
    public function add($args, $assoc_args): void
    {
        $id = wp_insert_post([
            'post_type' => 'gratis_task', 'post_title' => $args[0], 'post_status' => 'publish',
            'meta_input' => [
                '_project_id' => (int) ($assoc_args['project'] ?? 0),
                '_priority' => $assoc_args['priority'] ?? 'normal',
                '_due_date' => $assoc_args['due'] ?? '',
            ],
        ]);
        wp_set_object_terms($id, ['todo'], 'task_status');
        \WP_CLI::success("Task #{$id} created.");
    }
}
