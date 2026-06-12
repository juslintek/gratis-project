<?php declare(strict_types=1);

namespace Gratis\Project;

defined('ABSPATH') || exit;

/**
 * Admin settings for Gratis Project.
 */
final class Settings {

    private const GROUP = 'gratis_project_settings_group';
    private const PAGE  = 'gratis-project-settings';

    private const OPT_PRIORITY = 'gratis_project_default_priority';

    /** Register the settings submenu under the Projects menu. */
    public static function register_menu(): void {
        add_submenu_page(
            'edit.php?post_type=gratis_project',
            __('Project Settings', 'gratis-project'),
            __('Settings', 'gratis-project'),
            'manage_options',
            self::PAGE,
            [__CLASS__, 'render_page']
        );
    }

    /** Register options with typed sanitisers. */
    public static function register_settings(): void {
        register_setting(self::GROUP, self::OPT_PRIORITY, [
            'type'              => 'string',
            'sanitize_callback' => [__CLASS__, 'sanitize_priority'],
            'default'           => 'normal',
        ]);
    }

    /** Whitelist the default priority. */
    public static function sanitize_priority($value): string {
        $value = sanitize_key((string) $value);
        return in_array($value, ProjectSystem::PRIORITIES, true) ? $value : 'normal';
    }

    /** Configured default task priority. */
    public static function defaultPriority(): string {
        $value = (string) get_option(self::OPT_PRIORITY, 'normal');
        return in_array($value, ProjectSystem::PRIORITIES, true) ? $value : 'normal';
    }

    /** Render the settings screen. */
    public static function render_page(): void {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'gratis-project'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Project Settings', 'gratis-project') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields(self::GROUP);
        echo '<table class="form-table" role="presentation"><tbody>';

        echo '<tr><th scope="row"><label for="gp_priority">' . esc_html__('Default task priority', 'gratis-project') . '</label></th><td>';
        echo '<select id="gp_priority" name="' . esc_attr(self::OPT_PRIORITY) . '">';
        $current = self::defaultPriority();
        foreach (ProjectSystem::PRIORITIES as $slug) {
            echo '<option value="' . esc_attr($slug) . '"' . selected($current, $slug, false) . '>' . esc_html(ucfirst($slug)) . '</option>';
        }
        echo '</select></td></tr>';

        echo '</tbody></table>';
        submit_button(__('Save settings', 'gratis-project'));
        echo '</form>';

        echo '<h2>' . esc_html__('Kanban board', 'gratis-project') . '</h2>';
        echo '<p>' . esc_html__('Embed a board with the shortcode:', 'gratis-project') . ' <code>[gratis_board project="ID"]</code></p>';
        echo '</div>';
    }
}
