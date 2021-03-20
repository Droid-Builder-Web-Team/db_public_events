<?php
/**
 * @package db_public_events
 * @version 0.0.1
 */
/*
Plugin Name: DB Public Events
Plugin URI: https://droidbuilders.uk
Description: Pull in any upcoming public events from the DB Portal
Author: Darren Poulson, Droid Builders Web Team
Version: 0.0.1
Author URI: https://droidbuilders.uk/
*/

add_shortcode('db_events', 'db_public_events');
add_action('admin_menu', 'db_public_events_settings_menu');

function db_public_events_settings_menu() {

	//create new top-level menu
    add_submenu_page(
        'options-general.php', // top level menu page
        'DB Public Events Settings', // title of the settings page
        'DB Public Events', // title of the submenu
        'manage_options', // capability of the user to see this page
        'db-public-events-settings-page', // slug of the settings page
        'db_public_events_settings_page' // callback function when rendering the page
        );

	//call register settings function
	add_action( 'admin_init', 'db_public_events_settings' );
}

function db_public_events_settings() {
	//register our settings
	register_setting( 'db-public-events-settings-group', 'db_public_events_site_url' );
	register_setting( 'db-public-events-settings-group', 'db_public_events_key' );
}

function db_public_events() {
  $output = "";
  $year = "";
  $url = esc_attr(get_option('db_public_events_site_url', ''));
  $key = esc_attr(get_option('db_public_events_key', ''));
  $events = json_decode(file_get_contents($url.'?api='.$key), true);
  usort($events, function ($a, $b) {
    return $a['date'] <=> $b['date'];
});
  $output .= "<table padding=2 border=1><tr><th>Date</th><th>Name</th><th>Location</th><th>Link</th></tr>";
  foreach($events as $event)
  {
      $tmp = "";
      $date = DateTime::createFromFormat('Y-m-d', $event['date']);
      $current_year = $date->format('Y');
      if ($current_year != $year) {
        $year = $current_year;
        $tmp = "<tr><th colspan=4>".$year."</th></td>";
      }
      $tmp .= "<tr>";
      $tmp .= "<td>".$date->format('d M')."</td>";
      $tmp .= "<td>".$event['name']."</td>";
      $tmp .= "<td>".$event['location']['name'];
      $tmp .= "<br>".$event['location']['town'];
      $tmp .= "<br>".$event['location']['postcode'];
      $tmp .= "</td>";
      if ($event['url'] != "")
        $tmp .= "<td><a target='_blank' href='".$event['url']."'>".link."</a></td>";
      else
        $tmp .= "<td></td>";
      $tmp .= "</tr>";
      $output .= $tmp;
  }
  $output .= "</table>";
  return $output;

}

function db_public_events_settings_page() {
?>

<div class="wrap">
<h1>DB Public Events Settings</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'db-public-events-settings-group' ); ?>
    <?php do_settings_sections( 'db-public-events-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Site URL</th>
        <td><input type="text" name="db_public_events_site_url" value="<?php echo esc_attr( get_option('db_public_events_site_url') ); ?>" /></td>
        </tr>

        <tr valign="top">
        <th scope="row">API Key</th>
        <td><input type="text" name="db_public_events_key" value="<?php echo esc_attr( get_option('db_public_events_key') ); ?>" /></td>
        </tr>

    </table>

    <?php submit_button(); ?>

</form>
</div>
<?php
}
