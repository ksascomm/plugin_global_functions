<?php
/*
Plugin Name: KSAS Global Functions
Plugin URI: http://krieger.jhu.edu/news/communications/web/plugins/global
Description: This plugin should be network activated.  Provides functions for creating the Academic Department and Affiliation Taxonomies, formats meta boxes, provides upload capability, change "Posts" labels to "News", removes unnecessary classes from navigation menus, removes unwanted widgets, responsive images (remove width/height attributes) function, add custom post type capabilities for user roles. and global security needs.
Version: 2.0
Author: Cara Peckens
Author URI: mailto:ksasweb@jhu.edu
License: GPL2
*/

/*****************TABLE OF CONTENTS***************
	1.0 Security and Performance Functions
		1.1 Prevent Login Errors
		1.2 Block Malicious Queries
		1.3 Remove Junk from Head
		1.4 Disable WP REST API requests for logged out users
	2.0 Taxonomies
		2.1 academicdepartment Taxonomy
		2.2 academicdepartment Terms
		2.3 affiliation Taxonomy
		2.4 affiliation Terms
	3.0 Custom Post Type UI Functions *NOTE - Check these when Easy Content Types plugin is updated
	4.0 Responsive Images
	5.0 Remove Unwanted Widgets
		5.1 Eliminate Auto-Linking of Image
	6.0 Change Posts Labels to News
	7.0 User Capabilities for Custom Post Types
	8.0 Theme Functions
		8.1 Pagination
		8.2 Return the slug
		8.3 Subpage of conditional statement
		8.4 Get page id from page slug
		8.5 In taxonomy conditional statement
		8.6 Limit words, can be used in templates on both the_excerpt and the_content or any content call for that matter
		8.7 Obfuscate Email address email_munge($string);
		8.8 Create Title for <head> section
		8.9 Return the Parent's Title - Used with courses
	9.0 Navigation and Menus
		9.1 Walker class for foundation
		9.2 Walker class for mobile dropdowns
		9.3 Remove CSS classes from menu
		9.4 Walker class for breadcrumbs
		9.5 Walker class for tertiary
		9.6 Walker class to add page IDs as classes
	10.0 Global Shortcodes
		10.1 Custom Menu
		10.2 Quicksearch Form Shortcode - For use with accordions
	11.0 Add Mime Types for LaTex files
	12.0 WYSIWYG Mods
		12.1 Add sub and sup buttons
		12.2 Keep html attributes
	13.0 Login Screen
	14.0 Toolbar Changes
	15.0 Disable PDF Previews
/*****************1.0 SECURITY AND PERFORMANCE FUNCTIONS*****************************/

	// 1.3 remove junk from head
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wp_generator');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		remove_action('wp_head', 'feed_links', 2);
		remove_action('wp_head', 'index_rel_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'feed_links_extra', 3);
		remove_action('wp_head', 'start_post_rel_link', 10, 0);
		remove_action('wp_head', 'parent_post_rel_link', 10, 0);
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_styles', 'print_emoji_styles');
	
		//remove version info from head and feeds
		    function complete_version_removal() {
		    	return '';
		    }
		    add_filter('the_generator', 'complete_version_removal');

/*****************2.0 TAXONOMIES*****************************/


/*****************3.0 CUSTOM POST TYPE UI FUNCTIONS*****************************/
//**NOTE** Check these functions when the Easy Content Types Plugin is updated 
//**NOTE** YOU NEED THESE FOR CPT UPLOADS TO WORK
	function ecpt_export_ui_scripts() {
	
		global $ecpt_options;
	?> 
	<script type="text/javascript">
			jQuery(document).ready(function($)
			{
				
				if($('.form-table .ecpt_upload_field').length > 0 ) {
					// Media Uploader
					window.formfield = '';
	
					$('.ecpt_upload_image_button').live('click', function() {
						var send_attachment_bkp = wp.media.editor.send.attachment;
						var button = $(this);
	
	        wp.media.editor.send.attachment = function(props, attachment) {
	
	            $(button).prev().prev().attr('src', attachment.url);
	            $(button).prev().val(attachment.url);
	
	            wp.media.editor.send.attachment = send_attachment_bkp;
	        }
	
	        wp.media.editor.open(button);
	
	        return false;       
	    });
						window.original_send_to_editor = window.send_to_editor;
						window.send_to_editor = function(html) {
							if (window.formfield) {
								imgurl = $('a','<div>'+html+'</div>').attr('href');
								window.formfield.val(imgurl);
								tb_remove();
							}
							else {
								window.original_send_to_editor(html);
							}
							window.formfield = '';
							window.imagefield = false;
						}
				}			
				// add new repeatable field
				$(".ecpt_add_new_field").on('click', function() {					
					var field = $(this).closest('td').find("div.ecpt_repeatable_wrapper:last").clone(true);
					var fieldLocation = $(this).closest('td').find('div.ecpt_repeatable_wrapper:last');
					// get the hidden field that has the name value
					var name_field = $("input.ecpt_repeatable_field_name", ".ecpt_field_type_repeatable:first");
					// set the base of the new field name
					var name = $(name_field).attr("id");
					// set the new field val to blank
					$('input', field).val("");
					
					// set up a count var
					var count = 0;
					$('.ecpt_repeatable_field').each(function() {
						count = count + 1;
					});
					name = name + '[' + count + ']';
					$('input', field).attr("name", name);
					$('input', field).attr("id", name);
					field.insertAfter(fieldLocation, $(this).closest('td'));
	
					return false;
				});		
	
				// add new repeatable upload field
				$(".ecpt_add_new_upload_field").on('click', function() {	
					var container = $(this).closest('tr');
					var field = $(this).closest('td').find("div.ecpt_repeatable_upload_wrapper:last").clone(true);
					var fieldLocation = $(this).closest('td').find('div.ecpt_repeatable_upload_wrapper:last');
					// get the hidden field that has the name value
					var name_field = $("input.ecpt_repeatable_upload_field_name", container);
					// set the base of the new field name
					var name = $(name_field).attr("id");
					// set the new field val to blank
					$('input[type="text"]', field).val("");
					
					// set up a count var
					var count = 0;
					$('.ecpt_repeatable_upload_field', container).each(function() {
						count = count + 1;
					});
					name = name + '[' + count + ']';
					$('input', field).attr("name", name);
					$('input', field).attr("id", name);
					field.insertAfter(fieldLocation, $(this).closest('td'));
	
					return false;
				});
				
				// remove repeatable field
				$('.ecpt_remove_repeatable').on('click', function(e) {
					e.preventDefault();
					var field = $(this).parent();
					$('input', field).val("");
					field.remove();				
					return false;
				});											
														
			});
	  </script>
	<?php
	}
if ((isset($_GET['post']) && (isset($_GET['action']) && $_GET['action'] == 'edit') ) || (strstr($_SERVER['REQUEST_URI'], 'wp-admin/post-new.php'))) {
	add_action('admin_head', 'ecpt_export_ui_scripts');
}

/*****************4.0 RESPONSIVE IMAGES - REMOVES WIDTH/HEIGHT ATTRIBUTES FROM IMAGE INSERT*****************************/
function remove_image_size_attributes( $html ) {
    return preg_replace( '/(width|height)="\d*"/', '', $html );
}
 
// Remove image size attributes from post thumbnails
add_filter( 'post_thumbnail_html', 'remove_image_size_attributes' );
 
// Remove image size attributes from images added to a WordPress post
add_filter( 'image_send_to_editor', 'remove_image_size_attributes' );

/*****************5.0 REMOVE UNWANTED WIDGETS*****************************/

	function unregister_default_wp_widgets() {
		unregister_widget('WP_Widget_Pages');
		unregister_widget('WP_Widget_Calendar');
		unregister_widget('WP_Widget_Archives');
		unregister_widget('WP_Widget_Meta');
		unregister_widget('WP_Widget_Categories');
		unregister_widget('WP_Widget_Recent_Comments');
		unregister_widget('WP_Widget_RSS');
		unregister_widget('WP_Widget_Tag_Cloud');
	}
	add_action('widgets_init', 'unregister_default_wp_widgets', 1);

	//***5.1 Eliminate Auto-Linking of Image
	//***http://wordpress.org/support/topic/insert-image-default-to-no-link
 
update_option('image_default_link_type','none');

/*****************6.0 CHANGE POSTS LABELS TO NEWS*****************************/
	function change_post_menu_label() {
		global $menu;
		global $submenu;
		$menu[5][0] = 'News';
		$submenu['edit.php'][5][0] = 'News';
		$submenu['edit.php'][10][0] = 'Add News';
		$submenu['edit.php'][16][0] = 'News Tags';
		echo '';
	}
	function change_post_object_label() {
		global $wp_post_types;
		$labels = &$wp_post_types['post']->labels;
		$labels->name = 'News';
		$labels->singular_name = 'News';
		$labels->add_new = 'Add News';
		$labels->add_new_item = 'Add News';
		$labels->edit_item = 'Edit News';
		$labels->new_item = 'News';
		$labels->view_item = 'View News';
		$labels->search_items = 'Search News';
		$labels->not_found = 'No News found';
		$labels->not_found_in_trash = 'No News found in Trash';
	}
	add_action( 'init', 'change_post_object_label' );
	add_action( 'admin_menu', 'change_post_menu_label' );

/*****************7.0 USER - ADD CUSTOM POST TYPE CAPABILITIES*****************************/

/*****************8.0 THEME FUNCTIONS*****************************/

	//***8.0 NEW Pagination (2017) ************/

	function new_flagship_paging_nav() {
		// Don't print empty markup if there's only one page.
		if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
			return;
		}

		$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
		$pagenum_link = html_entity_decode( get_pagenum_link() );
		$query_args   = array();
		$url_parts    = explode( '?', $pagenum_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}

		$pagenum_link = remove_query_arg( array_keys( $query_args ), $pagenum_link );
		$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

		$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
		$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

		// Set up paginated links.
		$links = paginate_links( array(
			'base'     => $pagenum_link,
			'format'   => $format,
			'total'    => $GLOBALS['wp_query']->max_num_pages,
			'current'  => $paged,
			'mid_size' => 3,
			'add_args' => array_map( 'urlencode', $query_args ),
			'prev_text' => __( '&larr; Previous', 'new_flagship' ),
			'next_text' => __( 'Next &rarr;', 'new_flagship' ),
			'type'      => 'list',
		) );

		if ( $links ) :

		?>
		<nav class="navigation paging-navigation" role="navigation">
			<h1 class="screen-reader-text"><?php _e( 'Posts navigation', 'new_flagship' ); ?></h1>
				<?php echo $links; ?>
		</nav><!-- .navigation -->
		<?php
		endif;
	}

	//***8.1 Pagination
		function flagship_pagination($pages = '', $range = 2)
		{  
		     $showitems = ($range * 2)+1;  
		
		     global $paged;
		     if(empty($paged)) $paged = 1;
		
		     if($pages == '')
		     {
		         global $wp_query;
		         $pages = $wp_query->max_num_pages;
		         if(!$pages)
		         {
		             $pages = 1;
		         }
		     }   
		
		     if(1 != $pages)
		     {
		         echo "<div class='pagination three columns centered mobile-four'>";
		         if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a href='".get_pagenum_link(1)."'>&laquo;</a>";
		         if($paged > 1 && $showitems < $pages) echo "<a href='".get_pagenum_link($paged - 1)."'>&lsaquo;</a>";
		
		         for ($i=1; $i <= $pages; $i++)
		         {
		             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
		             {
		                 echo ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>";
		             }
		         }
		
		         if ($paged < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($paged + 1)."'>&rsaquo;</a>";  
		         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a href='".get_pagenum_link($pages)."'>&raquo;</a>";
		         echo "</div>\n";
		     }
		}

	//***8.2 to generate Slug as a class - useful for background images
		function the_slug() {
		    $post_data = get_post($post->ID, ARRAY_A);
		    $slug = $post_data['post_name'];
		    return $slug; 
		}
	
	
	//***8.3 add is subpage of conditional statement
		function ksas_is_subpage_of( $parentpage = '' ) {
			$posts = $GLOBALS['posts'];
			if ( is_numeric($parentpage) ) {
				if ( $parentpage == $posts[0]->post_parent ) {
					return true;
				} else {
					is_subpage_of( $posts[0]->post_parent );
				}
			} else {
				return false;
			}
		}
		
	//***8.4 Get page ID from page slug - Used in scripts-initiators.php
		function ksas_get_page_id($page_name){
			global $wpdb;
			$page_name = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = '".$page_name."'");
			return $page_name;
		}
		
	//***8.5 Conditional function to check if post belongs to term in a custom taxonomy. 
		function ksas_in_taxonomy($tax, $term, $_post = NULL) {
			// if neither tax nor term are specified, return false
			if ( !$tax || !$term ) { return FALSE; }
			// if post parameter is given, get it, otherwise use $GLOBALS to get post
			if ( $_post ) {
			$_post = get_post( $_post );
			} else {
			$_post =& $GLOBALS['post'];
			}
			// if no post return false
			if ( !$_post ) { return FALSE; }
			// check whether post matches term belongin to tax
			$return = is_object_in_term( $_post->ID, $tax, $term );
			// if error returned, then return false
			if ( is_wp_error( $return ) ) { return FALSE; }
		return $return;
		}
		
	//***8.6 Limit Words 
		function limit_words($string, $word_limit) {

			// creates an array of words from $string (this will be our excerpt)
			// explode divides the excerpt up by using a space character

			$words = explode(' ', $string);
			return implode(' ', array_slice($words, 0, $word_limit));
		}
	//***8.7 Obfuscate Email Address
		function email_munge($string) {
			$ascii_string = '';
			foreach (str_split($string) as $char) 
			{ 
				$ascii_string .= '&#' . ord($char) . ';'; 
			}
			return $ascii_string;
		}
	
	//***8.8 Create Title for <head> section
		function create_page_title() {
			if ( is_front_page() )  { 
				$page_title = bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
			} 
			elseif ( is_home() ) { // blog page
				$page_title = single_post_title();
				$page_title .= print(' | ');
				$page_title .= print(' '); 
				$page_title .= bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
			} 
			elseif ( is_category() ) { 
				$page_title = single_cat_title();
				$page_title .= print(' | ');
				$page_title .= print(' '); 
				$page_title .= bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
			}
			elseif ( is_archive() ) { 
				$page_title = the_archive_title();
				$page_title .= print(' | ');
				$page_title .= print(' '); 
				$page_title .= bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
			}			
			elseif (is_single() ) { 
				$page_title = single_post_title(); 
				$page_title .= print(' | ');
				$page_title .= print(' '); 
				$page_title .= bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
			}
			elseif (is_page() ) { 
				$page_title = single_post_title();
				$page_title .= print(' | ');
				$page_title .= print(' '); 
				$page_title .= bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
			}
			elseif (is_404()) {
				$page_title = print('Page Not Found'); 
				$page_title .= print(' | ');
				$page_title .= print(' '); 
				$page_title .= bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
			}
			elseif (is_tax('bbtype')) {
				$page_title = single_tag_title();
				$page_title .= print(' | ');
				$page_title .= print(' '); 
				$page_title .= bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
			}
			else { 
				$page_title .= print(' '); 
				$page_title .= bloginfo('name');
				$page_title .= print(' | Johns Hopkins University'); 
				} 
			return $page_title;
		}

		/**
		 * Filter the page title.
		 *
		 * Creates a nicely formatted and more specific title element text
		 * for output in head of document, based on current view.
		 *
		 * @since Twenty Twelve 1.0
		 *
		 * @param string $title Default title text for current view.
		 * @param string $sep Optional separator.
		 * @return string Filtered title.
		 */
		function twentytwelve_wp_title( $title, $sep ) {
			global $paged, $page;

			if ( is_feed() )
				return $title;

			// Add the site name.
			$title .= get_bloginfo( 'name' ) . ' ' . $sep . ' Johns Hopkins University';

			// Add a page number if necessary.
			if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() )
				$title = "$title $sep " . sprintf( __( 'Page %s', 'twentytwelve' ), max( $paged, $page ) );

			return $title;
		}
		add_filter( 'wp_title', 'twentytwelve_wp_title', 10, 2 );
		
		
	//***8.9 Return the Parent's Title - Used with SIS courses API
		function the_parent_title() {
		  global $post;
		  if($post->post_parent == 0) return '';
		  $post_data = get_post($post->post_parent);
		  return $post_data->post_title;
		  }
/*******************9.0 NAVIGATION & MENU FUNCTIONS & HELPERS******************/

	//***9.1 Menu Walker to add Foundation CSS classes
		class foundation_navigation extends Walker_Nav_Menu
		{
		      function start_el(&$output, $item, $depth = 0, $args = array(), $current_object_id = 0)
		      {
					global $wp_query;
					$indent = ( $depth ) ? str_repeat( "", $depth ) : '';
					
					$class_names = $value = '';
					
					// If the item has children, add the dropdown class for bootstrap
					if ( $args->has_children ) {
						$class_names = "has-flyout ";
					}
					$classes = empty( $item->classes ) ? array() : (array) $item->classes;
					
					$class_names .= join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
					$class_names = ' class="'. esc_attr( $class_names ) . ' page-id-' . esc_attr( $item->object_id ) .'" ';
		           
					$output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'role="menuitem">';
		           
		
		           	$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		           	$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		           	$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		           	$attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		           	// if the item has children add these two attributes to the anchor tag
		           	if ( $args->has_children ) {
						$attributes .= ' aria-haspopup="true" data-toggle="dropdown" ';
					}
		
		            $item_output = $args->before;
		            $item_output .= '<a' . $attributes . ' aria-label="'. $item->title .' Page-' . $item->ID . '">';
		            $item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );
		            $item_output .= $args->link_after;
		            $item_output .= '</a>';
		            $item_output .= $args->after;
		
		            $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		            }
		            
		function start_lvl(&$output, $depth = 0, $args = array()) {
			$output .= "\n<ul class=\"flyout up\" aria-hidden=\"true\" aria-label=\"submenu\">\n";
		}
		            
		      	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output )
		      	    {
		      	        $id_field = $this->db_fields['id'];
		      	        if ( is_object( $args[0] ) ) {
		      	            $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
		      	        }
		      	        return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		      	    }
		      	
		            
		}
		
		// Add a class to the wp_page_menu fallback
		function foundation_page_menu_class($ulclass) {
			return preg_replace('/<ul>/', '<ul class="nav-bar" role="navigation">', $ulclass, 1);
		}
		
		add_filter('wp_page_menu','foundation_page_menu_class');


	//***9.2 Menu Walker to create a dropdown menu for mobile devices	
		class mobile_select_menu extends Walker_Nav_Menu{
		    function start_lvl(&$output, $depth = 0, $args = array()){
		      $indent = str_repeat("\t", $depth); // don't output children opening tag (`<ul>`)
		    }
		
		    function end_lvl(&$output, $depth = 0, $args = array()){
		      $indent = str_repeat("\t", $depth); // don't output children closing tag
		    }
		
		    function start_el(&$output, $item, $depth = 0, $args = array(), $current_object_id = 0){
		      // add spacing to the title based on the depth
		      $item->title = str_repeat("&nbsp;", $depth * 4).$item->title;
	
				//deleted '&' on $output; TG 8-13-2014
		      parent::start_el($output, $item, $depth, $args);
		
		      // no point redefining this method too, we just replace the li tag...
		      $output = str_replace('<li', '<option value="'. esc_attr( $item->url        ) .'"', $output);
		    }
		
		    function end_el(&$output, $item, $depth = 0, $args= array(), $current_object_id = 0){
		      $output .= "</option>\n"; // replace closing </li> with the option tag
		    }
		}

	//***9.3 remove unneccessary classes for navigation menus
		function ksasaca_css_attributes_filter($var) {
			 $newnavclasses = is_array($var) ? array_intersect($var, array(
	                'current_page_item',
	                'current_page_parent',
	                'current_page_ancestor',
	                'first',
	                'last',
	                'vertical',
	                'horizontal',
	                'children',
	                'logo',
	                'external',
	                'hide',
	                'hide-for-small',
	                'show-for-small',
	                'purple',
	                'green',
	                'yellow',
	                'blue',
	                'orange',
	                'home',
	                'exclude'
			 )) : '';
			 return $newnavclasses;
		}
		add_filter('nav_menu_css_class', 'ksasaca_css_attributes_filter', 100, 1);
		add_filter('page_css_class', 'ksasaca_css_attributes_filter', 100, 1);
		
	//***9.4 Menu Walker for breadcrumbs
		class flagship_bread_crumb extends Walker{
		    var $tree_type = array( 'post_type', 'taxonomy', 'custom' );
		    var $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );
		    var $delimiter = '';
		    function start_el(&$output, $item, $depth = 0, $args = array(), $current_object_id = 0) {
		
		        //Check if menu item is an ancestor of the current page
		        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
		        $current_identifiers = array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor' ); 
		        $ancestor_of_current = array_intersect( $current_identifiers, $classes );     
		
		
		        if( $ancestor_of_current ){
		            $title = apply_filters( 'the_title', $item->title, $item->ID );
		
		            //Preceed with delimter for all but the first item.
		            if( 0 != $depth )
		                $output .= $this->delimiter;
		
		            //Link tag attributes
		            $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		            $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		            $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		            $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		
		            //Add to the HTML output
		            $output .= '<li role="menuitem"><a'. $attributes .'>'.$title.'</a></li>';
		        }
		    }
		}
		
	//***9.5 Menu Walker for Tertiary links
		add_filter( 'wp_nav_menu_objects', 'submenu_limit', 10, 2 );

		function submenu_limit( $items, $args ) {

		    if ( empty($args->submenu) )
		        return $items;

		    $filter_object_list = wp_filter_object_list( $items, array( 'title' => $args->submenu ), 'and', 'ID' );
		    $parent_id = array_pop( $filter_object_list );
		    $children  = submenu_get_children_ids( $parent_id, $items );

		    foreach ( $items as $key => $item ) {

		        if ( ! in_array( $item->ID, $children ) )
		            unset($items[$key]);
		    }

		    return $items;
		}

		function submenu_get_children_ids( $id, $items ) {

		    $ids = wp_filter_object_list( $items, array( 'menu_item_parent' => $id ), 'and', 'ID' );

		    foreach ( $ids as $id ) {

		        $ids = array_merge( $ids, submenu_get_children_ids( $id, $items ) );
		    }

		    return $ids;
		}

	//***9.6 Menu Walker to add page IDs as classes
		class page_id_classes extends Walker_Nav_Menu{
	        /**
	         *      Walker object, appends page id to data-url attribute on link
	         */
	        function start_el(&$output, $item, $depth = 0, $args = array(), $current_object_id = 0) {
	                
	           global $wp_query;
	                
	           $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	
	           $class_names = $value = '';
	
					$classes = empty( $item->classes ) ? array() : (array) $item->classes;
					
					$class_names .= join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
					$class_names = ' class="'. esc_attr( $class_names ) . ' page-id-' . esc_attr( $item->object_id ) .'"';
		           
					$output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .' role="menuitem">';
	
	           $attributes  = ! empty( $item->attr_title )   ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
	           $attributes .= ! empty( $item->target ) ? ' target="'  . esc_attr( $item>-target     ) .'"' : '';
	           $attributes .= ! empty( $item->xfn )  ? ' rel="' . esc_attr( $item->xfn        ) .'"' : '';
	           $attributes .= ! empty( $item->url ) ? ' href="'  . esc_attr( $item->url        ) .'"' : '';
	           $attributes .= ! empty( $item->object_id )    ? ' data-id="' . esc_attr( $item->object_id )  .'"' : '';
	           $item_output = $args->before;
	           $item_output .= '<a'. $attributes .'>';
	           $item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );
	           $item_output .= $args->link_after;
	           $item_output .= '</a>';
	           $item_output .= $args->after;
	
	           $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	            
	                        
	                }
	      } 

/*******************10.0 SHORTCODES & WYSIWYG******************/
	//***10.1 Custom Menu Shortcode
	function ksas_custom_menu_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( 
			array(  
				'menu'            => '', 
				'container'       => 'div', 
				'container_class' => '', 
				'container_id'    => '', 
				'menu_class'      => 'menu', 
				'menu_id'         => '',
				'echo'            => true,
				'fallback_cb'     => 'wp_page_menu',
				'before'          => '',
				'after'           => '',
				'link_before'     => '',
				'link_after'      => '',
				'depth'           => 0,
				'walker'          => '',
				'submenu'		  => '',	
				'theme_location'  => ''
			), $atts )
		); 
	 
		return wp_nav_menu( 
			array( 
				'menu'            => $menu, 
				'container'       => $container, 
				'container_class' => $container_class, 
				'container_id'    => $container_id, 
				'menu_class'      => $menu_class, 
				'menu_id'         => $menu_id,
				'echo'            => false,
				'fallback_cb'     => $fallback_cb,
				'before'          => $before,
				'after'           => $after,
				'link_before'     => $link_before,
				'link_after'      => $link_after,
				'depth'           => $depth,
				'walker'          => $walker,
				'submenu'		  => $submenu,
				'theme_location'  => $theme_location
			) 
		);
	}
	
	add_shortcode( "custommenu", "ksas_custom_menu_shortcode" );

/*******************12.0 WYSIWYG******************/	
	// 12.1 Reveal hidden buttons - sub and sup
		function my_mce_buttons_2($buttons) {	
			/**
			 * Add in a core button that's disabled by default
			 */
			$buttons[] = 'sup';
			$buttons[] = 'sub';
		
			return $buttons;
		}
		add_filter('mce_buttons_2', 'my_mce_buttons_2');

	//12.2 Keep html attributes
		add_action( 'after_setup_theme', 'x_kses_allow_data_attributes_on_links' );
		function x_kses_allow_data_attributes_on_links() {
		  global $allowedposttags;

		    $tags = array( 'dl', 'ul', 'li', 'dd', 'div' );
		    $new_attributes = array(
		        'data-accordion' => array(),
		        'data-tab' => array(),
		        'data-accordion-item' => array(),
		        'data-tab-content' => array(),
		    );

		    foreach ( $tags as $tag ) {
		        if ( isset( $allowedposttags[ $tag ] ) && is_array( $allowedposttags[ $tag ] ) )
		            $allowedposttags[ $tag ] = array_merge( $allowedposttags[ $tag ], $new_attributes );
		    }
		}

		add_filter( 'tiny_mce_before_init', 'x_tinymce_allow_data_attributes_on_links' );
		function x_tinymce_allow_data_attributes_on_links( $options ) { 
		    if ( ! isset( $options['extended_valid_elements'] ) ) 
		        $options['extended_valid_elements'] = ''; 

		    $options['extended_valid_elements'] .= ',dl[data-accordion|data-tab|data-tabs|class|id|style|href]';
		    $options['extended_valid_elements'] .= ',ul[data-accordion|data-tab|data-tabs|class|id|style|href]';
		    $options['extended_valid_elements'] .= ',li[data-accordion-item|class|id|style|href]';
		    $options['extended_valid_elements'] .= ',dd[data-accordion-item|class|id|style|href]';
		    $options['extended_valid_elements'] .= ',div[data-tab-content|class|id|style|href]';
		    $options['extended_valid_elements'] .= ',div[data-tabs-content|class|id|style|href]';
		    return $options; 
		}


	//12.3 Remove unneeded buttons that produce inline styles

		function myplugin_tinymce_buttons($buttons)
		 {
			//Remove unneeded buttons from first WYSIWYG line
			$remove = array(
				'strikethrough', 
				'alignleft', 
				'aligncenter', 
				'alignright',
				'hr',
				'wp_more');

			return array_diff($buttons,$remove);
		 }
		add_filter('mce_buttons','myplugin_tinymce_buttons');

		function myplugin_tinymce_buttons2($buttons)
		 {
			//Remove unneeded buttons from second WYSIWYG line
			$remove = array(
				'underline',
				'alignjustify',
				'forecolor',
				'outdent', 
				'indent',
				'charmap');

			return array_diff($buttons,$remove);
		 }
		add_filter('mce_buttons_2','myplugin_tinymce_buttons2');

		function mce_remove_headings($init) {
		  $init['block_formats'] = "Paragraph=p; Heading 3=h3; Heading 4=h4;";
		  return $init;
		}

		add_filter('tiny_mce_before_init', 'mce_remove_headings' );		


		/* ======================================================================
		 * Disable-Inline-Styles.php
		 * Removes inline styles and other coding junk added by the WYSIWYG editor.
		 * Script by Chris Ferdinandi - http://gomakethings.com
		 * ====================================================================== */

		add_filter( 'the_content', 'clean_post_content' );
		function clean_post_content($content) {

		    // Remove inline styling
		    $content = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $content);

		    // Remove font tag
		    $content = preg_replace('/<font[^>]+>/', '', $content);

		    // Remove empty tags
		    $post_cleaners = array('<p></p>' => '', '<p> </p>' => '', '<p>&nbsp;</p>' => '', '<span></span>' => '', '<span> </span>' => '', '<span>&nbsp;</span>' => '', '<span>' => '', '</span>' => '', '<font>' => '', '</font>' => '');
		    $content = strtr($content, $post_cleaners);

		    return $content;
		}

/*******************14.0 Toolbar Changes******************/	

//remove comments node
function my_admin_bar_render() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
}
add_action( 'wp_before_admin_bar_render', 'my_admin_bar_render' );

// Add links to sites.krieger documentation

function custom_toolbar_link($wp_admin_bar) {
	$args = array(
		'id' => 'webservices',
		'title' => __('<img src="'.get_bloginfo('wpurl').'/wp-content/themes/ksas_flagship_2017/assets/images/shield.png" style="height:25px;vertical-align:middle;margin-right:5px" alt="JHU Shield" title="KSAS Web Services" />Web Services & Documentation &#9662;' ),
		'href' => 'http://sites.krieger.jhu.edu', 
		'meta' => array(
			'target' => '_blank',
			'class' => 'webservices', 
			'title' => 'KSAS Web Services'
			)
	);
	$wp_admin_bar->add_node($args);

// first child link 
	
	$args = array(
		'id' => 'wordpress-ksas',
		'title' => 'WordPress & KSAS', 
		'href' => 'http://sites.krieger.jhu.edu/wordpress-ksas/',
		'parent' => 'webservices',
		'meta' => array(
			'target' => '_blank',
			'class' => 'wordpress-ksas', 
			'title' => 'WordPress & KSAS'
			)
	);
	$wp_admin_bar->add_node($args);

// second child link
	$args = array(
		'id' => 'writing-web',
		'title' => 'Writing for the Web', 
		'href' => 'http://sites.krieger.jhu.edu/guidelines/',
		'parent' => 'webservices', 
		'meta' => array(
			'target' => '_blank',
			'class' => 'writing-web', 
			'title' => 'Writing for the Web'
			)
	);
	$wp_admin_bar->add_node($args);

// third child link
	$args = array(
		'id' => 'request-support',
		'title' => 'Request Support', 
		'href' => 'http://sites.krieger.jhu.edu/forms/request-service/',
		'parent' => 'webservices', 
		'meta' => array(
			'target' => '_blank',
			'class' => 'request-support', 
			'title' => 'Request Support'
			)
	);
	$wp_admin_bar->add_node($args);	

}

add_action('admin_bar_menu', 'custom_toolbar_link', 999);

/*******************15.0 Disable PDF Thumbnails******************/	
function wpb_disable_pdf_previews() { 
	$fallbacksizes = array(); 
	return $fallbacksizes; 
}
add_filter('fallback_intermediate_image_sizes', 'wpb_disable_pdf_previews');

?>