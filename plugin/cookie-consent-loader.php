<?php
/*
Plugin Name: Cookie Consent Loader
Description: Loads <a href="https://cookieconsent.orestbida.com/" target="_blank">Cookie Consent</a> by Orest Bida into Wordpress
Author: Zac Ariel
Version: 0.4
Compatibility: WordPress 5.7
*/

// Enqueue the JavaScript and CSS files
function cc_loader_enqueue_scripts() {
  // File URLs
  $css_file_url = plugin_dir_url(__FILE__) . 'assets/cookieconsent.css';
  $custom_css_file_url = plugin_dir_url(__FILE__) . 'assets/cookieconsent-custom.css';
  $js_file_url = plugin_dir_url(__FILE__) . 'assets/cookieconsent.umd.js';

  // Cookie Consent Version
  $js_file_path = plugin_dir_path(__FILE__) . 'assets/cookieconsent.umd.js';  
  if (file_exists($js_file_path) && filesize($js_file_path) > 10) {
	$cc_version = file($js_file_path)[1];
    $cc_version = preg_replace('/[^0-9.]/', '', $cc_version);
  } else {
    $cc_version = 'null';
  }

  // Enqueue the CSS file and add it to the head of the HTML
  wp_enqueue_style('cc-loader-css', $css_file_url, array(), $cc_version);

  // Check if there's anything in the custom CSS file - if not, don't enqueue it!
  if (filesize(plugin_dir_path( __FILE__ ) . 'assets/cookieconsent-custom.css') > 10) {
    wp_enqueue_style('cc-loader-custom-css', $custom_css_file_url, array(), $cc_version);
  }

  // Enqueue the JavaScript file and add it to the body of the HTML
  wp_enqueue_script('cc-loader-js', $js_file_url, array(), $cc_version, true);
}

// Set up the admin page
function cc_loader_admin_menu() {
  add_options_page(
    'Cookie Consent Loader',
    'Cookie Consent Loader',
    'manage_options',
    'cc-loader',
    'cc_loader_admin_page',
  );
}

function cc_loader_admin_page() {
  // Check if the user has the required capabilities
  if (!current_user_can('manage_options')) {
    return;
  }

  // Handle file saving
  if (isset($_POST['save_file'])) {
    $file = $_POST['file_path'];
    $content = $_POST['file_content'];
    $result = file_put_contents($file, $content);
    if ($result === false) {
      $message = 'Failed to save the file.';
    } else {
      $message = 'File saved successfully.';
    }
  }

  // Render the admin page
  ?>
  <div class="wrap">
    <h1>Cookie Consent Loader</h1>
    <?php if (isset($message)) echo '<div class="notice notice-info is-dismissible"><p>' . $message . '</p></div>'; ?>
    <div id="cc-loader">
      <div id="file-list">
        <p>You can use this (very) simple editing interface to update the Javascript and CSS files for CookieConsent, as well as any custom CSS you're using.</p>
        <p>Updated CookieConsent files can be obtained here:</p>
        <ul>
          <li><strong>Javascript:</strong> <a href="https://github.com/orestbida/cookieconsent/blob/master/dist/cookieconsent.umd.js" target="_blank">https://github.com/orestbida/cookieconsent/blob/master/dist/cookieconsent.umd.js</a></li>
          <li><strong>CSS:</strong> <a href="https://github.com/orestbida/cookieconsent/blob/master/dist/cookieconsent.css" target="_blank">https://github.com/orestbida/cookieconsent/blob/master/dist/cookieconsent.css</a></li>
        </ul>
        <p><strong>Please note:</strong> there is <em>no</em> syntax checking or error detection!</p>
        <h2>Files</h2>
        <?php
        $assets_dir = plugin_dir_path(__FILE__) . 'assets/';
        $files = array_diff(scandir($assets_dir), array('.', '..'));
        foreach ($files as $file) {
          echo '<a href="?page=cc-loader&file=' . urlencode($assets_dir . $file) . '">' . $file . '</a><br>';
        }
        ?>
      </div>
      <div id="editor">
        <h2>Editor</h2>
        <?php
        if (isset($_GET['file'])) {
          $file_path = urldecode($_GET['file']);
          $file_content = file_get_contents($file_path);
          echo '<form method="post" action="?page=cc-loader">';
          echo '<input type="hidden" name="file_path" value="' . $file_path . '">';
          echo '<textarea name="file_content" rows="20" cols="100" style="font-family: Monaco,Lucida Console,monospace;">' . $file_content . '</textarea>';
          echo '<br><input type="submit" name="save_file" value="Save" class="button button-primary">';
          echo '</form>';
        } else {
          echo 'No file selected.';
        }
        ?>
      </div>
    </div>
  </div>
  <?php
}

// Add everything
add_action('wp_enqueue_scripts', 'cc_loader_enqueue_scripts');
add_action('admin_menu', 'cc_loader_admin_menu');
