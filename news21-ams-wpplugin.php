<?php
/*
Plugin Name:News21 AMS-2-WordPress
Plugin URI: http://github.com/news21-ams-wpplugin
Description: Allows WordPress to easily integrate with News21 AMS.
Version: 0.1
Author: bhalle
Author URI: http://news21.com
*/

define ('N21WP_PATH', 'news21-ams-wpplugin');
define ('N21WP_PAGENAME_STORIES', 'n21-top-level-handle');
define ('N21WP_PAGENAME_TAGS', 'sub-page');
define ('N21WP_PAGENAME_STORYFILTERS', 'sub-page2');
define ('N21WP_PAGENAME_API', 'sub-page3');


// Hook for adding admin menus
add_action('admin_menu', 'news21_add_pages');


// action function for above hook
function news21_add_pages() {
    // Add a new submenu under Options:
    add_options_page('N21 Options', 'News21 AMS', 'administrator', 'testoptions', 'news21_available_stories_page');

    // Add a new top-level menu (ill-advised):
    $icon = WP_PLUGIN_URL.'/'.$fwp_path.'/'.N21WP_PATH.'/news21-ams2wp-tiny.png';
    add_menu_page('Test Toplevel', 'News21 AMS', 'administrator', N21WP_PAGENAME_STORIES, 'news21_available_stories_page',$icon);

    // Add a submenu to the custom top-level menu:
    add_submenu_page('n21-top-level-handle', 'Import News21 Categories to Wordpress Tags', 'Import Tags', 'administrator', 'sub-page', 'news21_manage_categories');

    // Add a submenu to the custom top-level menu:
    add_submenu_page('n21-top-level-handle', 'Pull Stories from these Newsrooms', 'Newsroom Filter', 'administrator', 'sub-page2', 'news21_manage_storyfilters');

    // Add a second submenu to the custom top-level menu:
    add_submenu_page('n21-top-level-handle', 'News21 AMS API Settings', 'API Settings', 'administrator', 'sub-page3', 'news21_options_page');
}




function news21_available_stories_page() {
	echo '<div class="wrap">';
	echo "<h2>" . __( 'Stories Available From News21 AMS', 'news21_trans_domain' ) . "</h2>";
?>
	<div>Results Filtered to include these Newsrooms: </div>
	</div>
<?php
 
}




function news21_manage_categories() {

    // variables for the field and option names 
    $hidden_field_name = 'news21_submit_hidden';

	$opt_name_api_key = 'news21_api_key';
    $opt_name_ams_uri = 'news21_ams_uri';

    $opt_val_ams_uri = get_option( $opt_name_ams_uri );
    $opt_val_api_key = get_option( $opt_name_api_key );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val = $_POST[ $data_field_name ];

        // Save the posted value in the database
        update_option( $opt_name, $opt_val );

        // Put an options updated message on the screen

?>
<div class="updated"><p><strong><?php _e('Filters saved.', 'news21_trans_domain' ); ?></strong></p></div>

<?php
	
    }
	
	$tags = get_tags();
	$tagsarray = array();
	if ($tags) {
		foreach($tags as $tag) {
			array_push($tagsarray,$tag->slug);
		}
	}
	
    echo '<div class="wrap">';
    echo "<h2>" . __( 'Import News21 Categories to Wordpress Tags', 'news21_trans_domain' ) . "</h2>";

	echo'<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script type="text/javascript" src="'.WP_PLUGIN_URL.'/'.N21WP_PATH.'/json2.js"></script>	
	<script type="text/javascript">
			$(document).ready(function() {
				var jsontags = '.json_encode($tagsarray).';
				var btagused;
				var lookup_uri = "'.$opt_val_ams_uri.''.$opt_val_api_key.'/categories/json/?callback=?";				
				$.getJSON(lookup_uri, 
				function(data){
					$.each(data.categories, function(i,item){
						btagused = false;
						$.each(jsontags, function(index, value) {
							if(item.id.toLowerCase() == value){
								btagused = true;
							}
						});
						if(btagused == false){
							$("<div/>").html(\'<span style=\"font-weight:bold;\">\'+item.id.toLowerCase()+\'</span> [Add to Tags]\').appendTo("#cat_list");
						} else {
							$("<div/>").html(\'<span style=\"text-decoration: line-through;color:gray;\">\'+item.id.toLowerCase()+\'</span> \').appendTo("#cat_list");
						}
					});
				});
			});
	</script><div id="cat_list"><h3>N21 Categories</h3><span style="text-decoration: line-through;color:gray;">tag</span> = Already Being Used<br/><br/></div>';
    
	echo '</div>'; 
	
}




function news21_manage_storyfilters() {
	$hidden_field_name = 'news21_submit_hidden';
	
	$opt_name_api_key = 'news21_api_key';
    $data_form_api_key = 'news21_api_key';

    $opt_name_ams_uri = 'news21_ams_uri';
    $data_form_ams_uri = 'news21_ams_uri';

    $opt_name_newsroom_filter = 'news21_newsroom_filter';
    $data_form_newsroom_filter = 'news21_newsroom_filter';

    // Read in existing option value from database
    $opt_val_ams_uri = get_option( $opt_name_ams_uri );
    $opt_val_api_key = get_option( $opt_name_api_key );
    $opt_val_newsroom_filter = get_option( $opt_name_newsroom_filter );
	
	if( $_POST[ $hidden_field_name ] == 'Y' ) {
		
        $opt_val_newsroom_filter = $_POST[ $data_form_newsroom_filter ];
		
        // Save the posted value in the database
        update_option( $opt_name_newsroom_filter, $opt_val_newsroom_filter );
?>
	<div class="updated"><p><strong><?php _e('Newsrooms saved. ', 'news21_trans_domain' ); ?></strong></p></div>

<?php
    }

	echo '<div class="wrap">';
	echo "<h2>" . __( 'Pull Stories Tagged with these Newsrooms', 'news21_trans_domain' ) . "</h2>";
	
	echo'<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
	<script type="text/javascript" src="'.WP_PLUGIN_URL.'/'.N21WP_PATH.'/json2.js"></script>	
	<script type="text/javascript">
			$(document).ready(function() {
				var jsontags = "'.$opt_val_newsroom_filter.'";
				var btagused;
				var lookup_uri = "'.$opt_val_ams_uri.''.$opt_val_api_key.'/newsrooms/json/?callback=?";

				$.getJSON(lookup_uri, 
				function(data){
					$.each(data.newsrooms, function(i,item){
						btagused = (jsontags.indexOf(item.id.toLowerCase()) != -1)?"checked":"";
						
						$("<div/>").html(\'<li><input type="checkbox" \'+btagused+\' name="newsrooms" value="\'+item.id.toLowerCase()+\'" id="newsroom_\'+item.id.toLowerCase()+\'" /> <label for="newsroom_\'+item.id.toLowerCase()+\'">\'+item.id.toLowerCase()+\'</label></li>\').appendTo("#newsroom_list");
					});
				});
				
				$("#chkall").click(function(){
					$(":checkbox").attr("checked", true);
				});
				
				$("#unchkall").click(function(){
					$(":checkbox").attr("checked", false);
				});
				
				$("#submitfilters").click(function() {			
					var fields = $(":checkbox").serializeArray();
					var delim = "";
					var nlist = "";
					$.each(fields, function(i, field){
						nlist = nlist+delim+field.value;
						delim = ",";
					});
					$("#'.$data_form_newsroom_filter.'").val(nlist);
					$("#filterform").submit()
				});
				
			});
	</script><div id="cat_list"><h3>N21 Newsroom Tags</h3></div>';
	 
	
?>
	<form id="filterform" method="post" action="admin.php?page=<?php echo N21WP_PAGENAME_STORYFILTERS ?>">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		<input type="hidden" name="<?php echo $data_form_newsroom_filter; ?>" id="<?php echo $data_form_newsroom_filter; ?>">
		<?php _e("Only show stories tagged with these newsrooms:", 'news21_trans_domain' ); ?> 
		<ul id="newsroom_list" style="padding-left:10px;">
		<li><a href="#" id="chkall">Check All</a>  <a href="#" id="unchkall">Uncheck All</a></li>
		</ul>
		<hr />
		<p class="submit">
		<input type="button" id="submitfilters" name="Submit" value="<?php _e('Update Newsrooms', 'news21_trans_domain' ) ?>" />
		</p>
	</form>
</div>

<?php
 
}




function news21_options_page() {
    // variables for the field and option names 
    $hidden_field_name = 'news21_submit_hidden';

	$opt_name_api_key = 'news21_api_key';
    $data_form_api_key = 'news21_api_key';

    $opt_name_ams_uri = 'news21_ams_uri';
    $data_form_ams_uri = 'news21_ams_uri';

    // Read in existing option value from database
    $opt_val_ams_uri = get_option( $opt_name_ams_uri );
    $opt_val_api_key = get_option( $opt_name_api_key );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $opt_val_api_key = $_POST[ $data_form_api_key ];
        $opt_val_ams_uri = $_POST[ $data_form_ams_uri ];

        // Save the posted value in the database
        update_option( $opt_name_api_key, $opt_val_api_key );
        update_option( $opt_name_ams_uri, $opt_val_ams_uri );

        // Put an options updated message on the screen

?>
<div class="updated"><p><strong><?php _e('Settings saved.', 'news21_trans_domain' ); ?></strong></p></div>
<?php
    }
    // Now display the options editing screen
    echo '<div class="wrap">';

    // header
    echo "<h2>" . __( 'News21 AMS API Settings', 'news21_trans_domain' ) . "</h2>";

    // options form
    ?>
	<form method="post" action="admin.php?page=<?php echo N21WP_PAGENAME_API ?>">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		<p><?php _e("Your API Key:", 'news21_trans_domain' ); ?> 
		<input type="text" name="<?php echo $data_form_api_key; ?>" value="<?php echo $opt_val_api_key; ?>" size="30">
		</p>
		<p><?php _e("AMS URI:", 'news21_trans_domain' ); ?> 
		<input type="text" name="<?php echo $data_form_ams_uri; ?>" value="<?php echo $opt_val_ams_uri; ?>" size="50">
		</p>
		<hr />
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Update Settings', 'news21_trans_domain' ) ?>" />
		</p>
	</form>
</div>

<?php
 
}

?>