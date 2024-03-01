<?php
/**
 * Plugin Name: Woocommerce Product Dimensions Updater
 * Plugin URI: https://kwirx.com
 * Description: Updates WooCommerce product dimensions based on attribute values.
 * Version: 1.0.5
 * Author: Kwirx
 * Author URI: https://kwirx.com
 * WC requires at least: 3.0
 * WC tested up to: 6.0
 */

// Exit if accessed directly
if (!defined("WPINC")) {
    die();
}

/**
 * Display admin notices if WooCommerce is not installed or activated.
 */
function wc_pdu_check_woocommerce()
{
    // Check if WooCommerce is active.
    if (!in_array("woocommerce/woocommerce.php", apply_filters("active_plugins", get_option("active_plugins")))) {
        // Determine the message based on whether WooCommerce is installed.
        if (!file_exists(WP_PLUGIN_DIR . "/woocommerce/woocommerce.php")) {
            // WooCommerce is not installed.
            $message = sprintf(
                '<strong>WooCommerce is not installed.</strong> <a href="%s">Click here to install and activate WooCommerce.</a>',
                admin_url(
                    "plugin-install.php?s=WooCommerce&tab=search&type=term"
                )
            );
        } else {
            // WooCommerce is installed but not activated.
            $message = sprintf(
                '<strong>WooCommerce is not activated.</strong> <a href="%s">Click here to activate WooCommerce.</a>',
                admin_url("plugins.php")
            );
        }

        // Output the admin notice.
        echo '<div class="error"><p>' . $message . "</p></div>";
    }
}
// Hook the check into admin notices to display our message if needed.
add_action("admin_notices", "wc_pdu_check_woocommerce");

// Add the submenu page under WooCommerce menu
add_action("admin_menu", "wc_pdu_register_menu_page");

// Define the AJAX action for updating product dimensions
add_action( "wp_ajax_update_product_dimensions", "wc_pdu_update_product_dimensions_ajax");

/**
 * Register menu page for the plugin under WooCommerce settings
 */
function wc_pdu_register_menu_page() {
  add_submenu_page(
    "woocommerce",
    "Update Product Dimensions",
    "Update Dimensions",
    "manage_options",
    "update-product-dimensions",
    "wc_pdu_settings_page"
  );
}

/**
 * Render settings page content
 */
function wc_pdu_settings_page()
{ ?>
  <div class="wrap">
    <h2>Update Product Dimensions</h2>
    <div class="info-block" style="padding: 15px 0;">
      <p>This tool allows you to update the dimensions (height, width, and length) of all WooCommerce products based on their set attributes. It processes the updates in batches to avoid timeouts and provides real-time progress updates.</p>
      <p>Simply click the "<strong>Update Dimensions</strong>" button below to start the update process. Please do not close this page until the process is completed. You will see the progress of updates and any important messages in the sections below.</p>
    </div>
    <button id="wc-pdu-update-button" class="button button-primary">Update Dimensions</button>
    <div id="wc-pdu-progress" style="padding: 10px;"></div>
    <div id="wc-pdu-update-result" style="padding: 10px;"></div>
    <!-- <p><strong>Debug Information:</strong> Below, you'll find detailed logs of the update process, including which products were updated and any errors that occurred.</p>
    <pre id="wc-pdu-debug-info"></pre> -->
  </div>

  <script type="text/javascript">
    jQuery('#wc-pdu-update-button').click(function() {
      console.log('Update started');
      jQuery('#wc-pdu-progress').html('');
      jQuery('#wc-pdu-update-result').html('Starting...');
      jQuery('#wc-pdu-debug-info').html('');
      let data = {
        action: 'update_product_dimensions',
        nonce: '<?php echo wp_create_nonce("update-product-dimensions-nonce"); ?>',
        batch_number: 1
      };
      wc_pdu_handle_ajax(data);
    });

    function wc_pdu_handle_ajax(data) {
      jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: data,
        success: function(response) {
          // console.log('Batch ' + data.batch_number + ' processed:', response);
          // jQuery('#wc-pdu-debug-info').append('Batch ' + data.batch_number + ' processed:\n' + JSON.stringify(response.data.debugInfo, null, 2) + '\n\n');
          if (response.success) {
            jQuery('#wc-pdu-progress').html(response.data.progress);
            if (!response.data.done) {
              data.batch_number++;
              wc_pdu_handle_ajax(data);
            } else {
              jQuery('#wc-pdu-update-result').html(response.data.message);
            }
          } else {
            jQuery('#wc-pdu-update-result').html('An error occurred.');
          }
        }
      });
    }
  </script>
  <?php
}

/**
 * Handle AJAX request to update product dimensions
 */
function wc_pdu_update_product_dimensions_ajax()
{
    check_ajax_referer("update-product-dimensions-nonce", "nonce");

    $batch_size = 10; // Adjust as needed for optimal performance.
    $batch_number = isset($_POST["batch_number"]) ? absint($_POST["batch_number"]) : 1;
    // $debugInfo = [];

    $args = [
        "status" => "publish",
        "limit" => $batch_size,
        "page" => $batch_number,
        "type" => "simple",
    ];
    $products = wc_get_products($args);
    $total_products = wp_count_posts("product")->publish + wp_count_posts("product_variation")->publish;

    foreach ($products as $product) {
      $height = $product->get_attribute("height");
      $width = $product->get_attribute("width");
      $length = $product->get_attribute("length");

      // Check for and handle attributes with multiple values separated by "|", take the last value
      $height = strpos($height, '|') !== false ? trim(end(explode('|', $height))) : $height;
      $width  = strpos($width, '|')  !== false ? trim(end(explode('|', $width)))  : $width;
      $depth  = strpos($depth, '|')  !== false ? trim(end(explode('|', $depth)))  : $depth;

      if (!empty($height) && !empty($width) && !empty($length)) {
        $product->set_height($height);
        $product->set_width($width);
        $product->set_length($length);
        $product->save();
        $debugInfo[] = "Product ID {$product->get_id()} dimensions: Height - {$height}, Width - {$width}, Length - {$length}";
      }
    }

    $updated_count = $batch_size * ($batch_number - 1) + count($products);
    $done = $updated_count >= $total_products;

    $progress = "Updated {$updated_count} of {$total_products} products.";

    if ($done) {
      wp_send_json_success([
        "message" => "Product dimensions updated successfully.",
        "progress" => $progress,
        "done" => true,
        // "debugInfo" => $debugInfo,
      ]);
    } else {
      wp_send_json_success([
        "progress" => $progress,
        "done" => false,
        // "debugInfo" => $debugInfo,
      ]);
    }
}
