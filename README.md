# Update WooCommerce Product Dimensions in Batches

This WordPress plugin enables WooCommerce store administrators to automatically update the dimensions (height, width, and depth) of products in bulk. It fetches the dimension values from the product attributes and updates the product dimensions accordingly. The plugin intelligently processes updates in batches to prevent server timeouts, ensuring a smooth operation for stores with a large number of products.

## Features

- **Batch Processing**: Updates product dimensions in batches to avoid server timeouts, suitable for shops with a large number of products.
- **Live Feedback**: Provides a live progress update on the settings page under WooCommerce, with immediate feedback on the update status.
- **Debug Information**: Optionally logs debug information to the plugin page and console, including detailed logs about the update process and outcome.

## Installation

1. Download the plugin files.
2. Log in to your WordPress dashboard.
3. Navigate to **Plugins** > **Add New**.
4. Click the **Upload Plugin** button at the top of the page.
5. Click **Choose File**, then find and select the plugin zip file you downloaded.
6. Click **Install Now** to install the plugin.
7. After the installation is complete, click **Activate Plugin** to activate it.

## Usage

1. After installation and activation, go to the WooCommerce settings page.
2. Find and click on **Update Dimensions** under the WooCommerce menu.
3. Click the **Update Dimensions** button to start the process.
4. Monitor the progress in real-time on the same page. Do not navigate away from the page until the process completes.

### TODO

The plugin comes with a debug mode feature that, when enabled, displays detailed debug information on both the settings page and the console. By default, the debug mode is turned on. To toggle this feature:

- Access the plugin's main PHP file and locate the `$debug_enabled` variable.
- Set `$debug_enabled` to `true` to enable debug mode or `false` to disable it.

## Notes

- The plugin automatically identifies dimension attributes based on their names: "height", "width", and "depth". Ensure your product dimension attributes are named accordingly.
- If a product has multiple values for a dimension attribute, separated by "|", the plugin will only consider the last value for updating that dimension.

## Support

For support, feature requests, or contributions, please contact dev@kwirx.com.

## License

This plugin is open-sourced software licensed under the [license name].

## Author

Kwirx - Visit [kwirx](http://kwirx.com) for more info.
