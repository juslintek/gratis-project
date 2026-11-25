<?php declare(strict_types=1);

namespace Gratis\Project;

defined('ABSPATH') || exit;

/**
 * Project + task CPTs, task-status taxonomy and default seed terms.
 */
final class ProjectSystem {

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    /** Default board columns: slug => label. */
    public const STATUSES = [
        'todo'        => 'To Do',
        'in-progress' => 'In Progress',
        'review'      => 'Review',
        'done'        => 'Done',
    ];

    /** Register CPTs, taxonomy and meta; seed default statuses once. */
    public static function register(): void {
        register_post_type('gratis_project', [
            'labels'       => ['name' => __('Projects', 'gratis-project'), 'singular_name' => __('Project', 'gratis-project')],
            'public'       => false,
            'show_ui'      => true,
            'show_in_rest' => false,
            'menu_icon'    => 'dashicons-clipboard',
            'supports'     => ['title', 'editor', 'custom-fields'],
        ]);
        register_post_type('gratis_task', [
            'labels'       => ['name' => __('Tasks', 'gratis-project'), 'singular_name' => __('Task', 'gratis-project')],
            'public'       => false,
            'show_ui'      => true,
            'show_in_rest' => false,
            'show_in_menu' => 'edit.php?post_type=gratis_project',
            'supports'     => ['title', 'editor', 'custom-fields'],
        ]);

        register_taxonomy('task_status', 'gratis_task', [
            'labels'            => ['name' => __('Status', 'gratis-project')],
            'hierarchical'      => false,
            'show_ui'           => true,
            'show_in_rest'      => false,
            'show_admin_column' => true,
        ]);

        register_post_meta('gratis_task', '_project_id', ['type' => 'integer', 'single' => true, 'show_in_rest' => false, 'auth_callback' => fn() => current_user_can('edit_posts')]);
        register_post_meta('gratis_task', '_assignee', ['type' => 'integer', 'single' => true, 'show_in_rest' => false, 'auth_callback' => fn() => current_user_can('edit_posts')]);
        register_post_meta('gratis_task', '_due_date', ['type' => 'string', 'single' => true, 'show_in_rest' => false, 'sanitize_callback' => 'sanitize_text_field', 'auth_callback' => fn() => current_user_can('edit_posts')]);
        register_post_meta('gratis_task', '_priority', ['type' => 'string', 'single' => true, 'show_in_rest' => false, 'sanitize_callback' => 'sanitize_key', 'auth_callback' => fn() => current_user_can('edit_posts')]);

        self::seedStatuses();
    }

    /** Insert the default status terms once (flag-guarded). */
    private static function seedStatuses(): void {
        if (get_option('gratis_project_seeded')) {
            return;
        }
        foreach (self::STATUSES as $slug => $label) {
            if (! term_exists($slug, 'task_status')) {
                wp_insert_term($label, 'task_status', ['slug' => $slug]);
            }
        }
        update_option('gratis_project_seeded', 1, false);
    }
}
