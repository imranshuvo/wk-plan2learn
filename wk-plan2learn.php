<?php 
/* 
Plugin Name: Plan2Learn for nlphuset.dk
Plugin URI: https://nlphuset.dk
Description: Integration of plan2learn api into nlphuset.dk
Version: 1.0
Author: Webkonsulenterne
Author URI: https://webkonsulenterne.dk
License: GPLv2
Text Domain: wk-plan2learn
*/

require_once('p2l/api.php');
require_once('p2l/helper.php');

add_action('init','wk_create_plan2learn');

function wk_create_plan2learn(){
    register_post_type('plan2learn',
		array(
			'labels' => array(
				'name' => __('P2l Kurser','wk-plan2learn'),
				'singular_name' => __('Plan2learn','wk-plan2learn'),
				'add_new' => __('Add New','wk-plan2learn'),
				'add_new_item' => __('Add New Plan2learn','wk-plan2learn'),
				'edit' => __('Edit','wk-plan2learn'),
				'edit_item' => __('Edit Plan2learn','wk-plan2learn'),
				'new_item' => __('New Plan2learn','wk-plan2learn'),
				'view' => __('View','wk-plan2learn'),
				'view_item' => __('View Plan2learn','wk-plan2learn'),
				'search_items' => __('Search Plan2learn','wk-plan2learn'),
				'not_found' => __('No Plan2learn found','wk-plan2learn'),
				'not_found_in_trash' => __('No Plan2learn found in Trash','wk-plan2learn'),
				'parent' => __('Parent Plan2learn','wk-plan2learn'),
			),
			'public' => true,
			'menu_position' => 5,
			'supports' => array('title','editor','excerpt'),
			'taxonomies' => array('p2l_categories'),
			'has_archive' => true,
			'rewrite' => array('slug' => 'p2lkurser','with_front' => false),
		)
	);
	//sessions taxonomy
	register_taxonomy('p2l_categories', ['plan2learn'], [
		'label' => __('Categories', 'wk-plan2learn'),
		'hierarchical' => true,
		'rewrite' => ['slug' => 'p2l-categories'],
		'show_admin_column' => true,
		'show_in_rest' => true,
		'labels' => [
			'singular_name' => __('Category', 'wk-plan2learn'),
			'all_items' => __('All Categories', 'wk-plan2learn'),
			'edit_item' => __('Edit Category', 'wk-plan2learn'),
			'view_item' => __('View Category', 'wk-plan2learn'),
			'update_item' => __('Update Category', 'wk-plan2learn'),
			'add_new_item' => __('Add New Category', 'wk-plan2learn'),
			'new_item_name' => __('New Category Name', 'wk-plan2learn'),
			'search_items' => __('Search Categories', 'wk-plan2learn'),
			'parent_item' => __('Parent Category', 'wk-plan2learn'),
			'parent_item_colon' => __('Parent Category:', 'wk-plan2learn'),
			'not_found' => __('No Category found', 'wk-plan2learn'),
		]
	]);
	register_taxonomy_for_object_type('p2l_categories', 'plan2learn');
}



//Adding the settings page 
add_action('admin_menu','wk_plan2learn_settings_page_menu');

function wk_plan2learn_settings_page_menu(){
    add_submenu_page(
    'edit.php?post_type=plan2learn',
    __('Plan2learn Setting','wk-plan2learn'),
    __('Settings','wk-plan2learn'),
    'manage_options',
    'plan2learn-settings',
    'plan2learn_settings_html'
    );
}


//Settings page layout 
function plan2learn_settings_html(){
   //check user capabilities
   if(!current_user_can('manage_options')){
       return;
   }
   
   //check if user have submitted the settings
   if( isset($_GET['settings-updated'])){
       //add settings saved message with class 'updated'
       add_settings_error('plan2learn_messages','plan2learn_message',__('Settings Saved!','wk-plan2learn'), 'updated');
   }
   
   //show error/update message
   settings_errors('plan2learn_messages');
   ?>
   <style>
       button#p2lbutton {
            width: 50%;
            margin-left: auto;
            margin-right: auto;
            float: none;
            display: block;
            color: white;
            background: #2271b1;
            font-size: 22px;
        }
        button#p2lbutton:hover {
            background: white;
            color: #2271b1;
        }
        .loader img {
            margin-left: auto;
            margin-right: auto;
            width: 90px;
            display: block;
            margin-bottom: 20px;
        }
   </style>
   <div class="wrap">
       <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
       <!-- <form action="options.php" method="post"> -->
       <!-- Loading icon -->
       <div class="loader" style="display: none;">
           <img src="<?php echo plugin_dir_url(__FILE__).'/assets/Spinner-1s-200px.svg'; ?>">
       </div>
       <div class="message" style="display: none;">
           <p style="margin: 5px 0 15px; background: white; border: 1px solid #c3c4c7; border-left-width: 4px;border-left-color: #00a32a; box-shadow: 0 1px 1px rgba(0,0,0,0.4);padding: 10px 12px;"></p>
        </div>
        <button type="submit" class="button" id="p2lbutton">Push Updates!</button>
       <!-- </form> -->
       <?php
        // $courses = getCourse(89687);
        
        // echo '<pre>';
        // print_r($courses);
        // echo '</pre>';
       ?>
   </div>
   
   
   
   <script>
       jQuery(document).ready(function($){
           $('#p2lbutton').on('click', function(){
              let loader = $('.loader');
              let button = $(this);
              let messageContainer = $('.message');
              
              $.ajax({
                  url: "<?php echo admin_url('admin-ajax.php'); ?>",
                  data: {
                      action: 'wk_get_plan2learn_courses',
                  },
                  beforeSend: function(){
                      //Loading icon
                      loader.show();
                      button.attr('disabled','disabled');
                  },
                  success: function(response){
                      if(response.error){
                          console.log('error happened!');
                          messageContainer.find('p').text('Something went wrong!');
                      }else{
                          //successfull
                          messageContainer.find('p').text('Updates pushed successfully!');
                      }
                      loader.hide();
                      button.attr('disabled',false);
                      
                      messageContainer.show();
                      
                      console.log(response);
                      
                      
                  },
                  error: function(response, status, error) {
                      console.log(response);
                      console.log(status);
                      console.log(error);
                      loader.hide();
                      button.attr('disabled',false);
                  }
              });
           });
       });
       
   </script>
   <?php 
}



//registering the custom template
add_filter('template_include','kh_plan2learn_template_func');

function kh_plan2learn_template_func($template_path) {
	if(get_post_type() == 'plan2learn'){
		if(is_single()){
			if($theme_file = locate_template( array('single-plan2learn.php'))){
				$template_path = $theme_file;
			}else{
				$template_path = plugin_dir_path(__FILE__).'/single-plan2learn.php';
			}
		}
	}

	return $template_path;
}

