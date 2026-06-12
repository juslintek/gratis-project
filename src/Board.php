<?php declare(strict_types=1);

namespace Gratis\Project;

defined('ABSPATH') || exit;

/**
 * Read-only Kanban board renderer via the [gratis_board] shortcode.
 */
final class Board {

    /** Register the board shortcodes. */
    public static function register_shortcode(): void {
        add_shortcode('gratis_board', [__CLASS__, 'render']);
        add_shortcode('gratis-board', [__CLASS__, 'render']);
    }

    /** Render a Kanban board grouped by task status. */
    public static function render($atts): string {
        $atts    = shortcode_atts(['project' => 0], $atts, 'gratis_board');
        $project = absint($atts['project']);

        $columns = '';
        foreach (ProjectSystem::STATUSES as $slug => $label) {
            $columns .= self::renderColumn($slug, $label, $project);
        }

        return '<div class="gratis-board" style="display:flex;gap:16px;overflow-x:auto;align-items:flex-start">' . $columns . '</div>';
    }

    /** Render a single status column. */
    private static function renderColumn(string $slug, string $label, int $project): string {
        $query = [
            'post_type'   => 'gratis_task',
            // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts -- bounded board column.
            'numberposts' => 100,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- board grouped by status taxonomy.
            'tax_query'   => [['taxonomy' => 'task_status', 'field' => 'slug', 'terms' => $slug]],
        ];
        if ($project > 0) {
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- board scoped to a project.
            $query['meta_query'] = [['key' => '_project_id', 'value' => $project]];
        }

        $tasks = get_posts($query);
        $cards = '';
        foreach ($tasks as $task) {
            $priority = (string) get_post_meta($task->ID, '_priority', true);
            $due      = (string) get_post_meta($task->ID, '_due_date', true);
            $cards   .= '<div class="gratis-card" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-bottom:10px">';
            $cards   .= '<strong>' . esc_html($task->post_title) . '</strong>';
            if ($priority !== '') {
                $cards .= '<span style="display:inline-block;margin-left:6px;font-size:11px;color:#64748b">' . esc_html(ucfirst($priority)) . '</span>';
            }
            if ($due !== '') {
                $cards .= '<div style="font-size:12px;color:#94a3b8;margin-top:4px">' . esc_html__('Due', 'gratis-project') . ' ' . esc_html($due) . '</div>';
            }
            $cards .= '</div>';
        }
        if ($cards === '') {
            $cards = '<p style="color:#94a3b8;font-size:13px">' . esc_html__('No tasks.', 'gratis-project') . '</p>';
        }

        return '<div class="gratis-board-col" style="min-width:240px;background:#f1f5f9;border-radius:10px;padding:12px">'
            . '<h3 style="margin:0 0 12px;font-size:14px">' . esc_html($label) . '</h3>'
            . $cards
            . '</div>';
    }
}
