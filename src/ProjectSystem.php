<?php declare(strict_types=1);
namespace Gratis\Project;

final class ProjectSystem
{
    public static function register(): void
    {
        register_post_type('gratis_project', [
            'labels' => ['name' => 'Projects', 'singular_name' => 'Project'],
            'public' => false, 'show_ui' => true, 'show_in_rest' => true,
            'menu_icon' => 'dashicons-clipboard', 'supports' => ['title', 'editor', 'custom-fields'],
        ]);
        register_post_type('gratis_task', [
            'labels' => ['name' => 'Tasks', 'singular_name' => 'Task'],
            'public' => false, 'show_ui' => true, 'show_in_rest' => true,
            'show_in_menu' => 'edit.php?post_type=gratis_project',
            'supports' => ['title', 'editor', 'custom-fields'],
        ]);

        register_taxonomy('task_status', 'gratis_task', [
            'labels' => ['name' => 'Status'], 'hierarchical' => false,
            'show_ui' => true, 'show_in_rest' => true,
        ]);

        register_post_meta('gratis_task', '_project_id', ['type' => 'integer', 'single' => true, 'show_in_rest' => true]);
        register_post_meta('gratis_task', '_assignee', ['type' => 'integer', 'single' => true, 'show_in_rest' => true]);
        register_post_meta('gratis_task', '_due_date', ['type' => 'string', 'single' => true, 'show_in_rest' => true]);
        register_post_meta('gratis_task', '_priority', ['type' => 'string', 'single' => true, 'show_in_rest' => true]);

        // Default statuses
        if (!term_exists('todo', 'task_status')) {
            wp_insert_term('To Do', 'task_status', ['slug' => 'todo']);
            wp_insert_term('In Progress', 'task_status', ['slug' => 'in-progress']);
            wp_insert_term('Review', 'task_status', ['slug' => 'review']);
            wp_insert_term('Done', 'task_status', ['slug' => 'done']);
        }
    }
}
