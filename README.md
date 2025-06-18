# WooCommerce Product Category Filter Implementation Guide

This guide provides step-by-step instructions on how to implement the AJAX product category filter functionality on your WordPress site using cPanel.

## Files Overview

You should have the following new/modified files:

1.  **`page-boutique-ortoc.php`**: The modified PHP template for your shop page. This file now includes the HTML for the filter buttons and the necessary CSS styling.
2.  **`ortoc-category-filter.js`**: The JavaScript file that handles the user interaction (clicking filter buttons) and makes AJAX requests to filter products without reloading the page.
3.  **`ortoc-ajax-functions.php`**: A PHP file containing the necessary WordPress hooks and functions to:
    *   Properly load (enqueue) the `ortoc-category-filter.js` script.
    *   Provide the JavaScript with necessary data (like the AJAX URL and a security nonce).
    *   Handle the AJAX requests sent by the JavaScript to fetch and return filtered product lists.

## Implementation Steps via cPanel

**Prerequisite:** Ensure you know the name of your currently active WordPress theme. You can find this in your WordPress admin area under "Appearance" > "Themes".

### Step 1: Access Your Theme Files in cPanel

1.  Log in to your **cPanel**.
2.  Navigate to the **File Manager**.
3.  In the File Manager, go to your WordPress installation directory. This is usually `public_html` or a subdirectory if WordPress is installed there.
4.  Navigate to your active theme's directory: `wp-content/themes/your-active-theme-name/` (replace `your-active-theme-name` with the actual folder name of your theme).

### Step 2: Upload and Update Files

1.  **`page-boutique-ortoc.php`**
    *   **If `page-boutique-ortoc.php` already exists in your theme's root directory (`wp-content/themes/your-active-theme-name/`):**
        *   Upload the new version of `page-boutique-ortoc.php` (that I provided) to this directory, overwriting the existing file.
    *   **If your shop page template is located elsewhere (e.g., a subfolder like `templates/` or `woocommerce/` within your theme):**
        *   You'll need to identify the correct file and path for your specific shop page. The filtering buttons and CSS were added directly to `page-boutique-ortoc.php`. If your theme uses a different file for the main shop/product listing, the changes (PHP for buttons, CSS block) would need to be integrated into that specific file instead. **For now, assume it's in the theme root as `page-boutique-ortoc.php`.**
    *   **Action:** Upload `page-boutique-ortoc.php` to `wp-content/themes/your-active-theme-name/`.

2.  **`ortoc-category-filter.js`**
    *   **Action:** Upload the `ortoc-category-filter.js` file to your theme's root directory: `wp-content/themes/your-active-theme-name/`.
    *   *(Note: The PHP code in `ortoc-ajax-functions.php` assumes this location. If you place it in a subdirectory, like `js/`, you'll need to modify the path in `ortoc-ajax-functions.php` as commented in that file).*

3.  **`ortoc-ajax-functions.php` and `functions.php`**
    *   This is the most critical part for making the AJAX functionality work.
    *   **Action A (Upload `ortoc-ajax-functions.php`):** Upload the `ortoc-ajax-functions.php` file to your theme's root directory: `wp-content/themes/your-active-theme-name/`.
    *   **Action B (Edit `functions.php`):**
        1.  In cPanel's File Manager, find your theme's `functions.php` file. This is located at `wp-content/themes/your-active-theme-name/functions.php`.
        2.  **Right-click** on `functions.php` and select **"Edit"** or **"Code Edit"**. (It's wise to make a backup of this file before editing, just in case).
        3.  Scroll to the very end of the `functions.php` file.
        4.  Add the following line of PHP code just before any closing `?>` tag (if one exists). If there's no closing `?>` tag, add it at the very end of the file:
            ```php
            require_once( get_template_directory() . '/ortoc-ajax-functions.php' );
            ```
        5.  Save the changes to `functions.php`.

    *   **Alternative for `functions.php` (Less Recommended for Organization):** Instead of Actions A and B above, you could copy the *entire content* of `ortoc-ajax-functions.php` and paste it directly into the end of your theme's `functions.php` file. However, using `require_once` keeps your `functions.php` cleaner.

### Step 3: How WordPress Links These Files

You don't need to manually link `ortoc-category-filter.js` in your HTML like a traditional script tag. WordPress handles it:

*   **`functions.php` tells WordPress about `ortoc-category-filter.js`:**
    The code you added to `functions.php` (via `ortoc-ajax-functions.php`) includes a function hooked to `wp_enqueue_scripts`. This function, `ortoc_enqueue_category_filter_script()`, tells WordPress:
    1.  "Here is a JavaScript file: `ortoc-category-filter.js`." (WordPress then generates the correct `<script>` tag in your site's HTML).
    2.  "When you load this script, also give it some data: the AJAX URL (`admin-ajax.php`) and a security key (nonce). Make this data available to the script via the `ortoc_filter_params` JavaScript object."

*   **JavaScript uses the data from `functions.php`:**
    Your `ortoc-category-filter.js` script then uses `ortoc_filter_params.ajax_url` to know where to send its AJAX requests and `ortoc_filter_params.nonce` to include the security key.

*   **`functions.php` listens for AJAX calls from the JavaScript:**
    The `add_action('wp_ajax_filter_products_by_category', ...)` and `add_action('wp_ajax_nopriv_filter_products_by_category', ...)` lines in `ortoc-ajax-functions.php` tell WordPress:
    1.  "If you receive an AJAX request specifically named `filter_products_by_category` (which our JavaScript is sending), then run the PHP function `ortoc_filter_products_by_category_handler()`."
    2.  This handler function then queries the products based on the category sent by the JavaScript, generates the HTML for those products, and sends it back to the JavaScript.

*   **JavaScript updates the page:**
    The `ortoc-category-filter.js` receives this HTML from the PHP handler and updates the product section on your page without a full reload.

### Step 4: Testing and Verification

1.  **Clear Caches:** After uploading and modifying files, clear all caches:
    *   Your browser cache.
    *   Any caching plugins you use on your WordPress site (e.g., LiteSpeed Cache, WP Rocket).
    *   Server-side caches if applicable (less common to need manual clearing for this type of change).
2.  Go to your shop page (`page-boutique-ortoc.php`) on your website.
3.  Open your browser's **Developer Tools** (usually by pressing F12) and switch to the **Console** tab and the **Network** tab. This will help you see if there are any errors or if the AJAX requests are working.
4.  Test the filter buttons as described in the "Testing and Refinement" step of our main plan.

## Troubleshooting Tips

*   **White Screen or Errors after editing `functions.php`?** This usually means a PHP syntax error. Restore `functions.php` from a backup or carefully check the code you added. Ensure you didn't miss a semicolon or have incorrect syntax.
*   **Filters not appearing or not working?**
    *   Double-check all file paths in cPanel.
    *   Ensure you added the `require_once` line correctly to `functions.php`.
    *   Check the browser console for JavaScript errors.
    *   Check the Network tab to see if `admin-ajax.php` calls are being made when you click a filter. Check their status and response.
*   **Path to `ortoc-category-filter.js`:** If you placed `ortoc-category-filter.js` in a subfolder (e.g., `your-theme/js/ortoc-category-filter.js`), you MUST update the path in `ortoc-ajax-functions.php`. Change:
    `get_template_directory_uri() . '/ortoc-category-filter.js'`
    to:
    `get_template_directory_uri() . '/js/ortoc-category-filter.js'`

Good luck!
