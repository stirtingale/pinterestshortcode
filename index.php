<?php

namespace Stirtingale\PinterestFeed;

/**
 * Plugin Name: Pinterest Feed Display
 * Description: Displays the latest images from a Pinterest account using RSS
 * Version: 1.0
 * Author: Tom Hole
 * Organization: Stirtingale
 * Requires PHP: 7.4
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 */
final class PinterestFeed
{
    /** @var string */
    private const OPTION_USERNAME = 'pinterest_username';

    /** @var string */
    private const OPTION_ITEM_COUNT = 'pinterest_item_count';

    /** @var int */
    private const DEFAULT_ITEM_COUNT = 9;

    /**
     * Initialize the plugin
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addSettingsPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_shortcode('pinterest_feed', [$this, 'renderShortcode']);
    }

    /**
     * Add settings page to WordPress admin
     */
    public function addSettingsPage(): void
    {
        add_options_page(
            'Pinterest Feed Settings',
            'Pinterest Feed',
            'manage_options',
            'pinterest-feed-settings',
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Register plugin settings
     */
    public function registerSettings(): void
    {
        register_setting(
            'pinterest_feed_settings',
            self::OPTION_USERNAME,
            [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ]
        );

        register_setting(
            'pinterest_feed_settings',
            self::OPTION_ITEM_COUNT,
            [
                'type' => 'integer',
                'default' => self::DEFAULT_ITEM_COUNT,
                'sanitize_callback' => [$this, 'sanitizeItemCount'],
            ]
        );
    }

    /**
     * Sanitize the item count option
     * 
     * @param mixed $value
     * @return int
     */
    public function sanitizeItemCount($value): int
    {
        $count = absint($value);
        return max(1, min(50, $count));
    }

    /**
     * Render the settings page HTML
     */
    public function renderSettingsPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('pinterest_feed_settings');
                do_settings_sections('pinterest_feed_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Pinterest Username</th>
                        <td>
                            <input type="text" name="<?php echo esc_attr(self::OPTION_USERNAME); ?>"
                                value="<?php echo esc_attr(get_option(self::OPTION_USERNAME)); ?>">
                            <p class="description">Enter your Pinterest username (e.g., thatsnotmyage)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Number of Items</th>
                        <td>
                            <input type="number" name="<?php echo esc_attr(self::OPTION_ITEM_COUNT); ?>"
                                min="1" max="50"
                                value="<?php echo esc_attr(get_option(self::OPTION_ITEM_COUNT, self::DEFAULT_ITEM_COUNT)); ?>">
                            <p class="description">Number of Pinterest items to display (default: <?php echo self::DEFAULT_ITEM_COUNT; ?>)</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
<?php
    }

    /**
     * Fetch Pinterest RSS feed
     * 
     * @return array|false
     */
    private function fetchFeed()
    {
        $username = get_option(self::OPTION_USERNAME);

        if (empty($username)) {
            return false;
        }

        $cache_key = 'pinterest_feed_' . $username;
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            return $cached_data;
        }

        $rss_url = sprintf('https://uk.pinterest.com/%s/feed.rss', urlencode($username));
        $response = wp_remote_get($rss_url);

        if (is_wp_error($response)) {
            error_log(sprintf('Pinterest Feed Plugin Error: %s', $response->get_error_message()));
            return false;
        }

        $body = wp_remote_retrieve_body($response);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);

        if ($xml === false) {
            error_log('Pinterest Feed Plugin Error: Failed to parse RSS feed');
            return false;
        }

        $items = [];
        $count = 0;
        $max_items = (int) get_option(self::OPTION_ITEM_COUNT, self::DEFAULT_ITEM_COUNT);

        foreach ($xml->channel->item as $item) {
            if ($count >= $max_items) {
                break;
            }

            $description = (string) $item->description;
            if (!preg_match('/<img[^>]+src="([^"]+)"/', $description, $matches)) {
                continue;
            }

            $items[] = [
                'image_url' => $matches[1],
                'link' => (string) $item->link,
                'title' => (string) $item->title
            ];
            $count++;
        }

        if (!empty($items)) {
            set_transient($cache_key, $items, 6 * HOUR_IN_SECONDS);
            return $items;
        }

        return false;
    }

    /**
     * Render the shortcode output
     * 
     * @return string
     */
    public function renderShortcode(): string
    {
        $items = $this->fetchFeed();

        if (!$items) {
            return '<p>No Pinterest images found.</p>';
        }

        $output = '<ul class="pinterest-feed">';

        foreach ($items as $item) {
            $output .= sprintf(
                '<li><a href="%s" target="_blank" rel="noopener"><img src="%s" alt="%s"></a></li>',
                esc_url($item['link']),
                esc_url($item['image_url']),
                esc_attr($item['title'])
            );
        }

        $output .= '</ul>';
        $output .= $this->getStyles();

        return $output;
    }

    /**
     * Get the CSS styles for the feed
     * 
     * @return string
     */
    private function getStyles(): string
    {
        return '
        <style>
            ul.pinterest-feed {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                list-style: none;
                padding: 0;
                margin: 0;
            }
            ul.pinterest-feed a img {
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                object-fit: cover;
                display: block;
            }
            ul.pinterest-feed li a {
                padding-bottom: 100%;
                position: relative;
                display: block;
                height: 0;
                width: 100%;
            }
            ul.pinterest-feed li {
                margin: 0;
                padding: 0;
            }
        </style>';
    }
}

// Initialize the plugin
new PinterestFeed();
