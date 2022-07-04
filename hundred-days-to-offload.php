<?php

/**
 * Plugin Name:       Hundred Days to Offload
 * Plugin URI:        https://github.com/imrehg/hundred-days-to-offload-wordpress
 * Description:       Easy overview of progress towards 100DaysToOffload.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Author:            Gergely Imreh
 * Author URI:        https://gergely.imreh.net
 * License:           apache2
 * License URI:       https://www.apache.org/licenses/LICENSE-2.0
 */

function hudred_days_posts_page_html(): void
{
    // check user capabilities
    if (! current_user_can('publish_posts')) {
        return;
    }

    $date_format = "Y-m-d";
    # Calculate relevant time intervals
    $cutoff_date = date($date_format, strtotime("-1 year"));
    #TODO: today's date might not be needed, as post publish days are only in the past?
    $today_date = date($date_format, getdate());

    $target_count = 100;

    $args = array(
        'post_type'   => 'post',
        'post_status' => 'publish',
        'date_query' => [
            [
                'after' => $cutoff_date,
                'before' => $today_date,
                'inclusive' => true,
            ]
        ],
        'orderby' => 'date',
        'order' => 'ASC',
        'nopaging' => true,
    );
    $query = new WP_Query($args);
    $post_count = $query->found_posts;
    $post_count_text = $post_count . " " . ngettext('post', 'posts', $post_count);

    if ($query->have_posts()) {
        # Get the first post
        $query->the_post();
        $have_posts = true;
        $oldest_date = get_the_date($date_format);
    } else {
        $have_posts = false;
    } ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>
        <?php
            #TODO: This is not correctly translatable just yet
            echo "Found $post_count_text in the interval from $cutoff_date to today.";
        ?>

        <p>
        <?php
        #TODO: This is not correctly translatable just yet
        if ($post_count >= $target_count) {
            echo "You've achieved <a href=\"https://100daystooffload.com/\">#100DaysOfOffload!</a> 🎉";
        } else {
            $required_count = $target_count - $post_count;
            echo "Still need to publish $required_count " . ngettext('post', 'posts', $required_count). " ⌨️.";
        }
        ?>
        </p>

        <?php if ($have_posts): ?>
        <p>
            <?php echo "The oldest post in interval is from $oldest_date." ?>
        </p>
        <?php endif; ?>


    </div>
    <?php
}

function hundred_days_options_page(): void
{
    add_posts_page(
        __('Hundred Days to Offload Progress', 'textdomain'),
        __('Hundred Days to Offload', 'textdomain'),
        'publish_posts',
        'hundreddays',
        'hudred_days_posts_page_html'
    );
}
add_action('admin_menu', 'hundred_days_options_page');

?>
