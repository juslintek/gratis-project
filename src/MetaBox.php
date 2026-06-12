<?php declare(strict_types=1);

namespace Gratis\Project;

defined('ABSPATH') || exit;

/**
 * Task editing meta box (project, assignee, priority, due date).
 */
final class MetaBox {

    private const NONCE = 'gratis_project_meta';

    /** Register the meta box on the task CPT. */
    public static function register(): void {
        add_meta_box(
            'gratis_task_details',
            __('Task Details', 'gratis-project'),
            [__CLASS__, 'render'],
            'gratis_task',
            'side',
            'default'
        );
    }

    /** Render the meta box fields. */
    public static function render(\WP_Post $post): void {
        wp_nonce_field(self::NONCE, self::NONCE);

        $project  = (int) get_post_meta($post->ID, '_project_id', true);
        $assignee = (int) get_post_meta($post->ID, '_assignee', true);
        $priority = (string) get_post_meta($post->ID, '_priority', true);
        $priority = $priority !== '' ? $priority : 'normal';
        $due      = (string) get_post_meta($post->ID, '_due_date', true);

        echo '<p><label for="gp_project"><strong>' . esc_html__('Project', 'gratis-project') . '</strong></label><br>';
        echo '<select id="gp_project" name="gp_project" style="width:100%">';
        echo '<option value="0">' . esc_html__('— None —', 'gratis-project') . '</option>';
        // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts -- bounded project picker.
        foreach (get_posts(['post_type' => 'gratis_project', 'numberposts' => 200]) as $proj) {
            echo '<option value="' . esc_attr((string) $proj->ID) . '"' . selected($project, $proj->ID, false) . '>' . esc_html($proj->post_title) . '</option>';
        }
        echo '</select></p>';

        echo '<p><label for="gp_assignee"><strong>' . esc_html__('Assignee', 'gratis-project') . '</strong></label><br>';
        echo '<select id="gp_assignee" name="gp_assignee" style="width:100%">';
        echo '<option value="0">' . esc_html__('— Unassigned —', 'gratis-project') . '</option>';
        foreach (get_users(['fields' => ['ID', 'display_name'], 'number' => 200]) as $user) {
            echo '<option value="' . esc_attr((string) $user->ID) . '"' . selected($assignee, $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
        }
        echo '</select></p>';

        echo '<p><label for="gp_priority"><strong>' . esc_html__('Priority', 'gratis-project') . '</strong></label><br>';
        echo '<select id="gp_priority" name="gp_priority">';
        foreach (ProjectSystem::PRIORITIES as $slug) {
            echo '<option value="' . esc_attr($slug) . '"' . selected($priority, $slug, false) . '>' . esc_html(ucfirst($slug)) . '</option>';
        }
        echo '</select></p>';

        echo '<p><label for="gp_due"><strong>' . esc_html__('Due date', 'gratis-project') . '</strong></label><br>';
        echo '<input type="date" id="gp_due" name="gp_due" value="' . esc_attr($due) . '"></p>';
    }

    /** Persist the meta box fields. */
    public static function save(int $post_id): void {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (! isset($_POST[self::NONCE]) || ! wp_verify_nonce(sanitize_key(wp_unslash($_POST[self::NONCE])), self::NONCE)) {
            return;
        }
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // Nonce + capability verified above.
        update_post_meta($post_id, '_project_id', isset($_POST['gp_project']) ? absint(wp_unslash($_POST['gp_project'])) : 0);
        update_post_meta($post_id, '_assignee', isset($_POST['gp_assignee']) ? absint(wp_unslash($_POST['gp_assignee'])) : 0);
        update_post_meta($post_id, '_due_date', sanitize_text_field(wp_unslash($_POST['gp_due'] ?? '')));

        $priority = sanitize_key(wp_unslash($_POST['gp_priority'] ?? 'normal'));
        update_post_meta($post_id, '_priority', in_array($priority, ProjectSystem::PRIORITIES, true) ? $priority : 'normal');
    }
}
