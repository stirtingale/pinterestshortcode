# Pinterest Feed Plugin

A WordPress plugin that displays the latest images from a Pinterest account using their RSS feed.

## Features

- Display Pinterest images in a responsive 3x3 grid
- Configurable number of images to display (1-50)
- Square image display with proper aspect ratio
- Caches feed data for 6 hours for improved performance
- Clean, modern PHP 7.4+ implementation
- Lightweight with no external dependencies
- Mobile-friendly responsive design

## Installation

1. Upload the `pinterest-feed` directory to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Pinterest Feed to configure your settings

## Configuration

1. Navigate to Settings > Pinterest Feed in your WordPress admin panel
2. Enter your Pinterest username (e.g., "thatsnotmyage")
3. Set the number of images you want to display (default: 9)
4. Save your changes

## Usage

Use the shortcode `[pinterest_feed]` in any post or page where you want the Pinterest feed to appear.

Example:
```
[pinterest_feed]
```

## Technical Details

### Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher

### Caching
- Feed data is cached for 6 hours using WordPress transients
- Cache is automatically refreshed when:
  - Cache expires
  - Settings are updated
  - New feed data is available

### CSS Grid Layout
The plugin uses CSS Grid to create a responsive layout:
- 3-column grid design
- 1:1 aspect ratio for all images
- Maintains image quality using object-fit
- Responsive on all screen sizes

### Security Features
- Properly escaped output
- Sanitized inputs
- XSS protection
- Direct file access prevention
- Safe RSS parsing

## Customization

### Styling
The plugin includes default styles for the grid layout. You can override these styles in your theme's CSS using the following selectors:

```css
ul.pinterest-feed { /* Grid container */ }
ul.pinterest-feed li { /* Grid items */ }
ul.pinterest-feed a img { /* Images */ }
ul.pinterest-feed li a { /* Image links */ }
```

### Advanced Usage

To integrate the feed programmatically, you can use the following code:

```php
if (shortcode_exists('pinterest_feed')) {
    echo do_shortcode('[pinterest_feed]');
}
```

## Troubleshooting

### Common Issues

1. **No Images Appearing**
   - Check your Pinterest username is correct
   - Verify your Pinterest account is public
   - Check WordPress error logs for any RSS feed errors

2. **Caching Issues**
   - Clear WordPress transients to force a feed refresh
   - Check server PHP timeout settings if feed isn't updating

3. **Layout Problems**
   - Ensure your theme isn't overriding grid styles
   - Check for CSS conflicts
   - Verify mobile responsiveness

### Debug Mode

To enable debug logging, add the following to your wp-config.php:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## Support

For support:
1. Check this documentation
2. Review WordPress error logs
3. Contact the plugin author

## Credits

- Developed by Tom Hole
- Organization: Stirtingale
- Version: 1.0

## License

This plugin is released under the GPL v2 or later license.

## Changelog

### 1.0
- Initial release
- Basic Pinterest RSS feed integration
- Configurable image count
- Responsive grid layout
- Feed caching system
