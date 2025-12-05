<?php
/**
 * Plugin Name: GV Snippets Core
 * Description: Migrated Code Snippets (generated from tobm_snippets).
 * Author: Gemini Valve
 * Version: 1.0.0
 *
 * NOTE: This file was auto-generated. Review and clean up as needed.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ===== Snippet #5: Add an Woocommerce order number search field to the WP Admin top bar =====
// Scope: admin | Priority: 10

// Add an Woocommerce order number search field to the WP Admin top bar
add_action('wp_before_admin_bar_render', function() {
  if(!current_user_can('administrator')) {
    return;
  }
  
  global $wp_admin_bar;

  $search_query = '';
  if (!empty($_GET['post_type']) && $_GET['post_type'] == 'shop_order' ) {

    $search_query = !empty($_GET['s']) ? $_GET['s'] : '';

    if($search_query) {
        $order = get_post(intval($search_query));

        if($order) {
            wp_redirect(get_edit_post_link($order->ID, ''));
            exit;
        }
    }
  }

  $wp_admin_bar->add_menu(array(
    'id' => 'admin_bar_shop_order_form',
    'title' => '<form method="get" action="'.get_site_url().'/wp-admin/edit.php?post_type=shop_order">
      <input name="s" type="text" placeholder="Order ID" value="' . esc_attr($search_query) . '" style="width:100px; height: 25px; padding-left: 5px;">
      <button  class="button button-primary" style="height: 25px; padding: 0px 10px 0px 10px; margin-left: -5px;">Go</button>
      <input name="post_type" value="shop_order" type="hidden">
    </form>'
  ));
},100
);


// ===== Snippet #6: Filter Woocommerce orders by user role =====
// Scope: admin | Priority: 10

// Filter Woocommerce orders by user role

function wpsh_user_role_filter() {

	global $typenow, $wp_query;

	if ( in_array( $typenow, wc_get_order_types( 'order-meta-boxes' ) ) ) {
		$user_role	= '';

		// Get all user roles
		$user_roles = array( 'guest' => 'Guest' );
		foreach ( get_editable_roles() as $key => $values ) {
			$user_roles[ $key ] = $values['name'];
		}

		// Set a selected user role
		if ( ! empty( $_GET['_user_role'] ) ) {
			$user_role	= sanitize_text_field( $_GET['_user_role'] );
		}

		// Display drop down
		?><select name='_user_role'>
			<option value=''><?php _e( 'Select a user role', 'woocommerce' ); ?></option><?php
			foreach ( $user_roles as $key => $value ) :
				?><option <?php selected( $user_role, $key ); ?> value='<?php echo $key; ?>'><?php echo $value; ?></option><?php
			endforeach;
		?></select><?php
	}

}
add_action( 'restrict_manage_posts', 'wpsh_user_role_filter' );

function wpsh_user_role_filter_where( $query ) {

	if ( ! $query->is_main_query() || empty( $_GET['_user_role'] ) || $_GET['post_type'] !== 'shop_order' ) {
		return;
	}

	if ( $_GET['_user_role'] != 'guest' ) {
		$ids = get_users( array( 'role' => sanitize_text_field( $_GET['_user_role'] ), 'fields' => 'ID' ) );
		$ids = array_map( 'absint', $ids );
	} else {
		$ids = array( 0 );
	}

	$query->set( 'meta_query', array(
		array(
			'key' => '_customer_user',
			'compare' => 'IN',
			'value' => $ids,
		)
	) );

	if ( empty( $ids ) ) {
		$query->set( 'posts_per_page', 0 );
	}
}
add_filter( 'pre_get_posts', 'wpsh_user_role_filter_where' );


// ===== Snippet #7: Display custom info in orders =====
// Scope: admin | Priority: 10

// Display customer phone and email in Woocommerce orders table
add_action( 'manage_shop_order_posts_custom_column' , 'wpsh_phone_and_email_column', 50, 2 );

function wpsh_phone_and_email_column( $column, $post_id ) 
{
    if ( $column == 'order_number' )
    {
        global $the_order;
		// Display scheduled delivery date
		if( $deliverydate =get_post_meta($post_id,'delivery_date'))
		{
			
	//		$format_in = 'Ymd'; // the format your value is saved in (set in the field options)
	//		$format_out = 'd-M'; // the format you want to end up with
	//	$date = DateTime::createFromFormat($format_in, $deliverydate[0]);
			//echo $date->format( $format_out );
			$date = $deliverydate[0];
			$day = substr($date,-2);
			$month = substr($date,-4,2);
	//		$date->format('Y-m-d H:i:s');
			echo '<strong> - ' . $day . '-'. $month.'</strong>';
		}
		
		/*
		// Display customer phone in Woocommerce orders table
        if( $phone = $the_order->get_billing_phone() )
		{
            $phone_wp_dashicon = '<span class="dashicons dashicons-phone"></span> ';
            echo '<br><a href="tel:'.$phone.'">' . $phone_wp_dashicon . $phone.'</a></strong>';
        }
		*/
		/*
	 	// Display customer email in Woocommerce orders table --> from table 
	    if( $email = $the_order->get_billing_email() )
		{
			echo ' - <strong><a href="mailto:'.$email.'">' . $email . '</a></strong>';
		}
		*/
        // Display customer reference in Woocommerce orders table
		if( $order_reference =get_post_meta($post_id,'reference'))
		{
			
			echo '<a HREF="https://geminivalvebv-my.sharepoint.com/personal/aukebouwense_geminivalve_nl/Lists/Orders/Alle%20Items.aspx?FilterField1=LinkTitle&FilterValue1=' . $order_reference[0] . '&FilterType1=Computed&env=WebViewList"><br> <strong>' . $order_reference[0] . '</strong></a>';
		}
		// Display customer reference 
		if( $melding_invoice =get_post_meta($post_id,'melding_invoice'))
		{
			echo ' - <strong>' . $melding_invoice[0] . '</strong>';
		}
		
    }
}

////


// ===== Snippet #8: Add custom button to Woocommerce orders and products page =====
// Scope: admin | Priority: 10

// Add custom button to Woocommerce orders page 
add_action( 'manage_posts_extra_tablenav', 'wpsh_button_on_orders_page', 20, 1 );
function wpsh_button_on_orders_page( $which ) {
    global $typenow;

    if ( 'shop_order' === $typenow && 'top' === $which ) {
        ?>
        <div class="alignright actions custom">
            <button type="submit" name="custom_" style="height:32px;" class="button"  value=""><?php
	  // Change your URL and button text here
                echo __( '<a style="text-decoration: none;" href="/wp-admin/edit.php?post_type=product">
				Go to products »
				</a>', 'woocommerce' ); ?></button>
        </div>
        <?php
    }
}
// Add custom button to Woocommerce products page 
add_action( 'manage_posts_extra_tablenav', 'wpsh_button_on_products_page', 20, 1 );
function wpsh_button_on_products_page( $which ) {
    global $typenow;

    if ( 'product' === $typenow && 'top' === $which ) {
        ?>
        <div class="alignleft actions custom">
            <button type="submit" name="custom_" style="height:32px;" class="button"  value=""><?php
	  	  // Change your URL and button text here
                echo __( '<a style="text-decoration: none;" href="/wp-admin/edit.php?post_type=shop_order">
				Go to orders »
				</a>', 'woocommerce' ); ?></button>
        </div>
        <?php
    }
}


// ===== Snippet #9: Set default Woocommerce login page to "Orders" page =====
// Scope: admin | Priority: 10

// Set default Woocommerce login page to "Orders" page
add_action( 'load-index.php', 'wpsh_redirect_to_orders' );
function wpsh_redirect_to_orders(){
    wp_redirect( admin_url( 'edit.php?post_status=wc-processing&post_type=shop_order' ) );
}
add_filter( 'login_redirect', 'wpsh_redirect_to_orders_dashboard', 9999, 3 );
function wpsh_redirect_to_orders_dashboard( $redirect_to, $request, $user ){
    $redirect_to = admin_url( 'edit.php?post_status=wc-processing&post_type=shop_order' );
	//https://geminivalve.nl/wp-admin/edit.php?post_status=wc-processing&post_type=shop_order
    return $redirect_to;
}


//edit.php?post_type=shop_order


// ===== Snippet #10: Add custom Woocommerce order status - In-progress =====
// Scope: admin | Priority: 10

// Add custom Woocommerce order status
function register_in_progress_order_status() {
    register_post_status( 'wc-invoiced', array(
            'label' => _x( 'In-progress', 'Order Status', 'woocommerce' ),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_all_admin_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop( 'In-progress <span class="count">(%s)</span>', 'In-progress <span class="count">(%s)</span>', 'woocommerce' )
        )
    );
}

add_action( 'init', 'register_in_progress_order_status' );

function my_invoiced_order_status( $order_statuses ){
    $order_statuses['wc-invoiced'] = _x( 'In-progress', 'Order Status', 'woocommerce' );
    return $order_statuses;

}
add_filter( 'wc_order_statuses', 'my_invoiced_order_status' );

function show_in_bulk_actions() {
    global $post_type;

    if( 'shop_order' == $post_type ) {
        ?>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery('<option>').val('mark_invoiced').text('<?php _e( 'Change Status to In-progress','woocommerce' ); ?>').appendTo("select[name='action']");
                    jQuery('<option>').val('mark_invoiced').text('<?php _e( 'Change Status to In-progress','woocommerce' ); ?>').appendTo("select[name='action2']");
                });
            </script>
        <?php
    }
}
add_action( 'admin_footer', 'show_in_bulk_actions' );


// ===== Snippet #11: Display the product thumbnail in Woocommerce order view pages =====
// Scope: front-end | Priority: 10

// Display the product thumbnail in Woocommerce order view pages
add_filter( 'woocommerce_order_item_name', 'wpsh_display_product_image_in_order_item', 20, 3 );
function wpsh_display_product_image_in_order_item( $item_name, $item, $is_visible ) {
    // Targeting view order pages only
    if( is_wc_endpoint_url( 'view-order' ) ) {
        $product   = $item->get_product(); // Get the WC_Product object (from order item)
        $thumbnail = $product->get_image(array( 50, 50)); // Get the product thumbnail (from product object)
        if( $product->get_image_id() > 0 )
            $item_name = '<div class="item-thumbnail">' . $thumbnail . '</div>' . $item_name;
    }
    return $item_name;
}


// ===== Snippet #12: Add Purchased products tab to Woocommerce my account page =====
// Scope: global | Priority: 10

// Add Purchased products tab to Woocommerce my account page

// here we hook the My Account menu links and add our custom one
add_filter( 'woocommerce_account_menu_items', 'wpsh_purchased_products', 40 );
function wpsh_purchased_products( $menu_links ){

// Set 5 in this line to something else if you would like to move the position of the tab
	return array_slice( $menu_links, 0, 5, true )
	+ array( 'purchased-products' => 'Purchased Products' )
	+ array_slice( $menu_links, 2, NULL, true );

}

add_action( 'init', 'wpsh_purchased_products_endpoint' );
function wpsh_purchased_products_endpoint() {
	add_rewrite_endpoint( 'purchased-products', EP_PAGES );
}

// Add purchased porducts as a tab content
add_action( 'woocommerce_account_purchased-products_endpoint', 'wpsh_purchased_products_content' );
function wpsh_purchased_products_content() {

	global $wpdb;

	// Purchased products are sorted by date
	$purchased_products_ids = $wpdb->get_col( 
		$wpdb->prepare(
			"
			SELECT      itemmeta.meta_value
			FROM        " . $wpdb->prefix . "woocommerce_order_itemmeta itemmeta
			INNER JOIN  " . $wpdb->prefix . "woocommerce_order_items items
			            ON itemmeta.order_item_id = items.order_item_id
			INNER JOIN  $wpdb->posts orders
			            ON orders.ID = items.order_id
			INNER JOIN  $wpdb->postmeta ordermeta
			            ON orders.ID = ordermeta.post_id
			WHERE       itemmeta.meta_key = '_product_id'
			            AND ordermeta.meta_key = '_customer_user'
			            AND ordermeta.meta_value = %s
			ORDER BY    orders.post_date DESC
			",
			get_current_user_id()
		)
	);

	// Don’t display duplicate products
	$purchased_products_ids = array_unique( $purchased_products_ids );
	if( ! empty( $purchased_products_ids ) ) {
	  
	 echo '<div class="woocommerce-message">Purchased products</div>';
	  
		$purchased_products = new WP_Query( array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'post__in' => $purchased_products_ids,
			'orderby' => 'post__in',
			'posts_per_page' => -1,
		) );
	
		woocommerce_product_loop_start();

		while ( $purchased_products->have_posts() ) : $purchased_products->the_post();

			wc_get_template_part( 'content', 'product' );

		endwhile;

		woocommerce_product_loop_end();

		woocommerce_reset_loop();
		wp_reset_postdata();

	} else {
	  // Change this text if needed)
		echo 'Unfortunately you have no purchases yet';
	}
}


// ===== Snippet #14: Set Woocommerce my account page orders display limit =====
// Scope: front-end | Priority: 10

// Set Woocommerce my account page orders display limit
add_filter( 'woocommerce_my_account_my_orders_query', 'wpsh_my_account_orders_limit', 10, 1 );
function wpsh_my_account_orders_limit( $args ) {
    // Set the post per page
    $args['limit'] = 5;
    return $args;
}


// ===== Snippet #15: Display How to display products name and quantity in a new column on Woocommerce "My account" page orders table =====
// Scope: front-end | Priority: 10

// Display How to display products name and quantity in a new column on Woocommerce "My account" page orders table

add_filter( 'woocommerce_my_account_my_orders_columns', 'wpsh_product_column', 10, 1 );
function wpsh_product_column( $columns ) {
    $new_columns = [];

    foreach ( $columns as $key => $name ) {
        $new_columns[ $key ] = $name;

        if ( 'order-status' === $key ) {
            $new_columns['order-items'] = __( 'Product | Qty', 'woocommerce' );
        }
    }
    return $new_columns;
}

add_action( 'woocommerce_my_account_my_orders_column_order-items', 'wpsh_product_column_content', 10, 1 );
function wpsh_product_column_content( $order ) {
    $details = array();

    foreach( $order->get_items() as $item )
        $details[] = $item->get_name() . ' × ' . $item->get_quantity();

    echo count( $details ) > 0 ? implode( '<br>', $details ) : '–';
}


// ===== Snippet #16: Link previous WooCommerce guest orders to customer account after registration =====
// Scope: global | Priority: 10

// Link previous WooCommerce guest orders to customer account after registration
function action_woocommerce_created_customer( $customer_id, $new_customer_data, $password_generated ) {
    // Link past orders to this newly created customer
    wc_update_new_customer_past_orders( $customer_id );
}
add_action( 'woocommerce_created_customer', 'action_woocommerce_created_customer', 10, 3 );


// ===== Snippet #18: Register new custom order status =====
// Scope: admin | Priority: 10

// Register new custom order status
add_action('init', 'register_custom_order_statuses');
function register_custom_order_statuses() {
    register_post_status('wc-test-accepted ', array(
        'label' => __( 'Accepted', 'woocommerce' ),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Accepted <span class="count">(%s)</span>', 'Accepted <span class="count">(%s)</span>')
    ));
}


// Add new custom order status to list of WC Order statuses
add_filter('wc_order_statuses', 'add_custom_order_statuses');
function add_custom_order_statuses($order_statuses) {
    $new_order_statuses = array();

    // add new order status before processing
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-test-accepted'] = __('Accepted', 'woocommerce' );
        }
    }
    return $new_order_statuses;
}


// Adding new custom status to admin order list bulk dropdown
add_filter( 'bulk_actions-edit-shop_order', 'custom_dropdown_bulk_actions_shop_order', 50, 1 );
function custom_dropdown_bulk_actions_shop_order( $actions ) {
    $new_actions = array();

    // add new order status before processing
    foreach ($actions as $key => $action) {
        if ('mark_processing' === $key)
            $new_actions['mark_test-accepted'] = __( 'Change status to Accepted', 'woocommerce' );

        $new_actions[$key] = $action;
    }
    return $new_actions;
}


// ===== Snippet #19: Add custom order field =====
// Scope: admin | Priority: 10

function custom_woocommerce_product_query( $query ){

    $query->set( 'orderby', 'title' ); // Return the posts in an alphabetical order.
    $query->set( 'order', 'ASC' ); // Return the posts in an ascending order.

    $tax_query = array(
        'taxonomy' => 'marca',  // The taxonomy we want to query, in our case 'marca'.
        'operator' => 'EXISTS'  // Return the post if it has the selected taxonomy with terms set, in our case 'marca'.
    );
    $query->set( 'tax_query', $tax_query );
}

add_action( 'woocommerce_product_query', 'custom_woocommerce_product_query', 10, 1 );


// ===== Snippet #20: edit order in later phase =====
// Scope: global | Priority: 10

/**
 * @snippet       Allow Order Edit @ Custom Status
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 7
 * @community     https://businessbloomer.com/club/
 */
 
add_filter( 'wc_order_is_editable', 'custom_order_status_editable_processing', 9999, 2 );
 
function custom_order_status_editable_processing( $allow_edit, $order ) {
    if ( $order->get_status() === 'processing' ) {
        $allow_edit = true;
    }
    return $allow_edit;
}

add_filter( 'wc_order_is_editable', 'custom_order_status_editable_offerte_aanvraag', 9999, 2 );
 
function custom_order_status_editable_offerte_aanvraag( $allow_edit, $order ) {
    if ( $order->get_status() === 'offerte-aanvraag' ) {
        $allow_edit = true;
    }
    return $allow_edit;
}


// ===== Snippet #23: Add woocommerce admin menu =====
// Scope: admin | Priority: 10

function register_my_custom_submenu_page() 
{
    add_submenu_page(
            'woocommerce',
            __('Open Orders', 'woocommerce-quote-request2'),
            __('Open Orders', 'woocommerce-quote-request2'),
            'manage_woocommerce',
            'edit.php?post_status=wc-processing&post_type=shop_order'
        );
	
}
add_action('admin_menu', 'register_my_custom_submenu_page',10);



/*
private static $instance = null;
add_action('admin_menu', array($this, 'add_open_order_menu')); 
public static function get_instance() 
{
	if (null === self::$instance) 
	{
		self::$instance = new self();
    }
        return self::$instance;
}

public function add_offerte_aanvragen_menu() 
{
*/
	/*Voorbeeld : 
	 *     public function add_offerte_aanvragen_menu() {
        add_submenu_page(
            'woocommerce',
            __('Offerte aanvragen', 'woocommerce-quote-request'),
            __('Offerte aanvragen', 'woocommerce-quote-request'),
            'manage_woocommerce',
            'edit.php?post_status=wc-offerte-aanvraag&post_type=shop_order'
        );
    }
	*/
	
	//add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $callback = '', int|float $position = null ): string|false
    // add_submenu_page('woocommerce',('Open orders', 'woocommerce-order-request'),('Open orders', 'woocommerce-order-request'),'manage_woocommerce','edit.php?post_status=wc-processing&post_type=shop_order'        );
    // 
   /*
     add_submenu_page(
        '/pluginname/includes/admin-menu.php',
        $value["page_title"],
        $value["menu_title"],
        'manage_options',
        $value["slug"],
        '',
        plugins_url( 'pluginname/images/icon.png' )
    );*/
	


//DEZE EERST BEGRIJPEN 
//
/*
	function register_my_sub_admin() {
 add_submenu_page( '/pluginname/includes/admin-menu.php',
        'title',
        'menu_title',
        'manage_options',
        '/pluginname/includes/submenu.php',
        '',
        plugins_url( 'pluginname/images/icon.png' )
    );
}

	add_action( 'admin_menu', 'register_my_sub_admin' );

    }
	*/

//DAN DEZE
//
 /*
  $submenus = 
	[
    ["page_title" => "page1","menu_title" => "title1","slug" => "wp-admin.php", ],
    ["page_title" => "page2","menu_title" => "title2","slug" => "wp-login.php", ],
	];


foreach ($submenus as $value) 
{
    add_submenu_page(
        '/pluginname/includes/admin-menu.php',
        $value["page_title"],
        $value["menu_title"],
        'manage_options',
        $value["slug"],
        '',
        plugins_url( 'pluginname/images/icon.png' )
    );
}
*/
//https://wordpress.stackexchange.com/questions/101773/add-a-subitem-to-woocommerce-section
//
//
//
/*
function register_my_custom_submenu_page() 
{
//	 add_submenu_page( 'woocommerce', 'Open orders', 'Open orders', 'manage_options', 'my-custom-submenu-page', 'my_custom_submenu_page_callback' ); 
    add_submenu_page(
            'woocommerce',
            __('Open Orders', 'woocommerce-quote-request2'),
            __('Open Orders', 'woocommerce-quote-request2'),
            'manage_woocommerce',
            'edit.php?post_status=wc-offerte-aanvraag&post_type=shop_order'
        );
	
    //add_submenu_page( 'woocommerce', 'My Custom Submenu Page', 'My Custom Submenu Page', 'manage_options', 'my-custom-submenu-page', 'my_custom_submenu_page_callback' ); 
   	//edit.php?post_status=wc-processing&post_type=shop_order
	//add_submenu_page('woocommerce',('Open orders', 'woocommerce-order-request'),('Open orders', 'woocommerce-order-request'),'manage_woocommerce','edit.php?post_status=wc-processing&post_type=shop_order'        );
}
 // function my_custom_submenu_page_callback() {    echo '<h3>My Custom Submenu Page</h3>';}
add_action('admin_menu', 'register_my_custom_submenu_page',10);
*/


// ===== Snippet #26: DHL reference Snippet =====
// Scope: global | Priority: 10


	// get order info
	// 
   //     global $the_order;
		


// The following example code can be added to the child theme's functions.php file
add_filter('dhlpwc_default_reference_value', 'dhlpwc_change_reference_value', 10, 2);

function dhlpwc_change_reference_value($reference_value, $order_id)
{
    // Set a difference reference value
    $order = new WC_Order($order_id);
    $new_reference_value = $order->get_order_number();
	if( $melding_invoice =get_post_meta($order_id,'melding_invoice')) { 			$new_reference_value= $melding_invoice[0];  }
    if (!$new_reference_value) {  return $reference_value;    }
    return $new_reference_value;
}


// The following example code can be added to the child theme's functions.php file
add_filter('dhlpwc_default_reference2_value', 'dhlpwc_change_reference2_value', 10, 2);

function dhlpwc_change_reference2_value($reference2_value, $order_id)
{
    // Set a difference reference value
    $order = new WC_Order($order_id);
    $new_reference2_value = $order->get_order_number();
	
	if( $order_reference =get_post_meta($order_id,'reference'))	{			$new_reference2_value.=  ' - '.$order_reference[0];	}
//	if( $melding_invoice =get_post_meta($order_id,'melding_invoice')) { 			$new_reference2_value.= ' - '.$melding_invoice[0];  }

  //  $new_reference2_value = $order->get_order_number();
    
	if (!$new_reference2_value) {         return $reference2_value;     }
	
    return $new_reference2_value;
}


/*
// The following example code can be added to the child theme's functions.php file
add_filter('dhlpwc_default_reference2_value', 'dhlpwc_change_reference2_value', 10, 2);

function dhlpwc_change_reference2_value($reference2_value, $order_id)
{
    // Set a difference reference value
    $order = new WC_Order($order_id);
    $new_reference2_value = $order->get_order_number();
    if (!$new_reference2_value) {
        return $reference2_value;
    }
    return $new_reference2_value;
}
*/


// ===== Snippet #27: Functions for visibility =====
// Scope: global | Priority: 10

function wcpdf_show_date_in_eu($document)
{
	
	if ( empty( $document->order ) ) { return; }
	
	$order = $document ->order;
 	$deliverydate =get_post_meta($post_id,'delivery_date');
	$date = $deliverydate[0];
	$day = substr($date,-2);
	$month = substr($date,-4,2);
	$return_date= '<strong> - ' . $day . '-'. $month.'</strong>';	
	
	return $return_date;
}

function show_deliverydate_in_eu()
{
	global $the_order;
	if( $deliverydate =get_post_meta($post_id,'delivery_date'))
		{
		
			$date = $deliverydate[0];
			$day = substr($date,-2);
			$month = substr($date,-4,2);
			$return_date= '<strong> - ' . $day . '-'. $month.'</strong>';
		}
	
	
	
	return $return_date;
}


// ===== Snippet #28: Customer Information - Organize tabs customer account =====
// Scope: front-end | Priority: 10


// NB! In order to make it work you need to go to Settings > Permalinks and just push "Save Changes" button.

// If you need to change endpoint order then add your own order here

add_filter ( 'woocommerce_account_menu_items', 'wpsh_custom_endpoint_order' );
function wpsh_custom_endpoint_order() {
 $myorder = array(
        'dashboard'          => __( 'Dashboard', 'woocommerce' ),
        'orders'             => __( 'Your orders', 'woocommerce' ), 
	 	'purchased-products'             => __( 'Purchased products', 'woocommerce' ), 
        'edit-account'       => __( 'Account details', 'woocommerce' ),
	 	'edit-address'       => __( 'Edit address', 'woocommerce' ),
	// 	'woo-wish-list'       => __( 'Wishlist', 'woocommerce' ),
	 //	'support'    => __( 'Support', 'woocommerce' ), // Don’t forget to change the slug and title here
        'customer-logout'    => __( 'Log out', 'woocommerce' ),
    );
    return $myorder;
}


// ===== Snippet #29: Account - Add fields - email to shipping =====
// Scope: front-end | Priority: 10

add_filter( 'woocommerce_checkout_fields' , 'add_name_to_registration_form' );
    function add_mail_to_registration_form( $fields ) {
         $fields['shipping']['shipping_email'] = array(
             'label'     => __('Shipping Email', 'woocommerce'),
             'placeholder'   => _x('Shipping Email', 'placeholder'),
             'required'  => true,
             'class'     => array('form-row-first'),
             'clear'     => true
         );
         return $fields;
    }

add_action( 'woocommerce_checkout_update_customer', 'save_extra_register_fields' );
    function save_extra_register_fields( $customer ) {
        if ( isset( $_POST['shipping_email'] ) ) {
            update_user_meta( $customer->get_id(), 'shipping_email', sanitize_text_field( $_POST['shipping_email'] ) );
        }
    }


// ===== Snippet #34: Add Customer Roles =====
// Scope: global | Priority: 10

// Voeg extra customer rollen toe
function custom_add_customer_roles() {
    // Basis capabilities kopiëren van de standaard 'customer' rol
    $customer_role = get_role('customer');

    if ($customer_role) {
        // Customer_1 aanmaken
        if (!get_role('customer_1')) {
            add_role(
                'customer_1',
                __('Customer 1', 'woocommerce'),
                $customer_role->capabilities
            );
        }

        // Customer_2 aanmaken
        if (!get_role('customer_2')) {
            add_role(
                'customer_2',
                __('Customer 2', 'woocommerce'),
                $customer_role->capabilities
            );
        }
		// Customer_3 aanmaken
        if (!get_role('customer_3')) {
            add_role(
                'customer_3',
                __('Customer 3', 'woocommerce'),
                $customer_role->capabilities
            );
        }
		// Customer_4 aanmaken
        if (!get_role('customer_4')) {
            add_role(
                'customer_4',
                __('Customer 4', 'woocommerce'),
                $customer_role->capabilities
            );
        }
		// Customer_5 aanmaken
        if (!get_role('customer_5')) {
            add_role(
                'customer_5',
                __('Customer 5', 'woocommerce'),
                $customer_role->capabilities
            );
        }
		// Customer_6 aanmaken
        if (!get_role('customer_6')) {
            add_role(
                'customer_6',
                __('Customer 6', 'woocommerce'),
                $customer_role->capabilities
            );
        }
		// Customer_7 aanmaken
        if (!get_role('customer_7')) {
            add_role(
                'customer_7',
                __('Customer 7', 'woocommerce'),
                $customer_role->capabilities
            );
        }
		
    }
}
add_action('init', 'custom_add_customer_roles');


// ===== Snippet #36: Discount based on customer type (discount on sales price when available) =====
// Scope: global | Priority: 10

/**
 * Rol-gebaseerde korting voor WooCommerce
 * - customer_1: 10%
 * - customer_2: 20%
 *
 * Nu berekent hij de korting op de SALE prijs als die bestaat,
 * anders op de reguliere prijs.
 */

// === Instellingen: rollen en hun korting in procenten ===
function gv_get_role_discounts() {
    return array(
        'customer_1' => 5, // 5%
        'customer_2' => 10, // 10%
		'customer_3' => 15, // 15%
		'customer_4' => 20, // 20%
		'customer_5' => 25, // 25%
		'customer_6' => 30, // 30%
		'customer_7' => 35, // 35%
    );
}

/**
 * Haal de korting (%) op voor de ingelogde gebruiker op basis van rol.
 */
function gv_get_current_user_discount_percent() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $role_discounts = gv_get_role_discounts();

        foreach ($user->roles as $role) {
            if (isset($role_discounts[$role])) {
                return (float) $role_discounts[$role];
            }
        }
    }
    return 0;
}

/**
 * Haal de basisprijs (sale indien actief, anders regular).
 */
function gv_get_base_price( $product ) {
    $sale = (float) $product->get_sale_price();
    $regular = (float) $product->get_regular_price();

    if ( $sale > 0 && $sale < $regular ) {
        return $sale;
    }
    return $regular > 0 ? $regular : (float) $product->get_price();
}

/**
 * Bereken de afgeprijsde prijs.
 */
function gv_get_discounted_price( $product ) {
    $discount_percent = gv_get_current_user_discount_percent();
    if ($discount_percent <= 0) {
        return false;
    }

    $base = gv_get_base_price( $product );
    if ($base <= 0) return false;

    $decimals = wc_get_price_decimals();
    return round( $base * (1 - ($discount_percent / 100)), $decimals );
}

/**
 * Toon aangepaste prijs HTML op productpagina.
 */
add_filter('woocommerce_get_price_html', 'gv_price_html_with_role_discount', 9999, 2);
function gv_price_html_with_role_discount( $price_html, $product ) {
    if (is_admin() && !wp_doing_ajax()) return $price_html;

    $discount_percent = gv_get_current_user_discount_percent();
    if ($discount_percent <= 0) return $price_html;

    $base = gv_get_base_price( $product );
    $discounted = gv_get_discounted_price( $product );

    if ($discounted === false || $base <= 0) return $price_html;

    $orig_html = wc_price( $base );
    $new_html  = wc_price( $discounted );
    $label     = sprintf( __('%s%% rolkorting', 'woocommerce'), $discount_percent );

    return sprintf(
        '<span class="price"><del>%s</del> <ins>%s</ins> <small class="gv-role-discount-note">(%s)</small></span>',
        $orig_html,
        $new_html,
        esc_html($label)
    );
}

/**
 * Pas de daadwerkelijke prijs van cart items aan.
 */
add_action('woocommerce_before_calculate_totals', 'gv_apply_role_discount_on_cart_prices', 20);
function gv_apply_role_discount_on_cart_prices( $cart ) {
    if ( is_admin() && !wp_doing_ajax() ) return;
    if ( did_action('woocommerce_before_calculate_totals') >= 2 ) return;

    $discount_percent = gv_get_current_user_discount_percent();
    if ($discount_percent <= 0) return;

    foreach ( $cart->get_cart() as $cart_item ) {
        $product = $cart_item['data'];
        if ( ! $product || ! is_a($product, 'WC_Product') ) continue;

        $base = gv_get_base_price( $product );
        if ($base <= 0) continue;

        $discounted = round( $base * (1 - ($discount_percent / 100)), wc_get_price_decimals() );

        $product->set_price( $discounted );
    }
}

/**
 * Notitie in de winkelwagen / checkout.
 */
add_action('woocommerce_cart_totals_before_shipping', 'gv_role_discount_cart_note');
add_action('woocommerce_review_order_before_shipping', 'gv_role_discount_cart_note');
function gv_role_discount_cart_note() {
    $discount_percent = gv_get_current_user_discount_percent();
    if ($discount_percent > 0) {
        printf(
            '<tr class="gv-role-discount-row"><th>%s</th><td>%s%%</td></tr>',
            esc_html__('Rolkorting', 'woocommerce'),
            esc_html($discount_percent)
        );
    }
}


// ===== Snippet #37: FORM - Formulier voor op de website =====
// Scope: global | Priority: 10

// Shortcode: [contact_form]
function gemini_contact_form() {
    ob_start();

    $notice = '';

    if ( isset($_POST['gemini_contact_submit']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'gemini_contact_nonce') ) {
        // 1) Collect input
        $name    = sanitize_text_field( $_POST['gemini_name'] ?? '' );
        $email   = sanitize_email( $_POST['gemini_email'] ?? '' );
        $phone   = sanitize_text_field( $_POST['gemini_phone'] ?? '' );
        $message_text = sanitize_textarea_field( $_POST['gemini_message'] ?? '' );

        $errors = [];
        if ( empty($name) )  { $errors[] = 'Please enter your name.'; }
        if ( empty($email) || !is_email($email) ) { $errors[] = 'Please enter a valid email address.'; }
        if ( empty($phone) ) { $errors[] = 'Please enter your phone number.'; }
        if ( empty($message_text) ) { $errors[] = 'Please enter a message.'; }

        // 2) File uploads
        $attachments = [];
        $max_size_mb = 8; 
        $max_size    = $max_size_mb * 1024 * 1024;

        $allowed_mimes = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
        ];

        if ( ! empty($_FILES['gemini_files']) && is_array($_FILES['gemini_files']['name']) ) {
            foreach ( $_FILES['gemini_files']['name'] as $i => $filename ) {
                if ( empty($filename) ) { continue; }
                $file = [
                    'name'     => $_FILES['gemini_files']['name'][$i],
                    'type'     => $_FILES['gemini_files']['type'][$i],
                    'tmp_name' => $_FILES['gemini_files']['tmp_name'][$i],
                    'error'    => $_FILES['gemini_files']['error'][$i],
                    'size'     => $_FILES['gemini_files']['size'][$i],
                ];

                if ( $file['error'] !== UPLOAD_ERR_OK ) {
                    $errors[] = sprintf('Upload error for file "%s" (code %d).', esc_html($file['name']), $file['error']);
                    continue;
                }
                if ( $file['size'] > $max_size ) {
                    $errors[] = sprintf('File "%s" is larger than %d MB.', esc_html($file['name']), $max_size_mb);
                    continue;
                }

                $check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed_mimes );
                if ( empty($check['ext']) || empty($check['type']) ) {
                    $errors[] = sprintf('File type not allowed: "%s".', esc_html($file['name']));
                    continue;
                }

                $uploaded = wp_handle_upload( $file, [ 'test_form' => false, 'mimes' => $allowed_mimes ] );
                if ( isset($uploaded['error']) ) {
                    $errors[] = sprintf('Could not process file "%s": %s', esc_html($file['name']), esc_html($uploaded['error']));
                    continue;
                }

                if ( ! empty($uploaded['file']) ) {
                    $attachments[] = $uploaded['file'];
                }
            }
        }

        if ( empty($errors) ) {
            // 3) Send mail
            $to      = 'sales@geminivalve.nl';
            $subject = 'New contact form submission';
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                "Reply-To: {$name} <{$email}>"
            ];

            $message = '
                <h3>New contact form submission</h3>
                <p><strong>Name:</strong> '.esc_html($name).'</p>
                <p><strong>Email:</strong> '.esc_html($email).'</p>
                <p><strong>Phone:</strong> '.esc_html($phone).'</p>
                <p><strong>Message:</strong><br>'.nl2br(esc_html($message_text)).'</p>
                <p><em>Attachments: '.count($attachments).' file(s)</em></p>
            ';

            $sent = wp_mail( $to, $subject, $message, $headers, $attachments );

            if ( $sent ) {
                foreach ( $attachments as $path ) {
                    @unlink( $path ); // remove uploaded files
                }
                $notice = "<p style='color:green;'>Thank you, your message has been sent.</p>";
            } else {
                $notice = "<p style='color:#b00;'>There was a problem sending your message. Please try again later.</p>";
            }
        } else {
            $notice = "<div style='color:#b00;'><p><strong>Please correct the following errors:</strong></p><ul><li>" .
                      implode('</li><li>', array_map('esc_html', $errors)) .
                      "</li></ul></div>";
        }
    }

    echo $notice;
    ?>
    <form method="post" class="gemini-contact-form" enctype="multipart/form-data">
        <?php wp_nonce_field('gemini_contact_nonce'); ?>
        <p>
            <label>Name<br>
                <input type="text" name="gemini_name" required>
            </label>
        </p>
        <p>
            <label>Email<br>
                <input type="email" name="gemini_email" required>
            </label>
        </p>
        <p>
            <label>Phone<br>
                <input type="text" name="gemini_phone" required>
            </label>
        </p>
        <p>
            <label>Message<br>
                <textarea name="gemini_message" rows="5" required></textarea>
            </label>
        </p>
        <p>
            <label>Attachments (max <?php echo (int)$max_size_mb; ?> MB each)<br>
                <input type="file" name="gemini_files[]" multiple>
            </label>
        </p>
        <p>
            <input type="submit" name="gemini_contact_submit" value="Send">
        </p>
    </form>
    <?php

    return ob_get_clean();
}
add_shortcode('contact_form', 'gemini_contact_form');


// ===== Snippet #39: Set default login page =====
// Scope: global | Priority: 10

/**
 * Vervang standaard wp_login_url door je eigen pagina
 */
add_filter( 'login_url', function( $login_url, $redirect, $force_reauth ) {
    // Zet hier de slug van jouw nieuwe loginpagina
    $custom_login = home_url( '/login/' );

    if ( ! empty( $redirect ) ) {
        $custom_login = add_query_arg( 'redirect_to', urlencode( $redirect ), $custom_login );
    }

    return $custom_login;
}, 10, 3 );


// ===== Snippet #42: FORM - login pagina My account =====
// Scope: global | Priority: 10

/**
 * Shortcode: [gv_wc_login redirect="/my-account/"]
 * - Eenvoudig WooCommerce login formulier (email + password + remember me)
 * - Redirect na succesvolle login (default = WooCommerce My Account)
 */
function gv_wc_login_shortcode( $atts = [] ) {
    if ( ! function_exists('wc_get_page_permalink') ) {
        return '<p>WooCommerce is required for this login form.</p>';
    }

    // Shortcode attributen
    $a = shortcode_atts([
        'redirect' => '', // leeg = My Account
    ], $atts, 'gv_wc_login');

    $redirect_to = ! empty($a['redirect']) ? esc_url_raw($a['redirect']) : wc_get_page_permalink('myaccount');

    // Als de gebruiker al is ingelogd: toon link
    if ( is_user_logged_in() ) {
        return '<p>You are already logged in. Go to <a href="'.esc_url($redirect_to).'">My account</a>.</p>';
    }

    $notice = '';
    $posted_user  = '';
    $remember_val = false;

    // Formulier afhandeling
    if ( isset($_POST['gv_wc_login_submit']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'gv_wc_login_nonce') ) {
        $login    = sanitize_text_field( $_POST['gv_login'] ?? '' );
        $password = (string) ($_POST['gv_password'] ?? '' );
        $remember = ! empty($_POST['gv_remember']);
        $posted_user  = $login;
        $remember_val = $remember;

        $errors = [];
        if ( empty($login) )    { $errors[] = 'Please enter your email.'; }
        if ( empty($password) ) { $errors[] = 'Please enter your password.'; }

        // Indien e-mail ingevuld → omzettten naar gebruikersnaam
        if ( empty($errors) && is_email($login) ) {
            $user = get_user_by('email', $login);
            if ( $user ) {
                $login = $user->user_login;
            } else {
                $errors[] = 'No account found with that email address.';
            }
        }

        if ( empty($errors) ) {
            $creds = [
                'user_login'    => $login,
                'user_password' => $password,
                'remember'      => $remember,
            ];
            $user = wp_signon( $creds, is_ssl() );

            if ( is_wp_error($user) ) {
                $msg = $user->get_error_message() ?: 'Login failed. Please check your credentials and try again.';
                $notice = '<div style="color:#b00;">'.esc_html($msg).'</div>';
            } else {
                wp_safe_redirect( $redirect_to );
                exit;
            }
        } else {
            $notice = "<div style='color:#b00;'><ul><li>".implode('</li><li>', array_map('esc_html', $errors))."</li></ul></div>";
        }
    }

    // Lost password URL (WooCommerce indien aanwezig)
    $lost_url = function_exists('wc_lostpassword_url') ? wc_lostpassword_url() : wp_lostpassword_url();

    ob_start();
    echo $notice;
    ?>
    <form method="post" class="gv-wc-login-only" autocomplete="on">
        <?php wp_nonce_field('gv_wc_login_nonce'); ?>
        <p>
            <label>Email<br>
                <input type="text" name="gv_login" value="<?php echo esc_attr($posted_user); ?>" required>
            </label>
        </p>
        <p>
            <label>Password<br>
                <input type="password" name="gv_password" required>
            </label>
        </p>
        <p>
            <label>
                <input type="checkbox" name="gv_remember" <?php checked($remember_val, true); ?>>
                Remember me
            </label>
        </p>
        <input type="hidden" name="gv_redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
        <p>
            <input type="submit" name="gv_wc_login_submit" value="Log in">
		</p>
		<p style="margin-top:12px;">
			<a class="button" href="<?php echo esc_url($lost_url); ?>">Lost your password?</a>  <a class="button" href="https://geminivalve.nl/registration/">Register</a>
        </p>
		
    </form>
    <style>
      .gv-wc-login-only input[type="text"],
      .gv-wc-login-only input[type="email"],
      .gv-wc-login-only input[type="password"] { width:100%; max-width:420px; }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('gv_wc_login', 'gv_wc_login_shortcode');

/* (Optioneel) Laat alle standaard login-links naar je /login/ pagina wijzen
add_filter( 'login_url', function( $login_url, $redirect, $force_reauth ){
    $custom = home_url('/login/');
    if ( ! empty($redirect) ) {
        $custom = add_query_arg( 'redirect_to', urlencode( $redirect ), $custom );
    }
    return $custom;
}, 10, 3 );
*/


// ===== Snippet #43: MENU - Voeg account en uitloggen aan menu toe =====
// Scope: global | Priority: 10

/**
 * Voeg "Mijn account" menu-item toe als gebruiker is ingelogd (Blocksy + WooCommerce)
 * Kies hieronder je modus:
 *   - 'location'  -> target op menu-locatie (bijv. 'primary')
 *   - 'menu_name' -> target op specifieke menu-naam (zoals in Weergave > Menu's)
 *   - 'all'       -> voeg toe aan alle menus (handig als je twijfelt)
 */
add_filter('wp_nav_menu_items', function ($items, $args) {

    if ( ! is_user_logged_in() ) {
        return $items;
    }

    // === CONFIG ===
    $mode             = 'all';       // 'location' | 'menu_name' | 'all'
    $target_location  = 'primary';         // gebruik als $mode === 'location'
    $target_menu_name = 'Hoofdmenu';       // exact zoals je menunaam heet als $mode === 'menu_name'
    $label            = 'My account';    // linktekst
    $extra_classes    = 'menu-item-myaccount'; // css class voor styling

    // URL van My Account bepalen
    if ( function_exists('wc_get_page_permalink') ) {
        $myaccount_url = wc_get_page_permalink('myaccount');
    } else {
        $myaccount_url = home_url('/my-account/');
    }

    // Bepaal of we in dit menu moeten injecteren
    $inject = false;

    if ( $mode === 'all' ) {
        $inject = true;

    } elseif ( $mode === 'location' ) {
        if ( ! empty($args->theme_location) && $args->theme_location === $target_location ) {
            $inject = true;
        }

    } elseif ( $mode === 'menu_name' ) {
        // Blocksy Header Builder gebruikt vaak direct een specifieke menu-selectie (dan is theme_location leeg)
        $menu_obj = null;
        if ( is_object($args->menu) ) {
            $menu_obj = $args->menu;
        } elseif ( ! empty($args->menu) ) {
            $menu_obj = wp_get_nav_menu_object( $args->menu );
        }
        if ( $menu_obj && ! empty($menu_obj->name) && $menu_obj->name === $target_menu_name ) {
            $inject = true;
        }
        // fallback: als theme_location toch gevuld is en overeenkomt met 'primary'
        if ( ! $inject && ! empty($args->theme_location) && $args->theme_location === $target_location ) {
            $inject = true;
        }
    }

    if ( $inject ) {
        $items .= '<li class="menu-item menu-item-type-custom ' . esc_attr($extra_classes) . '">'
                . '<a href="' . esc_url($myaccount_url) . '">' . esc_html($label) . '</a>'
                . '</li>';
    }

    return $items;
}, 20, 2);
/*	
	$logout_url = wp_logout_url( home_url('/') );
	$items .= '<li class="menu-item menu-item-logout"><a href="'.esc_url($logout_url).'">Log out</a></li>';
*/


// ===== Snippet #46: Shipping rules =====
// Scope: global | Priority: 10


/**
 * Free shipping in NL above €500, otherwise €25 flat.
 * Threshold is based on cart subtotal AFTER coupons (excl. tax).
 * For NL only; other countries keep their normal methods.
 */

add_filter( 'woocommerce_package_rates', function( $rates, $package ) {
    if ( ! function_exists( 'WC' ) || empty( $package['destination']['country'] ) ) {
        return $rates;
    }

    // Settings
    $country_code      = 'NL';     // Apply rule only to Netherlands
    $free_threshold    = 500.00;   // euros
    $flat_cost         = 25.00;    // euros
    $hide_other_rates  = true;     // show only our method for NL

    // Only act for NL and when shipping is actually needed
    if ( strtoupper( $package['destination']['country'] ) !== $country_code || ( WC()->cart && ! WC()->cart->needs_shipping() ) ) {
        return $rates;
    }

    // Calculate subtotal AFTER coupons (excl. tax)
    $subtotal_ex_tax = WC()->cart ? (float) WC()->cart->get_subtotal() : 0.0;          // before coupons, excl. tax
    $discount_ex_tax = WC()->cart ? (float) WC()->cart->get_discount_total() : 0.0;    // excl. tax
    $effective       = max( 0.0, $subtotal_ex_tax - $discount_ex_tax );

    $is_free = $effective >= $free_threshold;

    // Build a single custom rate
    if ( ! class_exists( 'WC_Shipping_Rate' ) ) {
        return $rates; // safety
    }

    $method_id = 'gv_nl_shipping';
    $rate_id   = $method_id . ':' . ( $is_free ? 'free' : 'flat' );
    $label     = $is_free ? __( 'Free shipping (NL)', 'your-textdomain' )
                          : __( 'Standard shipping (NL)', 'your-textdomain' );
    $cost      = $is_free ? 0.0 : $flat_cost;

    $new_rate = new WC_Shipping_Rate( $rate_id, $label, $cost, array(), $method_id );

    // Apply shipping tax if your store taxes shipping
    if ( wc_tax_enabled() ) {
        $taxes = WC_Tax::calc_shipping_tax( $cost, WC_Tax::get_shipping_tax_rates() ); // respects your "Shipping tax class" setting
        $new_rate->set_taxes( $taxes );
    } else {
        $new_rate->set_taxes( false );
    }

    if ( $hide_other_rates ) {
        // Replace all NL rates with our single rate
        return array( $rate_id => $new_rate );
    }

    // Or, add alongside existing rates
    $rates[ $rate_id ] = $new_rate;
    return $rates;
}, 100, 2 );


// ===== Snippet #47: Add shopping card to menu when it has items =====
// Scope: global | Priority: 10


/**
 * Append a Cart item to ALL front-end menus.
 * - Hidden when cart is empty.
 * - Auto-updates via WooCommerce fragments (AJAX).
 * - Works with any theme (including Blocksy).
 */

if ( ! function_exists( 'gv_render_menu_cart_li' ) ) {
    function gv_render_menu_cart_li() {
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            // Print a hidden placeholder so fragments can replace it later
            $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
            return '<li class="menu-item gv-menu-cart-item is-empty" style="display:none"><a class="menu-cart-link" href="' . esc_url($cart_url) . '"><span class="cart-icon" aria-hidden="true">🛒</span><span class="cart-text">' . esc_html__('Cart', 'gv') . '</span><span class="cart-count">0</span></a></li>';
        }

        $count    = (int) WC()->cart->get_cart_contents_count();
        $is_empty = $count < 1;
        $cart_url = wc_get_cart_url();
        $label    = sprintf( _n('%d item', '%d items', $count, 'gv'), $count );

        ob_start(); ?>
        <li class="menu-item gv-menu-cart-item <?php echo $is_empty ? 'is-empty' : ''; ?>" style="<?php echo $is_empty ? 'display:none' : ''; ?>">
            <a class="menu-cart-link" href="<?php echo esc_url( $cart_url ); ?>"
               aria-label="<?php echo esc_attr( sprintf( __('View cart (%s)', 'gv'), $label ) ); ?>">
                <span class="cart-icon" aria-hidden="true">🛒</span>
                <span class="cart-text"><?php esc_html_e('Cart', 'gv'); ?></span>
                <span class="cart-count" aria-live="polite"><?php echo esc_html( $count ); ?></span>
            </a>
        </li>
        <?php
        return ob_get_clean();
    }
}

/** Append to ALL menus (front-end). */
add_filter( 'wp_nav_menu_items', function( $items, $args ) {
    // Avoid affecting admin screens
    if ( is_admin() && ! wp_doing_ajax() ) return $items;
    // Append once per rendered menu
    return $items . gv_render_menu_cart_li();
}, 20, 2 );

/** Handle fallback menus when no custom menu is assigned. */
add_filter( 'wp_page_menu', function( $menu ) {
    if ( is_admin() && ! wp_doing_ajax() ) return $menu;
    // Insert before the closing </ul>
    if ( false !== strpos( $menu, '</ul>' ) ) {
        $menu = str_replace( '</ul>', gv_render_menu_cart_li() . '</ul>', $menu );
    }
    return $menu;
}, 20 );

/** Woo fragments: update ALL cart items (class selector hits all instances). */
add_filter( 'woocommerce_add_to_cart_fragments', function( $fragments ) {
    $fragments['li.gv-menu-cart-item'] = gv_render_menu_cart_li();
    return $fragments;
});

/** Minimal styling (optional). */
add_action( 'wp_head', function() { ?>
    <style>
      .gv-menu-cart-item .menu-cart-link{display:inline-flex;align-items:center;gap:.45em}
      .gv-menu-cart-item .cart-count{
        display:inline-block;min-width:1.4em;padding:0 .45em;line-height:1.4em;font-size:.85em;
        border-radius:999px;background:#c00;color:#fff;text-align:center;font-weight:600
      }
      .gv-menu-cart-item .cart-icon{line-height:1}
      @media (max-width:600px){ .gv-menu-cart-item .cart-text{display:none} }
    </style>
<?php });


// ===== Snippet #48: Credentials - Register user - no password and validate login is approved =====
// Description: <p>Credentials - Register user - no password and validate login is approved</p>
// Scope: global | Priority: 10

// ===== Helper: bepaal default land (ISO2) o.b.v. gebruiker/sessie/instellingen =====
if ( ! function_exists( 'gv_wc_guess_default_country' ) ) {
    function gv_wc_guess_default_country( $fallback = 'NL' ) {
        $country = '';

        // 1) Ingelogde gebruiker
        if ( is_user_logged_in() ) {
            $uid = get_current_user_id();
            $country = get_user_meta( $uid, 'billing_country', true );
            if ( ! $country ) $country = get_user_meta( $uid, 'shipping_country', true );
        }

        // 2) WC customer sessie / geolocatie
        if ( ! $country && function_exists( 'WC' ) && WC()->customer ) {
            if ( method_exists( WC()->customer, 'get_billing_country' ) )  $country = WC()->customer->get_billing_country();
            if ( ! $country && method_exists( WC()->customer, 'get_shipping_country' ) ) $country = WC()->customer->get_shipping_country();
        }

        // 3) WooCommerce default customer location
        if ( ! $country && function_exists( 'wc_get_customer_default_location' ) ) {
            $loc = wc_get_customer_default_location(); // ['country'=>'XX','state'=>'YY']
            if ( ! empty( $loc['country'] ) ) $country = $loc['country'];
        }

        // 4) Store base country
        if ( ! $country && function_exists( 'WC' ) ) {
            $country = WC()->countries->get_base_country();
        }

        return $country ?: $fallback;
    }
}

// ===== Helper: haal toegestaan landen op (volgens Woo instellingen) =====
if ( ! function_exists( 'gv_wc_get_allowed_countries' ) ) {
    function gv_wc_get_allowed_countries() {
        if ( function_exists( 'WC' ) && WC()->countries ) {
            $allowed = WC()->countries->get_allowed_countries(); // respecteert "Selling locations"
            if ( empty( $allowed ) ) $allowed = WC()->countries->get_countries();
            return is_array( $allowed ) ? $allowed : [];
        }
        return [];
    }
}

// ===== Shortcode: [gv_wc_register redirect="/my-account/"] =====
add_shortcode( 'gv_wc_register', function( $atts = [] ) {
    if ( ! function_exists( 'wc_create_new_customer' ) ) {
        return '<div class="woocommerce"><div class="woocommerce-notices-wrapper"></div><p>WooCommerce is required for this form.</p></div>';
    }

    if ( is_user_logged_in() ) {
        $account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url('/');
        return '<div class="woocommerce"><p>' . esc_html__( 'You are already logged in.', 'gv-admin-approval' ) . ' <a href="' . esc_url( $account_url ) . '">' . esc_html__( 'My account', 'gv-admin-approval' ) . '</a></p></div>';
    }

    $a = shortcode_atts( [
        'redirect' => '',
    ], $atts );

    $countries    = gv_wc_get_allowed_countries();
    $auto_country = gv_wc_guess_default_country( 'NL' );

    // ==== Submit handling ====
    if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['gv_wc_register_nonce'] )
         && wp_verify_nonce( $_POST['gv_wc_register_nonce'], 'gv_wc_register' ) ) {

        // Honeypot
        if ( ! empty( $_POST['website'] ) ) {
            wc_add_notice( __( 'Something went wrong. Please try again.', 'gv-admin-approval' ), 'error' );
        } else {
            $errors = new WP_Error();

            // Basis (geen wachtwoordvelden meer)
            $email      = sanitize_email( $_POST['email'] ?? '' );
            $first_name = wc_clean( $_POST['first_name'] ?? '' );
            $last_name  = wc_clean( $_POST['last_name'] ?? '' );
            $company    = wc_clean( $_POST['billing_company'] ?? '' );
            $kvk        = wc_clean( $_POST['billing_kvk'] ?? '' );
            $vat        = wc_clean( $_POST['billing_vat'] ?? '' );
            $b_addr1    = wc_clean( $_POST['billing_address_1'] ?? '' );
            $b_addr2    = wc_clean( $_POST['billing_address_2'] ?? '' );
            $b_post     = wc_clean( $_POST['billing_postcode'] ?? '' );
            $b_city     = wc_clean( $_POST['billing_city'] ?? '' );
            $b_phone    = wc_clean( $_POST['billing_phone'] ?? '' );

            // Landen (valideren tegen lijst)
            $b_ctry = strtoupper( wc_clean( $_POST['billing_country'] ?? $auto_country ) );
            if ( empty( $b_ctry ) || ! isset( $countries[ $b_ctry ] ) ) {
                $errors->add( 'billing_country', __( 'Please select a valid billing country.', 'gv-admin-approval' ) );
            }

            $ship_diff = ! empty( $_POST['ship_to_different_address'] );
            $s_first   = wc_clean( $_POST['shipping_first_name'] ?? $first_name );
            $s_last    = wc_clean( $_POST['shipping_last_name'] ?? $last_name );
            $s_company = wc_clean( $_POST['shipping_company'] ?? $company );
            $s_addr1   = wc_clean( $_POST['shipping_address_1'] ?? $b_addr1 );
            $s_addr2   = wc_clean( $_POST['shipping_address_2'] ?? $b_addr2 );
            $s_post    = wc_clean( $_POST['shipping_postcode'] ?? $b_post );
            $s_city    = wc_clean( $_POST['shipping_city'] ?? $b_city );
            $s_ctry    = strtoupper( wc_clean( $_POST['shipping_country'] ?? $b_ctry ) );
            $s_email   = sanitize_email( $_POST['shipping_email'] ?? '' );

            if ( $ship_diff && ( empty( $s_ctry ) || ! isset( $countries[ $s_ctry ] ) ) ) {
                $errors->add( 'shipping_country', __( 'Please select a valid shipping country.', 'gv-admin-approval' ) );
            }

            // Validaties (email + naam + legal)
            if ( empty( $email ) || ! is_email( $email ) ) {
                $errors->add( 'email', __( 'Please enter a valid email address.', 'gv-admin-approval' ) );
            } elseif ( email_exists( $email ) ) {
                $errors->add( 'email_exists', __( 'An account already exists with that email address.', 'gv-admin-approval' ) );
            }

            if ( empty( $first_name ) ) $errors->add( 'first_name', __( 'First name is required.', 'gv-admin-approval' ) );
            if ( empty( $last_name ) )  $errors->add( 'last_name',  __( 'Last name is required.', 'gv-admin-approval' ) );

            // Woo legal
            $terms_page_id   = wc_terms_and_conditions_page_id();
            $privacy_page_id = function_exists( 'wc_privacy_policy_page_id' ) ? wc_privacy_policy_page_id() : 0;
            if ( $terms_page_id && empty( $_POST['agree_terms'] ) )     $errors->add( 'terms', __( 'Please agree to the terms and conditions.', 'gv-admin-approval' ) );
            if ( $privacy_page_id && empty( $_POST['agree_privacy'] ) ) $errors->add( 'privacy', __( 'Please agree to the privacy policy.', 'gv-admin-approval' ) );

            if ( $errors->has_errors() ) {
                foreach ( $errors->get_error_messages() as $msg ) wc_add_notice( $msg, 'error' );
            } else {
                // Maak klant met een sterke random password (wordt niet gebruikt, we sturen reset-link)
                $random_pw = wp_generate_password( 32, true, true );
                $user_id   = wc_create_new_customer( $email, '', $random_pw );

                if ( is_wp_error( $user_id ) ) {
                    wc_add_notice( $user_id->get_error_message(), 'error' );
                } else {
                    // Namen + display
                    wp_update_user( [
                        'ID'           => $user_id,
                        'first_name'   => $first_name,
                        'last_name'    => $last_name,
                        'display_name' => trim( $first_name . ' ' . $last_name ),
                    ] );

                    // Billing meta
                    update_user_meta( $user_id, 'billing_first_name', $first_name );
                    update_user_meta( $user_id, 'billing_last_name',  $last_name );
                    update_user_meta( $user_id, 'billing_company',    $company );
                    update_user_meta( $user_id, 'billing_address_1',  $b_addr1 );
                    update_user_meta( $user_id, 'billing_address_2',  $b_addr2 );
                    update_user_meta( $user_id, 'billing_postcode',   $b_post );
                    update_user_meta( $user_id, 'billing_city',       $b_city );
                    update_user_meta( $user_id, 'billing_country',    $b_ctry );
                    update_user_meta( $user_id, 'billing_phone',      $b_phone );
                    update_user_meta( $user_id, 'billing_email',      $email );

                    // Extra business
                    if ( $kvk ) update_user_meta( $user_id, 'billing_kvk', $kvk );
                    if ( $vat ) update_user_meta( $user_id, 'billing_vat', $vat );

                    // Shipping meta
                    update_user_meta( $user_id, 'shipping_first_name', $ship_diff ? $s_first : $first_name );
                    update_user_meta( $user_id, 'shipping_last_name',  $ship_diff ? $s_last  : $last_name );
                    update_user_meta( $user_id, 'shipping_company',    $ship_diff ? $s_company : $company );
                    update_user_meta( $user_id, 'shipping_address_1',  $ship_diff ? $s_addr1 : $b_addr1 );
                    update_user_meta( $user_id, 'shipping_address_2',  $ship_diff ? $s_addr2 : $b_addr2 );
                    update_user_meta( $user_id, 'shipping_postcode',   $ship_diff ? $s_post  : $b_post );
                    update_user_meta( $user_id, 'shipping_city',       $ship_diff ? $s_city  : $b_city );
                    update_user_meta( $user_id, 'shipping_country',    $ship_diff ? $s_ctry  : $b_ctry );

                    if ( $s_email ) update_user_meta( $user_id, 'shipping_email', $s_email );

                    // ---- Stuur "stel je wachtwoord in" e-mail via Woo mailer ----
                    $user = get_user_by( 'id', $user_id );
                    if ( $user && ! is_wp_error( $user ) ) {
                        // Maak reset key en Woo-account reset URL
                        $key       = get_password_reset_key( $user );
                        if ( ! is_wp_error( $key ) ) {
                            $lost_pw_base = wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) );
                            $reset_url    = add_query_arg(
                                [
                                    'key'   => rawurlencode( $key ),
                                    'login' => rawurlencode( $user->user_login ),
                                ],
                                $lost_pw_base
                            );

                            // E-mail (HTML) via Woo template
                            $mailer  = WC()->mailer();
                            $subject = sprintf( __( '[%s] Set your password', 'gv-admin-approval' ), wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
                            $heading = __( 'Create your password', 'gv-admin-approval' );

                            $message_html  = '<p>' . sprintf( esc_html__( 'Hi %s,', 'gv-admin-approval' ), esc_html( $first_name ?: $user->user_login ) ) . '</p>';
                            $message_html .= '<p>' . esc_html__( 'Thanks for registering. Please set your password using the button below:', 'gv-admin-approval' ) . '</p>';
                            $message_html .= '<p><a class="button" href="' . esc_url( $reset_url ) . '">' . esc_html__( 'Set your password', 'gv-admin-approval' ) . '</a></p>';
                            $message_html .= '<p>' . esc_html__( 'If you did not request this, you can ignore this email.', 'gv-admin-approval' ) . '</p>';

                            $mailer->send(
                                $email,
                                $subject,
                                $mailer->wrap_message( $heading, $message_html ),
                                '', // headers
                                []  // attachments
                            );
                        }
                    }

                    // Niet inloggen; toon melding
                    wc_add_notice(
                        __( 'Thanks for registering. Check your email to set your password. Your account is pending approval if applicable; we will email you once it’s approved.', 'gv-admin-approval' ),
                        'success'
                    );

                    if ( ! empty( $a['redirect'] ) ) {
                        wp_safe_redirect( esc_url_raw( $a['redirect'] ) );
                        exit;
                    }
                }
            }
        }
    }

    // Helper om velden te repopulaten in de markup
    $val = function( $key, $default = '' ) {
        return isset( $_POST[ $key ] ) ? esc_attr( wp_unslash( $_POST[ $key ] ) ) : esc_attr( $default );
    };

    // Voorselecties
    $sel_billing  = $val( 'billing_country',  $auto_country );
    $sel_shipping = $val( 'shipping_country', $auto_country );

    ob_start(); ?>
    <div class="woocommerce">
      <div class="woocommerce-notices-wrapper"><?php wc_print_notices(); ?></div>

      <form class="woocommerce-form woocommerce-form-register register" method="post" novalidate>
        <?php wp_nonce_field( 'gv_wc_register', 'gv_wc_register_nonce' ); ?>
        <input type="text" name="website" value="" style="position:absolute; left:-9999px; height:1px; width:1px;" tabindex="-1" autocomplete="off" />

        <h3><?php esc_html_e( 'Account details', 'gv-admin-approval' ); ?></h3>
        <p class="form-row form-row-first">
          <label for="first_name"><?php esc_html_e( 'First name', 'gv-admin-approval' ); ?> *</label>
          <input type="text" class="input-text" name="first_name" id="first_name" value="<?php echo $val('first_name'); ?>" required />
        </p>
        <p class="form-row form-row-last">
          <label for="last_name"><?php esc_html_e( 'Last name', 'gv-admin-approval' ); ?> *</label>
          <input type="text" class="input-text" name="last_name" id="last_name" value="<?php echo $val('last_name'); ?>" required />
        </p>
        <p class="form-row form-row-wide">
          <label for="email"><?php esc_html_e( 'Email address', 'gv-admin-approval' ); ?> *</label>
          <input type="email" class="input-text" name="email" id="email" value="<?php echo $val('email'); ?>" required />
        </p>

        <h3><?php esc_html_e( 'Billing address', 'gv-admin-approval' ); ?></h3>
        <p class="form-row form-row-first">
          <label for="billing_company"><?php esc_html_e( 'Company', 'gv-admin-approval' ); ?></label>
          <input type="text" class="input-text" name="billing_company" id="billing_company" value="<?php echo $val('billing_company'); ?>" />
        </p>
        <p class="form-row form-row-last">
          <label for="billing_phone"><?php esc_html_e( 'Phone', 'gv-admin-approval' ); ?></label>
          <input type="text" class="input-text" name="billing_phone" id="billing_phone" value="<?php echo $val('billing_phone'); ?>" />
        </p>
        <p class="form-row form-row-first">
          <label for="billing_kvk"><?php esc_html_e( 'Company registration ID', 'gv-admin-approval' ); ?></label>
          <input type="text" class="input-text" name="billing_kvk" id="billing_kvk" value="<?php echo $val('billing_kvk'); ?>" />
        </p>
        <p class="form-row form-row-last">
          <label for="billing_vat"><?php esc_html_e( 'TAX ID', 'gv-admin-approval' ); ?></label>
          <input type="text" class="input-text" name="billing_vat" id="billing_vat" value="<?php echo $val('billing_vat'); ?>" />
        </p>
        <p class="form-row form-row-wide">
          <label for="billing_address_1"><?php esc_html_e( 'Address line 1', 'gv-admin-approval' ); ?></label>
          <input type="text" class="input-text" name="billing_address_1" id="billing_address_1" value="<?php echo $val('billing_address_1'); ?>" />
        </p>
        <p class="form-row form-row-wide">
          <label for="billing_address_2"><?php esc_html_e( 'Address line 2', 'gv-admin-approval' ); ?></label>
          <input type="text" class="input-text" name="billing_address_2" id="billing_address_2" value="<?php echo $val('billing_address_2'); ?>" />
        </p>
        <p class="form-row form-row-first">
          <label for="billing_postcode"><?php esc_html_e( 'Postcode', 'gv-admin-approval' ); ?></label>
          <input type="text" class="input-text" name="billing_postcode" id="billing_postcode" value="<?php echo $val('billing_postcode'); ?>" />
        </p>
        <p class="form-row form-row-last">
          <label for="billing_city"><?php esc_html_e( 'City', 'gv-admin-approval' ); ?></label>
          <input type="text" class="input-text" name="billing_city" id="billing_city" value="<?php echo $val('billing_city'); ?>" />
        </p>

        <p class="form-row form-row-wide">
          <label for="billing_country"><?php esc_html_e( 'Country', 'gv-admin-approval' ); ?></label>
          <select class="country_to_state country_select" name="billing_country" id="billing_country">
            <?php foreach ( $countries as $code => $name ) : ?>
              <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $sel_billing ); ?>><?php echo esc_html( $name ); ?></option>
            <?php endforeach; ?>
          </select>
        </p>

        <p class="form-row">
          <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
            <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="ship_to_different_address" type="checkbox" value="1" <?php checked( ! empty( $_POST['ship_to_different_address'] ) ); ?> />
            <span><?php esc_html_e( 'Ship to a different address?', 'gv-admin-approval' ); ?></span>
          </label>
        </p>

        <div id="shipping_fields" style="<?php echo empty( $_POST['ship_to_different_address'] ) ? 'display:none' : '' ; ?>">
          <h3><?php esc_html_e( 'Shipping address', 'gv-admin-approval' ); ?></h3>
          <p class="form-row form-row-first">
            <label for="shipping_first_name"><?php esc_html_e( 'First name', 'gv-admin-approval' ); ?></label>
            <input type="text" class="input-text" name="shipping_first_name" id="shipping_first_name" value="<?php echo $val('shipping_first_name'); ?>" />
          </p>
          <p class="form-row form-row-last">
            <label for="shipping_last_name"><?php esc_html_e( 'Last name', 'gv-admin-approval' ); ?></label>
            <input type="text" class="input-text" name="shipping_last_name" id="shipping_last_name" value="<?php echo $val('shipping_last_name'); ?>" />
          </p>
          <p class="form-row form-row-wide">
            <label for="shipping_company"><?php esc_html_e( 'Company', 'gv-admin-approval' ); ?></label>
            <input type="text" class="input-text" name="shipping_company" id="shipping_company" value="<?php echo $val('shipping_company'); ?>" />
          </p>
          <p class="form-row form-row-wide">
            <label for="shipping_address_1"><?php esc_html_e( 'Address line 1', 'gv-admin-approval' ); ?></label>
            <input type="text" class="input-text" name="shipping_address_1" id="shipping_address_1" value="<?php echo $val('shipping_address_1'); ?>" />
          </p>
          <p class="form-row form-row-wide">
            <label for="shipping_address_2"><?php esc_html_e( 'Address line 2', 'gv-admin-approval' ); ?></label>
            <input type="text" class="input-text" name="shipping_address_2" id="shipping_address_2" value="<?php echo $val('shipping_address_2'); ?>" />
          </p>
          <p class="form-row form-row-first">
            <label for="shipping_postcode"><?php esc_html_e( 'Postcode', 'gv-admin-approval' ); ?></label>
            <input type="text" class="input-text" name="shipping_postcode" id="shipping_postcode" value="<?php echo $val('shipping_postcode'); ?>" />
          </p>
          <p class="form-row form-row-last">
            <label for="shipping_city"><?php esc_html_e( 'City', 'gv-admin-approval' ); ?></label>
            <input type="text" class="input-text" name="shipping_city" id="shipping_city" value="<?php echo $val('shipping_city'); ?>" />
          </p>
          <p class="form-row form-row-wide">
            <label for="shipping_country"><?php esc_html_e( 'Country', 'gv-admin-approval' ); ?></label>
            <select class="country_to_state country_select" name="shipping_country" id="shipping_country">
              <?php foreach ( $countries as $code => $name ) : ?>
                <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $code, $sel_shipping ); ?>><?php echo esc_html( $name ); ?></option>
              <?php endforeach; ?>
            </select>
          </p>
          <p class="form-row form-row-wide">
            <label for="shipping_email"><?php esc_html_e( 'Shipping email (optional)', 'gv-admin-approval' ); ?></label>
            <input type="email" class="input-text" name="shipping_email" id="shipping_email" value="<?php echo $val('shipping_email'); ?>" />
          </p>
        </div>

        <?php if ( wc_terms_and_conditions_page_id() ) : ?>
          <p class="form-row">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
              <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="agree_terms" type="checkbox" value="1" <?php checked( ! empty( $_POST['agree_terms'] ) ); ?> />
              <span><?php wc_terms_and_conditions_checkbox_text(); ?></span>
            </label>
          </p>
        <?php endif; ?>

        <?php if ( function_exists( 'wc_privacy_policy_page_id' ) && wc_privacy_policy_page_id() ) : ?>
          <p class="form-row">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
              <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="agree_privacy" type="checkbox" value="1" <?php checked( ! empty( $_POST['agree_privacy'] ) ); ?> />
              <span><?php printf( wp_kses_post( __( 'I agree to the <a href="%s" target="_blank" rel="noopener">privacy policy</a>.', 'gv-admin-approval' ) ), esc_url( get_permalink( wc_privacy_policy_page_id() ) ) ); ?></span>
            </label>
          </p>
        <?php endif; ?>

        <p class="form-row">
          <button type="submit" class="woocommerce-Button button"><?php esc_html_e( 'Register', 'gv-admin-approval' ); ?></button>
        </p>
      </form>
    </div>

    <script>
      (function(){
        var cb = document.querySelector('input[name="ship_to_different_address"]');
        var box = document.getElementById('shipping_fields');
        if (cb && box) {
          cb.addEventListener('change', function(){ box.style.display = this.checked ? '' : 'none'; });
        }
      })();
    </script>
    <?php
    return ob_get_clean();
} );


// ===== Snippet #49: Reorder sippets - all snippets in default desc modified =====
// Scope: global | Priority: 10

/**
 * Default order for Snippets list in admin:
 * Order by last modified date (DESC).
 */
add_filter( 'request', function( $vars ) {
    if ( ! is_admin() ) {
        return $vars;
    }

    global $pagenow;
    if ( 'edit.php' !== $pagenow ) {
        return $vars;
    }

    // Which post types to target (Code Snippets, legacy, WPCode)
    $snippet_types = array( 'code-snippet', 'snippet', 'wpcode' );

    $post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : 'post';
    if ( ! in_array( $post_type, $snippet_types, true ) ) {
        return $vars;
    }

    // Only set defaults if the user hasn’t explicitly chosen
    if ( empty( $_GET['orderby'] ) && empty( $vars['orderby'] ) ) {
        $vars['orderby'] = 'modified';
    }
    if ( empty( $_GET['order'] ) && empty( $vars['order'] ) ) {
        $vars['order'] = 'DESC';
    }

    return $vars;
} );


// ===== Snippet #50: Cookie consent banner =====
// Scope: global | Priority: 10

/**
 * Cookie Consent Banner for Gemini Valve
 */
function gv_cookie_consent_banner() {
    ?>
    <div id="gv-cookie-banner" style="position:fixed;bottom:0;left:0;width:100%;background:#f0e4d3;color:#000;padding:15px;z-index:9999;display:none;font-family:sans-serif;box-shadow:0 -2px 5px rgba(0,0,0,.2);">
        <p style="margin:0 0 10px 0;">
            Wij gebruiken cookies om onze website goed te laten werken, het verkeer te analyseren en uw winkelervaring te verbeteren. 
            Met uw toestemming gebruiken wij ook cookies voor gepersonaliseerde aanbiedingen en marketing. 
            Meer informatie vindt u in ons <a href="/cookie-policy" style="color:#891734;font-weight:bold;">Cookiebeleid</a>.
        </p>
        <button onclick="gvAcceptCookies()" style="background:#891734;color:#fff;border:none;padding:8px 15px;margin-right:10px;border-radius:4px;cursor:pointer;">Alle cookies accepteren</button>
        <button onclick="gvDenyCookies()" style="background:#ccc;color:#000;border:none;padding:8px 15px;margin-right:10px;border-radius:4px;cursor:pointer;">Alleen noodzakelijke cookies</button>
    </div>

    <script>
    // Show banner if no choice yet
    document.addEventListener("DOMContentLoaded", function() {
        if (!getCookie("gv_cookie_consent")) {
            document.getElementById("gv-cookie-banner").style.display = "block";
        } else {
            if (getCookie("gv_cookie_consent") === "accepted") {
                gvLoadAnalytics();
            }
        }
    });

    function gvAcceptCookies() {
        setCookie("gv_cookie_consent", "accepted", 365);
        document.getElementById("gv-cookie-banner").style.display = "none";
        gvLoadAnalytics();
    }

    function gvDenyCookies() {
        setCookie("gv_cookie_consent", "denied", 365);
        document.getElementById("gv-cookie-banner").style.display = "none";
    }

    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    // Load Google Analytics (or other scripts) only if consent is accepted
    function gvLoadAnalytics() {
        // Example: Google Analytics GA4
        var s = document.createElement('script');
        s.async = true;
        s.src = "https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"; // Replace with your GA ID
        document.head.appendChild(s);

        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-D919Q96JBK');
    }
    </script>
    <?php
}
add_action('wp_footer', 'gv_cookie_consent_banner');


// ===== Snippet #52: EMAIL - Adjust email confirmation on processing =====
// Scope: global | Priority: 10

/**
 * WooCommerce: Add custom fields to the "Processing order" email (customer only)
 * - Fields shown only when they have a value
 * - Scheduled delivery formatted as dd-mm-yyyy
 */
add_action( 'woocommerce_email_after_order_table', 'gv_add_custom_fields_to_processing_email', 20, 4 );
function gv_add_custom_fields_to_processing_email( $order, $sent_to_admin, $plain_text, $email ) {

    if ( ! $order instanceof WC_Order ) return;

    // Only for the customer "processing" email
    if ( empty( $email->id ) || $email->id !== 'customer_processing_order' ) {
        return;
    }

    // Map: Label => array of candidate meta keys (first non-empty wins)
    $fields = [
        'Reference'          => [ 'reference', '_reference', 'customer_reference', 'order_reference', '_order_reference', 'po_reference' ],
        'Order'              => [ 'order', 'customer_order', 'po_number', 'purchase_order', '_purchase_order', '_po_number','melding_invoice' ],
        'Scheduled delivery' => [ 'scheduled_delivery', '_scheduled_delivery', 'delivery_date', '_delivery_date', 'shipping_delivery_date', '_shipping_delivery_date' ],
    ];

    $resolved = [];
    foreach ( $fields as $label => $keys ) {
        $value = '';
        foreach ( $keys as $key ) {
            $maybe = $order->get_meta( $key );
            if ( is_string( $maybe ) )  { $maybe = trim( $maybe ); }
            if ( ! empty( $maybe ) ) {
                $value = $maybe;
                break;
            }
        }

        if ( $value !== '' ) {
            // Special formatting for Scheduled delivery date
            if ( $label === 'Scheduled delivery' ) {
                $timestamp = strtotime( $value );
                if ( $timestamp ) {
                    $value = date( 'd-m-Y', $timestamp ); // force dd-mm-yyyy
                }
            }
            $resolved[ $label ] = $value;
        }
    }

    if ( empty( $resolved ) ) return;

    // Output
    if ( $plain_text ) {
        echo "\n";
        echo "— Additional order details —\n";
        foreach ( $resolved as $label => $value ) {
            echo "{$label}: {$value}\n";
        }
        echo "\n";
    } else {
        echo '<div style="margin-top:20px">';
        echo '<h3 style="margin:0 0 8px 0;">Additional order details</h3>';
        echo '<table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;">';
        foreach ( $resolved as $label => $value ) {
            echo '<tr>';
            echo '<td style="padding:6px 0;font-weight:600;">' . esc_html( $label ) . ':</td>';
            echo '<td style="padding:6px 0;">' . esc_html( $value ) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }
}


// ===== Snippet #56: Products - update multiple product sales prices at the same time =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - WooCommerce Procurement Bulk Pricing
 * Description: Adds an admin page to bulk update product regular prices by supplier (or all) based on cost price and defined multipliers.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Get suppliers: reuse gv_procurement_suppliers() if present, else fallback.
 */
function gv_proc_bulk_get_suppliers() {
    if ( function_exists( 'gv_procurement_suppliers' ) ) {
        $opts = gv_procurement_suppliers(); // expected to include '' => 'Select a supplier'
        // Replace the empty option label to be clearer on the bulk page
        $opts[''] = __( 'All suppliers', 'gv' );
        return $opts;
    }
    // Fallback set (same as your main plugin)
    return array(
        ''                     => __( 'All suppliers', 'gv' ),
        'Gemini Valve'         => 'Gemini Valve',
        'Syveco'               => 'Syveco',
        'FJV'                  => 'FJV',
        'Rapidrop'             => 'Rapidrop',
        'Sea Metal Industries' => 'Sea Metal Industries',
        'Trutorq'              => 'Trutorq',
        'Wagner Gasket'        => 'Wagner Gasket',
        'WOD'                  => 'WOD',
    );
}

/**
 * Multipliers by supplier.
 */
function gv_proc_bulk_get_multipliers() {
    return array(
        'Syveco'       => 1.7,
        'Trutorq'      => 1.7,
        'Gemini Valve' => 2.0,
        'Rapidrop'     => 2.0,
        // default: 2.4 for everything else
    );
}

/**
 * Add submenu under WooCommerce.
 */
add_action( 'admin_menu', function() {
    add_submenu_page(
        'woocommerce',
        __( 'Procurement Pricing', 'gv' ),
        __( 'Procurement Pricing', 'gv' ),
        'manage_woocommerce',
        'gv-procurement-pricing',
        'gv_proc_bulk_render_page'
    );
} );

/**
 * Render page + handle form submit.
 */
function gv_proc_bulk_render_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( __( 'You do not have permission to access this page.', 'gv' ) );
    }

    $suppliers = gv_proc_bulk_get_suppliers();
    $selected  = isset( $_POST['gv_supplier'] ) ? sanitize_text_field( wp_unslash( $_POST['gv_supplier'] ) ) : '';
    $did_run   = false;
    $result    = array();

    if ( isset( $_POST['gv_proc_bulk_submit'] ) ) {
        check_admin_referer( 'gv_proc_bulk_run', 'gv_proc_bulk_nonce' );
        $did_run = true;
        $result  = gv_proc_bulk_run_update( $selected );
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Procurement Bulk Pricing', 'gv' ); ?></h1>
        <p><?php esc_html_e( 'Update Regular Price from Cost Price using supplier-specific multipliers.', 'gv' ); ?></p>

        <form method="post">
            <?php wp_nonce_field( 'gv_proc_bulk_run', 'gv_proc_bulk_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="gv_supplier"><?php esc_html_e( 'Supplier', 'gv' ); ?></label></th>
                    <td>
                        <select name="gv_supplier" id="gv_supplier">
                            <?php foreach ( $suppliers as $val => $label ) : ?>
                                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $selected, $val ); ?>>
                                    <?php echo esc_html( $label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Choose a supplier to update only those products, or select "All suppliers".', 'gv' ); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button( __( 'Run Update', 'gv' ), 'primary', 'gv_proc_bulk_submit' ); ?>
        </form>

        <?php if ( $did_run ) : ?>
            <hr />
            <h2><?php esc_html_e( 'Results', 'gv' ); ?></h2>
            <ul>
                <li><strong><?php esc_html_e( 'Supplier filter:', 'gv' ); ?></strong> <?php echo $selected === '' ? esc_html__( 'All suppliers', 'gv' ) : esc_html( $selected ); ?></li>
                <li><strong><?php esc_html_e( 'Processed products:', 'gv' ); ?></strong> <?php echo intval( $result['processed'] ?? 0 ); ?></li>
                <li><strong><?php esc_html_e( 'Updated prices:', 'gv' ); ?></strong> <?php echo intval( $result['updated'] ?? 0 ); ?></li>
                <li><strong><?php esc_html_e( 'Skipped (no cost price):', 'gv' ); ?></strong> <?php echo intval( $result['skipped_no_cost'] ?? 0 ); ?></li>
                <li><strong><?php esc_html_e( 'Skipped (no supplier match):', 'gv' ); ?></strong> <?php echo intval( $result['skipped_supplier'] ?? 0 ); ?></li>
                <li><strong><?php esc_html_e( 'Errors:', 'gv' ); ?></strong> <?php echo intval( $result['errors'] ?? 0 ); ?></li>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Core runner: loops products in batches and updates regular price based on cost & supplier.
 */
function gv_proc_bulk_run_update( $supplier_filter = '' ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return array( 'processed' => 0, 'updated' => 0, 'skipped_no_cost' => 0, 'skipped_supplier' => 0, 'errors' => 0 );
    }

    @set_time_limit( 0 );
    @ini_set( 'memory_limit', '512M' );

    $counts = array(
        'processed'        => 0,
        'updated'          => 0,
        'skipped_no_cost'  => 0,
        'skipped_supplier' => 0,
        'errors'           => 0,
    );

    $multipliers = gv_proc_bulk_get_multipliers();

    $paged = 1;
    $per_page = 200;

    // Process both products and variations (in case procurement meta is set there)
    $post_types = array( 'product', 'product_variation' );

    while ( true ) {
        $q = new WP_Query( array(
            'post_type'      => $post_types,
            'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
            'fields'         => 'ids',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'no_found_rows'  => true,
        ) );

        if ( ! $q->have_posts() ) {
            break;
        }

        foreach ( $q->posts as $pid ) {
            $counts['processed']++;

            $prod = wc_get_product( $pid );
            if ( ! $prod ) {
                $counts['errors']++;
                continue;
            }

            // Read procurement meta
            $supplier   = $prod->get_meta( '_gv_proc_supplier' );
            $cost_price = $prod->get_meta( '_gv_proc_cost_price' );

            // Supplier filter (if set)
            if ( $supplier_filter !== '' && $supplier !== $supplier_filter ) {
                $counts['skipped_supplier']++;
                continue;
            }

            // Need a numeric cost price
            if ( $cost_price === '' || ! is_numeric( $cost_price ) ) {
                $counts['skipped_no_cost']++;
                continue;
            }

            // Determine multiplier (default 2.4)
            $multiplier = isset( $multipliers[ $supplier ] ) ? $multipliers[ $supplier ] : 2.4;
            $new_price  = floatval( $cost_price ) * $multiplier;

            // Only save if changed (avoid unnecessary writes)
            $current_regular = $prod->get_regular_price();
            $formatted_new   = wc_format_decimal( $new_price );

            if ( $current_regular !== $formatted_new ) {
                $prod->set_regular_price( $formatted_new );

                // If sale price exists and is higher than the new regular, clear it
                $sale = $prod->get_sale_price();
                if ( $sale !== '' && floatval( $sale ) > floatval( $formatted_new ) ) {
                    $prod->set_sale_price( '' );
                }

                try {
                    $prod->save();
                    $counts['updated']++;
                } catch ( \Throwable $e ) {
                    $counts['errors']++;
                }
            }
        }

        wp_reset_postdata();
        $paged++;
    }

    return $counts;
}


// ===== Snippet #61: Product - update product regular price when updated and price is empty =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - WooCommerce Supplier Pricing
 * Description: Auto-calculate Regular Price from supplier margin % and cost price when saving products.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'woocommerce_admin_process_product_object', function( $product ) {
    // Figure out which meta key holds supplier id (depends on GV Suppliers Manager)
    $supplier_meta_key = class_exists('GV_Suppliers_Manager') 
        ? GV_Suppliers_Manager::PRODUCT_SUPPLIER_ID 
        : '_gv_proc_supplier_id';

    $supplier_id = (int) $product->get_meta( $supplier_meta_key );
    $cost        = $product->get_meta( '_gv_proc_cost_price' );

    if ( ! $supplier_id || $cost === '' || ! is_numeric( $cost ) ) {
        return; // nothing to do
    }

    // Get supplier margin (%)
    $margin = get_post_meta( $supplier_id, '_gv_margin_percent', true );
    if ( $margin === '' ) return;

    $margin = (float) $margin;

    // Convert margin % on sales price → multiplier
    // Example: 30% margin → 1 / (1 - 0.30) = 1.4286
    $multiplier = ( $margin >= 100 ) ? 999999 : ( 1 / ( 1 - ( $margin / 100 ) ) );

    $regular = round( (float) $cost * $multiplier, 2 );

    // Only set Regular Price if empty (don’t overwrite manual prices)
    if ( $product->get_regular_price() === '' ) {
        $product->set_regular_price( $regular );
    }
}, 20 );


// ===== Snippet #62: Products - bulk update tool with overwrite option =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - Supplier Pricing Bulk Tool
 * Description: Bulk update WooCommerce Regular Prices from supplier margin % and cost price. Includes overwrite option.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** ----- Helpers shared with your other snippets ----- */

/** Determine which meta key holds the supplier ID on products */
function gv_spb_supplier_meta_key() {
    if ( class_exists('GV_Suppliers_Manager') ) {
        return GV_Suppliers_Manager::PRODUCT_SUPPLIER_ID;
    }
    return '_gv_proc_supplier_id';
}

/** Get all published suppliers (CPT) for dropdown */
function gv_spb_get_suppliers() {
    return get_posts([
        'post_type'        => 'gv_supplier',
        'post_status'      => 'publish',
        'numberposts'      => -1,
        'orderby'          => 'title',
        'order'            => 'ASC',
        'suppress_filters' => false,
    ]);
}

/** Convert margin % on sales price to multiplier (e.g. 30 -> 1/(1-0.30) = 1.4286) */
function gv_spb_margin_to_multiplier( $margin_percent ) {
    $m = (float) $margin_percent;
    if ( $m >= 100 ) return 999999;
    if ( $m <= 0 )   return 1.0;
    return 1 / ( 1 - ( $m / 100 ) );
}

/** Compute regular price from product’s supplier + cost */
function gv_spb_compute_regular_from_supplier( $product ) {
    $supplier_meta_key = gv_spb_supplier_meta_key();
    $supplier_id = (int) $product->get_meta( $supplier_meta_key );
    $cost        = $product->get_meta( '_gv_proc_cost_price' );

    if ( ! $supplier_id || $cost === '' || ! is_numeric( $cost ) ) {
        return null; // insufficient data
    }

    $margin = get_post_meta( $supplier_id, '_gv_margin_percent', true );
    if ( $margin === '' ) return null;

    $multiplier = gv_spb_margin_to_multiplier( (float) $margin );
    return round( (float) $cost * $multiplier, 2 );
}

/** ----- Admin UI ----- */
// moved to seperate snippet! 
// 
/*
add_action( 'admin_menu', function() {
    // Under WooCommerce menu
    add_submenu_page(
        'gv-bulk-edit',
        __( 'Supplier Pricing Bulk', 'gv-spb' ),
        __( 'Supplier Pricing Bulk', 'gv-spb' ),
        'manage_woocommerce',
        'gv-supplier-pricing-bulk',
        'gv_spb_render_page'
    );
});
*/

function gv_spb_render_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( __( 'You do not have permission to access this page.', 'gv-spb' ) );
    }

    $action_url = admin_url( 'admin-post.php' );
    $suppliers  = gv_spb_get_suppliers();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Supplier Pricing Bulk Update', 'gv-spb' ); ?></h1>
        <p><?php esc_html_e( 'Update Regular Prices based on Supplier margin % and Cost Price.', 'gv-spb' ); ?></p>

        <form method="post" action="<?php echo esc_url( $action_url ); ?>">
            <?php wp_nonce_field( 'gv_spb_run', 'gv_spb_nonce' ); ?>
            <input type="hidden" name="action" value="gv_spb_run">

            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row"><label for="gv_spb_supplier_id"><?php esc_html_e( 'Supplier', 'gv-spb' ); ?></label></th>
                    <td>
                        <select id="gv_spb_supplier_id" name="supplier_id">
                            <option value=""><?php esc_html_e( 'All suppliers', 'gv-spb' ); ?></option>
                            <?php foreach ( $suppliers as $s ): ?>
                                <option value="<?php echo (int) $s->ID; ?>">
                                    <?php echo esc_html( $s->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Only update products linked to this supplier. Leave empty to update all suppliers.', 'gv-spb' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gv_spb_overwrite"><?php esc_html_e( 'Overwrite Regular Price', 'gv-spb' ); ?></label></th>
                    <td>
                        <label>
                            <input type="checkbox" id="gv_spb_overwrite" name="overwrite" value="1">
                            <?php esc_html_e( 'Yes, overwrite existing regular prices', 'gv-spb' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'If unchecked, only products with an empty regular price will be updated.', 'gv-spb' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gv_spb_limit"><?php esc_html_e( 'Batch size (optional)', 'gv-spb' ); ?></label></th>
                    <td>
                        <input type="number" id="gv_spb_limit" name="limit" min="1" step="1" placeholder="e.g. 300">
                        <p class="description"><?php esc_html_e( 'Process up to this many products in one run to avoid timeouts. Leave empty for all.', 'gv-spb' ); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php submit_button( __( 'Run Update', 'gv-spb' ) ); ?>
        </form>
    </div>
    <?php
}

/** ----- Runner ----- */
add_action( 'admin_post_gv_spb_run', function() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( __( 'Insufficient permissions.', 'gv-spb' ) );
    }
    if ( ! isset( $_POST['gv_spb_nonce'] ) || ! wp_verify_nonce( $_POST['gv_spb_nonce'], 'gv_spb_run' ) ) {
        wp_die( __( 'Security check failed.', 'gv-spb' ) );
    }

    $supplier_filter = isset( $_POST['supplier_id'] ) ? (int) $_POST['supplier_id'] : 0;
    $overwrite       = ! empty( $_POST['overwrite'] );
    $limit           = isset( $_POST['limit'] ) && is_numeric( $_POST['limit'] ) ? (int) $_POST['limit'] : 0;

    // Build product query
    $supplier_meta_key = gv_spb_supplier_meta_key();
    $meta_query = [
        'relation' => 'AND',
        [
            'key'     => $supplier_meta_key,
            'compare' => 'EXISTS',
        ],
    ];
    if ( $supplier_filter > 0 ) {
        $meta_query[] = [
            'key'   => $supplier_meta_key,
            'value' => $supplier_filter,
        ];
    }

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'meta_query'     => $meta_query,
    ];

    $product_ids = get_posts( $args );

    $updated = 0;
    $skipped = 0;
    $failed  = 0;

    foreach ( $product_ids as $pid ) {
        $product = wc_get_product( $pid );
        if ( ! $product ) { $skipped++; continue; }

        // If variable, also process variations
        $to_process = [ $product ];
        if ( $product->is_type( 'variable' ) ) {
            $children = $product->get_children();
            foreach ( $children as $vid ) {
                $v = wc_get_product( $vid );
                if ( $v ) $to_process[] = $v;
            }
        }

        foreach ( $to_process as $p ) {
            $new_regular = gv_spb_compute_regular_from_supplier( $p );
            if ( $new_regular === null ) { $skipped++; continue; }

            $current_regular = $p->get_regular_price();

            if ( $overwrite || $current_regular === '' ) {
                try {
                    $p->set_regular_price( $new_regular );
                    $p->save();
                    $updated++;
                } catch ( Exception $e ) {
                    $failed++;
                }
            } else {
                $skipped++;
            }
        }
    }

    // Redirect back with admin notice
    $args = [
        'page'          => 'gv-supplier-pricing-bulk',
        'gv_spb_done'   => 1,
        'updated_count' => $updated,
        'skipped_count' => $skipped,
        'failed_count'  => $failed,
        'supplier_id'   => $supplier_filter,
        'overwrite'     => $overwrite ? 1 : 0,
        'limit'         => $limit,
    ];
    $url = add_query_arg( $args, admin_url( 'admin.php' ) );
    wp_safe_redirect( $url );
    exit;
});

/** Show result notice */
add_action( 'admin_notices', function() {
    if ( ! isset($_GET['page']) || $_GET['page'] !== 'gv-supplier-pricing-bulk' ) return;
    if ( empty( $_GET['gv_spb_done'] ) ) return;

    $updated = isset($_GET['updated_count']) ? (int) $_GET['updated_count'] : 0;
    $skipped = isset($_GET['skipped_count']) ? (int) $_GET['skipped_count'] : 0;
    $failed  = isset($_GET['failed_count'])  ? (int) $_GET['failed_count']  : 0;

    printf(
        '<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s %d, %s %d, %s %d.</p></div>',
        esc_html__( 'Supplier Pricing Bulk Update finished.', 'gv-spb' ),
        esc_html__( 'Updated:', 'gv-spb' ), $updated,
        esc_html__( 'Skipped:', 'gv-spb' ), $skipped,
        esc_html__( 'Failed:',  'gv-spb' ), $failed
    );
});


// ===== Snippet #63: Products - Bulk update product supplier information =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - Procurement Paste Import (Matrix)
 * Description: Paste rows from Excel/CSV into a web matrix, map columns, and update procurement fields on products.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/** ===== Admin Menu ===== */
add_action( 'admin_menu', function() {
   /* add_submenu_page(
        'woocommerce',
        __( 'Procurement Paste Import', 'gv-paste' ),
        __( 'Procurement Paste Import', 'gv-paste' ),
        'manage_woocommerce',
        'gv-procurement-paste-import',
        'gv_pi_render_page'
    );*/
	/** ===== Admin Menu (Bulk Edit parent) ===== */
/** ===== Admin Menu (robust "Bulk Edit" parent just under WooCommerce) ===== */
	//Moved to seperate Snippet! 
	/*
add_action( 'admin_menu', function() {
    // 1) Add top-level "Bulk Edit" menu
    if ( ! function_exists('gv_be_landing_page') ) {
        function gv_be_landing_page() {
            if ( ! current_user_can( 'manage_woocommerce' ) ) {
                wp_die( __( 'You do not have permission to access this page.', 'gv-paste' ) );
            }
            echo '<div class="wrap"><h1>' . esc_html__( 'Bulk Edit', 'gv-paste' ) . '</h1>';
            echo '<p>' . esc_html__( 'Choose a bulk tool from the submenu:', 'gv-paste' ) . '</p>';
            echo '<ul style="list-style:disc;margin-left:20px">';
            echo '<li><a href="' . esc_url( admin_url('admin.php?page=gv-procurement-paste-import') ) . '">' . esc_html__( 'Procurement Paste Import', 'gv-paste' ) . '</a></li>';
            echo '</ul></div>';
        }
    }

    // Use a neutral position; we'll enforce the final spot with menu_order filter.
    add_menu_page(
        __( 'Bulk Edit', 'gv-paste' ),
        __( 'Bulk Edit', 'gv-paste' ),
        'manage_woocommerce',
        'gv-bulk-edit',                // parent slug
        'gv_be_landing_page',
        'dashicons-edit',
        58                              // any number; final order is handled below
    );

    // 2) Add our tool under the Bulk Edit parent
    add_submenu_page(
        'gv-bulk-edit',
        __( 'Procurement Paste Import', 'gv-paste' ),
        __( 'Procurement Paste Import', 'gv-paste' ),
        'manage_woocommerce',
        'gv-procurement-paste-import', // keep your existing slug
        'gv_pi_render_page'
    );
}, 20 );
	*.

/**
 * 3) Force "Bulk Edit" to appear immediately under WooCommerce in the admin menu.
 *    This avoids conflicts with other plugins using the same numeric position.
 */
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', function( $menu_order ) {
    $woocommerce_index = array_search( 'woocommerce', $menu_order, true );
    $bulkedit_index    = array_search( 'gv-bulk-edit', $menu_order, true );

    if ( $woocommerce_index !== false && $bulkedit_index !== false ) {
        // Remove Bulk Edit from its current spot
        unset( $menu_order[ $bulkedit_index ] );

        // Rebuild the array: insert Bulk Edit right after WooCommerce
        $new = [];
        $i = 0;
        foreach ( $menu_order as $slug ) {
            $new[] = $slug;
            if ( $slug === 'woocommerce' ) {
                $new[] = 'gv-bulk-edit';
            }
            $i++;
        }
        return $new;
    }
    return $menu_order;
} );

	

	
});

/** ===== Utilities ===== */

/** Detect delimiter by simple heuristics */
function gv_pi_detect_delimiter( $text ) {
    $first_line = strtok( str_replace(["\r\n","\r"], "\n", $text), "\n" );
    $counts = [
        "\t" => substr_count( $first_line, "\t" ),
        ","  => substr_count( $first_line, "," ),
        ";"  => substr_count( $first_line, ";" ),
        "|"  => substr_count( $first_line, "|" ),
    ];
    arsort( $counts );
    $delim = key( $counts );
    return $counts[$delim] > 0 ? $delim : "\t";
}

/** Robust CSV line parse with chosen delimiter */
function gv_pi_parse_lines( $text, $delimiter ) {
    $rows = [];
    $text = trim( $text );
    if ( $text === '' ) return $rows;

    // Normalize newlines
    $text = str_replace( ["\r\n", "\r"], "\n", $text );
    $lines = explode( "\n", $text );

    foreach ( $lines as $line ) {
        // Use PHP's CSV parser (handles quotes)
        $row = str_getcsv( $line, $delimiter );
        // Trim cell whitespace
        $row = array_map( function( $v ) { return trim( (string) $v ); }, $row );
        // Skip entirely empty rows
        if ( implode('', $row) === '' ) continue;
        $rows[] = $row;
    }
    return $rows;
}

/** Get gv_supplier ID by exact title, or 0 if not found */
function gv_pi_resolve_supplier_id_by_name( $name ) {
    if ( $name === '' ) return 0;
    $post = get_page_by_title( $name, OBJECT, 'gv_supplier' );
    return ( $post && $post->ID ) ? (int) $post->ID : 0;
}

/** Which meta key stores supplier id on products? */
function gv_pi_supplier_meta_key() {
    if ( class_exists('GV_Suppliers_Manager') ) {
        return GV_Suppliers_Manager::PRODUCT_SUPPLIER_ID;
    }
    return '_gv_proc_supplier_id';
}

/** Heuristic default mapping based on header names */
function gv_pi_guess_field( $header ) {
    $h = strtolower( trim( $header ) );
    // product identifier
    if ( in_array( $h, ['sku','product_sku'] ) )            return 'sku';
    if ( in_array( $h, ['id','product_id','post_id'] ) )     return 'id';
    // supplier id / name
    if ( in_array( $h, ['supplier_id','supplierid','supplier post id'] ) ) return 'supplier_id';
    if ( in_array( $h, ['supplier','supplier_name','suppliername'] ) )     return 'supplier_name';
    // cost price
    if ( in_array( $h, ['cost','cost_price','costprice','_gv_proc_cost_price','purchase_cost'] ) ) return 'cost_price';
    // supplier sku
    if ( in_array( $h, ['supplier_sku','suppliersku','vendor_sku'] ) ) return 'supplier_sku';
    // description/notes
    if ( in_array( $h, ['description','notes','procurement_description','_gv_proc_description'] ) ) return 'description';
    return '';
}

/** ===== Page Renderer ===== */
function gv_pi_render_page() {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( __( 'You do not have permission to access this page.', 'gv-paste' ) );
    }

    $step = isset($_POST['gv_pi_step']) ? sanitize_text_field($_POST['gv_pi_step']) : 'paste';

    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Procurement Paste Import', 'gv-paste' ) . '</h1>';

    if ( $step === 'map' ) {
        gv_pi_render_map_step();
    } elseif ( $step === 'run' ) {
        gv_pi_handle_run();
    } else {
        gv_pi_render_paste_step();
    }

    echo '</div>';
}

/** Step 1: Paste */
function gv_pi_render_paste_step( $error = '' ) {
    $sample = "sku\tsupplier_name\tcost_price\tsupplier_sku\tdescription\n".
              "GV-1001\tSyveco\t125.00\tSYV-7788\tFirst note\n".
              "GV-1002\tRapidrop\t89.95\tRPD-9981\tSecond note";

    if ( $error ) {
        echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
    }

    ?>
    <p><?php esc_html_e( 'Paste rows from Excel (Tab/CSV) below. Include a header row.', 'gv-paste' ); ?></p>
    <form method="post">
        <?php wp_nonce_field( 'gv_pi_map', 'gv_pi_nonce' ); ?>
        <input type="hidden" name="gv_pi_step" value="map">
        <textarea name="gv_pi_raw" rows="12" style="width:100%;font-family:monospace;" placeholder="<?php echo esc_attr( $sample ); ?>"></textarea>
        <p class="description">
            <?php esc_html_e( 'Supported delimiters: Tab (recommended), Comma, Semicolon, Pipe. Headers can be: sku or id, supplier_id or supplier_name, cost_price, supplier_sku, description.', 'gv-paste' ); ?>
        </p>
        <?php submit_button( __( 'Preview & Map Columns', 'gv-paste' ) ); ?>
    </form>
    <?php
}

/** Step 2: Map */
function gv_pi_render_map_step() {
    if ( ! isset($_POST['gv_pi_nonce']) || ! wp_verify_nonce( $_POST['gv_pi_nonce'], 'gv_pi_map' ) ) {
        return gv_pi_render_paste_step( __( 'Security check failed. Please try again.', 'gv-paste' ) );
    }

    $raw = isset($_POST['gv_pi_raw']) ? trim( (string) wp_unslash($_POST['gv_pi_raw']) ) : '';
    if ( $raw === '' ) {
        return gv_pi_render_paste_step( __( 'Please paste some data.', 'gv-paste' ) );
    }

    $delimiter = gv_pi_detect_delimiter( $raw );
    $rows = gv_pi_parse_lines( $raw, $delimiter );
    if ( count($rows) < 2 ) {
        return gv_pi_render_paste_step( __( 'Need at least a header row and one data row.', 'gv-paste' ) );
    }

    $headers = array_shift( $rows );
    $col_count = count( $headers );

    // Build default mapping suggestions
    $suggest = [];
    foreach ( $headers as $h ) {
        $suggest[] = gv_pi_guess_field( $h );
    }

    // Show preview + mapping dropdowns
    ?>
    <h2><?php esc_html_e( 'Step 2 — Map Columns', 'gv-paste' ); ?></h2>
    <form method="post">
        <?php wp_nonce_field( 'gv_pi_run', 'gv_pi_nonce' ); ?>
        <input type="hidden" name="gv_pi_step" value="run">
        <input type="hidden" name="gv_pi_raw" value="<?php echo esc_attr( $raw ); ?>">
        <input type="hidden" name="gv_pi_delim" value="<?php echo esc_attr( $delimiter ); ?>">

        <table class="widefat striped">
            <thead>
                <tr>
                    <?php foreach ( $headers as $i => $h ): ?>
                        <th>
                            <div><?php echo esc_html( $h ); ?></div>
                            <div>
                                <select name="map[<?php echo (int)$i; ?>]">
                                    <?php
                                    $fields = [
                                        ''              => __('— Ignore —','gv-paste'),
                                        'sku'           => __('Product SKU','gv-paste'),
                                        'id'            => __('Product ID','gv-paste'),
                                        'supplier_id'   => __('Supplier ID','gv-paste'),
                                        'supplier_name' => __('Supplier Name','gv-paste'),
                                        'cost_price'    => __('Cost Price','gv-paste'),
                                        'supplier_sku'  => __('Supplier SKU','gv-paste'),
                                        'description'   => __('Description / Notes','gv-paste'),
                                    ];
                                    foreach ( $fields as $key => $label ) {
                                        printf(
                                            '<option value="%s"%s>%s</option>',
                                            esc_attr($key),
                                            selected( $suggest[$i] ?? '', $key, false ),
                                            esc_html($label)
                                        );
                                    }
                                    ?>
                                </select>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $preview = array_slice( $rows, 0, 5 );
                foreach ( $preview as $r ):
                    echo '<tr>';
                    for ( $i=0; $i<$col_count; $i++ ) {
                        echo '<td>' . esc_html( $r[$i] ?? '' ) . '</td>';
                    }
                    echo '</tr>';
                endforeach;
                ?>
            </tbody>
        </table>

        <p style="margin-top:12px;">
            <label>
                <input type="checkbox" name="clear_legacy" value="1">
                <?php esc_html_e( 'Clear legacy string supplier meta (_gv_proc_supplier) when Supplier is provided', 'gv-paste' ); ?>
            </label>
        </p>

        <?php submit_button( __( 'Run Update', 'gv-paste' ), 'primary' ); ?>
        <a href="<?php echo esc_url( add_query_arg( ['page'=>'gv-procurement-paste-import'], admin_url('admin.php') ) ); ?>" class="button"><?php esc_html_e('Start Over','gv-paste'); ?></a>
    </form>
    <?php
}

/** Step 3: Run updates */
function gv_pi_handle_run() {
    if ( ! isset($_POST['gv_pi_nonce']) || ! wp_verify_nonce( $_POST['gv_pi_nonce'], 'gv_pi_run' ) ) {
        return gv_pi_render_paste_step( __( 'Security check failed. Please try again.', 'gv-paste' ) );
    }

    $raw       = isset($_POST['gv_pi_raw']) ? (string) wp_unslash($_POST['gv_pi_raw']) : '';
    $delimiter = isset($_POST['gv_pi_delim']) ? (string) $_POST['gv_pi_delim'] : "\t";
    $map       = isset($_POST['map']) && is_array($_POST['map']) ? array_map('sanitize_text_field', $_POST['map']) : [];
    $clear_legacy = ! empty( $_POST['clear_legacy'] );

    $rows = gv_pi_parse_lines( $raw, $delimiter );
    if ( count($rows) < 2 ) {
        return gv_pi_render_paste_step( __( 'Nothing to import.', 'gv-paste' ) );
    }
    $headers = array_shift( $rows );

    // Build index map: which column index corresponds to which field
    $idx = [
        'sku'           => -1,
        'id'            => -1,
        'supplier_id'   => -1,
        'supplier_name' => -1,
        'cost_price'    => -1,
        'supplier_sku'  => -1,
        'description'   => -1,
    ];
    foreach ( $map as $col => $field ) {
        if ( isset($idx[$field]) && $field !== '' ) {
            $idx[$field] = (int) $col;
        }
    }

    // Require an identifier
    if ( $idx['sku'] < 0 && $idx['id'] < 0 ) {
        echo '<div class="notice notice-error"><p>' . esc_html__( 'You must map either Product SKU or Product ID.', 'gv-paste' ) . '</p></div>';
        return gv_pi_render_map_step(); // back to map
    }

    $supplier_meta_key = gv_pi_supplier_meta_key();

    $updated = 0;
    $skipped = 0;
    $failed  = 0;
    $notes   = [];

    foreach ( $rows as $rnum => $row ) {

        // Identify product
        $product = null;
        if ( $idx['id'] >= 0 && ! empty( $row[ $idx['id'] ] ) ) {
            $pid = (int) $row[ $idx['id'] ];
            $product = wc_get_product( $pid );
            if ( ! $product ) {
                $skipped++; $notes[] = "Row ".($rnum+2).": product ID not found.";
                continue;
            }
        } elseif ( $idx['sku'] >= 0 && ! empty( $row[ $idx['sku'] ] ) ) {
            $sku = $row[ $idx['sku'] ];
            $pid = wc_get_product_id_by_sku( $sku );
            if ( ! $pid ) {
                $skipped++; $notes[] = "Row ".($rnum+2).": SKU '".esc_html($sku)."' not found.";
                continue;
            }
            $product = wc_get_product( $pid );
        } else {
            $skipped++; $notes[] = "Row ".($rnum+2).": no identifier.";
            continue;
        }

        // Collect updates
        $changes = 0;

        // Supplier ID / Name
        if ( $idx['supplier_id'] >= 0 && isset($row[ $idx['supplier_id'] ]) && $row[ $idx['supplier_id'] ] !== '' ) {
            $sid = (int) $row[ $idx['supplier_id'] ];
            if ( $sid > 0 && get_post_type( $sid ) === 'gv_supplier' ) {
                $product->update_meta_data( $supplier_meta_key, $sid );
                $product->update_meta_data( '_gv_proc_supplier_id', $sid ); // keep a compatibility copy
                if ( $clear_legacy ) $product->delete_meta_data( '_gv_proc_supplier' );
                $changes++;
            } else {
                $notes[] = "Row ".($rnum+2).": supplier_id '$sid' invalid.";
            }
        } elseif ( $idx['supplier_name'] >= 0 && isset($row[ $idx['supplier_name'] ]) && $row[ $idx['supplier_name'] ] !== '' ) {
            $sname = $row[ $idx['supplier_name'] ];
            $sid = gv_pi_resolve_supplier_id_by_name( $sname );
            if ( $sid > 0 ) {
                $product->update_meta_data( $supplier_meta_key, $sid );
                $product->update_meta_data( '_gv_proc_supplier_id', $sid );
                if ( $clear_legacy ) $product->delete_meta_data( '_gv_proc_supplier' );
                $changes++;
            } else {
                $notes[] = "Row ".($rnum+2).": supplier '".esc_html($sname)."' not found.";
            }
        }

        // Cost Price
        if ( $idx['cost_price'] >= 0 && isset($row[ $idx['cost_price'] ]) ) {
            $raw_cost = str_replace(',', '.', $row[ $idx['cost_price'] ]); // be forgiving
            $cost = ( $raw_cost === '' ) ? '' : wc_format_decimal( $raw_cost );
            $product->update_meta_data( '_gv_proc_cost_price', $cost );
            $changes++;
        }

        // Supplier SKU
        if ( $idx['supplier_sku'] >= 0 && isset($row[ $idx['supplier_sku'] ]) ) {
            $product->update_meta_data( '_gv_proc_supplier_sku', sanitize_text_field( $row[ $idx['supplier_sku'] ] ) );
            $changes++;
        }

        // Description
        if ( $idx['description'] >= 0 && isset($row[ $idx['description'] ]) ) {
            $product->update_meta_data( '_gv_proc_description', wp_kses_post( $row[ $idx['description'] ] ) );
            $changes++;
        }

        try {
            if ( $changes > 0 ) {
                $product->save();
                $updated++;
            } else {
                $skipped++;
            }
        } catch ( Exception $e ) {
            $failed++; $notes[] = "Row ".($rnum+2).": save failed.";
        }
    }

    // Result
    printf(
        '<div class="notice notice-success"><p><strong>%s</strong> %s %d, %s %d, %s %d.</p></div>',
        esc_html__( 'Procurement import finished.', 'gv-paste' ),
        esc_html__( 'Updated:', 'gv-paste' ), $updated,
        esc_html__( 'Skipped:', 'gv-paste' ), $skipped,
        esc_html__( 'Failed:',  'gv-paste' ), $failed
    );

    if ( $notes ) {
        echo '<div class="notice notice-warning"><ul style="margin:8px 20px;">';
        foreach ( $notes as $n ) {
            echo '<li>' . wp_kses_post( $n ) . '</li>';
        }
        echo '</ul></div>';
    }

    echo '<p><a class="button" href="' . esc_url( add_query_arg( ['page'=>'gv-procurement-paste-import'], admin_url('admin.php') ) ) . '">' . esc_html__( 'Import More', 'gv-paste' ) . '</a></p>';
}


// ===== Snippet #64: Products - Add Bulk edit menu =====
// Scope: global | Priority: 10

add_action( 'admin_menu', function() {
    // Create top-level parent once
    add_menu_page(
        __( 'Bulk Edit', 'gv-spb' ),
        __( 'Bulk Edit', 'gv-spb' ),
        'manage_woocommerce',
        'gv-bulk-edit',
        function () {
            echo '<div class="wrap"><h1>Bulk Edit</h1><p>Select a tool from the submenu.</p></div>';
        },
        'dashicons-update',
        56 // just below WooCommerce (which uses ~55)
    );

    // Now add your submenu page under that parent
    add_submenu_page(
        'gv-bulk-edit',
        __( 'Supplier Pricing Bulk', 'gv-spb' ),
        __( 'Supplier Pricing Bulk', 'gv-spb' ),
        'manage_woocommerce',
        'gv-supplier-pricing-bulk',
        'gv_spb_render_page'
    );
    // 2) Add our tool under the Bulk Edit parent
    // Now add your submenu page under that parent
    add_submenu_page(
        'gv-bulk-edit',
        __( 'Procurement Import', 'gv-paste' ),
        __( 'Procurement Import', 'gv-paste' ),
        'manage_woocommerce',
        'gv-procurement-paste-import', // keep your existing slug
        'gv_pi_render_page'
    );
	
}, 9);


// ===== Snippet #65: Add datasheet button to products =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - WooCommerce Product Datasheet
 * Description: Adds a Datasheet field to the Product Data > General tab and displays a "Download datasheet" button on the product page.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1) ADMIN: Add field to Product Data > General
 */
add_action( 'woocommerce_product_options_general_product_data', function () {
    echo '<div class="options_group">';

    woocommerce_wp_text_input( array(
        'id'          => '_gv_datasheet_url',
        'label'       => __( 'Datasheet URL', 'gv' ),
        'placeholder' => __( 'https://example.com/file.pdf', 'gv' ),
        'desc_tip'    => true,
        'description' => __( 'Paste a URL or click the button below to choose a file from the media library.', 'gv' ),
        'type'        => 'text',
    ) );

    // Media uploader button
    echo '<p><button type="button" class="button gv-upload-datasheet">' .
         esc_html__( 'Upload/Select datasheet', 'gv' ) .
         '</button></p>';

    echo '</div>';
} );

/**
 * 2) ADMIN: Save field
 * Uses the modern hook compatible with HPOS.
 */
add_action( 'woocommerce_admin_process_product_object', function ( $product ) {
    if ( isset( $_POST['_gv_datasheet_url'] ) ) {
        $url = esc_url_raw( wp_unslash( $_POST['_gv_datasheet_url'] ) );
        $product->update_meta_data( '_gv_datasheet_url', $url );
    }
} );

/**
 * 3) ADMIN: Enqueue media uploader (only on product edit screens) + inline JS for the button
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( ( $hook === 'post.php' || $hook === 'post-new.php' ) && get_post_type() === 'product' ) {
        // Ensure the media frame is available
        wp_enqueue_media();

        // Attach a tiny inline script to jQuery to open the media frame
        $js = <<<JS
jQuery(function($){
  $('.gv-upload-datasheet').on('click', function(e){
    e.preventDefault();

    var frame = wp.media({
      title: 'Select datasheet',
      button: { text: 'Use this file' },
      multiple: false,
      library: {
        // Allow common doc types; adjust as needed
        type: ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document']
      }
    });

    frame.on('select', function(){
      var file = frame.state().get('selection').first().toJSON();
      $('#_gv_datasheet_url').val(file.url).trigger('change');
    });

    frame.open();
  });
});
JS;
        wp_add_inline_script( 'jquery-core', $js );
    }
} );

/**
 * 4) FRONTEND: Show "Download datasheet" button on single product page
 * Position: after the product meta (SKU, categories, tags). Change hook/priority to move it.
 */
add_action( 'woocommerce_product_meta_end', function() {
    global $product;
    if ( ! $product instanceof WC_Product ) return;

    $url = $product->get_meta( '_gv_datasheet_url' );
    if ( $url ) {
        // Simple styling hook: uses Woo button classes if your theme supports them
        echo '<div class="gv-datasheet" style="margin-top:0.75rem;">';
        echo '<a class="button alt" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">';
        echo esc_html__( 'Download datasheet', 'gv' );
        echo '</a>';
        echo '</div>';
    }
} );


// ===== Snippet #66: Product - Add filter based on supplier dynamic =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - WooCommerce Procurement Supplier Filter (CPT + Legacy)
 * Description: Adds a Supplier filter to the Products list using gv_supplier (CPT) IDs + legacy string values.
 * Author: Gemini Valve
 * Version: 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Resolve the product meta key that stores the supplier ID */
function gv_pf_supplier_meta_key() {
    if ( class_exists('GV_Suppliers_Manager') ) {
        return GV_Suppliers_Manager::PRODUCT_SUPPLIER_ID;
    }
    return '_gv_proc_supplier_id'; // fallback
}

/**
 * Build dropdown options (cached 12h):
 * - CPT suppliers: value = numeric ID
 * - Legacy names from _gv_proc_supplier: value = 'legacy::<name>'
 */
function gv_proc_filter_get_suppliers() {
    $transient_key = 'gv_proc_filter_suppliers_v2';
    $options = get_transient( $transient_key );
    if ( $options !== false ) return $options;

    $options = array( '' => __( 'All suppliers', 'gv' ) );

    // 1) CPT suppliers
    $cpt = get_posts([
        'post_type'        => 'gv_supplier',
        'post_status'      => 'publish',
        'numberposts'      => -1,
        'orderby'          => 'title',
        'order'            => 'ASC',
        'suppress_filters' => false,
        'fields'           => 'ids',
    ]);
    if ( $cpt ) {
        foreach ( $cpt as $sid ) {
            $title = get_the_title( $sid );
            if ( $title !== '' ) {
                // value is numeric ID
                $options[ (string) (int) $sid ] = $title;
            }
        }
    }

    // 2) Legacy string suppliers still present on products/variations
    global $wpdb;
    $legacy_values = $wpdb->get_col( $wpdb->prepare("
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE pm.meta_key = %s
          AND pm.meta_value <> ''
          AND p.post_type IN ('product','product_variation')
          AND p.post_status NOT IN ('trash','auto-draft','inherit','revision')
        ORDER BY pm.meta_value ASC
    ", '_gv_proc_supplier' ) );

    if ( ! empty( $legacy_values ) ) {
        // Separator (disabled option)
        $options['__sep__'] = '— ' . __( 'Legacy (by name)', 'gv' ) . ' —';
        foreach ( $legacy_values as $name ) {
            $label = wp_kses_post( $name ) . ' ' . __( '(legacy)', 'gv' );
            // value is prefixed to distinguish from IDs
            $options[ 'legacy::' . $name ] = $label;
        }
    }

    set_transient( $transient_key, $options, 12 * HOUR_IN_SECONDS );
    return $options;
}

/** Invalidate cache when products/variations/suppliers change */
function gv_proc_filter_clear_suppliers_cache_on_save( $post_id ) {
    $pt = get_post_type( $post_id );
    if ( in_array( $pt, [ 'product', 'product_variation', 'gv_supplier' ], true ) ) {
        delete_transient( 'gv_proc_filter_suppliers_v2' );
    }
}
add_action( 'save_post_product', 'gv_proc_filter_clear_suppliers_cache_on_save', 10 );
add_action( 'save_post_product_variation', 'gv_proc_filter_clear_suppliers_cache_on_save', 10 );
add_action( 'save_post_gv_supplier', 'gv_proc_filter_clear_suppliers_cache_on_save', 10 );

// Extra: clear on WC product save hook too
add_action( 'woocommerce_admin_process_product_object', function() {
    delete_transient( 'gv_proc_filter_suppliers_v2' );
}, 99 );

/** Add dropdown to Products list */
add_action( 'restrict_manage_posts', function( $post_type ) {
    if ( 'product' !== $post_type ) return;

    $current = isset( $_GET['gv_supplier'] ) ? sanitize_text_field( wp_unslash( $_GET['gv_supplier'] ) ) : '';
    $options = gv_proc_filter_get_suppliers();

    echo '<label for="gv_supplier" class="screen-reader-text">'.esc_html__( 'Filter by supplier', 'gv' ).'</label>';
    echo '<select name="gv_supplier" id="gv_supplier">';
    foreach ( $options as $value => $label ) {
        if ( $value === '__sep__' ) {
            // Visual separator: disabled option
            echo '<option value="" disabled>— ' . esc_html( $label ) . ' —</option>';
            continue;
        }
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr( $value ),
            selected( $current, $value, false ),
            esc_html( $label )
        );
    }
    echo '</select>';
}, 20 );

/** Apply meta_query for selected supplier */
add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) return;

    global $pagenow;
    if ( 'edit.php' !== $pagenow ) return;
    if ( 'product' !== $query->get( 'post_type' ) ) return;

    if ( isset( $_GET['gv_supplier'] ) ) {
        $selected = sanitize_text_field( wp_unslash( $_GET['gv_supplier'] ) );
        if ( $selected === '' ) return;

        $meta_query = (array) $query->get( 'meta_query' );

        if ( strpos( $selected, 'legacy::' ) === 0 ) {
            // Legacy name filter
            $legacy_name = substr( $selected, 8 );
            if ( $legacy_name !== '' ) {
                $meta_query[] = [
                    'key'     => '_gv_proc_supplier',
                    'value'   => $legacy_name,
                    'compare' => '=',
                ];
            }
        } else {
            // Numeric supplier ID filter (match canonical + fallback keys)
            $sid = (int) $selected;
            if ( $sid > 0 ) {
                $supplier_meta_key = gv_pf_supplier_meta_key();
                $meta_query[] = [
                    'relation' => 'OR',
                    [
                        'key'     => $supplier_meta_key,   // canonical key (GV plugin)
                        'value'   => $sid,
                        'compare' => '=',
                    ],
                    [
                        'key'     => '_gv_proc_supplier_id', // compatibility key
                        'value'   => $sid,
                        'compare' => '=',
                    ],
                ];
            }
        }

        $query->set( 'meta_query', $meta_query );
    }
}, 20 );


// ===== Snippet #70: DATASHEET - Maak link beschikbaar voor PDF customizer =====
// Scope: global | Priority: 10


if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper: haal datasheet-URL van product/variatie uit _gv_datasheet_url
 * (met variatie -> parent fallback). Gedefinieerd met guard om dubbele
 * definities te voorkomen.
 */
if ( ! function_exists( 'gv_get_product_datasheet_url_simple' ) ) {
    function gv_get_product_datasheet_url_simple( $product ) {
        if ( ! $product instanceof WC_Product ) return '';
        if ( $product->is_type( 'variation' ) ) {
            $url = $product->get_meta( '_gv_datasheet_url' );
            if ( ! $url && ( $parent = wc_get_product( $product->get_parent_id() ) ) ) {
                $url = $parent->get_meta( '_gv_datasheet_url' );
            }
            return $url ?: '';
        }
        return $product->get_meta( '_gv_datasheet_url' ) ?: '';
    }
}

/** Helper: maak HTML link */
if ( ! function_exists( 'gv_make_datasheet_link' ) ) {
    function gv_make_datasheet_link( $url, $label = 'Download' ) {
        $url = esc_url( $url );
        if ( ! $url ) return '';
        return sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">📄 %2$s</a>',
            $url,
            esc_html( $label )
        );
    }
}

/** Zet Datasheet_link op één item (veilig, idempotent) */
if ( ! function_exists( 'gv_set_datasheet_link_on_item' ) ) {
    function gv_set_datasheet_link_on_item( WC_Order_Item_Product $item ) {
        $product = $item->get_product();
        if ( ! $product ) return;

        // Skip als al aanwezig
        if ( $item->get_meta( 'Datasheet_link', true ) ) return;

        $url = gv_get_product_datasheet_url_simple( $product );
        if ( ! $url ) return;

        $item->update_meta_data( 'Datasheet_link', gv_make_datasheet_link( $url ) );
        // (optioneel) kale URL ook beschikbaar:
        /*
        if ( ! $item->get_meta( 'Datasheet', true ) ) {
            $item->update_meta_data( 'Datasheet', esc_url( $url ) );
        }
		*/
        $item->save();
    }
}

/** A) Tijdens checkout: zet Datasheet_link */
add_action( 'woocommerce_checkout_create_order_line_item', function( $item, $cart_item_key, $values, $order ) {
    if ( $item instanceof WC_Order_Item_Product ) {
        gv_set_datasheet_link_on_item( $item );
    }
}, 10, 4 );

/** B) Items via admin toegevoegd: zet Datasheet_link */
add_action( 'woocommerce_new_order_item', function( $item_id, $item, $order_id ) {
    if ( $item instanceof WC_Order_Item_Product ) {
        gv_set_datasheet_link_on_item( $item );
    }
}, 10, 3 );

/** C) Backfill net vóór PDF render (voor bestaande orders) */
add_action( 'wpo_wcpdf_before_document', function( $document_type, $document ) {
    if ( ! is_object( $document ) || ! method_exists( $document, 'get_order' ) ) return;
    $order = $document->get_order();
    if ( ! $order instanceof WC_Order ) return;

    $changed = false;
    foreach ( $order->get_items( 'line_item' ) as $item ) {
        if ( $item instanceof WC_Order_Item_Product ) {
            $before = $item->get_meta( 'Datasheet_link', true );
            gv_set_datasheet_link_on_item( $item );
            $after  = $item->get_meta( 'Datasheet_link', true );
            if ( ! $before && $after ) $changed = true;
        }
    }
    if ( $changed ) $order->save();
}, 10, 2 );


// ===== Snippet #72: Credentials - Log out when resetting password =====
// Scope: global | Priority: 10


/**
 * GV – Vereis opnieuw inloggen na wachtwoord reset
 * - Logt gebruiker uit na reset
 * - Redirect naar Mijn account met melding
 */

// 1) WooCommerce: na het resetten van het wachtwoord
add_action( 'woocommerce_customer_reset_password', function( $user ) {
    // Voor de zekerheid: uitloggen (ook als WC/WordPress zou autologgen)
    wp_logout();

    // Toon een nette notice die na redirect zichtbaar is
    if ( function_exists( 'wc_add_notice' ) ) {
        wc_add_notice( __( 'Your password has been reset. Please log in with your new password.', 'woocommerce' ), 'success' );
    }
}, 10, 1 );

// 2) WooCommerce: bepaal waarheen we redirecten na reset
add_filter( 'woocommerce_customer_reset_password_redirect', function( $redirect ) {
    // Stuur naar de Mijn account pagina (login formulier) zodat gebruiker opnieuw inlogt
    if ( function_exists( 'wc_get_page_permalink' ) ) {
        return wc_get_page_permalink( 'myaccount' );
    }
    return $redirect;
}, 10, 1 );

// 3) WordPress-core fallback (als reset via wp-login.php gebeurt i.p.v. via WC endpoint)
add_action( 'password_reset', function( $user, $new_pass ) {
    // Zorg óók hier dat er geen login-sessie actief blijft
    wp_logout();
}, 10, 2 );


// ===== Snippet #74: Credentials - Disable changing email address in My Account =====
// Scope: global | Priority: 10

/**
 * Disable changing email address in WooCommerce My Account
 */
add_filter( 'woocommerce_save_account_details_required_fields', function( $fields ) {
    unset( $fields['account_email'] ); // make email not required for saving
    return $fields;
});

add_filter( 'woocommerce_edit_account_form', function() {
    ?>
    <style>
        /* Hide the email field in My Account -> Account details */
        p.woocommerce-FormRow.woocommerce-FormRow--wide.form-row.form-row-wide:nth-of-type(3),
        #account_email {
            display: none !important;
        }
        label[for="account_email"] {
            display: none !important;
        }
    </style>
    <?php
});


// ===== Snippet #77: Appearence - set colors =====
// Scope: global | Priority: 10

add_action('wp_head', function() {
    ?>
    <style>
    .button, .button.alt, .wc-block-components-button, 
    .wc-block-cart__submit-button, 
    .wc-block-components-checkout-place-order-button, 
    .add_to_cart_button, .single_add_to_cart_button, 
    .wp-element-button {
      background: #891734 !important;
      border-color: #891734 !important;
      color: var(--theme-palette-color-5);
    }
    .button:hover, .button.alt:hover, 
    .wc-block-components-button:hover, 
    .wc-block-cart__submit-button:hover, 
    .wc-block-components-checkout-place-order-button:hover, 
    .add_to_cart_button:hover, .single_add_to_cart_button:hover, 
    .wp-element-button:hover {
      filter: brightness(0.9) !important;
      color: var(--theme-palette-color-5);
    }
    </style>
    <?php
});


// ===== Snippet #88: News - Send mail to users and add Opt-out to my account [CLONE] =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - Post Publish Approval + User Opt-out
 * Description: Approve & email all users on publish (featured image + title + first paragraph + Read more button). Adds WooCommerce My Account opt-out and a per-post test email button.
 * Author: Gemini Valve
 * Version: 1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class GV_Post_Publish_Approvals {
    const META_APPROVED = '_gv_notify_users_approved';
    const META_SENT     = '_gv_notify_users_sent';
    const USER_META_OPT = '_gv_notify_optout'; // 'yes' = opted-out

    // Theme colors (Blocksy palette for Gemini Valve)
    const BRAND_BG      = '#891734'; // deep red
    const BRAND_ACCENT  = '#f0e4d3'; // warm tan

    public function __construct() {
        // Admin UI on posts
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_meta_box' ] );

        // Admin notices after test send
        add_action( 'admin_notices', [ $this, 'maybe_admin_notice' ] );

        // Fire on publish (only once per post)
        add_action( 'transition_post_status', [ $this, 'maybe_notify_on_publish' ], 10, 3 );

        // Test mail endpoint
        add_action( 'admin_post_gv_notify_test', [ $this, 'handle_test_send' ] );

        // Woo My Account checkbox (show + save)
        add_action( 'woocommerce_edit_account_form', [ $this, 'render_account_optout_field' ] );
        add_action( 'woocommerce_save_account_details', [ $this, 'save_account_optout_field' ] );

        // Email templates
        add_filter( 'gv_notify_email_subject', [ $this, 'default_subject' ], 10, 2 );
        add_filter( 'gv_notify_email_body',    [ $this, 'default_body' ], 10, 2 );
    }

    /* -----------------------------
     *  ADMIN: Meta box on posts
     * ----------------------------- */
    public function add_meta_box() {
        add_meta_box(
            'gv_notify_users',
            'Notify Users on Publish',
            [ $this, 'render_meta_box' ],
            'post',
            'side',
            'high'
        );
    }

    public function render_meta_box( $post ) {
        $approved = get_post_meta( $post->ID, self::META_APPROVED, true ) === 'yes';
        $sent     = get_post_meta( $post->ID, self::META_SENT, true ) === 'yes';
        wp_nonce_field( 'gv_notify_users_meta', 'gv_notify_users_nonce' );

        $test_url = wp_nonce_url(
            add_query_arg(
                [ 'action' => 'gv_notify_test', 'post_id' => $post->ID ],
                admin_url( 'admin-post.php' )
            ),
            'gv_notify_test_'.$post->ID
        );
        ?>
        <p>
            <label>
                <input type="checkbox" name="gv_notify_users_approved" value="yes" <?php checked( $approved ); ?> />
                Approve & send this post to users when published
            </label>
        </p>
        <p><strong>Status:</strong> <?php echo $sent ? '<span style="color:#0073aa">Already sent</span>' : '<span style="color:#777">Not sent</span>'; ?></p>

        <p>
            <a href="<?php echo esc_url( $test_url ); ?>" class="button button-primary">
                Send test email to me
            </a>
        </p>
        <p style="font-size:12px;color:#666;margin-top:8px;">
            Test sends only to your account email, ignoring preferences. No other users are emailed.
        </p>
        <?php
    }

    public function save_meta_box( $post_id ) {
        if ( ! isset( $_POST['gv_notify_users_nonce'] ) || ! wp_verify_nonce( $_POST['gv_notify_users_nonce'], 'gv_notify_users_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $approved = isset( $_POST['gv_notify_users_approved'] ) && $_POST['gv_notify_users_approved'] === 'yes' ? 'yes' : 'no';
        update_post_meta( $post_id, self::META_APPROVED, $approved );
    }

    public function maybe_admin_notice() {
        if ( ! is_admin() || ! isset( $_GET['gv_test_sent'] ) ) return;
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if ( $screen && $screen->id !== 'post' ) return;

        $msg = sanitize_text_field( wp_unslash( $_GET['gv_test_sent'] ) );
        if ( $msg === 'ok' ) {
            echo '<div class="notice notice-success is-dismissible"><p>Test email sent to your address.</p></div>';
        } elseif ( $msg === 'err' ) {
            echo '<div class="notice notice-error is-dismissible"><p>Test email failed. Check email settings or logs.</p></div>';
        }
    }

    /* ---------------------------------------
     *  PUBLISH HOOK: Send notifications once
     * --------------------------------------- */
    public function maybe_notify_on_publish( $new_status, $old_status, $post ) {
        if ( $post->post_type !== 'post' ) return;

        // Only when transitioning from non-published to published
        if ( $old_status === 'publish' || $new_status !== 'publish' ) return;

        // Require approval, and only once
        $approved = get_post_meta( $post->ID, self::META_APPROVED, true ) === 'yes';
        $sent     = get_post_meta( $post->ID, self::META_SENT, true ) === 'yes';
        if ( ! $approved || $sent ) return;

        $this->send_notifications_for_post( $post );
        update_post_meta( $post->ID, self::META_SENT, 'yes' );
    }

    /* ---------------------------------------
     *  TEST SEND: only to current user
     * --------------------------------------- */
    public function handle_test_send() {
        if ( ! is_user_logged_in() ) {
            wp_die( 'Unauthorized', 403 );
        }
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_die( 'Unauthorized', 403 );
        }
        check_admin_referer( 'gv_notify_test_'.$post_id );

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'post' ) {
            wp_redirect( add_query_arg( 'gv_test_sent', 'err', get_edit_post_link( $post_id, 'url' ) ) );
            exit;
        }

        $user  = wp_get_current_user();
        $email = $user && is_email( $user->user_email ) ? $user->user_email : '';

        if ( ! $email ) {
            wp_redirect( add_query_arg( 'gv_test_sent', 'err', get_edit_post_link( $post_id, 'url' ) ) );
            exit;
        }

        $subject = apply_filters( 'gv_notify_email_subject', '', $post );
        $body    = apply_filters( 'gv_notify_email_body',    '', $post );

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        $ok      = false;

        if ( function_exists( 'WC' ) && WC()->mailer() ) {
            $mailer = WC()->mailer();
            $ok = (bool) $mailer->send( $email, $subject, $mailer->wrap_message( $subject, $body ), $headers );
        } else {
            $ok = (bool) wp_mail( $email, $subject, $body, $headers );
        }

        $flag = $ok ? 'ok' : 'err';
        wp_redirect( add_query_arg( 'gv_test_sent', $flag, get_edit_post_link( $post_id, 'url' ) ) );
        exit;
    }

    /* ---------------------------------------
     *  BULK SEND CORE (all users except opt-out)
     * --------------------------------------- */
    protected function send_notifications_for_post( $post ) {
        $recipients = $this->get_recipient_emails();
        if ( empty( $recipients ) ) return 0;

        $subject = apply_filters( 'gv_notify_email_subject', '', $post );
        $body    = apply_filters( 'gv_notify_email_body',    '', $post );

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        $sent = 0;

        // Use Woo mailer for consistent header/footer and styling
        if ( function_exists( 'WC' ) && WC()->mailer() ) {
            $mailer = WC()->mailer();
            foreach ( $recipients as $email ) {
                if ( $mailer->send( $email, $subject, $mailer->wrap_message( $subject, $body ), $headers ) ) $sent++;
            }
        } else {
            foreach ( $recipients as $email ) {
                if ( wp_mail( $email, $subject, $body, $headers ) ) $sent++;
            }
        }
        return $sent;
    }

    protected function get_recipient_emails() {
        // All users EXCEPT those who opted out
        $args = [
            'meta_query' => [
                'relation' => 'OR',
                [ 'key' => self::USER_META_OPT, 'compare' => 'NOT EXISTS' ],
                [ 'key' => self::USER_META_OPT, 'value' => 'yes', 'compare' => '!=' ],
            ],
            'fields' => [ 'user_email' ],
            'number' => 20000,
        ];
        $users = get_users( $args );
        return array_values( array_unique( wp_list_pluck( $users, 'user_email' ) ) );
    }

    /* ------------------------------------
     *  Email templates (theme matched)
     * ------------------------------------ */
    public function default_subject( $subject, $post ) {
        return 'New article: ' . get_the_title( $post );
    }
	
	public function default_body( $body, $post ) {
    // Build intro (first paragraph from the post content)
    $content_html = wpautop( $post->post_content );
    $paras        = preg_split( '/<\/p>/', $content_html, -1, PREG_SPLIT_NO_EMPTY );
    $intro        = trim( wp_strip_all_tags( $paras[0] ?? '' ) );
    $link         = get_permalink( $post );

    // Featured image (if available)
    $img_url  = '';
    $img_alt  = '';
    if ( has_post_thumbnail( $post ) ) {
        $img_url  = get_the_post_thumbnail_url( $post, 'large' );
        $thumb_id = get_post_thumbnail_id( $post );
        $img_alt  = trim( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
        if ( $img_alt === '' ) $img_alt = get_the_title( $post );
    }

    // Excerpt (manual or auto-generated)
    if ( has_excerpt( $post ) ) {
        $excerpt = wp_strip_all_tags( get_the_excerpt( $post ) );
    } else {
        // Prefer excerpt from content *after* the first paragraph to avoid duplication
        $rest_html = '';
        if ( count( $paras ) > 1 ) {
            $rest_html = implode( ' ', array_slice( $paras, 1 ) );
        }
        $rest_text = trim( wp_strip_all_tags( $rest_html ) );
        if ( $rest_text === '' ) {
            $rest_text = trim( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
        }
        $excerpt = wp_trim_words( $rest_text, 30, '…' ); // ~30 words
    }

    // Styles
    $wrapStyle  = 'font-family:Arial,Helvetica,sans-serif;color:#222222;';
    $titleStyle = 'font-size:18px;line-height:1.4;margin:12px 0 8px 0;';
    $paraStyle  = 'font-size:15px;line-height:1.7;margin:0 0 18px 0;';
    $imgWrap    = 'margin:0 0 10px 0;text-align:left;';
    $imgStyle   = 'display:block;border:0;outline:none;text-decoration:none;width:100%;max-width:640px;height:auto;border-radius:10px;';
    $footWrap   = sprintf('margin-top:22px;padding:12px 14px;background:%s;border-radius:10px;', self::BRAND_ACCENT);
    $small      = 'font-size:12px;color:#444;margin:0;';

    // Woo-style button + brand color
    $btnClass   = 'button';
    $btnInline  = 'background:' . self::BRAND_BG . ';color:#ffffff;border-radius:12px;padding:12px 18px;text-decoration:none;display:inline-block;font-weight:600;letter-spacing:.2px;';

    $account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');

    $body  = '<div style="'.$wrapStyle.'">';

    // Image
    if ( $img_url ) {
        $body .= '<div style="'.$imgWrap.'">';
        $body .= '<a href="'. esc_url( $link ) .'"><img src="'. esc_url( $img_url ) .'" alt="'. esc_attr( $img_alt ) .'" style="'.$imgStyle.'" /></a>';
        $body .= '</div>';
    }

    // Title
    $body .= '<h2 style="'.$titleStyle.'">'. esc_html( get_the_title( $post ) ) .'</h2>';

    // First paragraph
    if ( $intro !== '' ) {
        $body .= '<p style="'.$paraStyle.'">'. esc_html( $intro ) .'</p>';
    }

    // Excerpt (manual or auto)
    if ( $excerpt !== '' ) {
        $body .= '<p style="'.$paraStyle.'"><em>'. esc_html( $excerpt ) .'</em></p>';
    }

    // Read more button
    $body .= '<p><a href="'. esc_url( $link ) .'" class="'. esc_attr( $btnClass ) .'" style="'. esc_attr( $btnInline ) .'">Read more…</a></p>';

    // Footer
    $body .= '<div style="'.$footWrap.'">';
    $body .= '<p style="'.$small.'">You are receiving this because you have an account at '. esc_html( get_bloginfo('name') ) .'.</p>';
    $body .= '<p style="'.$small.'">To change your email preferences, visit <a href="'. esc_url( $account_url ) .'" style="color:#000;text-decoration:underline;">My account</a> → <em>Account details</em>.</p>';
    $body .= '</div>';

    $body .= '</div>';

    return $body;
}

	
/*
    public function default_body( $body, $post ) {
        // Build intro (first paragraph from the post content)
        $content  = wpautop( $post->post_content );
        $paras    = preg_split( '/<\/p>/', $content, -1, PREG_SPLIT_NO_EMPTY );
        $intro    = trim( wp_strip_all_tags( $paras[0] ?? '' ) );
        $link     = get_permalink( $post );

        // Featured image (if available)
        $img_url  = '';
        $img_alt  = '';
        if ( has_post_thumbnail( $post ) ) {
            $img_url   = get_the_post_thumbnail_url( $post, 'large' ); // WP will choose best available size
            $thumb_id  = get_post_thumbnail_id( $post );
            $img_alt   = trim( get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
            if ( $img_alt === '' ) $img_alt = get_the_title( $post );
        }

        // Styles for broad email client support
        $wrapStyle  = 'font-family:Arial,Helvetica,sans-serif;color:#222222;';
        $titleStyle = 'font-size:18px;line-height:1.4;margin:12px 0 8px 0;';
        $paraStyle  = 'font-size:15px;line-height:1.7;margin:0 0 18px 0;';
        $imgWrap    = 'margin:0 0 10px 0;text-align:left;';
        $imgStyle   = 'display:block;border:0;outline:none;text-decoration:none;width:100%;max-width:640px;height:auto;border-radius:10px;';
        $footWrap   = sprintf('margin-top:22px;padding:12px 14px;background:%s;border-radius:10px;', self::BRAND_ACCENT);
        $small      = 'font-size:12px;color:#444;margin:0;';

        // Woo-style button + brand color (inline for maximum support)
        $btnClass   = 'button';
        $btnInline  = 'background:' . self::BRAND_BG . ';color:#ffffff;border-radius:12px;padding:12px 18px;text-decoration:none;display:inline-block;font-weight:600;letter-spacing:.2px;';

        $account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/my-account/');

        $body  = '<div style="'.$wrapStyle.'">';

        // Image (if available)
        if ( $img_url ) {
            $body .= '<div style="'.$imgWrap.'">';
            $body .= '<a href="'. esc_url( $link ) .'"><img src="'. esc_url( $img_url ) .'" alt="'. esc_attr( $img_alt ) .'" style="'.$imgStyle.'" /></a>';
            $body .= '</div>';
        }

        // Title
        $body .= '<h2 style="'.$titleStyle.'">'. esc_html( get_the_title( $post ) ) .'</h2>';

        // First paragraph
        if ( $intro !== '' ) {
            $body .= '<p style="'.$paraStyle.'">'. esc_html( $intro ) .'</p>';
        }

        // Read more button
        $body .= '<p><a href="'. esc_url( $link ) .'" class="'. esc_attr( $btnClass ) .'" style="'. esc_attr( $btnInline ) .'">Read more…</a></p>';

        // Footer: preferences in My Account
        $body .= '<div style="'.$footWrap.'">';
        $body .= '<p style="'.$small.'">You are receiving this because you have an account at '. esc_html( get_bloginfo('name') ) .'.</p>';
        $body .= '<p style="'.$small.'">To change your email preferences, visit <a href="'. esc_url( $account_url ) .'" style="color:#000;text-decoration:underline;">My account</a> → <em>Account details</em>.</p>';
        $body .= '</div>';

        $body .= '</div>';

        return $body;
    }*/

    /* ------------------------------------------------
     *  WooCommerce My Account: Opt-out checkbox
     * ------------------------------------------------ */
    public function render_account_optout_field() {
        if ( ! is_user_logged_in() ) return;
        $user_id = get_current_user_id();
        $optout  = get_user_meta( $user_id, self::USER_META_OPT, true ) === 'yes';
        ?>
        <fieldset>
            <legend><?php esc_html_e( 'Email preferences', 'gv' ); ?></legend>
            <p class="form-row">
                <label for="gv_notify_optout" class="woocommerce-form__label woocommerce-form__label-for-checkbox">
                    <input type="checkbox" name="gv_notify_optout" id="gv_notify_optout" value="yes" <?php checked( $optout ); ?> />
                    <span><?php esc_html_e( 'Do not email me when new articles are published', 'gv' ); ?></span>
                </label>
            </p>
        </fieldset>
        <?php
    }

    public function save_account_optout_field( $user_id ) {
        $opt = isset( $_POST['gv_notify_optout'] ) && $_POST['gv_notify_optout'] === 'yes' ? 'yes' : 'no';
        update_user_meta( $user_id, self::USER_META_OPT, $opt );
    }
}

new GV_Post_Publish_Approvals();


// ===== Snippet #91: Set Button color text to =====
// Scope: site-css | Priority: 10
// Converted from CSS snippet to PHP that prints CSS on the front-end.

add_action( 'wp_head', function () {
    ?>
    <style>
    a.wp-block-file__button.wp-element-button {
      color: var(--theme-palette-color-5);
    }

    .wc-block-components-button__text {
      color: var(--theme-palette-color-5);
    }

    .add_to_cart_button {
      /* background-color: #ff0000; /* Change this to your desired background color */
      color: var(--theme-palette-color-5); /* Change this to your desired text color */
    }

    a.button.product_type_simple.add_to_cart_button.ajax_add_to_cart {
      /* background-color: #ff0000; /* Change this to your desired background color */
      color: var(--theme-palette-color-5); /* Change this to your desired text color */
    }
    </style>
    <?php
} );


// ===== Snippet #93: Hide Products Without Images by Setting Visibility Private =====
// Description: Automatically sets products without main or gallery images to private and hides them from category and archive views.
// Scope: global | Priority: 10

add_action( 'save_post_product', function( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$product_image = get_post_thumbnail_id( $post_id );
	$product_gallery = get_post_meta( $post_id, '_product_image_gallery', true );
	if ( empty( $product_image ) && empty( $product_gallery ) ) {
		wp_set_post_terms( $post_id, 'private', 'product_visibility', false );
	} else {
		wp_remove_object_terms( $post_id, 'private', 'product_visibility' );
	}
}, 20 );
add_action( 'pre_get_posts', function( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ( is_post_type_archive( 'product' ) || is_tax( 'product_cat' ) ) ) {
		$meta_query = array(
			'relation' => 'OR',
			array(
				'key' => '_thumbnail_id',
				'compare' => 'EXISTS',
			),
			array(
				'key' => '_product_image_gallery',
				'value' => '',
				'compare' => '!=',
			),
		);
		$query->set( 'meta_query', $meta_query );
	}
}, 20 );


// ===== Snippet #97: Hide 'Hidden' Category and Its Products Sitewide =====
// Description: Hides the WooCommerce category 'Hidden' and all its products from the shop and all public queries sitewide.
// Scope: global | Priority: 10

/**
 * GV — Hide "Hidden" + "Uncategorized" from the FRONT-END shop UI only
 * (Admin stays unaffected so you can still filter by these categories.)
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Define once, at load time (NOT in init) so early filters can use it safely. */
// Safe default for the hidden product categories (by slug)
if ( ! defined('GV_HIDDEN_CAT_SLUGS') ) {
    // Add or change slugs here (lowercase, hyphenated)
    define('GV_HIDDEN_CAT_SLUGS', ['internal', 'hidden', 'draft']);
}

add_filter('get_terms_args', function ($args, $taxonomies) {
    if ( empty($taxonomies) || ! in_array('product_cat', (array) $taxonomies, true) ) {
        return $args;
    }
    // Ensure we ALWAYS have an array, even if something else was passed in earlier
    if ( ! isset($args['slug__not_in']) || ! is_array($args['slug__not_in']) ) {
        $args['slug__not_in'] = [];
    }
    $args['slug__not_in'] = array_unique(array_merge($args['slug__not_in'], GV_HIDDEN_CAT_SLUGS));
    return $args;
}, 10, 2);


/** Helper: return term IDs for the slugs we want to hide (product_cat). */
function gv_hidden_product_cat_ids() {
    static $ids = null;
    if ( $ids !== null ) return $ids;

    $ids   = array();
    $slugs = defined('GV_HIDDEN_CAT_SLUGS') ? (array) GV_HIDDEN_CAT_SLUGS : array('hidden','uncategorized');

    foreach ( $slugs as $slug ) {
        $term = get_term_by( 'slug', $slug, 'product_cat' );
        if ( $term && ! is_wp_error( $term ) ) {
            $ids[] = (int) $term->term_id;
        }
    }
    return $ids;
}

/** 1) Hide products in those categories from shop & archives (front-end only) */
add_action( 'pre_get_posts', function( $q ) {
    if ( is_admin() || ! $q->is_main_query() ) return;

    if ( $q->is_post_type_archive( 'product' ) || $q->is_tax( array( 'product_cat', 'product_tag' ) ) ) {
        $hidden_ids = gv_hidden_product_cat_ids();
        if ( empty( $hidden_ids ) ) return;

        $tax_query   = (array) $q->get( 'tax_query' );
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $hidden_ids,
            'operator' => 'NOT IN',
        );
        $q->set( 'tax_query', $tax_query );
    }
});

/** 2) Hide the category tiles (subcategory grid) — front-end only */
add_filter( 'woocommerce_product_subcategories_args', function( $args ) {
    if ( is_admin() ) return $args;
    $hidden_ids = gv_hidden_product_cat_ids();
    if ( ! empty( $hidden_ids ) ) {
        $args['exclude'] = array_unique( array_merge( $args['exclude'] ?? array(), $hidden_ids ) );
    }
    return $args;
});

/** 3) Hide from Product Categories widget / block — front-end only */
add_filter( 'woocommerce_product_categories_widget_args', function( $args ) {
    if ( is_admin() ) return $args;
    $hidden_ids = gv_hidden_product_cat_ids();
    if ( ! empty( $hidden_ids ) ) {
        $args['exclude'] = array_unique( array_merge( $args['exclude'] ?? array(), $hidden_ids ) );
    }
    return $args;
});

/** 4) Fallback for generic get_terms calls — front-end only */
add_filter( 'get_terms_args', function( $args, $taxonomies ) {
    if ( is_admin() ) return $args;
    $taxonomies = (array) $taxonomies;
    if ( in_array( 'product_cat', $taxonomies, true ) ) {
        $hidden_ids = gv_hidden_product_cat_ids();
        if ( ! empty( $hidden_ids ) ) {
            $existing        = isset( $args['exclude'] ) ? (array) $args['exclude'] : array();
            $args['exclude'] = array_unique( array_merge( $existing, $hidden_ids ) );
        }
    }
    return $args;
}, 10, 2);

/** 5) Hide from [product_categories] shortcode — front-end only */
add_filter( 'shortcode_atts_product_categories', function( $out, $pairs, $atts ) {
    if ( is_admin() ) return $out;
    $hidden_ids = gv_hidden_product_cat_ids();
    if ( ! empty( $hidden_ids ) ) {
        $existing = array_filter( array_map( 'absint', explode( ',', (string) ( $out['exclude'] ?? '' ) ) ) );
        $out['exclude'] = implode( ',', array_unique( array_merge( $existing, $hidden_ids ) ) );
    }
    return $out;
}, 10, 3);


// ===== Snippet #98: Credentials - Hide Prices and Add to Cart for Guests with Login Link [CLONE] =====
// Description: Hides product price, Add to Cart, and Download Datasheet buttons for guests everywhere products are shown, showing a login link instead.
// Scope: global | Priority: 10

/**
 * Hide prices & add-to-cart for guests + show login CTA
 */
add_action( 'init', function () {
    if ( is_user_logged_in() ) return;

    // Remove SINGLE product price & add-to-cart early enough
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

    // Remove ARCHIVE (shop/category) price & add-to-cart early enough
    remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

    // Make products not purchasable for guests
    add_filter( 'woocommerce_is_purchasable', '__return_false' );

    // Belt-and-braces: blank out any price HTML coming from theme overrides
    add_filter( 'woocommerce_get_price_html', function( $price, $product ) { return ''; }, 10, 2 );
    add_filter( 'woocommerce_variable_price_html', '__return_empty_string' );
    add_filter( 'woocommerce_variable_sale_price_html', '__return_empty_string' );
} );

// Add login CTA on SINGLE
add_action( 'woocommerce_single_product_summary', function () {
    if ( is_user_logged_in() ) return;
    echo '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="button login-to-view">'
        . esc_html__( 'Login to view price & buy', 'woocommerce' ) . '</a>';
}, 5 );

// Add login CTA on ARCHIVE (shop/category)
add_action( 'woocommerce_after_shop_loop_item', function () {
    if ( is_user_logged_in() ) return;
    echo '<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="button login-to-view">'
        . esc_html__( 'Login to view price & buy', 'woocommerce' ) . '</a>';
}, 5 );

// Keep your datasheet button only for logged-in users
add_action( 'init', function () {
    add_action( 'woocommerce_single_product_summary', function () {
        if ( is_user_logged_in() && function_exists( 'csai_download_datasheet_button' ) ) {
            csai_download_datasheet_button();
        }
    }, 20 );
} );


// ===== Snippet #99: Products - Remove count behind category =====
// Scope: global | Priority: 10

/**
 * Remove product count (number) after WooCommerce categories
 */
add_filter( 'woocommerce_subcategory_count_html', '__return_false' );


// ===== Snippet #101: Users - Remove social information from admin =====
// Scope: global | Priority: 10

/**
 * Remove unwanted social profile fields from WordPress user profile
 */
add_filter( 'user_contactmethods', function( $methods ) {

    $remove = [
        'facebook',
        'twitter',
//        'linkedin',
        'dribbble',
        'instagram',
        'pinterest',
        'wordpress',
        'github',
        'medium',
        'youtube',
        'vimeo',
        'vk',
        'ok',
        'tiktok',
        'mastodon',
		'VKontakte',
		'Odnoklassniki',
    ];

    foreach ( $remove as $field ) {
        if ( isset( $methods[ $field ] ) ) {
            unset( $methods[ $field ] );
        }
    }

    return $methods;
}, 99 );


// ===== Snippet #102: Users - Update approved in overview =====
// Scope: global | Priority: 10

/**
 * GV – Zorg dat user_meta 'approved' altijd een waarde heeft
 * - Als het meta veld nog niet bestaat of leeg is, zet het op '0' (niet approved)
 */

add_action( 'init', function() {
    if ( is_user_logged_in() ) {
        $user_id = get_current_user_id();

        // Pas de meta key aan naar die van jouw systeem
        $approved = get_user_meta( $user_id, 'approved', true );

        if ( $approved === '' ) {
            update_user_meta( $user_id, 'approved', '0' ); // of 'no'
        }
    }
});


// ===== Snippet #104: Credentials - Add filter on pending approval to Users - rev 3.0 =====
// Description: <p>Add filter to Users --&gt; All users to show what users are approved</p>
// Scope: admin | Priority: 10


/**
 * Users list filters/views:
 * - Approved:      _gv_account_approved == '1'
 * - Not approved:  _gv_account_approved == '0'
 * - Pending:       meta not set OR empty ('')  ← geen expliciete waarde
 * Order: All | Approved | Not approved | Pending
 */

if ( ! defined( 'GV_AA_META' ) ) {
    define( 'GV_AA_META', '_gv_account_approved' );
}

/**
 * View links met live counts.
 */
add_filter( 'views_users', function( $views ) {
    if ( ! current_user_can( 'list_users' ) ) return $views;

    // Approved == '1'
    $q_approved = new WP_User_Query( [
        'fields'      => 'ID',
        'number'      => 1,
        'count_total' => true,
        'meta_key'    => GV_AA_META,
        'meta_value'  => '1',
        'meta_compare'=> '=',
    ] );
    $count_approved = (int) $q_approved->get_total();

    // Not approved == '0'
    $q_not_approved = new WP_User_Query( [
        'fields'      => 'ID',
        'number'      => 1,
        'count_total' => true,
        'meta_key'    => GV_AA_META,
        'meta_value'  => '0',
        'meta_compare'=> '=',
    ] );
    $count_not_approved = (int) $q_not_approved->get_total();

    // Pending = unset/empty
    $q_pending = new WP_User_Query( [
        'fields'      => 'ID',
        'number'      => 1,
        'count_total' => true,
        'meta_query'  => [
            'relation' => 'OR',
            [ 'key' => GV_AA_META, 'compare' => 'NOT EXISTS' ],
            [ 'key' => GV_AA_META, 'value' => '', 'compare' => '=' ],
        ],
    ] );
    $count_pending = (int) $q_pending->get_total();

    // Huidige staten
    $is_current_approved     = ( isset($_GET['gv_approval']) && $_GET['gv_approval'] === 'approved' );
    $is_current_not_approved = ( isset($_GET['gv_approval']) && $_GET['gv_approval'] === 'not_approved' );
    $is_current_pending      = ( isset($_GET['gv_approval']) && $_GET['gv_approval'] === 'pending' );

    // URLs
    $url_approved     = add_query_arg( 'gv_approval', 'approved' );
    $url_not_approved = add_query_arg( 'gv_approval', 'not_approved' );
    $url_pending      = add_query_arg( 'gv_approval', 'pending' );

    // Labels
    $label_approved     = sprintf( __( 'Approved <span class="count">(%s)</span>', 'gv-admin-approval' ),     number_format_i18n( $count_approved ) );
    $label_not_approved = sprintf( __( 'Not approved <span class="count">(%s)</span>', 'gv-admin-approval' ), number_format_i18n( $count_not_approved ) );
    $label_pending      = sprintf( __( 'Pending <span class="count">(%s)</span>', 'gv-admin-approval' ),      number_format_i18n( $count_pending ) );

    // Opnieuw opbouwen met gewenste volgorde
    $ordered = [];
    if ( isset( $views['all'] ) ) {
        $ordered['all'] = $views['all'];
    }
    $ordered['gv_approved']     = '<a ' . ( $is_current_approved ? 'class="current"' : '' ) . ' href="' . esc_url( $url_approved )     . '">' . $label_approved     . '</a>';
    $ordered['gv_not_approved'] = '<a ' . ( $is_current_not_approved ? 'class="current"' : '' ) . ' href="' . esc_url( $url_not_approved ) . '">' . $label_not_approved . '</a>';
    $ordered['gv_pending']      = '<a ' . ( $is_current_pending ? 'class="current"' : '' ) . ' href="' . esc_url( $url_pending )      . '">' . $label_pending      . '</a>';

    return $ordered;
} );

/**
 * Dropdown filter (All | Approved | Not approved | Pending)
 */
add_action( 'restrict_manage_users', function( $which ) {
    if ( ! current_user_can( 'list_users' ) ) return;

    $current = isset( $_GET['gv_approval'] ) ? sanitize_text_field( $_GET['gv_approval'] ) : '';
    ?>
    <label for="gv_approval" class="screen-reader-text"><?php esc_html_e( 'Filter by approval', 'gv-admin-approval' ); ?></label>
    <select name="gv_approval" id="gv_approval">
        <option value=""><?php esc_html_e( 'Approval: All', 'gv-admin-approval' ); ?></option>
        <option value="approved"     <?php selected( $current, 'approved' );     ?>><?php esc_html_e( 'Approved only', 'gv-admin-approval' ); ?></option>
        <option value="not_approved" <?php selected( $current, 'not_approved' ); ?>><?php esc_html_e( 'Not approved only', 'gv-admin-approval' ); ?></option>
        <option value="pending"      <?php selected( $current, 'pending' );      ?>><?php esc_html_e( 'Pending (unset only)', 'gv-admin-approval' ); ?></option>
    </select>
    <?php
}, 10, 1 );

/**
 * Query filter voor de gekozen Approval-status.
 */
add_action( 'pre_get_users', function( WP_User_Query $query ) {
    if ( ! is_admin() || ! current_user_can( 'list_users' ) ) return;
    global $pagenow;
    if ( 'users.php' !== $pagenow ) return;

    $status = isset( $_GET['gv_approval'] ) ? sanitize_text_field( $_GET['gv_approval'] ) : '';
    if ( ! $status ) return;

    $meta_query = (array) $query->get( 'meta_query' );

    if ( 'approved' === $status ) {
        $meta_query[] = [
            'key'     => GV_AA_META,
            'value'   => '1',
            'compare' => '=',
        ];
    } elseif ( 'not_approved' === $status ) {
        $meta_query[] = [
            'key'     => GV_AA_META,
            'value'   => '0',
            'compare' => '=',
        ];
    } elseif ( 'pending' === $status ) {
        // Alleen unset/empty
        $meta_query[] = [
            'relation' => 'OR',
            [ 'key' => GV_AA_META, 'compare' => 'NOT EXISTS' ],
            [ 'key' => GV_AA_META, 'value' => '', 'compare' => '=' ],
        ];
    }

    $query->set( 'meta_query', $meta_query );
} );


// ===== Snippet #105: Credentials - Approval buttons in user overview Users --> All users =====
// Scope: admin | Priority: 10


/**
 * GV – Approve / Unapprove users in Users-list
 * - Row actions: Approve / Unapprove
 * - Bulk actions: Approve selected / Unapprove selected
 */

if ( ! defined( 'GV_AA_META' ) ) {
    define( 'GV_AA_META', '_gv_account_approved' ); // jouw meta key
}

/**
 * Row actions: voeg "Approve" / "Unapprove" toe onder gebruikersnaam
 */
add_filter( 'user_row_actions', function( $actions, $user_object ) {

    if ( ! current_user_can( 'edit_users' ) ) {
        return $actions;
    }

    $val   = get_user_meta( $user_object->ID, GV_AA_META, true );
    $nonce = wp_create_nonce( 'gv_toggle_user_' . $user_object->ID );

    // Approve tonen als NIET approved (val !== '1')
    if ( $val !== '1' ) {
        $url = add_query_arg( array(
            'gv_action' => 'approve_user',
            'user_id'   => $user_object->ID,
            '_wpnonce'  => $nonce,
        ), admin_url( 'users.php' ) );

        $actions['gv_approve'] = sprintf(
            '<a href="%s" class="button button-small" aria-label="%s">%s</a>',
            esc_url( $url ),
            esc_attr( sprintf( __( 'Approve user %s', 'gv-admin-approval' ), $user_object->user_login ) ),
            esc_html__( 'Approve', 'gv-admin-approval' )
        );
    }

    // Unapprove tonen als approved (val === '1')
    if ( $val === '1' ) {
        $url = add_query_arg( array(
            'gv_action' => 'unapprove_user',
            'user_id'   => $user_object->ID,
            '_wpnonce'  => $nonce,
        ), admin_url( 'users.php' ) );

        $actions['gv_unapprove'] = sprintf(
            '<a href="%s" class="button button-small" aria-label="%s">%s</a>',
            esc_url( $url ),
            esc_attr( sprintf( __( 'Unapprove user %s', 'gv-admin-approval' ), $user_object->user_login ) ),
            esc_html__( 'Unapprove', 'gv-admin-approval' )
        );
    }

    return $actions;
}, 10, 2 );

/**
 * Handler voor row actions (approve_user / unapprove_user)
 */
add_action( 'admin_init', function() {
    if ( ! is_admin() ) return;

    if ( empty( $_GET['gv_action'] ) || empty( $_GET['user_id'] ) || empty( $_GET['_wpnonce'] ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_users' ) ) {
        wp_die( __( 'You do not have permission to change user approval.', 'gv-admin-approval' ) );
    }

    $action  = sanitize_key( $_GET['gv_action'] );
    $user_id = absint( $_GET['user_id'] );

    if ( ! in_array( $action, array( 'approve_user', 'unapprove_user' ), true ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'gv_toggle_user_' . $user_id ) ) {
        wp_die( __( 'Security check failed.', 'gv-admin-approval' ) );
    }

    if ( ! $user_id || ! get_user_by( 'ID', $user_id ) ) {
        wp_die( __( 'Invalid user.', 'gv-admin-approval' ) );
    }

    if ( 'approve_user' === $action ) {
        update_user_meta( $user_id, GV_AA_META, '1' );
        $redirect = add_query_arg( array( 'gv_notice' => 'approved', 'user' => $user_id ), admin_url( 'users.php' ) );
    } else {
        update_user_meta( $user_id, GV_AA_META, '0' );
        $redirect = add_query_arg( array( 'gv_notice' => 'unapproved', 'user' => $user_id ), admin_url( 'users.php' ) );
    }

    wp_safe_redirect( $redirect );
    exit;
} );

/**
 * Bulk actions toevoegen aan Users-list
 */
add_filter( 'bulk_actions-users', function( $actions ) {
    if ( current_user_can( 'edit_users' ) ) {
        $actions['gv_bulk_approve']   = __( 'Approve selected', 'gv-admin-approval' );
        $actions['gv_bulk_unapprove'] = __( 'Unapprove selected', 'gv-admin-approval' );
    }
    return $actions;
} );

/**
 * Bulk actions afhandelen (approve/unapprove)
 */
add_action( 'load-users.php', function() {
    if ( ! is_admin() || ! current_user_can( 'edit_users' ) ) return;

    $wp_list_table = _get_list_table( 'WP_Users_List_Table' );
    $action = $wp_list_table->current_action();

    if ( ! in_array( $action, array( 'gv_bulk_approve', 'gv_bulk_unapprove' ), true ) ) {
        return;
    }

    check_admin_referer( 'bulk-users' );

    $user_ids = array();
    if ( ! empty( $_REQUEST['users'] ) ) {
        $user_ids = array_map( 'absint', (array) $_REQUEST['users'] );
    }

    if ( empty( $user_ids ) ) {
        wp_safe_redirect( admin_url( 'users.php' ) );
        exit;
    }

    $updated = 0;
    foreach ( $user_ids as $uid ) {
        if ( ! $uid || ! get_user_by( 'ID', $uid ) ) {
            continue;
        }
        if ( 'gv_bulk_approve' === $action ) {
            $ok = update_user_meta( $uid, GV_AA_META, '1' );
        } else {
            $ok = update_user_meta( $uid, GV_AA_META, '0' );
        }
        // update_user_meta geeft false terug als de waarde hetzelfde blijft; we tellen daarom altijd als geslaagd
        $updated++;
    }

    $notice = ( 'gv_bulk_approve' === $action ) ? 'bulk_approved' : 'bulk_unapproved';
    $redirect = add_query_arg( array(
        'gv_notice' => $notice,
        'count'     => $updated,
    ), admin_url( 'users.php' ) );

    wp_safe_redirect( $redirect );
    exit;
} );

/**
 * Admin notices na acties
 */
add_action( 'admin_notices', function() {
    if ( ! is_admin() ) return;

    if ( isset( $_GET['gv_notice'] ) && $_GET['gv_notice'] === 'approved' && ! empty( $_GET['user'] ) ) {
        $user = get_user_by( 'ID', absint( $_GET['user'] ) );
        echo '<div class="notice notice-success is-dismissible"><p>'
           . esc_html( sprintf( __( 'User %s approved.', 'gv-admin-approval' ), $user ? $user->user_login : '#' . absint( $_GET['user'] ) ) )
           . '</p></div>';
    }

    if ( isset( $_GET['gv_notice'] ) && $_GET['gv_notice'] === 'unapproved' && ! empty( $_GET['user'] ) ) {
        $user = get_user_by( 'ID', absint( $_GET['user'] ) );
        echo '<div class="notice notice-warning is-dismissible"><p>'
           . esc_html( sprintf( __( 'User %s unapproved.', 'gv-admin-approval' ), $user ? $user->user_login : '#' . absint( $_GET['user'] ) ) )
           . '</p></div>';
    }

    if ( isset( $_GET['gv_notice'] ) && in_array( $_GET['gv_notice'], array( 'bulk_approved', 'bulk_unapproved' ), true ) && ! empty( $_GET['count'] ) ) {
        $count = (int) $_GET['count'];
        $msg   = $_GET['gv_notice'] === 'bulk_approved'
            ? sprintf( _n( '%s user approved.', '%s users approved.', $count, 'gv-admin-approval' ), number_format_i18n( $count ) )
            : sprintf( _n( '%s user unapproved.', '%s users unapproved.', $count, 'gv-admin-approval' ), number_format_i18n( $count ) );

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
    }
} );


// Optioneel: Approval-statuskolom in Gebruikersoverzicht
if ( ! defined( 'GV_AA_META' ) ) {
    define( 'GV_AA_META', '_gv_account_approved' );
}

add_filter( 'manage_users_columns', function( $cols ) {
    $cols['gv_approval'] = __( 'Approval', 'gv-admin-approval' );
    return $cols;
});

add_filter( 'manage_users_custom_column', function( $val, $col, $user_id ) {
    if ( 'gv_approval' !== $col ) return $val;
    $v = get_user_meta( $user_id, GV_AA_META, true );
    if ( $v === '1' ) {
        return '<span class="dashicons dashicons-yes" style="color:#46b450;" title="Approved"></span> ' . __( 'Approved', 'gv-admin-approval' );
    } elseif ( $v === '0' ) {
        return '<span class="dashicons dashicons-no" style="color:#dc3232;" title="Not approved"></span> ' . __( 'Not approved', 'gv-admin-approval' );
    }
    return '<span class="dashicons dashicons-clock" title="Pending"></span> ' . __( 'Pending', 'gv-admin-approval' );
}, 10, 3);

add_action( 'admin_head-users.php', function() {
    echo '<style>
      .column-gv_approval{width:130px}
      .column-gv_approval .dashicons{vertical-align:middle;margin-right:4px}
    </style>';
});


// ===== Snippet #107: Credentials - Send mail to user and admin when (not)approved or deleted rev 2.0 =====
// Scope: admin | Priority: 10


/**
 * GV – WooCommerce-styled emails on Approved / Not approved / Deleted (EN) + Admin copies
 * - Uses user meta key _gv_account_approved ('1' = approved, '0' = not approved)
 * - Sends WooCommerce-styled HTML emails via WC()->mailer()
 * - Shows "My account" button ONLY for the user when status becomes Approved
 * - Sends separate admin notifications (no button)
 */

if ( ! defined( 'GV_AA_META' ) ) {
    define( 'GV_AA_META', '_gv_account_approved' ); // adjust if different
}

/** Resolve admin email (constant > filter > WP admin_email) */
function gv_get_approval_admin_email() {
    $default = get_option( 'admin_email' );
    if ( defined('GV_APPROVAL_ADMIN_EMAIL') && is_email( GV_APPROVAL_ADMIN_EMAIL ) ) {
        $default = GV_APPROVAL_ADMIN_EMAIL;
    }
    return apply_filters( 'gv_approval_admin_email', $default );
}

/**
 * Build WooCommerce-styled email content and send it.
 * - $show_my_account_btn: if true, includes a primary CTA to My Account
 */
function gv_wc_mail_send( $to, $subject, $heading, $paragraphs = array(), $show_my_account_btn = false ) {
    if ( ! is_email( $to ) ) return false;

    // Require WooCommerce
    if ( ! function_exists( 'WC' ) || ! WC()->mailer() ) {
        // Fallback to plain wp_mail if needed
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        $html    = '<h2>' . esc_html( $heading ) . '</h2>';
        foreach ( (array) $paragraphs as $p ) { $html .= '<p>' . wp_kses_post( $p ) . '</p>'; }
        return wp_mail( $to, $subject, $html, $headers );
    }

    $mailer     = WC()->mailer();
    $site_name  = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
    $my_account = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();

    // Build inner message HTML
    ob_start();
    echo '<div style="margin:0 0 12px;">';
    foreach ( (array) $paragraphs as $p ) {
        echo '<p style="margin:0 0 12px;">' . wp_kses_post( $p ) . '</p>';
    }
    echo '</div>';

    if ( $show_my_account_btn && $my_account ) {
        // WooCommerce button style inline
        echo '<p style="margin:18px 0 0;">'
           . '<a href="' . esc_url( $my_account ) . '" '
           . 'style="display:inline-block;padding:12px 18px;background:#96588a;color:#ffffff;text-decoration:none;border-radius:3px;">'
           . esc_html__( 'My account', 'woocommerce' )
           . '</a></p>';
    }

    $message_html = ob_get_clean();

    // Wrap with WooCommerce email template styling
    if ( method_exists( $mailer, 'wrap_message' ) ) {
        $wrapped = $mailer->wrap_message( $heading, $message_html ); // uses Woo email header/footer + typography
    } else {
        // Older WC fallback: minimal wrapper
        $wrapped = '<h2>' . esc_html( $heading ) . '</h2>' . $message_html;
    }

    // Let WooCommerce send it (uses proper headers + styling)
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    return $mailer->send( $to, $subject, $wrapped, $headers, array() );
}

/** Notify user + admin for a given status change */
function gv_notify_approval_status_change_wc( $user_id, $new_status ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) return;

    $admin_email = gv_get_approval_admin_email();
    $uname       = $user->user_login;
    $uemail      = $user->user_email;

    if ( $new_status === '1' ) {
        // USER: Approved → include My Account button
        gv_wc_mail_send(
            $uemail,
            'Your account has been approved',
            'Your account has been approved',
            array(
                'Good news — your account has just been <strong>approved</strong> and is now active.',
                'You can log in and access all features.',
            ),
            true // show My Account button ONLY here
        );

        // ADMIN: Approved (no button)
        gv_wc_mail_send(
            $admin_email,
            '[Admin] User approved: ' . $uname,
            'User approved',
            array(
                'The following user has been <strong>approved</strong>:',
                'Username: <strong>' . esc_html( $uname ) . '</strong><br>Email: <strong>' . esc_html( $uemail ) . '</strong>',
            ),
            false
        );

    } elseif ( $new_status === '0' ) {
        // USER: Not approved (no button)
        gv_wc_mail_send(
            $uemail,
            'Your account is not approved',
            'Your account is not approved',
            array(
                'Dear Customer, We validate new accounts. Based on the providede information your account is <strong>not approved</strong>.',
                'If you believe this is a mistake, please call us.',
            ),
            false // NO My Account button
        );

        // ADMIN: Not approved (no button)
        gv_wc_mail_send(
            $admin_email,
            '[Admin] User not approved: ' . $uname,
            'User set to not approved',
            array(
                'The following user was set to <strong>not approved</strong>:',
                'Username: <strong>' . esc_html( $uname ) . '</strong><br>Email: <strong>' . esc_html( $uemail ) . '</strong>',
            ),
            false
        );
    }
}

/** Hook: meta first set */
add_action( 'added_user_meta', function( $mid, $user_id, $key, $value ) {
    if ( $key !== GV_AA_META ) return;
    $v = (string) $value;
    if ( $v === '1' || $v === '0' ) {
        gv_notify_approval_status_change_wc( $user_id, $v );
    }
}, 10, 4 );

/** Hook: meta updated */
add_action( 'updated_user_meta', function( $mid, $user_id, $key, $value ) {
    if ( $key !== GV_AA_META ) return;
    $v = (string) $value;
    if ( $v === '1' || $v === '0' ) {
        gv_notify_approval_status_change_wc( $user_id, $v );
    }
}, 10, 4 );

/** Hook: when a user is deleted → notify user and admin (no button) */
add_action( 'delete_user', function( $user_id, $reassign ) {
    $user = get_userdata( $user_id );
    if ( ! $user ) return;

    $admin_email = gv_get_approval_admin_email();
    $uname       = $user->user_login;
    $uemail      = $user->user_email;

    // USER
    if ( is_email( $uemail ) ) {
        gv_wc_mail_send(
            $uemail,
            'Your account has been deleted',
            'Your account has been deleted',
            array(
                'Your account has just been <strong>deleted</strong> from our system.',
                'If this was unexpected, please contact us immediately.',
            ),
            false // no button
        );
    }

    // ADMIN
    gv_wc_mail_send(
        $admin_email,
        '[Admin] User deleted: ' . $uname,
        'User deleted',
        array(
            'The following user has been <strong>deleted</strong>:',
            'Username: <strong>' . esc_html( $uname ) . '</strong><br>Email: <strong>' . esc_html( $uemail ) . '</strong>',
        ),
        false
    );
}, 10, 2 );

/*
 * 
Aanpassen

Vast adminadres instellen (i.p.v. site admin e-mail):

define('GV_APPROVAL_ADMIN_EMAIL', 'info@geminivalve.nl');


of via filter:

add_filter('gv_approval_admin_email', fn($email) => 'info@geminivalve.nl');
 */


// ===== Snippet #108: Credentials - Ban new users based on words in the request =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV — Registration Banned Words
 * Description: Blocks user creation if First Name, Last Name, or Email contains banned words. Works for WooCommerce & default WP registration. Adds a Users > Banned Words settings page.
 * Author: Gemini Valve
 * Version: 1.0.0
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'GV_BW_OPTION', 'gv_banned_words' );

/**
 * Get banned words as an array (lowercased, trimmed).
 * Admin enters one per line or comma-separated.
 */
function gv_bw_get_list(): array {
    $raw = (string) get_option( GV_BW_OPTION, "test\nspam\ndemo\nfake\nadmin" );
    // Split on newlines or commas
    $parts = preg_split( '/[\r\n,]+/', $raw );
    $parts = array_filter( array_map( function( $s ) {
        $s = trim( (string) $s );
        // Normalize wildcards like *.ru -> just keep text, treat as substring match
        return mb_strtolower( $s );
    }, $parts ) );
    return array_values( array_unique( $parts ) );
}

/**
 * Check if $text contains any banned word (case-insensitive).
 */
function gv_bw_contains_banned( string $text ): bool {
    if ( $text === '' ) return false;
    $text = mb_strtolower( $text );
    foreach ( gv_bw_get_list() as $needle ) {
        if ( $needle !== '' && mb_stripos( $text, $needle ) !== false ) {
            return true;
        }
    }
    return false;
}

/**
 * Unified validation routine used by all entry points.
 */
function gv_bw_validate_payload( $first_name, $last_name, $email ) : ?string {
    $first_name = is_string( $first_name ) ? sanitize_text_field( $first_name ) : '';
    $last_name  = is_string( $last_name )  ? sanitize_text_field( $last_name )  : '';
    $email      = is_string( $email )      ? sanitize_email( $email )           : '';

    if ( gv_bw_contains_banned( $first_name ) ) {
        return __( 'Registration Failed. Please contact us by phone.', 'gv-bw' );
    }
    if ( gv_bw_contains_banned( $last_name ) ) {
        return __( 'Registration Failed. Please contact us by phone. ', 'gv-bw' );
    }
    if ( gv_bw_contains_banned( $email ) ) {
        return __( 'Registration Failed. Please contact us by phone. ', 'gv-bw' );
    }

    return null; // OK
}

/**
 * Enforce on default WordPress registration (/wp-login.php?action=register).
 */
add_filter( 'registration_errors', function( $errors, $sanitized_user_login, $user_email ) {
    $first = isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : '';
    $last  = isset( $_POST['last_name'] )  ? wp_unslash( $_POST['last_name'] )  : '';
    $msg = gv_bw_validate_payload( $first, $last, $user_email );
    if ( $msg ) {
        $errors->add( 'gv_bw_blocked', $msg );
    }
    return $errors;
}, 10, 3 );

/**
 * Enforce on WooCommerce registration (My Account).
 */
add_action( 'woocommerce_register_post', function( $username, $email, $validation_errors ) {
    $first = isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : '';
    $last  = isset( $_POST['last_name'] )  ? wp_unslash( $_POST['last_name'] )  : '';
    $msg = gv_bw_validate_payload( $first, $last, $email );
    if ( $msg ) {
        $validation_errors->add( 'gv_bw_blocked', $msg );
    }
}, 10, 3 );

/**
 * Enforce when an admin creates a user in wp-admin (Users > Add New) or edits a user.
 */
add_action( 'user_profile_update_errors', function( $errors, $update, $user ) {
    // $user is a WP_User-like array when creating; defensively read from $_POST
    $first = isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : '';
    $last  = isset( $_POST['last_name'] )  ? wp_unslash( $_POST['last_name'] )  : '';
    $email = isset( $_POST['email'] )      ? wp_unslash( $_POST['email'] )      : '';
    $msg = gv_bw_validate_payload( $first, $last, $email );
    if ( $msg ) {
        $errors->add( 'gv_bw_blocked', $msg );
    }
}, 10, 3 );

/* ======================
 * Admin UI under Users
 * ====================== */

/**
 * Add submenu under "Users".
 */
add_action( 'admin_menu', function() {
    add_users_page(
        __( 'Banned Words', 'gv-bw' ),
        __( 'Banned Words', 'gv-bw' ),
        'manage_options',
        'gv-banned-words',
        'gv_bw_render_admin_page'
    );
});

/**
 * Register the option and sanitize input.
 */
add_action( 'admin_init', function() {
    register_setting( 'gv_bw_group', GV_BW_OPTION, [
        'type'              => 'string',
        'sanitize_callback' => function( $input ) {
            // Normalize newlines, strip tags
            $input = wp_kses_post( $input );
            $input = str_replace( [ "\r\n", "\r" ], "\n", $input );
            // Optional: limit size
            $input = substr( $input, 0, 20000 );
            return $input;
        },
        'default' => "test\nspam\ndemo\nfake\nadmin",
    ] );
});

/**
 * Render the settings page.
 */
function gv_bw_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'gv-bw' ) );
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Banned Words', 'gv-bw' ); ?></h1>
        <p><?php esc_html_e( 'Users will be blocked from being created if their First Name, Last Name, or Email address contains any of these words (substring match, case-insensitive).', 'gv-bw' ); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields( 'gv_bw_group' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="gv_bw_list"><?php esc_html_e( 'Banned words list', 'gv-bw' ); ?></label>
                    </th>
                    <td>
                        <textarea id="gv_bw_list" name="<?php echo esc_attr( GV_BW_OPTION ); ?>" rows="12" cols="80" class="large-text code" placeholder="One per line or comma-separated"><?php echo esc_textarea( (string) get_option( GV_BW_OPTION, '' ) ); ?></textarea>
                        <p class="description">
                            <?php esc_html_e( 'Examples: test, spam, demo, *@tempmail, disposable, noreply', 'gv-bw' ); ?><br>
                            <?php esc_html_e( 'Tip: Substring match means entering “tempmail” will block emails like user@tempmail.xyz.', 'gv-bw' ); ?>
                        </p>
                        <p>
                            <strong><?php esc_html_e( 'Current total:', 'gv-bw' ); ?></strong>
                            <?php echo esc_html( count( gv_bw_get_list() ) ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Save Changes', 'gv-bw' ) ); ?>
        </form>
    </div>
    <?php
}


// ===== Snippet #109: Credentials - only allow https://geminivalve.nl/registration/ =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - Lock Registration To /registration/
 * Description: Only allow user registration via https://geminivalve.nl/registration/ and block all other registration paths.
 * Author: Gemini Valve
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Change this if you ever move the page */
define( 'GV_REG_URL', home_url( '/registration/' ) );

/**
 * 1) Redirect all core registration entry points to /registration/
 *    - wp-login.php?action=register (GET/POST)
 *    - legacy wp-register.php
 */
add_action( 'login_init', function () {
    $action    = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : '';
    $is_legacy = ( basename( $_SERVER['PHP_SELF'] ?? '' ) === 'wp-register.php' );
    if ( $action === 'register' || $is_legacy ) {
        wp_safe_redirect( GV_REG_URL );
        exit;
    }
});

/**
 * 2) Point any "Register" links generated by WP/plugins to /registration/
 */
add_filter( 'register_url', function( $url ) { return GV_REG_URL; } );
add_filter( 'wp_registration_url', function( $url ) { return GV_REG_URL; } );

/**
 * 3) WooCommerce: allow account creation ONLY on /registration/.
 *    - Disable at My Account & Checkout
 *    - Enable on the dedicated page
 */
add_filter( 'woocommerce_registration_enabled', function( $enabled ) {
    return is_page() && untrailingslashit( esc_url_raw( GV_REG_URL ) ) === untrailingslashit( home_url( add_query_arg( [] , $_SERVER['REQUEST_URI'] ?? '' ) ) );
});
add_filter( 'woocommerce_enable_myaccount_registration', '__return_false' );
add_filter( 'woocommerce_checkout_registration_enabled', '__return_false' );

/**
 * 4) Optional safety: block REST user-creation for non-admins
 *    (prevents external POST /wp/v2/users from creating accounts).
 */
add_filter( 'rest_endpoints', function( $endpoints ) {
    if ( is_user_logged_in() && current_user_can( 'create_users' ) ) {
        return $endpoints; // admins can still use REST to create users
    }
    if ( isset( $endpoints['/wp/v2/users'] ) && is_array( $endpoints['/wp/v2/users'] ) ) {
        foreach ( $endpoints['/wp/v2/users'] as $i => $route ) {
            $methods = isset( $route['methods'] ) ? (array) $route['methods'] : [];
            if ( in_array( 'POST', $methods, true ) || in_array( 'CREATABLE', $methods, true ) ) {
                unset( $endpoints['/wp/v2/users'][ $i ] );
            }
        }
    }
    return $endpoints;
});

/**
 * 5) (Optional) Keep "Anyone can register" off globally.
 *    Your /registration/ page (Woo form or custom) still works because Woo bypasses that option.
 */
add_filter( 'pre_option_users_can_register', function() { return 0; } );


// ===== Snippet #110: Credentials - Register login, update, create and delete activity =====
// Scope: global | Priority: 5


/**
 * Plugin Name: GV - User Activity Log
 * Description: Logs user logins, login blocked (not approved), failed logins, password resets, account creation, profile updates, and user deletions. Admin page under Users → User Activity Log.
 * Author: Gemini Valve
 * Version: 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $gvual_db_version;
$gvual_db_version = '1.0.0'; // schema unchanged

function gvual_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'gv_user_audit';
}

function gvual_install() {
    global $wpdb, $gvual_db_version;
    $table = gvual_table_name();
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        event VARCHAR(40) NOT NULL,
        details LONGTEXT NULL,
        ip VARCHAR(100) NULL,
        user_agent TEXT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY event (event),
        KEY created_at (created_at)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    update_option( 'gvual_db_version', $gvual_db_version );
}
register_activation_hook( __FILE__, 'gvual_install' );

/** ---- Helpers ---- */
function gvual_now() { return current_time( 'mysql' ); }

function gvual_client_ip() {
    foreach ( [ 'HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR' ] as $k ) {
        if ( ! empty( $_SERVER[$k] ) ) {
            $ip = trim( explode( ',', $_SERVER[$k] )[0] );
            return sanitize_text_field( $ip );
        }
    }
    return '';
}

function gvual_user_agent() {
    return isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ), 0, 1000 ) : '';
}

function gvual_approval_meta_key() {
    if ( defined( 'GV_AA_META' ) ) {
        return GV_AA_META; // your existing constant, e.g. '_gv_account_approved'
    }
    return '_gv_account_approved';
}

/**
 * Insert a log row.
 * @param int    $user_id
 * @param string $event   One of: login, login_failed, login_blocked, password_reset, user_register, profile_update, user_deleted
 * @param array  $details Arbitrary key/value (will be JSON)
 */
function gvual_log( $user_id, $event, array $details = [] ) {
    global $wpdb;
    $table = gvual_table_name();

    $wpdb->insert(
        $table,
        [
            'user_id'    => (int) $user_id,
            'event'      => sanitize_key( $event ),
            'details'    => wp_json_encode( $details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
            'ip'         => gvual_client_ip(),
            'user_agent' => gvual_user_agent(),
            'created_at' => gvual_now(),
        ],
        [ '%d','%s','%s','%s','%s','%s' ]
    );
}

/** ------- Guarantee logging for NOT APPROVED accounts ------- */
/**
 * We intercept authenticate EARLY, check approval, log, then block.
 * Priority 5 ensures we run before most other auth handlers.
 */
add_filter( 'authenticate', function( $user, $username, $password ) {

    // Nothing to do if no username given
    if ( ! $username ) {
        return $user;
    }

    // Try to resolve user by login, then email
    $u = get_user_by( 'login', $username );
    if ( ! $u && is_email( $username ) ) {
        $u = get_user_by( 'email', $username );
    }

    if ( ! $u instanceof WP_User ) {
        return $user; // Unknown account: let normal flow continue (will be login_failed later)
    }

    // Read approval flag
    $approved = get_user_meta( $u->ID, gvual_approval_meta_key(), true );
    $is_approved = (string) $approved === '1';

    if ( ! $is_approved ) {
        // De-dupe guard for this exact attempt (IP + username within 60s)
        $dedupe_key = 'gvual_blk_' . md5( strtolower( $username ) . '|' . gvual_client_ip() );
        if ( ! get_transient( $dedupe_key ) ) {
            gvual_log( $u->ID, 'login_blocked', [
                'user_login' => $u->user_login,
                'reason'     => 'User not approved',
            ] );
            set_transient( $dedupe_key, 1, 60 );
        }

        // Block login with our canonical error code
        return new WP_Error(
            'gv_not_approved',
            __( 'Your account has not been approved yet.', 'gv' )
        );
    }

    return $user;
}, 5, 3 );

/**
 * Safety net: if another plugin already blocked with our error code,
 * add a log entry (de-duped) even if they ran earlier.
 */
add_filter( 'authenticate', function( $user, $username ) {
    if ( is_wp_error( $user ) && in_array( 'gv_not_approved', $user->get_error_codes(), true ) ) {
        $u = get_user_by( 'login', $username );
        if ( ! $u && is_email( $username ) ) {
            $u = get_user_by( 'email', $username );
        }
        $uid = $u instanceof WP_User ? (int) $u->ID : 0;

        $dedupe_key = 'gvual_blk_' . md5( strtolower( (string) $username ) . '|' . gvual_client_ip() );
        if ( ! get_transient( $dedupe_key ) ) {
            gvual_log( $uid, 'login_blocked', [
                'user_login' => $u instanceof WP_User ? $u->user_login : (string) $username,
                'reason'     => 'User not approved',
            ] );
            set_transient( $dedupe_key, 1, 60 );
        }
    }
    return $user;
}, 99, 2 );

/** ---- Other event hooks ---- */

// Successful login
add_action( 'wp_login', function( $user_login, $user ) {
    gvual_log( $user->ID, 'login', [ 'user_login' => $user_login ] );
}, 10, 2 );

// Failed login
add_action( 'wp_login_failed', function( $username ) {
    $user_id = 0;
    $u = get_user_by( 'login', $username );
    if ( ! $u && is_email( $username ) ) $u = get_user_by( 'email', $username );
    if ( $u ) $user_id = (int) $u->ID;

    gvual_log( $user_id, 'login_failed', [
        'attempt' => $username,
        'note'    => 'Authentication failed',
    ] );
}, 10, 1 );

// Password reset (via reset flow)
add_action( 'password_reset', function( $user ) {
    gvual_log( $user->ID, 'password_reset', [ 'note' => 'Password reset completed' ] );
}, 10, 1 );

// Account creation
add_action( 'user_register', function( $user_id ) {
    $u = get_userdata( $user_id );
    gvual_log( $user_id, 'user_register', [
        'user_login' => $u ? $u->user_login : '',
        'user_email' => $u ? $u->user_email : '',
    ] );
}, 10, 1 );

// Profile update (diff core fields)
add_action( 'profile_update', function( $user_id, $old_user_data ) {
    $new = get_userdata( $user_id );
    $changed = [];
    foreach ( [ 'user_email', 'display_name', 'user_url' ] as $k ) {
        $old = isset( $old_user_data->$k ) ? (string) $old_user_data->$k : '';
        $now = isset( $new->$k ) ? (string) $new->$k : '';
        if ( $old !== $now ) {
            $changed[$k] = [ 'from' => $old, 'to' => $now ];
        }
    }
    gvual_log( $user_id, 'profile_update', [ 'changed' => $changed ] );
}, 10, 2 );

// User deleted
add_action( 'deleted_user', function( $user_id, $reassign, $user ) {
    $reassign_id = is_numeric( $reassign ) ? (int) $reassign : 0;
    $snapshot = [];
    if ( $user instanceof WP_User ) {
        $snapshot = [
            'user_login'   => $user->user_login,
            'user_email'   => $user->user_email,
            'display_name' => $user->display_name,
        ];
    }
    if ( $reassign_id ) {
        $r = get_userdata( $reassign_id );
        $snapshot['reassign_to'] = $r ? ( $r->user_login . ' (#' . $reassign_id . ')' ) : ('#' . $reassign_id);
    } else {
        $snapshot['reassign_to'] = 'none';
    }
    gvual_log( (int) $user_id, 'user_deleted', $snapshot );
}, 10, 3 );

/** ---- Admin Screen: Users → User Activity Log ---- */

add_action( 'admin_menu', function() {
    add_users_page(
        __( 'User Activity Log', 'gv' ),
        __( 'User Activity Log', 'gv' ),
        'list_users',
        'gv-user-activity-log',
        'gvual_render_admin_page'
    );
});

function gvual_render_admin_page() {
    if ( ! current_user_can( 'list_users' ) ) {
        wp_die( __( 'You do not have permission to access this page.' ) );
    }

    global $wpdb;
    $table = gvual_table_name();

    // Filters
    $per_page = max( 10, min( 200, (int) ( $_GET['per_page'] ?? 50 ) ) );
    $paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
    $offset   = ( $paged - 1 ) * $per_page;

    $event    = isset( $_GET['event'] ) ? sanitize_key( $_GET['event'] ) : '';
    $s        = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

    $where    = 'WHERE 1=1';
    $params   = [];

    if ( $event ) {
        $where   .= ' AND event = %s';
        $params[] = $event;
    }

    // User search
    if ( $s ) {
        $users = get_users( [
            'search'         => '*' . esc_attr( $s ) . '*',
            'search_columns' => [ 'user_login', 'user_email', 'display_name' ],
            'fields'         => [ 'ID' ],
            'number'         => 200,
        ] );

        if ( $users ) {
            $user_ids = wp_list_pluck( $users, 'ID' );
            $placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
            $where .= " AND user_id IN ($placeholders)";
            $params = array_merge( $params, $user_ids );
        } else {
            $where .= ' AND 1=0';
        }
    }

    // Count
    $sql_count = "SELECT COUNT(*) FROM {$table} {$where}";
    $total = (int) $wpdb->get_var( $wpdb->prepare( $sql_count, $params ) );

    // Rows
    $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d";
    $rows = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $params, [ $per_page, $offset ] ) ) );

    $events = [
        ''               => __( 'All events', 'gv' ),
        'login'          => 'login',
        'login_failed'   => 'login_failed',
        'login_blocked'  => 'login_blocked', // NEW
        'password_reset' => 'password_reset',
        'user_register'  => 'user_register',
        'profile_update' => 'profile_update',
        'user_deleted'   => 'user_deleted',
    ];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'User Activity Log', 'gv' ); ?></h1>

        <form method="get" style="margin:12px 0;">
            <input type="hidden" name="page" value="gv-user-activity-log" />
            <label>
                <?php esc_html_e( 'Event:', 'gv' ); ?>
                <select name="event">
                    <?php foreach ( $events as $val => $label ) : ?>
                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $event, $val ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label style="margin-left:10px;">
                <?php esc_html_e( 'User search:', 'gv' ); ?>
                <input type="search" name="s" value="<?php echo esc_attr( $s ); ?>" placeholder="login / email / name" />
            </label>
            <label style="margin-left:10px;">
                <?php esc_html_e( 'Per page:', 'gv' ); ?>
                <input type="number" name="per_page" min="10" max="200" value="<?php echo esc_attr( $per_page ); ?>" style="width:80px;" />
            </label>
            <button class="button"><?php esc_html_e( 'Filter', 'gv' ); ?></button>
        </form>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:160px;"><?php esc_html_e( 'Time', 'gv' ); ?></th>
                    <th style="width:220px;"><?php esc_html_e( 'User', 'gv' ); ?></th>
                    <th style="width:140px;"><?php esc_html_e( 'Event', 'gv' ); ?></th>
                    <th><?php esc_html_e( 'Details', 'gv' ); ?></th>
                    <th style="width:130px;"><?php esc_html_e( 'IP', 'gv' ); ?></th>
                    <th><?php esc_html_e( 'User Agent', 'gv' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $rows ) ) : ?>
                <tr><td colspan="6"><?php esc_html_e( 'No activity found for this filter.', 'gv' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $rows as $r ) :
                    $u = $r->user_id ? get_userdata( (int) $r->user_id ) : null;
                    $user_label = $u
                        ? sprintf(
                            '<a href="%s">%s</a><br><span class="description">%s</span>',
                            esc_url( admin_url( 'user-edit.php?user_id=' . (int) $u->ID ) ),
                            esc_html( $u->display_name ?: $u->user_login ),
                            esc_html( $u->user_email )
                        )
                        : esc_html__( '(user not found / guest attempt)', 'gv' );

                    $details = '';
                    if ( $r->details ) {
                        $decoded = json_decode( $r->details, true );
                        if ( is_array( $decoded ) ) {
                            $pairs = [];
                            foreach ( $decoded as $k => $v ) {
                                if ( is_array( $v ) ) $v = wp_json_encode( $v );
                                $pairs[] = sprintf( '<strong>%s</strong>: %s', esc_html( $k ), esc_html( (string) $v ) );
                            }
                            $details = implode( ' | ', $pairs );
                        } else {
                            $details = esc_html( $r->details );
                        }
                    }
                ?>
                <tr>
                    <td><?php echo esc_html( $r->created_at ); ?></td>
                    <td><?php echo $user_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                    <td><code><?php echo esc_html( $r->event ); ?></code></td>
                    <td><?php echo $details ? $details : '&mdash;'; // phpcs:ignore ?></td>
                    <td><?php echo esc_html( $r->ip ); ?></td>
                    <td style="word-break:break-all;"><?php echo esc_html( $r->user_agent ); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <?php
        $total_pages = max( 1, (int) ceil( $total / $per_page ) );
        if ( $total_pages > 1 ) {
            $base_url = remove_query_arg( [ 'paged' ] );
            echo '<div class="tablenav"><div class="tablenav-pages">';
            echo paginate_links( [
                'base'      => add_query_arg( 'paged', '%#%', $base_url ),
                'format'    => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'current'   => $paged,
                'total'     => $total_pages,
            ] );
            echo '</div></div>';
        }
        ?>
    </div>
    <?php
}


// ===== Snippet #111: Credentials - Log blocked login in Users =====
// Scope: global | Priority: 10

/**
 * Log blocked logins when user is not approved
 */
add_filter( 'authenticate', function( $user, $username, $password ) {
    if ( $user instanceof WP_User ) {
        // Check your approval flag
        $approved = get_user_meta( $user->ID, '_gv_account_approved', true );
        if ( $approved !== '1' ) {
            // Log event
            if ( function_exists( 'gvual_log' ) ) {
                gvual_log( $user->ID, 'login_blocked', [
                    'user_login' => $username,
                    'reason'     => 'User not approved',
                ] );
            }

            // Return WP error to block login
            return new WP_Error(
                'gv_not_approved',
                __( 'Your account has not been approved yet.', 'gv' )
            );
        }
    }

    return $user;
}, 20, 3 );


// ===== Snippet #112: Credentials - Extra fetch for login fails =====
// Scope: global | Priority: 5

/**
 * GV - Approval Gate Logger (WooCommerce aware)
 * Logs login_blocked when a user is not approved, including Woo My Account logins.
 * Put in wp-content/mu-plugins/ so it loads very early.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'GV_AA_META' ) ) {
    define( 'GV_AA_META', '_gv_account_approved' ); // adjust if you use a different key
}

/** ---- tiny helpers ---- */
function gvual_client_ip_simple() {
    foreach ( [ 'HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR' ] as $k ) {
        if ( ! empty( $_SERVER[$k] ) ) return trim( explode( ',', $_SERVER[$k] )[0] );
    }
    return '';
}
function gv_block_dedupe_key( $identifier ) {
    return 'gvual_blk_' . md5( strtolower( (string) $identifier ) . '|' . gvual_client_ip_simple() );
}
function gv_resolve_user_from_identifier( $id ) {
    if ( ! $id ) return null;
    $u = get_user_by( 'login', $id );
    if ( ! $u && is_email( $id ) ) $u = get_user_by( 'email', $id );
    return $u instanceof WP_User ? $u : null;
}
function gv_is_approved_flag( $raw ) {
    $v = strtolower( (string) $raw );
    return in_array( $v, [ '1','yes','true','approved','on' ], true );
}

/** ---- fallback logger if main plugin isn't loaded ---- */
if ( ! function_exists( 'gvual_log' ) ) {
    function gvual_log( $user_id, $event, array $details = [] ) {
        global $wpdb;
        $table = $wpdb->prefix . 'gv_user_audit';
        // If table doesn't exist, bail quietly
        $exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );
        if ( $exists !== $table ) return;

        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ), 0, 1000 ) : '';
        $wpdb->insert(
            $table,
            [
                'user_id'    => (int) $user_id,
                'event'      => sanitize_key( $event ),
                'details'    => wp_json_encode( $details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ),
                'ip'         => gvual_client_ip_simple(),
                'user_agent' => $ua,
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d','%s','%s','%s','%s','%s' ]
        );
    }
}

/** ===== 1) EARLY core auth: authenticate (all login endpoints) ===== */
add_filter( 'authenticate', function( $user, $username, $password ) {
    $u = gv_resolve_user_from_identifier( $username );
    if ( ! $u ) return $user;

    if ( gv_is_approved_flag( get_user_meta( $u->ID, GV_AA_META, true ) ) ) return $user;

    $k = gv_block_dedupe_key( $username );
    if ( ! get_transient( $k ) ) {
        gvual_log( $u->ID, 'login_blocked', [ 'user_login' => $u->user_login, 'reason' => 'User not approved' ] );
        set_transient( $k, 1, 60 );
    }
    return new WP_Error( 'gv_not_approved', __( 'Your account has not been approved yet.', 'gv' ) );
}, 1, 3 );

/** ===== 2) Safety net: core user check ===== */
add_filter( 'wp_authenticate_user', function( $user, $password ) {
    if ( ! ( $user instanceof WP_User ) ) return $user;
    if ( gv_is_approved_flag( get_user_meta( $user->ID, GV_AA_META, true ) ) ) return $user;

    $k = gv_block_dedupe_key( $user->user_login );
    if ( ! get_transient( $k ) ) {
        gvual_log( $user->ID, 'login_blocked', [ 'user_login' => $user->user_login, 'reason' => 'User not approved' ] );
        set_transient( $k, 1, 60 );
    }
    return new WP_Error( 'gv_not_approved', __( 'Your account has not been approved yet.', 'gv' ) );
}, 1, 2 );

/** ===== 3) WooCommerce My Account: validate before wp_signon =====
 * This runs specifically for /my-account/ login form submissions.
 * It ensures a log row even if another plugin short-circuits authenticate.
 */
add_filter( 'woocommerce_process_login_errors', function( $errors, $username, $password ) {
    // If there are already errors unrelated to approval, we still may want to log.
    $u = gv_resolve_user_from_identifier( $username );
    if ( ! $u ) return $errors;

    if ( gv_is_approved_flag( get_user_meta( $u->ID, GV_AA_META, true ) ) ) return $errors;

    // Log once per attempt (username+IP)
    $k = gv_block_dedupe_key( $username );
    if ( ! get_transient( $k ) ) {
        gvual_log( $u->ID, 'login_blocked', [ 'user_login' => $u->user_login, 'reason' => 'User not approved (WooCommerce)' ] );
        set_transient( $k, 1, 60 );
    }

    // Add a visible Woo error so the customer sees the right message on /my-account/
    $errors->add( 'gv_not_approved', __( 'Your account has not been approved yet.', 'gv' ) );
    return $errors;
}, 1, 3 );


// ===== Snippet #114: Products - Add request proposal when a product does not have a price =====
// Scope: global | Priority: 10


/**
 * GeminiValve – Request Price flow (no-price products)
 * - Registers "Proposal Requested" order status
 * - Shows "Request price" button for logged-in users when product has no price
 * - Creates a new order and emails sales with admin link
 */

// 1) Register custom order status
add_action( 'init', function () {
	register_post_status( 'wc-proposal-requested', array(
		'label'                     => _x( 'Proposal Requested', 'Order status', 'geminivalve' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Proposal Requested (%s)', 'Proposal Requested (%s)', 'geminivalve' ),
	) );
} );

add_filter( 'wc_order_statuses', function ( $statuses ) {
	$new = array();
	foreach ( $statuses as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'wc-pending' === $key ) {
			$new['wc-proposal-requested'] = _x( 'Proposal Requested', 'Order status', 'geminivalve' );
		}
	}
	// Fallback: if pending wasn't present for some reason
	if ( ! isset( $new['wc-proposal-requested'] ) ) {
		$new['wc-proposal-requested'] = _x( 'Proposal Requested', 'Order status', 'geminivalve' );
	}
	return $new;
} );

// Helper: does product have no price?
function gv_product_has_no_price( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) return false;

	// Simple check: empty price string
	$price = $product->get_price();
	if ( '' === $price || null === $price ) return true;

	// Some themes/variations may mask price; keep it simple for now
	return false;
}

// 2) Front-end button on SINGLE product
add_action( 'woocommerce_single_product_summary', function () {
	if ( ! is_user_logged_in() ) return;
	global $product;
	if ( ! $product instanceof WC_Product ) return;
	if ( ! gv_product_has_no_price( $product ) ) return;

	echo '<form method="post" class="gv-request-price-form" style="margin:1rem 0;">';
	wp_nonce_field( 'gv_request_price', 'gv_request_price_nonce' );
	echo '<input type="hidden" name="gv_product_id" value="' . esc_attr( $product->get_id() ) . '"/>';
	echo '<button type="submit" class="button alt">'
		. esc_html__( 'Request price', 'geminivalve' )
		. '</button>';
	echo '</form>';
}, 29 );

// 3) Front-end button on ARCHIVE (category/shop)
add_action( 'woocommerce_after_shop_loop_item', function () {
	if ( ! is_user_logged_in() ) return;

	global $product;
	if ( ! $product instanceof WC_Product ) return;
	if ( ! gv_product_has_no_price( $product ) ) return;

	echo '<form method="post" class="gv-request-price-form" style="margin:0.5rem 0 1rem;">';
	wp_nonce_field( 'gv_request_price', 'gv_request_price_nonce' );
	echo '<input type="hidden" name="gv_product_id" value="' . esc_attr( $product->get_id() ) . '"/>';
	echo '<button type="submit" class="button alt">'
		. esc_html__( 'Request price', 'geminivalve' )
		. '</button>';
	echo '</form>';
}, 15 );

// 4) Handler: create order, set status, email sales
add_action( 'template_redirect', function () {
	if ( empty( $_POST['gv_request_price_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['gv_request_price_nonce'], 'gv_request_price' ) ) return;

	if ( ! is_user_logged_in() ) {
		wc_add_notice( __( 'Please log in first.', 'geminivalve' ), 'error' );
		wp_safe_redirect( wc_get_page_permalink( 'myaccount' ) );
		exit;
	}

	$product_id = isset( $_POST['gv_product_id'] ) ? absint( $_POST['gv_product_id'] ) : 0;
	$product    = wc_get_product( $product_id );

	if ( ! $product || ! gv_product_has_no_price( $product ) ) {
		wc_add_notice( __( 'This request is not available for this product.', 'geminivalve' ), 'error' );
		wp_safe_redirect( $product_id ? get_permalink( $product_id ) : home_url( '/' ) );
		exit;
	}

	$user_id   = get_current_user_id();
	$user      = get_userdata( $user_id );

	// Create order for this customer
	$order = wc_create_order( array( 'customer_id' => $user_id ) );

	// Add product (qty 1)
	$order->add_product( $product, 1 );

	// Copy basic billing info if available (helps Sales)
	$billing_fields = array(
		'billing_first_name', 'billing_last_name', 'billing_company',
		'billing_email', 'billing_phone', 'billing_address_1',
		'billing_address_2', 'billing_city', 'billing_postcode',
		'billing_state', 'billing_country',
	);
	foreach ( $billing_fields as $meta_key ) {
		$val = get_user_meta( $user_id, $meta_key, true );
		if ( $val ) {
			$order->update_meta_data( '_' . $meta_key, $val );
			$setter = 'set_' . $meta_key;
			if ( is_callable( array( $order, $setter ) ) ) {
				$order->$setter( $val );
			}
		}
	}

	// Zero totals (no price yet)
	$order->calculate_totals( false );

	// Mark as Proposal Requested
	$order->update_status( 'proposal-requested', __( 'Created from “Request price” button.', 'geminivalve' ), true );
	$order->update_meta_data( '_gv_proposal_request', '1' );
	$order->save();

	// Build email to Sales
	$order_id   = $order->get_id();
	$admin_link = admin_url( 'post.php?post=' . $order_id . '&action=edit' );

	$product_title = $product->get_name();
	$product_link  = get_permalink( $product_id );
	$customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	if ( '' === $customer_name ) $customer_name = $user ? $user->display_name : '';

	$subject = sprintf( '[Proposal Requested] Order #%s – %s', $order->get_order_number(), $product_title );

	$lines = array();
	$lines[] = 'A new proposal request has been created.';
	$lines[] = '';
	$lines[] = 'Product: ' . $product_title;
	$lines[] = 'Product URL: ' . $product_link;
	$lines[] = 'Customer: ' . $customer_name;
	$lines[] = 'Customer email: ' . ( $order->get_billing_email() ?: ( $user ? $user->user_email : '' ) );
	$lines[] = '';
	$lines[] = 'Review & process: ' . $admin_link;

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	wp_mail( 'sales@geminivalve.nl', $subject, implode( "\n", $lines ), $headers );

	// Front-end feedback
	wc_add_notice( __( 'Thanks! We’ve forwarded your request to Sales. You’ll receive a proposal soon.', 'geminivalve' ), 'success' );
	wp_safe_redirect( get_permalink( $product_id ) );
	exit;
} );


// ===== Snippet #115: ProductsBulk - Update data content =====
// Scope: admin | Priority: 10


/**
 * Plugin Name: GV Bulk Edit - Missing Descriptions (Supplier Post + AI Assist)
 * Description: Lists products with _gv_proc_supplier_id + _gv_proc_supplier_sku filled but missing descriptions. Joins supplier post (name + website). Optional AI writer uses get_option('gv_openai_api_key').
 * Version: 1.6
 * Author: Gemini Valve
 */

if ( ! defined( 'ABSPATH' ) ) exit;



/*

add_action( 'admin_menu', function () {
	
//	$parent = gv_bi_get_bulk_parent_slug();
	add_submenu_page(
//		$parent,
		'edit.php?post_type=product',
		'Bulk Edit - Missing Descr.',
		'Bulk Edit - Missing Descr.',
		'manage_woocommerce',
		'gv-bulk-edit-missing-desc',
		'gv_render_missing_desc_page_gvkeys_supplierpost'
	);
});
*/

/**
 * Page renderer
 */
function gv_render_missing_desc_page_gvkeys_supplierpost() {
	global $wpdb;

	$per_page = 50;
	$paged    = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
	$offset   = ($paged - 1) * $per_page;

	$search       = isset($_GET['s']) ? sanitize_text_field( wp_unslash($_GET['s']) ) : '';
	$where_search = $search !== '' ? $wpdb->prepare(" AND p.post_title LIKE %s ", '%'.$wpdb->esc_like($search).'%') : '';

	// Build the base SQL: exact meta keys + join supplier post (title) + supplier website
	$base_sql = "
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} ms
			ON ms.post_id = p.ID
			AND ms.meta_key = '_gv_proc_supplier_id'
			AND TRIM(COALESCE(ms.meta_value,'')) <> ''
		INNER JOIN {$wpdb->postmeta} mk
			ON mk.post_id = p.ID
			AND mk.meta_key = '_gv_proc_supplier_sku'
			AND TRIM(COALESCE(mk.meta_value,'')) <> ''
		LEFT JOIN {$wpdb->posts} sp
			ON sp.ID = CAST(ms.meta_value AS UNSIGNED)  /* supplier post */
		LEFT JOIN {$wpdb->postmeta} sw
			ON sw.post_id = sp.ID
			AND sw.meta_key = '_gv_website'
		WHERE p.post_type = 'product'
		  AND p.post_status IN ('publish','draft','private','pending')
		  AND (
				TRIM(COALESCE(p.post_content,'')) = ''
			 OR TRIM(COALESCE(p.post_excerpt,'')) = ''
		  )
		  {$where_search}
	";

	$total = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT p.ID) {$base_sql}" );

	$rows = $wpdb->get_results( $wpdb->prepare("
		SELECT DISTINCT
			p.ID,
			p.post_title,
			p.post_content,
			p.post_excerpt,
			ms.meta_value AS supplier_id,
			mk.meta_value AS supplier_sku,
			sp.post_title AS supplier_name,
			sw.meta_value AS supplier_website
		{$base_sql}
		ORDER BY p.post_title ASC
		LIMIT %d OFFSET %d
	", $per_page, $offset ) );

	$nonce = wp_create_nonce('gv_ai_desc');
	$has_api = (bool) get_option('gv_openai_api_key');
	
	// 1) point actions to admin.php (not edit.php)
	$action = admin_url('admin.php?page=gv-bulk-edit-missing-desc');

	// --- Search form ---
	echo '<form method="get" action="' . esc_url( $action ) . '">';
	// REMOVE this line (it breaks capability routing):
	// echo '<input type="hidden" name="post_type" value="product" />';
	// keep only the page param:
	echo '<input type="hidden" name="page" value="gv-bulk-edit-missing-desc" />';
	echo '<input type="search" name="s" placeholder="Search by product title…" value="'.esc_attr($search).'" />';
	submit_button('Filter', 'secondary', '', false);
	echo '</form>';

	// API status note
	echo '<div class="notice notice-info" style="padding:12px;margin:12px 0;">';
	if ( $has_api ) {
		echo '<p>✅ OpenAI key found via <code>get_option(\'gv_openai_api_key\')</code>. You can use “AI draft” to generate suggestions. (Optional org id via <code>gv_openai_org_id</code>.)</p>';
	} else {
		echo '<p>ℹ️ No OpenAI key in <code>gv_openai_api_key</code>. The list works, but “AI draft” buttons will be disabled.</p>';
	}
	echo '</div>';

	if ( $total === 0 ) {
		echo '<p><strong>No matches.</strong></p></div>';
		return;
	}

	printf('<p><strong>%d</strong> product(s) found.</p>', $total);

	echo '<table class="widefat fixed striped">';
	echo '<thead><tr>
		<th style="width:70px;">ID</th>
		<th>Product</th>
		<th>Supplier</th>
		<th>Supplier SKU</th>
		<th>Supplier Website</th>
		<th>Missing</th>
		<th style="width:210px;">Actions</th>
	</tr></thead><tbody>';

	foreach ( $rows as $r ) {
		$missing = [];
		if ( trim((string)$r->post_content) === '' ) $missing[] = 'Description';
		if ( trim((string)$r->post_excerpt) === '' ) $missing[] = 'Short Description';

		$supp_name = $r->supplier_name ? $r->supplier_name : ('#'.$r->supplier_id);
		$site = $r->supplier_website ? esc_url($r->supplier_website) : '';

		echo '<tr data-product-id="'.esc_attr($r->ID).'">';
		echo '<td>'.esc_html($r->ID).'</td>';
		echo '<td><a href="'.esc_url(get_edit_post_link($r->ID)).'">'.esc_html($r->post_title).'</a></td>';
		echo '<td>'.esc_html($supp_name).'</td>';
		echo '<td>'.esc_html($r->supplier_sku).'</td>';
		echo '<td>'.($site ? '<a href="'.$site.'" target="_blank" rel="noopener">Website</a>' : '&mdash;').'</td>';
		echo '<td><strong>'.esc_html(implode(', ', $missing)).'</strong></td>';
		echo '<td>';
			echo '<a class="button button-small" href="'.esc_url(get_edit_post_link($r->ID)).'">Edit</a> ';
			echo $has_api
				? '<button type="button" class="button button-small gv-ai-draft" data-id="'.esc_attr($r->ID).'" data-nonce="'.esc_attr($nonce).'">AI draft</button>'
				: '<button type="button" class="button button-small" disabled title="Add gv_openai_api_key in Options">AI draft</button>';
		echo '</td>';
		echo '</tr>';

		// Hidden inline panel for AI output
		echo '<tr class="gv-ai-row" id="gv-ai-row-'.esc_attr($r->ID).'" style="display:none;background:#fafafa">';
		echo '<td colspan="7">
				<div class="gv-ai-wrap">
					<p><strong>AI suggestions</strong></p>
					<label>Description</label><br/>
					<textarea class="large-text code gv-ai-desc" rows="6"></textarea>
					<br/><br/>
					<label>Short Description (excerpt)</label><br/>
					<textarea class="large-text code gv-ai-excerpt" rows="3"></textarea>
					<p class="submit">
						<button class="button button-primary gv-ai-save" data-id="'.esc_attr($r->ID).'" data-nonce="'.esc_attr($nonce).'">Save to product</button>
						<button class="button gv-ai-cancel" data-id="'.esc_attr($r->ID).'">Close</button>
						<span class="gv-ai-status" style="margin-left:8px;"></span>
					</p>
				</div>
			</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';

	// Pagination
	$total_pages = (int) ceil($total / $per_page);
	if ( $total_pages > 1 ) {
		// build base on admin.php
		$base_url = add_query_arg(array(
			'page' => 'gv-bulk-edit-missing-desc',
			's'    => $search,
		), admin_url('admin.php'));

		echo '<div class="tablenav"><div class="tablenav-pages">';
		for ( $i=1; $i<=$total_pages; $i++ ) {
			$url = add_query_arg('paged', $i, $base_url);
			$class = ($i === $paged) ? ' class="page-numbers current"' : ' class="page-numbers"';
			echo '<a'.$class.' href="'.esc_url($url).'">'.esc_html($i).'</a> ';
		}
		echo '</div></div>';
	}

	// Minimal inline JS (no separate enqueue needed in a snippet)
	?>
	<script>
	(function(){
		const $ = window.jQuery;
		function row(pid){ return $('#gv-ai-row-'+pid); }
		$(document).on('click', '.gv-ai-draft', function(){
			const btn = $(this), pid = btn.data('id'), nonce = btn.data('nonce');
			btn.prop('disabled', true).text('Drafting…');
			row(pid).show().find('.gv-ai-status').text('Drafting with ChatGPT…');
			$.post(ajaxurl, { action: 'gv_ai_generate_desc', pid, _wpnonce: nonce }, function(resp){
				btn.prop('disabled', false).text('AI draft');
				if(!resp || !resp.success){ row(pid).find('.gv-ai-status').text(resp && resp.data ? resp.data : 'Error'); return; }
				row(pid).find('.gv-ai-desc').val(resp.data.description || '');
				row(pid).find('.gv-ai-excerpt').val(resp.data.excerpt || '');
				row(pid).find('.gv-ai-status').text('Draft ready. Review and Save.');
			}).fail(function(){
				btn.prop('disabled', false).text('AI draft');
				row(pid).find('.gv-ai-status').text('Request failed.');
			});
		});
		$(document).on('click', '.gv-ai-save', function(){
			const pid = $(this).data('id'), nonce = $(this).data('nonce');
			const desc = row(pid).find('.gv-ai-desc').val();
			const ex = row(pid).find('.gv-ai-excerpt').val();
			row(pid).find('.gv-ai-status').text('Saving…');
			$.post(ajaxurl, { action: 'gv_ai_save_desc', pid, desc, ex, _wpnonce: nonce }, function(resp){
				if(!resp || !resp.success){ row(pid).find('.gv-ai-status').text(resp && resp.data ? resp.data : 'Error'); return; }
				row(pid).find('.gv-ai-status').text('Saved. Reload to update table.');
			}).fail(function(){
				row(pid).find('.gv-ai-status').text('Save failed.');
			});
		});
		$(document).on('click', '.gv-ai-cancel', function(){
			row($(this).data('id')).hide();
		});
	})();
	</script>
	<?php
	echo '</div>';
}

/**
 * AJAX: Generate descriptions via OpenAI (uses options gv_openai_api_key, gv_openai_org_id)
 */
add_action('wp_ajax_gv_ai_generate_desc', function(){
	if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error('Unauthorized', 403);
	check_ajax_referer('gv_ai_desc');
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
	if ( ! $pid ) wp_send_json_error('Missing product id', 400);

	$key = get_option('gv_openai_api_key');
	if ( ! $key ) wp_send_json_error('OpenAI key not set in option gv_openai_api_key', 400);
	$org = get_option('gv_openai_org_id');

	$post = get_post($pid);
	if ( ! $post || $post->post_type !== 'product' ) wp_send_json_error('Invalid product', 400);

	// Gather helpful context
	$title = $post->post_title;
	$sku = get_post_meta($pid, '_gv_proc_supplier_sku', true);
	$supp_id = get_post_meta($pid, '_gv_proc_supplier_id', true);
	$supp_name = $supp_id ? get_the_title((int)$supp_id) : '';
	$supp_site = $supp_id ? get_post_meta((int)$supp_id, '_gv_website', true) : '';

	$sys = "You write concise, technically accurate B2B product copy for industrial valves and accessories. Use neutral EU English. Avoid fluff.";
	$user = "Create two fields for a WooCommerce product:\n\n".
	        "1) Description (80–150 words) — clear benefits, typical use, key specs where known. Avoid marketing hype.\n".
	        "2) Short Description (1–2 sentences).\n\n".
	        "Product: {$title}\nSupplier: {$supp_name}\nSupplier SKU: {$sku}\nSupplier website: {$supp_site}\n".
	        "If specs are unknown, keep wording generic but useful.";

	$body = array(
		'model' => 'gpt-4o-mini', // lightweight, cost-effective; change if you prefer
		'messages' => array(
			array('role' => 'system', 'content' => $sys),
			array('role' => 'user',   'content' => $user),
		),
		'temperature' => 0.4,
		'max_tokens'  => 400,
	);

	$args = array(
		'timeout' => 30,
		'headers' => array(
			'Authorization'   => 'Bearer ' . $key,
			'Content-Type'    => 'application/json',
		),
		'body' => wp_json_encode($body),
	);

	// Optional org header
	if ( $org ) $args['headers']['OpenAI-Organization'] = $org;

	$resp = wp_remote_post('https://api.openai.com/v1/chat/completions', $args);
	if ( is_wp_error($resp) ) wp_send_json_error('HTTP error: '.$resp->get_error_message(), 500);

	$code = wp_remote_retrieve_response_code($resp);
	$json = json_decode( wp_remote_retrieve_body($resp), true );

	if ( $code !== 200 || empty($json['choices'][0]['message']['content']) ) {
		$msg = isset($json['error']['message']) ? $json['error']['message'] : 'Unexpected API response';
		wp_send_json_error('OpenAI: '.$msg, 500);
	}

	$text = trim( (string) $json['choices'][0]['message']['content'] );

	// Parse the two fields (simple split heuristic)
	$desc = $text; $excerpt = '';
	if ( preg_match('~Short\s*Description[:\-]\s*(.+)$~i', $text, $m) ) {
		$excerpt = trim($m[1]);
		$desc = trim( preg_replace('~Short\s*Description[:\-]\s*.+$~is', '', $text) );
	} elseif ( preg_match('~Description[:\-]\s*(.+?)$~is', $text, $m) ) {
		$desc = trim($m[1]);
	}

	wp_send_json_success(array(
		'description' => $desc,
		'excerpt'     => $excerpt,
	));
});

/**
 * AJAX: Save descriptions back to product
 */
add_action('wp_ajax_gv_ai_save_desc', function(){
	if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error('Unauthorized', 403);
	check_ajax_referer('gv_ai_desc');
	$pid  = isset($_POST['pid'])  ? intval($_POST['pid']) : 0;
	$desc = isset($_POST['desc']) ? wp_kses_post( wp_unslash($_POST['desc']) ) : '';
	$ex   = isset($_POST['ex'])   ? sanitize_textarea_field( wp_unslash($_POST['ex']) ) : '';

	if ( ! $pid ) wp_send_json_error('Missing product id', 400);

	$upd = array( 'ID' => $pid );
	if ( $desc !== '' ) $upd['post_content'] = $desc;
	if ( $ex !== '' )   $upd['post_excerpt'] = $ex;

	$r = wp_update_post( $upd, true );
	if ( is_wp_error($r) ) wp_send_json_error( $r->get_error_message(), 500 );

	wp_send_json_success('Saved');
});


// ===== Snippet #118: ProductsBulk - Update data content with AI split in desc, short desc, TAGS and Brand  [CLONE] =====
// Scope: admin | Priority: 10


/**
 * Plugin Name: GV Bulk Edit → Enrich data (filters + split AI)
 * Description: Under Bulk Edit → Enrich data: list products that have supplier & supplier SKU but miss Description/Short Description/Tags/Brand (filterable). Supplier is a post (title + _gv_website). Separate AI for Description, Short Description (from Description only), Tags, and Brand.
 * Version: 2.0
 * Author: Gemini Valve
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ---- MENU WIRING FIX ----
// Try to hook under the existing "Bulk Edit" parent (slug: gv-bulk-edit).
// If that parent isn't registered yet (or at all), we also add a fallback submenu under Products.
/* -------- Solid menu wiring for "Enrich data" --------
   - Visible under Bulk Edit (gv-bulk-edit)
   - Also registered as an orphan so the direct URL always loads
------------------------------------------------------ */
/* ===== MENU WIRING: always resolve gv-bulk-edit-enrich ===== */

/* Ensure the callback exists BEFORE menus are added */
if ( ! function_exists('gv_render_bulk_enrich_page') ) {
	// Define your full renderer here if it isn't already defined.
	function gv_render_bulk_enrich_page() {
		echo '<div class="wrap"><h1>Enrich data</h1><p>Renderer not loaded.</p></div>';
	}
}

/* Register as orphan (hidden) very early */
add_action('admin_menu', function () {
	$cap = 'edit_products'; // broader than manage_options; works for Shop Manager too
	add_submenu_page(
		null,                         // hidden/orphan page
		'Enrich data',
		'Enrich data',
		$cap,
		'gv-bulk-edit-enrich',
		'gv_render_bulk_enrich_page'
	);
}, 1);

/* Register under the Bulk Edit parent (visible) very late */
add_action('admin_menu', function () {
	$cap = 'edit_products';
	add_submenu_page(
		'gv-bulk-edit',               // your existing parent slug
		'Enrich data',
		'Enrich data',
		$cap,
		'gv-bulk-edit-enrich',
		'gv_render_bulk_enrich_page',
		30
	);
}, 999);



/* -------- Menu: add as submenu under existing Bulk Edit main page -------- */
/*
add_action( 'admin_menu', function () {
	add_submenu_page(
		'gv-bulk-edit',                 // parent slug (existing main menu)
		'Enrich data',                  // page title
		'Enrich data',                  // submenu title
		'manage_woocommerce',
		'gv-bulk-edit-enrich',          // submenu slug
		'gv_render_bulk_enrich_page'    // callback
	);
});
*/
/* -------- Helpers -------- */
function gv_detect_brand_taxonomy() {
	$candidates = array('pwb-brand','product_brand','yith_product_brand','brand');
	foreach ( $candidates as $tax ) if ( taxonomy_exists($tax) ) return $tax;
	return '';
}
function gv_get_openai_headers() {
	$key = get_option('gv_openai_api_key');
	if ( ! $key ) return new WP_Error('no_key','OpenAI key not set in gv_openai_api_key');
	$h = array('Authorization'=>'Bearer '.$key,'Content-Type'=>'application/json');
	$org = get_option('gv_openai_org_id'); if ($org) $h['OpenAI-Organization'] = $org;
	return $h;
}

/* -------- Page renderer -------- */
function gv_render_bulk_enrich_page() {
	global $wpdb;

	$brand_tax = gv_detect_brand_taxonomy();

	// Filters
	$search = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
	$f_desc    = isset($_GET['f_desc'])    ? (int) $_GET['f_desc']    : 1;
	$f_excerpt = isset($_GET['f_excerpt']) ? (int) $_GET['f_excerpt'] : 1;
	$f_tags    = isset($_GET['f_tags'])    ? (int) $_GET['f_tags']    : 0;
	$f_brand   = isset($_GET['f_brand'])   ? (int) $_GET['f_brand']   : 0;
	// If none selected, default to both descriptions (backward compatible)
	if ( ! $f_desc && ! $f_excerpt && ! $f_tags && ! $f_brand ) { $f_desc = $f_excerpt = 1; }

	$per_page = 50;
	$paged    = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
	$offset   = ($paged - 1) * $per_page;

	$where_search = $search !== '' ? $wpdb->prepare(" AND p.post_title LIKE %s ", '%'.$wpdb->esc_like($search).'%') : '';

	/* Build “missing” conditions based on checkboxes (ANY of selected empties) */
	$missing_parts = array();
	if ( $f_desc )    $missing_parts[] = "TRIM(COALESCE(p.post_content,'')) = ''";
	if ( $f_excerpt ) $missing_parts[] = "TRIM(COALESCE(p.post_excerpt,'')) = ''";

	// Empty tags: no product_tag terms attached
	if ( $f_tags ) {
		$missing_parts[] = "0 = (SELECT COUNT(1)
			FROM {$wpdb->term_relationships} tr
			JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'product_tag'
			WHERE tr.object_id = p.ID)";
	}

	// Empty brand: no brand term in detected taxonomy
	if ( $f_brand && $brand_tax ) {
		$brand_tax_sql = esc_sql( $brand_tax );
		$missing_parts[] = "0 = (SELECT COUNT(1)
			FROM {$wpdb->term_relationships} trb
			JOIN {$wpdb->term_taxonomy} ttb ON ttb.term_taxonomy_id = trb.term_taxonomy_id AND ttb.taxonomy = '{$brand_tax_sql}'
			WHERE trb.object_id = p.ID)";
	} elseif ( $f_brand && ! $brand_tax ) {
		// If user asked for brand but no taxonomy exists, force impossible so nothing matches by brand
	}

	$missing_clause = '';
	if ( ! empty($missing_parts) ) {
		$missing_clause = ' AND ( ' . implode(' OR ', $missing_parts) . ' ) ';
	}

	/* Base SQL: require supplier + supplier SKU (your exact keys),
	   join supplier post + website, apply missing clause */
	$base_sql = "
		FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} ms
			ON ms.post_id = p.ID
			AND ms.meta_key = '_gv_proc_supplier_id'
			AND TRIM(COALESCE(ms.meta_value,'')) <> ''
		INNER JOIN {$wpdb->postmeta} mk
			ON mk.post_id = p.ID
			AND mk.meta_key = '_gv_proc_supplier_sku'
			AND TRIM(COALESCE(mk.meta_value,'')) <> ''
		LEFT JOIN {$wpdb->posts} sp
			ON sp.ID = CAST(ms.meta_value AS UNSIGNED)
		LEFT JOIN {$wpdb->postmeta} sw
			ON sw.post_id = sp.ID
			AND sw.meta_key = '_gv_website'
		WHERE p.post_type = 'product'
		  AND p.post_status IN ('publish','draft','private','pending')
		  {$missing_clause}
		  {$where_search}
	";

	$total = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT p.ID) {$base_sql}" );

	$rows = $wpdb->get_results( $wpdb->prepare("
		SELECT DISTINCT
			p.ID,
			p.post_title,
			p.post_content,
			p.post_excerpt,
			ms.meta_value AS supplier_id,
			mk.meta_value AS supplier_sku,
			sp.post_title AS supplier_name,
			sw.meta_value AS supplier_website
		{$base_sql}
		ORDER BY p.post_title ASC
		LIMIT %d OFFSET %d
	", $per_page, $offset ) );

	// Preload brands
	$brand_terms = array();
	if ( $brand_tax ) {
		$terms = get_terms( array('taxonomy'=>$brand_tax, 'hide_empty'=>false) );
		if ( ! is_wp_error($terms) ) foreach ( $terms as $t ) $brand_terms[$t->term_id] = $t->name;
	}

	$nonce   = wp_create_nonce('gv_ai_desc');
	$has_api = (bool) get_option('gv_openai_api_key');

	echo '<div class="wrap"><h1>Enrich data</h1>';

	/* Filters UI */
	// Replace your current <form> opening tag with:
	$enrich_url = menu_page_url( 'gv-bulk-edit-enrich', false );
	echo '<form method="get" action="'.esc_url( $enrich_url ).'" style="margin:12px 0 18px;">';

	// And include the required hidden "page" param:
	echo '<input type="hidden" name="page" value="gv-bulk-edit-enrich" />';
	echo '<input type="hidden" name="post_type" value="product" />';

	// ... your inputs & checkboxes ...


	
	echo '<label style="margin-right:12px;">Search: <input type="search" name="s" value="'.esc_attr($search).'" /></label>';
	echo '<span style="margin-left:16px;margin-right:6px;">Show missing:</span>';
	echo '<label style="margin-right:10px;"><input type="checkbox" name="f_desc" value="1" '.checked(1,$f_desc,false).' /> Description</label>';
	echo '<label style="margin-right:10px;"><input type="checkbox" name="f_excerpt" value="1" '.checked(1,$f_excerpt,false).' /> Short description</label>';
	echo '<label style="margin-right:10px;"><input type="checkbox" name="f_tags" value="1" '.checked(1,$f_tags,false).' /> Tags</label>';
	echo '<label style="margin-right:10px;"><input type="checkbox" name="f_brand" value="1" '.checked(1,$f_brand,false).' /> Brand</label>';
	submit_button('Apply', 'secondary', '', false, array('style'=>'margin-left:12px;'));
	echo '</form>';

	// Info
	echo '<div class="notice notice-info" style="padding:12px;margin:12px 0;">';
	echo $has_api
		? '<p>✅ OpenAI key found in <code>gv_openai_api_key</code>. AI buttons are enabled.</p>'
		: '<p>ℹ️ No OpenAI key set in <code>gv_openai_api_key</code>. AI buttons will be disabled.</p>';
	if ( $f_brand && ! $brand_tax ) {
		echo '<p>⚠️ Brand filter selected, but no brand taxonomy detected (tried <code>pwb-brand</code>, <code>product_brand</code>, <code>yith_product_brand</code>, <code>brand</code>).</p>';
	} elseif ( $brand_tax ) {
		echo '<p>🧩 Brand taxonomy: <code>'.esc_html($brand_tax).'</code>.</p>';
	}
	echo '</div>';

	if ( $total === 0 ) { echo '<p><strong>No matches.</strong></p></div>'; return; }

	printf('<p><strong>%d</strong> product(s) found.</p>', $total);

	echo '<table class="widefat fixed striped"><thead><tr>
		<th style="width:60px;">ID</th>
		<th>Product</th>
		<th>Supplier</th>
		<th>Supplier SKU</th>
		<th>Supplier Website</th>
		<th>Tags</th>
		<th>Brand</th>
		<th style="width:420px;">Actions</th>
	</tr></thead><tbody>';

	foreach ( $rows as $r ) {
		$supp_name = $r->supplier_name ? $r->supplier_name : ('#'.$r->supplier_id);
		$site      = $r->supplier_website ? esc_url($r->supplier_website) : '';

		// Current tags/brand
		$tags_disp = '—';
		$terms = get_the_terms( $r->ID, 'product_tag' );
		if ( $terms && ! is_wp_error($terms) ) $tags_disp = implode(', ', wp_list_pluck($terms,'name'));
		$brand_disp = '—'; $current_brand_id = 0;
		if ( $brand_tax ) {
			$bterms = get_the_terms( $r->ID, $brand_tax );
			if ( $bterms && ! is_wp_error($bterms) ) { $brand_disp = implode(', ', wp_list_pluck($bterms,'name')); $current_brand_id = (int)$bterms[0]->term_id; }
		}

		echo '<tr data-product-id="'.esc_attr($r->ID).'" data-brand-tax="'.esc_attr($brand_tax).'">';
		echo '<td>'.esc_html($r->ID).'</td>';
		echo '<td><a href="'.esc_url(get_edit_post_link($r->ID)).'">'.esc_html($r->post_title).'</a></td>';
		echo '<td>'.esc_html($supp_name).'</td>';
		echo '<td>'.esc_html($r->supplier_sku).'</td>';
		echo '<td>'.($site ? '<a href="'.$site.'" target="_blank" rel="noopener">Website</a>' : '&mdash;').'</td>';
		echo '<td>'.esc_html($tags_disp).'</td>';
		echo '<td>'.esc_html($brand_disp).'</td>';
		echo '<td>';
			echo '<a class="button button-small" href="'.esc_url(get_edit_post_link($r->ID)).'">Edit</a> ';
			echo (get_option('gv_openai_api_key') ? '<button type="button" class="button button-small gv-ai-desc" data-id="'.esc_attr($r->ID).'">AI Description</button> ' : '<button type="button" class="button button-small" disabled>AI Description</button> ');
			echo (get_option('gv_openai_api_key') ? '<button type="button" class="button button-small gv-ai-excerpt" data-id="'.esc_attr($r->ID).'">AI Short Description</button> ' : '<button type="button" class="button button-small" disabled>AI Short Description</button> ');
			echo (get_option('gv_openai_api_key') ? '<button type="button" class="button button-small gv-ai-tags" data-id="'.esc_attr($r->ID).'">AI Tags</button> ' : '<button type="button" class="button button-small" disabled>AI Tags</button> ');
			echo ($brand_tax && get_option('gv_openai_api_key') ? '<button type="button" class="button button-small gv-ai-brand" data-id="'.esc_attr($r->ID).'">AI Brand</button>' : '<button type="button" class="button button-small" disabled>AI Brand</button>');
		echo '</td></tr>';

		// Inline editor row
		echo '<tr class="gv-ai-row" id="gv-ai-row-'.esc_attr($r->ID).'" style="display:none;background:#fafafa"><td colspan="8">
			<div class="gv-ai-wrap">
				<p><strong>AI workspace</strong></p>
				<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
					<div><label>Description</label><br/>
						<textarea class="large-text code gv-ai-desc-text" rows="6">'.esc_textarea($r->post_content).'</textarea></div>
					<div><label>Short Description (excerpt)</label><br/>
						<textarea class="large-text code gv-ai-excerpt-text" rows="6">'.esc_textarea($r->post_excerpt).'</textarea></div>
				</div><br/>
				<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
					<div><label>Tags (comma-separated)</label><br/>
						<input type="text" class="regular-text gv-ai-tags-input" value="'.esc_attr($tags_disp==='—'?'':$tags_disp).'" /></div>';
					if ( $brand_tax ) {
						echo '<div><label>Brand</label><br/><select class="gv-ai-brand-select"><option value="0">— Select brand —</option>';
						foreach ($brand_terms as $tid=>$name) echo '<option value="'.esc_attr($tid).'" '.selected($tid,$current_brand_id,false).'>'.esc_html($name).'</option>';
						echo '</select></div>';
					} else {
						echo '<div><em>No brand taxonomy detected.</em></div>';
					}
				echo '</div>
				<p class="submit" style="margin-top:12px;">
					<button class="button button-primary gv-ai-save" data-id="'.esc_attr($r->ID).'">Save</button>
					<button class="button gv-ai-close" data-id="'.esc_attr($r->ID).'">Close</button>
					<span class="gv-ai-status" style="margin-left:8px;"></span>
				</p>
			</div></td></tr>';
	}

	echo '</tbody></table>';

	// Pagination
	$total_pages = (int) ceil($total / $per_page);
	if ( $total_pages > 1 ) {
		$base_url = add_query_arg(array(
			'page'      => 'gv-bulk-edit-enrich',
			'post_type' => 'product',
			's'         => $search,
			'f_desc'    => $f_desc,
			'f_excerpt' => $f_excerpt,
			'f_tags'    => $f_tags,
			'f_brand'   => $f_brand,
		), admin_url('admin.php'));
		echo '<div class="tablenav"><div class="tablenav-pages">';
		for ( $i=1; $i<=$total_pages; $i++ ) {
			$url = add_query_arg('paged', $i, $base_url);
			echo '<a class="page-numbers'.($i===$paged?' current':'').'" href="'.esc_url($url).'">'.esc_html($i).'</a> ';
		}
		echo '</div></div>';
	}

	// Inline JS (uses ajaxurl). Separate actions below.
	?>
	<script>
	(function($){
		function row(pid){ return $('#gv-ai-row-'+pid); }
		function openRow(pid){ row(pid).show(); }
		function status(pid,msg){ row(pid).find('.gv-ai-status').text(msg); }

		$(document).on('click','.gv-ai-desc',function(){
			const pid=$(this).data('id'); openRow(pid); status(pid,'Drafting Description…');
			$.post(ajaxurl,{action:'gv_ai_generate_desc_only',pid:pid},function(r){
				if(!r||!r.success){ status(pid,(r&&r.data)?r.data:'Error'); return; }
				row(pid).find('.gv-ai-desc-text').val(r.data.description||''); status(pid,'Description ready. Review and Save.');
			}).fail(()=>status(pid,'Request failed.'));
		});
		$(document).on('click','.gv-ai-excerpt',function(){
			const pid=$(this).data('id'); openRow(pid);
			const source=row(pid).find('.gv-ai-desc-text').val();
			status(pid,'Creating Short Description from Description only…');
			$.post(ajaxurl,{action:'gv_ai_generate_excerpt_from_desc',pid:pid,source:source},function(r){
				if(!r||!r.success){ status(pid,(r&&r.data)?r.data:'Error'); return; }
				row(pid).find('.gv-ai-excerpt-text').val(r.data.excerpt||''); status(pid,'Short Description ready. Review and Save.');
			}).fail(()=>status(pid,'Request failed.'));
		});
		$(document).on('click','.gv-ai-tags',function(){
			const pid=$(this).data('id'); openRow(pid);
			const source=row(pid).find('.gv-ai-desc-text').val();
			status(pid,'Generating tags…');
			$.post(ajaxurl,{action:'gv_ai_generate_tags',pid:pid,source:source},function(r){
				if(!r||!r.success){ status(pid,(r&&r.data)?r.data:'Error'); return; }
				row(pid).find('.gv-ai-tags-input').val(r.data.tags||''); status(pid,'Tags ready. Review and Save.');
			}).fail(()=>status(pid,'Request failed.'));
		});
		$(document).on('click','.gv-ai-brand',function(){
			const pid=$(this).data('id'); openRow(pid);
			const brandTax = $(this).closest('tr').data('brand-tax') || '';
			status(pid,'Selecting brand…');
			$.post(ajaxurl,{action:'gv_ai_generate_brand',pid:pid,brand_tax:brandTax},function(r){
				if(!r||!r.success){ status(pid,(r&&r.data)?r.data:'Error'); return; }
				if(r.data.brand_id){ row(pid).find('.gv-ai-brand-select').val(r.data.brand_id); }
				status(pid,r.data.message||'Brand selected. Review and Save.');
			}).fail(()=>status(pid,'Request failed.'));
		});
		$(document).on('click','.gv-ai-save',function(){
			const pid=$(this).data('id');
			const desc=row(pid).find('.gv-ai-desc-text').val();
			const ex=row(pid).find('.gv-ai-excerpt-text').val();
			const tags=row(pid).find('.gv-ai-tags-input').val();
			const brand_id=row(pid).find('.gv-ai-brand-select').val()||0;
			const brand_tax=$(this).closest('tr').prev().data('brand-tax')||'';
			status(pid,'Saving…');
			$.post(ajaxurl,{action:'gv_ai_save_all',pid:pid,desc:desc,ex:ex,tags:tags,brand_id:brand_id,brand_tax:brand_tax},function(r){
				if(!r||!r.success){ status(pid,(r&&r.data)?r.data:'Error'); return; }
				status(pid,'Saved. Reload to refresh table.');
			}).fail(()=>status(pid,'Save failed.'));
		});
		$(document).on('click','.gv-ai-close',function(){ row($(this).data('id')).hide(); });
	})(jQuery);
	</script>
	<?php
	echo '</div>';
}

/* ---------- AJAX endpoints (reuse from previous version) ---------- */
add_action('wp_ajax_gv_ai_generate_desc_only', function(){
	if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error('Unauthorized',403);
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
	if(!$pid) wp_send_json_error('Missing product id',400);
	$post = get_post($pid); if(!$post || $post->post_type!=='product') wp_send_json_error('Invalid product',400);

	$title = $post->post_title;
	$sku   = get_post_meta($pid,'_gv_proc_supplier_sku',true);
	$sid   = get_post_meta($pid,'_gv_proc_supplier_id',true);
	$sname = $sid ? get_the_title((int)$sid) : '';
	$site  = $sid ? get_post_meta((int)$sid,'_gv_website',true) : '';

	$headers = gv_get_openai_headers(); if ( is_wp_error($headers) ) wp_send_json_error($headers->get_error_message(),400);

	$body = array(
		'model'=>'gpt-4o-mini',
		'messages'=>array(
			array('role'=>'system','content'=>"You write concise, technically accurate B2B product copy for industrial valves and accessories. Use neutral EU English. Avoid hype."),
			array('role'=>'user','content'=>"Write a product Description (80–150 words). Be useful and specific where possible. If specs are unknown, keep it generic but practical. Do NOT include a short description.\n\nProduct: {$title}\nSupplier: {$sname}\nSupplier SKU: {$sku}\nSupplier website: {$site}"),
		),
		'temperature'=>0.4,'max_tokens'=>350,
	);
	$resp = wp_remote_post('https://api.openai.com/v1/chat/completions', array('timeout'=>30,'headers'=>$headers,'body'=>wp_json_encode($body)));
	if ( is_wp_error($resp) ) wp_send_json_error('HTTP error: '.$resp->get_error_message(),500);
	$json = json_decode(wp_remote_retrieve_body($resp), true);
	$desc = trim((string)($json['choices'][0]['message']['content'] ?? ''));
	if($desc==='') wp_send_json_error('OpenAI: empty response',500);
	wp_send_json_success(array('description'=>$desc));
});

add_action('wp_ajax_gv_ai_generate_excerpt_from_desc', function(){
	if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error('Unauthorized',403);
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
	$source = isset($_POST['source']) ? (string) wp_unslash($_POST['source']) : '';
	if(!$pid) wp_send_json_error('Missing product id',400);
	if(trim($source)===''){ $post=get_post($pid); if(!$post || $post->post_type!=='product') wp_send_json_error('Invalid product',400); $source=(string)$post->post_content; }
	if(trim($source)==='') wp_send_json_error('Description is empty; nothing to summarize.',400);

	$headers = gv_get_openai_headers(); if ( is_wp_error($headers) ) wp_send_json_error($headers->get_error_message(),400);

	$body=array(
		'model'=>'gpt-4o-mini',
		'messages'=>array(
			array('role'=>'system','content'=>'You are a careful summarizer. Use only the provided Description text. Do not add any new info.'),
			array('role'=>'user','content'=>"Input Description:\n{$source}\n\nTask: Create a Short Description (1–2 sentences, plain text). Only summarize what is in the input."),
		),
		'temperature'=>0.2,'max_tokens'=>120,
	);
	$resp=wp_remote_post('https://api.openai.com/v1/chat/completions', array('timeout'=>30,'headers'=>$headers,'body'=>wp_json_encode($body)));
	if ( is_wp_error($resp) ) wp_send_json_error('HTTP error: '.$resp->get_error_message(),500);
	$json=json_decode(wp_remote_retrieve_body($resp),true);
	$excerpt=trim((string)($json['choices'][0]['message']['content'] ?? ''));
	$excerpt=preg_replace('~\s+~',' ',$excerpt);
	if($excerpt==='') wp_send_json_error('OpenAI: empty response',500);
	if(strlen($excerpt)>400) $excerpt=substr($excerpt,0,400);
	wp_send_json_success(array('excerpt'=>$excerpt));
});

add_action('wp_ajax_gv_ai_generate_tags', function(){
	if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error('Unauthorized',403);
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
	$source = isset($_POST['source']) ? (string) wp_unslash($_POST['source']) : '';
	if(!$pid) wp_send_json_error('Missing product id',400);
	$post=get_post($pid); if(!$post || $post->post_type!=='product') wp_send_json_error('Invalid product',400);

	$title=$post->post_title;
	$sku=get_post_meta($pid,'_gv_proc_supplier_sku',true);
	$sid=get_post_meta($pid,'_gv_proc_supplier_id',true);
	$sname=$sid?get_the_title((int)$sid):'';
	$site =$sid?get_post_meta((int)$sid,'_gv_website',true):'';
	if(trim($source)==='') $source=(string)$post->post_content;

	$headers = gv_get_openai_headers(); if ( is_wp_error($headers) ) wp_send_json_error($headers->get_error_message(),400);

	$body=array(
		'model'=>'gpt-4o-mini',
		'messages'=>array(
			array('role'=>'system','content'=>'You create concise SEO/merchandising tags for industrial B2B products. Use lowercase; words separated by spaces; no punctuation; no duplicates; 5–12 tags.'),
			array('role'=>'user','content'=>"Generate tags for a WooCommerce product. Keep each tag short (1–3 words). Include relevant technology, function, certification, and medium if evident. Avoid generic words.\n\nProduct: {$title}\nSupplier: {$sname}\nSupplier SKU: {$sku}\nSupplier website: {$site}\nContext: {$source}\n\nReturn a single comma-separated line, no numbering."),
		),
		'temperature'=>0.3,'max_tokens'=>160,
	);
	$resp=wp_remote_post('https://api.openai.com/v1/chat/completions', array('timeout'=>30,'headers'=>$headers,'body'=>wp_json_encode($body)));
	if ( is_wp_error($resp) ) wp_send_json_error('HTTP error: '.$resp->get_error_message(),500);
	$json=json_decode(wp_remote_retrieve_body($resp),true);
	$out=trim((string)($json['choices'][0]['message']['content'] ?? ''));
	if($out==='') wp_send_json_error('OpenAI: empty response',500);
	$out=preg_replace('~\s*,\s*~',', ',$out); $out=preg_replace('~\s+~',' ',$out);
	wp_send_json_success(array('tags'=>$out));
});

add_action('wp_ajax_gv_ai_generate_brand', function(){
	if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error('Unauthorized',403);
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
	$brand_tax = isset($_POST['brand_tax']) ? sanitize_key($_POST['brand_tax']) : '';
	if(!$pid) wp_send_json_error('Missing product id',400);
	if(!$brand_tax || !taxonomy_exists($brand_tax)) wp_send_json_error('Brand taxonomy not available',400);

	$sid = get_post_meta($pid,'_gv_proc_supplier_id',true);
	$sname = $sid ? get_the_title((int)$sid) : '';

	$terms = get_terms(array('taxonomy'=>$brand_tax,'hide_empty'=>false));
	if ( ! is_wp_error($terms) && $terms ) {
		$sn = mb_strtolower($sname);
		foreach ($terms as $t) {
			$nm = mb_strtolower($t->name);
			if ($nm===$sn || strpos($sn,$nm)!==false || strpos($nm,$sn)!==false) {
				wp_send_json_success(array('brand_id'=>(int)$t->term_id,'message'=>'Matched supplier to existing brand.'));
			}
		}
	}

	$headers = gv_get_openai_headers(); if ( is_wp_error($headers) ) wp_send_json_error($headers->get_error_message(),400);
	$brand_names = array(); if ( ! is_wp_error($terms) && $terms ) foreach ($terms as $t) $brand_names[] = $t->name;
	$brand_list = implode(', ', array_slice($brand_names,0,200));

	$body=array(
		'model'=>'gpt-4o-mini',
		'messages'=>array(
			array('role'=>'system','content'=>"You select the most likely brand from a provided list based on the supplier name. Return exactly one brand from the list, or 'none'."),
			array('role'=>'user','content'=>"Supplier name: {$sname}\n\nAvailable brands: {$brand_list}\n\nReturn one exact brand name from the list, or 'none'."),
		),
		'temperature'=>0.0,'max_tokens'=>20,
	);
	$resp=wp_remote_post('https://api.openai.com/v1/chat/completions', array('timeout'=>30,'headers'=>$headers,'body'=>wp_json_encode($body)));
	if ( is_wp_error($resp) ) wp_send_json_error('HTTP error: '.$resp->get_error_message(),500);
	$json=json_decode(wp_remote_retrieve_body($resp),true);
	$choice=trim((string)($json['choices'][0]['message']['content'] ?? ''));
	if ($choice && strtolower($choice)!=='none') {
		foreach ($terms as $t) if ( strtolower($t->name)===strtolower($choice) ) wp_send_json_success(array('brand_id'=>(int)$t->term_id,'message'=>'AI selected existing brand.'));
	}
	wp_send_json_success(array('brand_id'=>0,'message'=>'No clear brand match.'));
});

add_action('wp_ajax_gv_ai_save_all', function(){
	if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error('Unauthorized',403);
	$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;
	$desc = isset($_POST['desc']) ? wp_kses_post(wp_unslash($_POST['desc'])) : '';
	$ex   = isset($_POST['ex'])   ? sanitize_textarea_field(wp_unslash($_POST['ex'])) : '';
	$tags_line = isset($_POST['tags']) ? (string) wp_unslash($_POST['tags']) : '';
	$brand_id  = isset($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
	$brand_tax = isset($_POST['brand_tax']) ? sanitize_key($_POST['brand_tax']) : '';

	if(!$pid) wp_send_json_error('Missing product id',400);

	$upd = array('ID'=>$pid);
	if($desc!=='') $upd['post_content']=$desc;
	if($ex  !=='') $upd['post_excerpt']=$ex;
	if(count($upd)>1){ $r=wp_update_post($upd,true); if(is_wp_error($r)) wp_send_json_error($r->get_error_message(),500); }

	if($tags_line!==''){
		$names = array_filter(array_map(function($s){ return trim(preg_replace('~\s+~',' ',$s)); }, explode(',',$tags_line)));
		if(!empty($names)) wp_set_post_terms($pid,$names,'product_tag',false);
	}

	if($brand_tax && taxonomy_exists($brand_tax) && $brand_id>0) {
		wp_set_post_terms($pid, array($brand_id), $brand_tax, false);
	}

	wp_send_json_success('Saved');
});


// ===== Snippet #121: Products - Enrich product data with Images =====
// Scope: admin-css | Priority: 10
// Converted from admin CSS snippet to PHP that prints CSS in wp-admin.

add_action( 'admin_head', function () {
    ?>
    <style>
    .gv-ei-wrap .gv-ei-bulkbar { margin:12px 0; display:flex; gap:8px; align-items:center; }
    .gv-ei-table .check-col { width:28px; }
    .gv-ei-table .gv-ei-suggestion img { border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,.06); }
    .gv-ei-table .spinner { margin-left:6px; }
    </style>
    <?php
} );



// ===== Snippet #123: Form User Login - My Account - Remove username and only ask for email =====
// Scope: global | Priority: 10

/**
 * WooCommerce My Account: change "Username or email address" → "Email Address"
 */
add_filter( 'gettext', function( $translated, $text, $domain ) {
    if ( is_admin() ) return $translated;           // front-end only
    if ( $domain !== 'woocommerce' ) return $translated;

    // Match EN + NL just in case your site switches languages
    if ( $text === 'Username or email address' || $text === 'Gebruikersnaam of e-mailadres' ) {
        return 'Email Address';
    }
    return $translated;
}, 20, 3 );

/**
 * (Optional) Also change the input placeholder + make sure the label shows exactly as desired.
 */
add_action( 'wp_footer', function () {
    if ( function_exists('is_account_page') && is_account_page() ) : ?>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var label = document.querySelector('form.woocommerce-form-login label[for="username"]');
            if (label) { label.firstChild.nodeValue = 'Email Address '; }
            var input = document.getElementById('username');
            if (input) { input.setAttribute('placeholder', 'Email Address'); }
        });
        </script>
    <?php endif;
});


// ===== Snippet #125: Products - SKU manager Air Headers =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV — Air Header SKU & DK Parser
 * Description: Parses short SKU + Design Key into WooCommerce meta for Air Headers.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined('ABSPATH') ) exit;

class GV_AH_SKU_Parser {
    const META_DK = '_gv_dk'; // optional separate field for DK if not appended to SKU

    public function __construct() {
        add_action('save_post_product', [$this, 'parse_on_save'], 20, 1);
    }

    public function parse_on_save($post_id){
        if ( wp_is_post_revision($post_id) || get_post_type($post_id) !== 'product') return;

        $sku_raw = (string) get_post_meta($post_id, '_sku', true);
        if (!$sku_raw) return;

        // Split optional +DK:
        $sku = $sku_raw;
        $dk  = '';
        if (str_contains($sku_raw, '+DK:')) {
            [$sku, $dk] = array_map('trim', explode('+DK:', $sku_raw, 2));
        } else {
            $dk = (string) get_post_meta($post_id, self::META_DK, true);
            // Accept DK with or without leading "DK:" prefix
            if (str_starts_with($dk, 'DK:')) $dk = substr($dk, 3);
        }

        $parsed = $this->parse_sku($sku);
        if (!$parsed) return;

        $dkp = $this->parse_dk($dk);

        // ——— Persist core (from SKU) ———
        update_post_meta($post_id, '_gv_family',         'AH');
        update_post_meta($post_id, '_gv_hdr_dn',         $parsed['hdr_dn']);     // e.g. 200
        update_post_meta($post_id, '_gv_len_mm',         $parsed['len_mm']);     // e.g. 4300
        update_post_meta($post_id, '_gv_end_code',       $parsed['end_code']);   // e.g. EW

        // Expand EW default (can be overridden by DK->Ends)
        $left_end  = $dkp['ends']['LE'] ?? $this->default_end_from_code($parsed['end_code'], 'LE');
        $right_end = $dkp['ends']['RE'] ?? $this->default_end_from_code($parsed['end_code'], 'RE');

        update_post_meta($post_id, '_gv_end_left_type',   $left_end['type']);
        update_post_meta($post_id, '_gv_end_left_rating', $left_end['rating']);
        update_post_meta($post_id, '_gv_end_left_mount',  $left_end['mount']);

        update_post_meta($post_id, '_gv_end_right_type',   $right_end['type']);
        update_post_meta($post_id, '_gv_end_right_rating', $right_end['rating']);
        update_post_meta($post_id, '_gv_end_right_mount',  $right_end['mount']);

        update_post_meta($post_id, '_gv_bot_dn',         $parsed['bot_dn']);    // e.g. 100
        update_post_meta($post_id, '_gv_bot_count',      $parsed['bot_count']); // e.g. 19
        update_post_meta($post_id, '_gv_material',       $parsed['material']);  // e.g. GJ2
        update_post_meta($post_id, '_gv_p_design_bar',   $parsed['pbar']);      // e.g. 10
        update_post_meta($post_id, '_gv_rev',            $parsed['rev']);       // e.g. A

        // ——— Persist DK (optional) ———
        if ($dk !== '') {
            update_post_meta($post_id, self::META_DK, $dk);
        }

        // Bottom thread (default NPT unless DK says otherwise)
        $bot_thread = $dkp['BT'] ?? 'NPT';
        update_post_meta($post_id, '_gv_bot_thread', $bot_thread);

        // Top nipple (optional)
        update_post_meta($post_id, '_gv_top_from',   $dkp['TN']['from'] ?? '');
        update_post_meta($post_id, '_gv_top_pos_mm', $dkp['TN']['pos_mm'] ?? '');
        update_post_meta($post_id, '_gv_top_size',   $dkp['TN']['size_str'] ?? '');
        update_post_meta($post_id, '_gv_top_thread', $dkp['TN']['thread'] ?? '');

        // Spacing
        update_post_meta($post_id, '_gv_bp_pattern',        $dkp['BP_raw'] ?? '');
        update_post_meta($post_id, '_gv_bp_expanded_mm',    wp_json_encode($dkp['BP_expanded'] ?? []));
        update_post_meta($post_id, '_gv_flange_rating',     $dkp['FR'] ?? ($left_end['rating'] ?: '150'));
        update_post_meta($post_id, '_gv_extras',            $dkp['EX'] ?? '');

        // Quick sanity flags (useful in admin UI)
        $ok_count = empty($dkp['BP_expanded']) ? '' : (string) $this->count_nipples_from_bp($dkp['BP_expanded']);
        update_post_meta($post_id, '_gv_bot_count_check', $ok_count);
    }

    private function parse_sku(string $sku): ?array {
        // AH20-43-EW-B10x19-GJ2-P10-A
        $pat = '#^AH(?P<H>\d{2,3})-(?P<L>\d{1,4})-(?P<E>[A-Z]{2,3})-B(?P<bd>\d{1,2})x(?P<c>\d{1,3})-(?P<M>[A-Z0-9]{2,4})-P(?P<p>\d{1,3})-(?P<R>[A-Z0-9]{1,3})$#';
        if (!preg_match($pat, $sku, $m)) return null;

        $hdr_dn  = (int)$m['H'] * 10;         // DN/10 → DN
        $len_mm  = (int)$m['L'] * 100;        // dm → mm
        $bot_dn  = (int)$m['bd'] * 10;        // DN/10 → DN
        $bot_cnt = (int)$m['c'];
        $pbar    = (int)$m['p'];

        return [
            'hdr_dn'   => $hdr_dn,
            'len_mm'   => $len_mm,
            'end_code' => $m['E'],
            'bot_dn'   => $bot_dn,
            'bot_count'=> $bot_cnt,
            'material' => $m['M'],
            'pbar'     => $pbar,
            'rev'      => $m['R'],
        ];
    }

    private function parse_dk(string $dk): array {
        // Example DK (no "DK:" prefix required):
        // BP160|220*5|170*2|220*5|170*2|220*5|160;BT=NPT;TN=R200,S04,NPT;FR=150;Ends=LE:WN150W,RE:WN150W;EX=LAPSTUB
        $out = [
            'BP_raw'     => '',
            'BP_expanded'=> [],
            'BT'         => null,
            'TN'         => [],
            'FR'         => null,
            'ends'       => [],
            'EX'         => null,
        ];
        if ($dk === '') return $out;

        // Split semicolon groups, tolerate spaces
        foreach (preg_split('/\s*;\s*/', trim($dk)) as $seg) {
            if ($seg === '') continue;
            if (str_starts_with($seg, 'BP')) {
                $bp = trim(substr($seg, 2));        // after "BP"
                $out['BP_raw']      = $bp;
                $out['BP_expanded'] = $this->expand_bp($bp);
            } elseif (str_starts_with($seg, 'BT=')) {
                $out['BT'] = substr($seg, 3);
            } elseif (str_starts_with($seg, 'TN=')) {
                // TN=R200,S04,NPT  OR TN=L150,1/2,NPT
                $tn = substr($seg, 3);
                $out['TN'] = $this->parse_tn($tn);
            } elseif (str_starts_with($seg, 'FR=')) {
                $out['FR'] = preg_replace('/^FR=/', '', $seg);
            } elseif (str_starts_with($seg, 'Ends=')) {
                // Ends=LE:WN150W,RE:LAP150S
                $pairs = explode(',', substr($seg, 5));
                foreach ($pairs as $p) {
                    if (preg_match('/^(LE|RE):([A-Z]+)(\d{2,3})?([A-Z]+)?$/', trim($p), $m)) {
                        $side   = $m[1];
                        $type   = $m[2];                    // WN, LAP, SO, BL, TH, CAP, PLAIN
                        $rating = $m[3] ?: '150';
                        $mount  = $m[4] ?: $this->mount_for_type($type);
                        $out['ends'][$side] = ['type'=>$type,'rating'=>$rating,'mount'=>$mount];
                    }
                }
            } elseif (str_starts_with($seg, 'EX=')) {
                $out['EX'] = substr($seg, 3); // comma list
            }
        }
        return $out;
    }

    private function expand_bp(string $bp): array {
        // "160|220*5|170*2|...|160" → [160, 220,220,220,220,220, 170,170, ... , 160]
        $out = [];
        foreach (explode('|', $bp) as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') continue;
            if (preg_match('/^(\d+)\*(\d+)$/', $chunk, $m)) {
                $val = (int)$m[1]; $rep = (int)$m[2];
                $out = array_merge($out, array_fill(0, $rep, $val));
            } else {
                $out[] = (int)$chunk;
            }
        }
        return $out;
    }

    private function parse_tn(string $tn): array {
        // Accept R/L + mm, size either Sxx (eighths) or fraction, thread label
        // Examples: "R200,S04,NPT"  or "L150,1/2,BSPT"
        $parts = array_map('trim', explode(',', $tn));
        $from=''; $pos=0; $size=''; $thread='';
        if (isset($parts[0]) && preg_match('/^([RL])(\d{1,5})$/', $parts[0], $m)) {
            $from = $m[1] === 'R' ? 'right' : 'left';
            $pos  = (int)$m[2];
        }
        if (isset($parts[1])) {
            if (preg_match('/^S(\d{2})$/', $parts[1], $m)) {
                // Sxx → size in eighths
                $eighths = (int)$m[1];
                $size = $this->eighths_to_fraction($eighths); // e.g. 04 → 1/2
            } else {
                $size = $parts[1]; // e.g. "1/2"
            }
        }
        if (isset($parts[2])) $thread = $parts[2];

        return [
            'from'     => $from,
            'pos_mm'   => $pos,
            'size_str' => $size,
            'thread'   => $thread,
        ];
    }

    private function eighths_to_fraction(int $e): string {
        // 01=1/8, 02=1/4, 03=3/8, 04=1/2, 06=3/4, 08=1, 12=1-1/2, etc.
        if ($e <= 0) return '';
        $num = $e;
        $den = 8;
        // reduce the fraction
        $g = function($a,$b) use (&$g){ return $b ? $g($b, $a % $b) : $a; };
        $d = $g($num,$den);
        $num/= $d; $den/= $d;
        if ($den == 1) return (string)$num;
        // handle >8 (multi-inch)
        if ($num > $den) {
            $whole = intdiv($num, $den);
            $rem   = $num % $den;
            return $rem ? "{$whole}-{$rem}/{$den}" : (string)$whole;
        }
        return "{$num}/{$den}";
    }

    private function default_end_from_code(string $E, string $side): array {
        // Today: EW = Weld-Neck 150#, mount=W (welded)
        // You can extend this mapping to support other compact E codes.
        if ($E === 'EW') return ['type'=>'WN','rating'=>'150','mount'=>'W'];
        return ['type'=>'WN','rating'=>'150','mount'=>'W'];
    }

    private function mount_for_type(string $type): string {
        return match($type){
            'WN'  => 'W',  // weld-neck
            'SO'  => 'S',  // slip-on
            'LAP' => 'LAP',
            'BL'  => '',   // blind
            'PL'  => 'W',  // plate/plain
            'TH'  => 'T',  // threaded
            'CAP' => '',
            default => '',
        };
    }

    private function count_nipples_from_bp(array $bp): int {
        // BP contains: [flange→1st, pitch..pitch, .., last→flange]
        // #nipples equals (#pitch segments), i.e., total items minus the 2 edge distances,
        // but because we don't know which are edges, we instead infer from meta:
        // In practice: the run-length between edges equals (#repeated pitches).
        // Here we just return count(bp) - 2 if bp >= 3, else 0.
        $n = count($bp);
        return $n >= 3 ? max(0, $n - 2) : 0;
    }
}

new GV_AH_SKU_Parser();


// ===== Snippet #126: Credentials - Registration Control enable/disable registration =====
// Scope: global | Priority: 10

/**
 * Plugin Name: GV – Registration Control (Woo)
 * Description: Adds a WooCommerce setting to globally enable/disable customer self-registration and blocks custom registration pages. Logs attempts to GV User Activity Log if available.
 * Author: Gemini Valve
 * Version: 1.0.0
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Helper: read 'yes'/'no' option written on WC Accounts & Privacy tab.
 */
function gvrc_is_registration_enabled(): bool {
    return get_option( 'gv_registration_enabled', 'no' ) === 'yes';
}

/**
 * WooCommerce Settings (Accounts & Privacy): add our controls.
 * NOTE: Hook is singular: woocommerce_get_settings_account
 */
add_filter( 'woocommerce_get_settings_account', function( $settings, $current_section ) {

    // Only on the main Accounts & Privacy screen (no sub-sections)
    if ( ! empty( $current_section ) ) {
        return $settings;
    }

    $settings[] = array(
        'title' => __( 'Gemini Registration Control', 'gv' ),
        'type'  => 'title',
        'id'    => 'gv_reg_section_title',
        'desc'  => __( 'Fully disable customer self-registration across My Account, Checkout, and wp-login.php. You can also block custom pages (e.g., /registration/). Admins can still create users in wp-admin. Attempts are logged as "register_blocked" if the GV User Activity Log plugin is active.', 'gv' ),
    );

    $settings[] = array(
        'title'    => __( 'Allow customer registration', 'gv' ),
        'id'       => 'gv_registration_enabled',
        'type'     => 'checkbox',
        'default'  => 'no', // default = disabled
        'desc'     => __( 'When unchecked, registration is blocked site-wide (frontend only).', 'gv' ),
        'desc_tip' => __( 'Blocks My Account / Checkout forms and wp-login.php?action=register. See the page block field below for custom pages.', 'gv' ),
    );

    $settings[] = array(
        'title'    => __( 'Block these registration pages (comma-separated slugs)', 'gv' ),
        'id'       => 'gv_registration_block_slugs',
        'type'     => 'text',
        'default'  => 'registration,my-account,register',
        'desc'     => __( 'Example: registration,my-account,signup', 'gv' ),
        'desc_tip' => __( 'If the current page slug matches any of these and registration is disabled, the request is redirected and logged.', 'gv' ),
    );

    $settings[] = array(
        'type' => 'sectionend',
        'id'   => 'gv_reg_section_end',
    );

    return $settings;
}, 10, 2 );

/**
 * Show a success notice after saving Woo settings (optional nicety).
 */
add_action( 'woocommerce_settings_saved', function () {
    if ( ! current_user_can( 'manage_woocommerce' ) ) return;
    $enabled = gvrc_is_registration_enabled();
    add_action( 'admin_notices', function () use ( $enabled ) {
        echo '<div class="notice notice-success is-dismissible"><p>'
           . esc_html( $enabled ? __( 'Customer registration has been ENABLED.', 'gv' ) : __( 'Customer registration has been DISABLED.', 'gv' ) )
           . '</p></div>';
    } );
} );

/**
 * Apply registration blocks only when our setting is OFF.
 */
add_action( 'init', function () {

    if ( gvrc_is_registration_enabled() ) {
        // Registration enabled: respect Woo/WordPress defaults.
        return;
    }

    // 1) Hide WooCommerce registration UIs (My Account & Checkout)
    add_filter( 'woocommerce_enable_myaccount_registration', '__return_false', 99 );
    add_filter( 'woocommerce_checkout_registration_enabled', '__return_false', 99 );

    // 2) Core "Anyone can register" → OFF (prevents wp-login.php?action=register)
    add_filter( 'pre_option_users_can_register', function () { return 0; }, 99 );

    // 3) Block Woo My Account submissions and log
    add_filter( 'woocommerce_process_registration_errors', function( $errors, $username, $email ) {
        if ( ! is_admin() ) {
            if ( ! is_wp_error( $errors ) ) $errors = new WP_Error();
            $errors->add( 'registration_disabled', __( 'Registration is currently disabled.', 'gv' ) );

            if ( function_exists( 'gvual_log' ) ) {
                $u   = get_user_by( 'login', $username );
                $uid = $u instanceof WP_User ? (int) $u->ID : 0;
                gvual_log( $uid, 'register_blocked', array(
                    'user_login' => (string) $username,
                    'user_email' => (string) $email,
                    'reason'     => 'Registration disabled',
                ) );
            }
        }
        return $errors;
    }, 10, 3 );

    // 4) Block wp-login.php?action=register and log
    add_action( 'login_init', function () {
        if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'register' && ! is_admin() ) {
            if ( function_exists( 'gvual_log' ) ) {
                $attempt = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';
                $email   = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
                gvual_log( 0, 'register_blocked', array(
                    'user_login' => $attempt,
                    'user_email' => $email,
                    'reason'     => 'Registration disabled (wp-login.php)',
                ) );
            }
            wp_die(
                __( 'Registration is disabled on this site.', 'gv' ),
                __( 'Registration disabled', 'gv' ),
                array( 'response' => 403 )
            );
        }
    } );

    // 5) Block unauthenticated REST user creation (POST /wp/v2/users)
    add_filter( 'rest_request_before_callbacks', function ( $response, $handler, $request ) {
        if ( $request->get_route() === '/wp/v2/users' && $request->get_method() === 'POST' && ! current_user_can( 'create_users' ) ) {
            return new WP_Error( 'registration_disabled', __( 'Registration is disabled.', 'gv' ), array( 'status' => 403 ) );
        }
        return $response;
    }, 10, 3 );

}, 20 );

/**
 * Universal front-end page block (includes custom URLs like /registration/).
 * Redirects to My Account and logs a "register_blocked" event when disabled.
 */
// Show a blocking message (no redirect) when visiting listed pages and registration is disabled
// Show a blocking message (no redirect) when visiting listed pages and registration is disabled.
// But if the user is LOGGED IN and is on "My Account", allow access.
add_action( 'template_redirect', function () {
    if ( gvrc_is_registration_enabled() ) return;
    if ( is_admin() ) return;

    // Allow logged-in users to access My Account normally
    if ( is_user_logged_in() && function_exists( 'is_account_page' ) && is_account_page() ) {
        return;
    }

    $slugs_csv = get_option( 'gv_registration_block_slugs', 'registration,my-account,register' );
    $slugs = array_filter( array_map( 'trim', explode( ',', (string) $slugs_csv ) ) );
    if ( empty( $slugs ) ) return;

    if ( is_page( $slugs ) ) {
        // Optional audit log if your logger exists
        if ( function_exists( 'gvual_log' ) ) {
            $attempt = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';
            $email   = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
            gvual_log( 0, 'register_blocked', array(
                'user_login' => $attempt,
                'user_email' => $email,
                'reason'     => 'Registration disabled (page block)',
                'path'       => sanitize_text_field( wp_parse_url( add_query_arg( array(), home_url( $_SERVER['REQUEST_URI'] ?? '/' ) ), PHP_URL_PATH ) ),
            ) );
        }

        // Show message and stop execution (403)
        wp_die(
            '<h1>' . esc_html__( 'Registration disabled', 'gv' ) . '</h1>'
            . '<p>' . esc_html__( 'Registration is currently disabled. If you need an account, please contact us.', 'gv' ) . '</p>',
            __( 'Registration disabled', 'gv' ),
            array( 'response' => 403 )
        );
    }
}, 5 );



/**
 * Optional: show a notice on My Account after redirect.
 */
add_action( 'woocommerce_before_customer_login_form', function(){
    if ( isset( $_GET['reg'] ) && $_GET['reg'] === 'disabled' ) {
        wc_print_notice( __( 'Registration is currently disabled. If you need an account, please contact us.', 'gv' ), 'notice' );
    }
} );


// ===== Snippet #127: AI Agent Manager =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV - AI Agent Manager
 * Description: Beheer van AI agents (specs/versies) + logging van Q&A. Integreert met n8n via REST endpoints.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

global $gv_aiam_db_version;
$gv_aiam_db_version = '1.0.0';

function gv_aiman_table_agents() { global $wpdb; return $wpdb->prefix . 'gv_ai_agents'; }
function gv_aiman_table_logs()   { global $wpdb; return $wpdb->prefix . 'gv_ai_agent_log'; }
function gv_aiman_cap()          { return 'manage_options'; } // eventueel verfijnen naar custom capability

// ---- Activation: create tables ----
function gv_aiman_activate() {
    global $wpdb, $gv_aiam_db_version;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $charset_collate = $wpdb->get_charset_collate();

    $agents = gv_aiman_table_agents();
    $logs   = gv_aiman_table_logs();

    $sql_agents = "CREATE TABLE $agents (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        agent_key VARCHAR(191) NOT NULL,              -- unieke machine key (bv. drawing_qa_auditor)
        name VARCHAR(191) NOT NULL,                   -- display naam
        version VARCHAR(50) NOT NULL DEFAULT '1.0.0',
        category VARCHAR(100) DEFAULT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'active', -- active | draft | archived
        spec_json LONGTEXT NULL,                      -- volledige YAML/JSON spec als JSON opgeslagen
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY agent_key_unique (agent_key)
    ) $charset_collate;";

    $sql_logs = "CREATE TABLE $logs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        agent_id BIGINT UNSIGNED NOT NULL,
        ts DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        customer_user_id BIGINT UNSIGNED NULL,        -- WooCommerce user id (optioneel)
        channel VARCHAR(50) NULL,                     -- bv. n8n, wc, email, cli
        category VARCHAR(100) NULL,                   -- CAD, Sales, Procurement, ...
        agent_version VARCHAR(50) NULL,
        question LONGTEXT NULL,
        answer LONGTEXT NULL,
        feedback_score INT NULL,                      -- 1..5 of -1 none
        metadata JSON NULL,                           -- vrije JSON
        PRIMARY KEY (id),
        KEY agent_id_idx (agent_id),
        CONSTRAINT fk_agent FOREIGN KEY (agent_id) REFERENCES $agents(id) ON DELETE CASCADE
    ) $charset_collate;";

    dbDelta($sql_agents);
    dbDelta($sql_logs);

    add_option('gv_aiam_db_version', $gv_aiam_db_version);
    // Default settings
    add_option('gv_aiam_settings', array(
        'api_token'    => wp_generate_password(32, false, false),
        'onedrive_dir' => '',
        'git_repo_url' => '',
    ));
}
//register_activation_hook(__FILE__, 'gv_aiman_activate');
// --- Snippet-safe installer: run once when version mismatch ---
add_action('init', 'gv_aiman_maybe_install', 5);
function gv_aiman_maybe_install() {
    if (!function_exists('dbDelta')) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }
    global $wpdb, $gv_aiam_db_version;
    if (empty($gv_aiam_db_version)) { $gv_aiam_db_version = '1.0.0'; }

    $current = get_option('gv_aiam_db_version');
    if ($current === $gv_aiam_db_version) return;

    $charset_collate = $wpdb->get_charset_collate();
    $agents = gv_aiman_table_agents();
    $logs   = gv_aiman_table_logs();

    // Let op: FOREIGN KEY wordt vaak genegeerd door dbDelta; we gebruiken alleen index.
    $sql_agents = "CREATE TABLE $agents (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        agent_key VARCHAR(191) NOT NULL,
        name VARCHAR(191) NOT NULL,
        version VARCHAR(50) NOT NULL DEFAULT '1.0.0',
        category VARCHAR(100) NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        spec_json LONGTEXT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY agent_key_unique (agent_key)
    ) $charset_collate;";

    $sql_logs = "CREATE TABLE $logs (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        agent_id BIGINT UNSIGNED NOT NULL,
        ts DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        customer_user_id BIGINT UNSIGNED NULL,
        channel VARCHAR(50) NULL,
        category VARCHAR(100) NULL,
        agent_version VARCHAR(50) NULL,
        question LONGTEXT NULL,
        answer LONGTEXT NULL,
        feedback_score INT NULL,
        metadata JSON NULL,
        PRIMARY KEY (id),
        KEY agent_id_idx (agent_id)
    ) $charset_collate;";

    dbDelta($sql_agents);
    dbDelta($sql_logs);

    // Defaults alleen zetten als ze ontbreken
    $opts = get_option('gv_aiam_settings');
    if (empty($opts) || !is_array($opts)) {
        add_option('gv_aiam_settings', array(
            'api_token'    => wp_generate_password(32, false, false),
            'onedrive_dir' => '',
            'git_repo_url' => '',
        ));
    }

    update_option('gv_aiam_db_version', $gv_aiam_db_version);
}

// Extra veiligheid: zorg dat installer ook loopt vóór REST inserts
add_action('rest_api_init', function() {
    gv_aiman_maybe_install();
}, 1);

// ---- Admin menu ----
function gv_aiman_admin_menu() {
    $parent = add_menu_page(
        'AI Agents',
        'AI Agents',
        gv_aiman_cap(),
        'gv-aiman-agents',
        'gv_aiman_render_agents',
        'dashicons-robot',
        56
    );
    add_submenu_page('gv-aiman-agents', 'Agents', 'Agents', gv_aiman_cap(), 'gv-aiman-agents', 'gv_aiman_render_agents');
    add_submenu_page('gv-aiman-agents', 'Logs', 'Logs', gv_aiman_cap(), 'gv-aiman-logs', 'gv_aiman_render_logs');
    add_submenu_page('gv-aiman-agents', 'Settings', 'Settings', gv_aiman_cap(), 'gv-aiman-settings', 'gv_aiman_render_settings');
}
add_action('admin_menu', 'gv_aiman_admin_menu');

// ---- Helpers ----
function gv_aiman_get_setting($key, $default = '') {
    $opts = get_option('gv_aiam_settings', array());
    return isset($opts[$key]) ? $opts[$key] : $default;
}
function gv_aiman_update_setting($key, $value) {
    $opts = get_option('gv_aiam_settings', array());
    $opts[$key] = $value;
    update_option('gv_aiam_settings', $opts);
}
function gv_aiman_sanitize_json($raw) {
    // Haal WP slashes weg (ipv stripslashes)
    $raw = wp_unslash($raw);

    // 1) Verwijder UTF-8 BOM
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

    // 2) Vervang NBSP en zero-width chars door gewone spatie of niets
    $raw = str_replace(
        array("\xC2\xA0", "\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D"),
        array(' ', '', '', ''),
        $raw
    );

    // 3) “Slimme” quotes -> normale quotes
    $raw = str_replace(
        array("\xE2\x80\x9C","\xE2\x80\x9D","\xE2\x80\x98","\xE2\x80\x99"),
        array('"','"',"'", "'"),
        $raw
    );

    // 4) Trailing commas in object/array verwijderen (veelvoorkomende copy/paste fout)
    $raw = preg_replace('/,\s*([}\]])/', '$1', $raw);

    // 5) JSON decoderen
    $decoded = json_decode($raw, true);

    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        // Geef preciezere fout terug
        return new WP_Error('json_error', 'Spec JSON is ongeldig: ' . json_last_error_msg());
    }
    return $decoded;
}

function gv_aiman_find_agent_by_key($agent_key) {
    global $wpdb; $t = gv_aiman_table_agents();
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE agent_key = %s", $agent_key), ARRAY_A);
}


// ---- Secure token helper ----
function gv_aiman_new_token($length = 64) {
    // Geeft een hex string van gewenste lengte (default 64 chars)
    try {
        $bytes = random_bytes(intval($length / 2));
        return bin2hex($bytes);
    } catch (Exception $e) {
        // Fallback: alfanumeriek, geen speciale tekens (makkelijker kopiëren)
        return wp_generate_password($length, false, false);
    }
}


// ---- Admin: Agents ----
function gv_aiman_render_agents() {
    if (!current_user_can(gv_aiman_cap())) return;

    global $wpdb; $t = gv_aiman_table_agents();

    // Handle create/update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'gv_aiman_agent_save')) {
        $id        = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $agent_key = sanitize_key($_POST['agent_key']);
        $name      = sanitize_text_field($_POST['name']);
        $version   = sanitize_text_field($_POST['version']);
        $category  = sanitize_text_field($_POST['category']);
        $status    = sanitize_text_field($_POST['status']);
        $spec_json_raw = isset($_POST['spec_json']) ? wp_unslash($_POST['spec_json']) : '';


        $spec = gv_aiman_sanitize_json($spec_json_raw);
        if (is_wp_error($spec)) {
            echo '<div class="notice notice-error"><p>' . esc_html($spec->get_error_message()) . '</p></div>';
        } else {
            $data = array(
                'agent_key' => $agent_key,
                'name'      => $name,
                'version'   => $version ?: '1.0.0',
                'category'  => $category ?: null,
                'status'    => in_array($status, array('active','draft','archived'), true) ? $status : 'active',
                'spec_json' => wp_json_encode($spec, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),
            );
            if ($id) {
                $wpdb->update($t, $data, array('id'=>$id));
                echo '<div class="notice notice-success"><p>Agent bijgewerkt.</p></div>';
            } else {
                $wpdb->insert($t, $data);
                echo '<div class="notice notice-success"><p>Agent aangemaakt.</p></div>';
            }
        }
    }

    // Handle edit
    $editing = null;
    if (isset($_GET['edit'])) {
        $editing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE id=%d", intval($_GET['edit'])), ARRAY_A);
    }

    // List
    $agents = $wpdb->get_results("SELECT * FROM $t ORDER BY updated_at DESC", ARRAY_A);

    echo '<div class="wrap"><h1 class="wp-heading-inline">AI Agents</h1>';
    echo '<hr class="wp-header-end">';

    // Form
    $id        = $editing ? intval($editing['id']) : 0;
    $agent_key = $editing ? esc_attr($editing['agent_key']) : '';
    $name      = $editing ? esc_attr($editing['name']) : '';
    $version   = $editing ? esc_attr($editing['version']) : '1.0.0';
    $category  = $editing ? esc_attr($editing['category']) : '';
    $status    = $editing ? esc_attr($editing['status']) : 'active';
    $spec_json = $editing ? esc_textarea($editing['spec_json']) : esc_textarea(wp_json_encode(array(
        'role' => 'Describe what the agent does',
        'goals' => ['Goal 1', 'Goal 2'],
        'inputs' => ['files', 'context', 'parameters'],
        'outputs' => ['report_markdown', 'step_files'],
        'policies' => ['house_rules.yaml', 'iso_128', 'iso_129-1'],
        'owner' => 'Gemini Valve',
    ), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

    echo '<h2>' . ($editing ? 'Bewerk agent' : 'Nieuwe agent') . '</h2>';
    echo '<form method="post">';
    wp_nonce_field('gv_aiman_agent_save');
    echo '<table class="form-table" role="presentation"><tbody>';
    echo '<tr><th><label>Agent Key</label></th><td><input required class="regular-text" name="agent_key" value="'.$agent_key.'" placeholder="drawing_qa_auditor"></td></tr>';
    echo '<tr><th><label>Naam</label></th><td><input required class="regular-text" name="name" value="'.$name.'" placeholder="Drawing QA Auditor"></td></tr>';
    echo '<tr><th><label>Versie</label></th><td><input class="regular-text" name="version" value="'.$version.'"></td></tr>';
    echo '<tr><th><label>Categorie</label></th><td><input class="regular-text" name="category" value="'.$category.'" placeholder="CAD/QA"></td></tr>';
    echo '<tr><th><label>Status</label></th><td><select name="status">
            <option '.selected($status,'active',false).' value="active">active</option>
            <option '.selected($status,'draft',false).' value="draft">draft</option>
            <option '.selected($status,'archived',false).' value="archived">archived</option>
        </select></td></tr>';
    echo '<tr><th><label>Spec (JSON)</label></th><td><textarea name="spec_json" rows="16" style="width:100%">'.$spec_json.'</textarea></td></tr>';
    echo '</tbody></table>';
    echo '<input type="hidden" name="id" value="'.$id.'">';
    submit_button($editing ? 'Bijwerken' : 'Aanmaken');
    echo '</form>';
	// --- Client-side JSON validator voor Spec (JSON) ---
	echo '<script>
	document.addEventListener("DOMContentLoaded",function(){
	  const ta = document.querySelector("textarea[name=\"spec_json\"]");
	  if(!ta) return;
	  const status = document.createElement("div");
	  status.id = "gv-json-status";
	  status.style.marginTop = "6px";
	  ta.parentNode.appendChild(status);
	  function validate(){
		try {
		  JSON.parse(ta.value);
		  status.textContent = "✅ Geldige JSON";
		  status.style.color = "#2d7";
		} catch(e){
		  status.textContent = "❌ Ongeldige JSON: " + e.message;
		  status.style.color = "#d33";
		}
	  }
	  ta.addEventListener("input", validate);
	  validate();
	});
	</script>';

	
    echo '<hr><h2>Overzicht</h2>';
    echo '<table class="widefat striped"><thead><tr>
            <th>ID</th><th>Key</th><th>Naam</th><th>Versie</th><th>Categorie</th><th>Status</th><th>Updated</th><th>Acties</th>
        </tr></thead><tbody>';
    if ($agents) {
        foreach ($agents as $a) {
            $edit_url = admin_url('admin.php?page=gv-aiman-agents&edit=' . intval($a['id']));
            echo '<tr>
                <td>'.intval($a['id']).'</td>
                <td>'.esc_html($a['agent_key']).'</td>
                <td>'.esc_html($a['name']).'</td>
                <td>'.esc_html($a['version']).'</td>
                <td>'.esc_html($a['category']).'</td>
                <td>'.esc_html($a['status']).'</td>
                <td>'.esc_html($a['updated_at']).'</td>
                <td><a class="button" href="'.esc_url($edit_url).'">Bewerken</a></td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="8">Geen agents gevonden.</td></tr>';
    }
    echo '</tbody></table>';

    echo '</div>';
}

// ---- Admin: Logs ----
function gv_aiman_render_logs() {
    if (!current_user_can(gv_aiman_cap())) return;
    global $wpdb; $t_logs = gv_aiman_table_logs(); $t_agents = gv_aiman_table_agents();

    // Filters
    $agent_id = isset($_GET['agent_id']) ? intval($_GET['agent_id']) : 0;
    $where = 'WHERE 1=1';
    $params = array();
    if ($agent_id) { $where .= ' AND l.agent_id = %d'; $params[] = $agent_id; }

    $logs = $wpdb->get_results($wpdb->prepare("
        SELECT l.*, a.name AS agent_name
        FROM $t_logs l
        JOIN $t_agents a ON a.id = l.agent_id
        $where
        ORDER BY l.ts DESC
        LIMIT 500
    ", $params), ARRAY_A);

    $agents = $wpdb->get_results("SELECT id, name FROM $t_agents ORDER BY name", ARRAY_A);

    echo '<div class="wrap"><h1>Agent Logs</h1>';
    echo '<form method="get"><input type="hidden" name="page" value="gv-aiman-logs">';
    echo '<label for="agent_id">Filter op agent: </label>';
    echo '<select name="agent_id" id="agent_id"><option value="0">(alle)</option>';
    foreach ($agents as $a) {
        echo '<option '.selected($agent_id, intval($a['id']), false).' value="'.intval($a['id']).'">'.esc_html($a['name']).'</option>';
    }
    echo '</select> ';
    submit_button('Filter', 'secondary', '', false);
    echo '</form><br>';

    echo '<table class="widefat striped"><thead><tr>
        <th>TS</th><th>Agent</th><th>Versie</th><th>Categorie</th><th>Score</th><th>Vraag</th><th>Antwoord</th><th>Channel</th>
    </tr></thead><tbody>';
    if ($logs) {
        foreach ($logs as $l) {
            echo '<tr>
                <td>'.esc_html($l['ts']).'</td>
                <td>'.esc_html($l['agent_name']).'</td>
                <td>'.esc_html($l['agent_version']).'</td>
                <td>'.esc_html($l['category']).'</td>
                <td>'.esc_html($l['feedback_score']).'</td>
                <td style="max-width:360px">'.wp_kses_post(nl2br(esc_html(wp_trim_words($l['question'], 50)))).'</td>
                <td style="max-width:360px">'.wp_kses_post(nl2br(esc_html(wp_trim_words($l['answer'], 50)))).'</td>
                <td>'.esc_html($l['channel']).'</td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="8">Geen logs gevonden.</td></tr>';
    }
    echo '</tbody></table></div>';
}

// ---- Admin: Settings ----
function gv_aiman_render_settings() {
    if (!current_user_can(gv_aiman_cap())) return;

    // Acties verwerken
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // A) Token regenereren via aparte knop
        if (isset($_POST['gv_aiman_action']) && $_POST['gv_aiman_action'] === 'regen_token') {
            check_admin_referer('gv_aiman_regen_token');
            $new = gv_aiman_new_token(64); // 64 tekens
            gv_aiman_update_setting('api_token', $new);
            echo '<div class="notice notice-success"><p>Nieuwe API-token gegenereerd. Werk je n8n-flows bij met deze nieuwe token.</p></div>';
        }

        // B) Normale settings opslaan
        if (isset($_POST['gv_aiman_action']) && $_POST['gv_aiman_action'] === 'save_settings') {
            check_admin_referer('gv_aiman_settings_save');
            gv_aiman_update_setting('onedrive_dir', sanitize_text_field($_POST['onedrive_dir']));
            gv_aiman_update_setting('git_repo_url', esc_url_raw($_POST['git_repo_url']));
            // Token alleen overschrijven als er expliciet iets is ingevuld
            if (!empty($_POST['api_token'])) {
                gv_aiman_update_setting('api_token', sanitize_text_field($_POST['api_token']));
            }
            echo '<div class="notice notice-success"><p>Instellingen opgeslagen.</p></div>';
        }
    }

    // Huidige waarden ophalen
    $api_token = gv_aiman_get_setting('api_token');
    $onedrive  = gv_aiman_get_setting('onedrive_dir');
    $git_repo  = gv_aiman_get_setting('git_repo_url');

    echo '<div class="wrap"><h1>AI Agents Settings</h1>';

    // --- Form 1: Token regenereren ---
    echo '<h2 class="title">API Token</h2>';
    echo '<p>Gebruik deze token als <code>Authorization: Bearer &lt;token&gt;</code> in n8n/HTTP. 
          Regenereren verbreekt directe toegang totdat je je flows bijwerkt.</p>';

    echo '<form method="post" style="margin-bottom:16px;">';
    wp_nonce_field('gv_aiman_regen_token');
    echo '<input type="hidden" name="gv_aiman_action" value="regen_token">';
    echo '<p><input type="text" readonly class="regular-text code" value="'. esc_attr($api_token) .'" style="width: 480px; max-width:100%;"> ';
    submit_button('Genereer nieuwe token', 'secondary', '', false);
    echo '</p></form>';

    // --- Form 2: Overige instellingen + (optioneel) token handmatig wijzigen ---
    echo '<h2 class="title">Overige instellingen</h2>';
    echo '<form method="post">';
    wp_nonce_field('gv_aiman_settings_save');
    echo '<input type="hidden" name="gv_aiman_action" value="save_settings">';

    echo '<table class="form-table"><tbody>';
    echo '<tr><th><label>API Token (handmatig overschrijven)</label></th>
            <td><input class="regular-text" name="api_token" value="" placeholder="(leeg laten om huidige te behouden)">
            <p class="description">Laat leeg om de huidige token te behouden. Vul iets in om te vervangen.</p></td></tr>';

    echo '<tr><th><label>OneDrive directory</label></th>
            <td><input class="regular-text" name="onedrive_dir" value="'. esc_attr($onedrive) .'" placeholder="C:\\Users\\...\\AI-Agents"></td></tr>';

    echo '<tr><th><label>Git repo URL</label></th>
            <td><input class="regular-text" name="git_repo_url" value="'. esc_attr($git_repo) .'" placeholder="https://github.com/....git"></td></tr>';

    echo '</tbody></table>';
    submit_button('Opslaan');
    echo '</form>';

    echo '</div>';
	echo '<h3>Huidige waarden</h3><table class="form-table"><tbody>';
	echo '<tr><th>OneDrive directory</th><td><code>' . esc_html( gv_aiman_get_setting('onedrive_dir') ) . '</code></td></tr>';
	echo '<tr><th>Git repo URL</th><td><code>' . esc_html( gv_aiman_get_setting('git_repo_url') ) . '</code></td></tr>';
	echo '</tbody></table>';

}


// ---- REST API ----
add_action('rest_api_init', function() {
    register_rest_route('gv-ai/v1', '/agents', array(
        'methods'  => WP_REST_Server::READABLE,
        'callback' => 'gv_aiman_rest_list_agents',
        'permission_callback' => 'gv_aiman_rest_auth'
    ));
    register_rest_route('gv-ai/v1', '/log', array(
        'methods'  => WP_REST_Server::CREATABLE,
        'callback' => 'gv_aiman_rest_create_log',
        'permission_callback' => 'gv_aiman_rest_auth'
    ));
});

function gv_aiman_rest_auth(WP_REST_Request $req) {
    $hdr = $req->get_header('authorization');
    $token = gv_aiman_get_setting('api_token');
    if (!$hdr || stripos($hdr, 'bearer ') !== 0) return new WP_Error('forbidden','Bearer token ontbreekt.', array('status'=>401));
    $bearer = trim(substr($hdr, 7));
    if (!hash_equals($token, $bearer)) return new WP_Error('forbidden','Ongeldige token.', array('status'=>403));
    return true;
}

function gv_aiman_rest_list_agents(WP_REST_Request $req) {
    global $wpdb; $t = gv_aiman_table_agents();
    $rows = $wpdb->get_results("SELECT id, agent_key, name, version, category, status, spec_json, updated_at FROM $t WHERE status!='archived' ORDER BY name", ARRAY_A);
    // decode spec_json for convenience
    foreach ($rows as &$r) { $r['spec'] = json_decode($r['spec_json'], true); unset($r['spec_json']); }
    return rest_ensure_response($rows);
}

function gv_aiman_rest_create_log(WP_REST_Request $req) {
    global $wpdb; $t_logs = gv_aiman_table_logs(); $t_agents = gv_aiman_table_agents();
    $p = $req->get_json_params();

    $agent_key = isset($p['agent_key']) ? sanitize_key($p['agent_key']) : '';
    if (!$agent_key) return new WP_Error('bad_request','agent_key vereist', array('status'=>400));
    $agent = $wpdb->get_row($wpdb->prepare("SELECT id, version FROM $t_agents WHERE agent_key=%s", $agent_key), ARRAY_A);
    if (!$agent) return new WP_Error('not_found','Agent niet gevonden', array('status'=>404));

    $data = array(
        'agent_id'      => intval($agent['id']),
        'customer_user_id' => isset($p['customer_user_id']) ? intval($p['customer_user_id']) : null,
        'channel'       => isset($p['channel']) ? sanitize_text_field($p['channel']) : 'n8n',
        'category'      => isset($p['category']) ? sanitize_text_field($p['category']) : null,
        'agent_version' => isset($p['agent_version']) ? sanitize_text_field($p['agent_version']) : $agent['version'],
        'question'      => isset($p['question']) ? wp_kses_post($p['question']) : null,
        'answer'        => isset($p['answer']) ? wp_kses_post($p['answer']) : null,
        'feedback_score'=> isset($p['feedback_score']) ? intval($p['feedback_score']) : null,
        'metadata'      => isset($p['metadata']) ? wp_json_encode($p['metadata'], JSON_UNESCAPED_SLASHES) : null,
    );
    $wpdb->insert($t_logs, $data);
    return rest_ensure_response(array('ok'=>true,'id'=>$wpdb->insert_id));
}


// ===== Snippet #128: Supplier Enrichment tool =====
// Scope: global | Priority: 10


/** GV – Supplier Enrichment (Snippet; uses gv_supplier dropdown) */
if (!defined('ABSPATH')) return;

if (!class_exists('GV_Supplier_Enrichment_Snippet')) {
class GV_Supplier_Enrichment_Snippet {
    const NONCE  = 'gv_supplier_enrichment_nonce';
    const CAP    = 'manage_woocommerce';
    const OPT    = 'gv_supplier_enrichment';

    /** Resolve the product meta key used to store the linked supplier post ID */
    public static function supplier_meta_key() {
        if (class_exists('GV_Suppliers_Manager') && defined('GV_Suppliers_Manager::PRODUCT_SUPPLIER_ID')) {
            return GV_Suppliers_Manager::PRODUCT_SUPPLIER_ID; // _gv_proc_supplier_id
        }
        return '_gv_proc_supplier_id';
    }

    /** Fetch suppliers for dropdown */
    public static function get_suppliers() {
        if (!post_type_exists('gv_supplier')) return [];
        $rows = get_posts([
            'post_type'        => 'gv_supplier',
            'post_status'      => 'publish',
            'numberposts'      => -1,
            'orderby'          => 'title',
            'order'            => 'ASC',
            'suppress_filters' => false,
        ]);
        // Attach margin for label decoration
        foreach ($rows as $r) {
            $r->gv_margin = get_post_meta($r->ID, '_gv_margin_percent', true);
        }
        return $rows;
    }

    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('wp_ajax_gv_enrich_start', [$this, 'ajax_start']);
        add_action('wp_ajax_gv_enrich_step',  [$this, 'ajax_step']);
        add_filter('gv_supplier_adapters',    [$this, 'register_adapters']);
        add_action('admin_head',              [$this, 'inline_css']);
        add_action('admin_footer',            [$this, 'inline_js']);
    }

    /** ---------------- Menu & Page ---------------- */
    public function menu() {
        add_submenu_page(
            'woocommerce',
            'Supplier Enrichment',
            'Supplier Enrichment',
            self::CAP,
            'gv-supplier-enrichment',
            [$this, 'page']
        );
    }

    public function page() {
        if (!current_user_can(self::CAP)) wp_die('Insufficient permissions');

        $adapters   = apply_filters('gv_supplier_adapters', []);
        $webhook    = esc_url(get_option(self::OPT.'_webhook', ''));
        $batch_size = intval(get_option(self::OPT.'_batch', 20));
        $rate_ms    = intval(get_option(self::OPT.'_rate', 800));

        $suppliers  = self::get_suppliers(); // may be empty (CPT missing)
        $supplier_list_url = admin_url('edit.php?post_type=gv_supplier');
        ?>
        <div class="wrap">
            <h1>Supplier Enrichment</h1>
            <form id="gv-enrich-form" onsubmit="return false;">
                <table class="form-table">
                    <tr>
                        <th><label for="adapter">Supplier adapter</label></th>
                        <td>
                            <select id="adapter" name="adapter">
                                <?php foreach ($adapters as $id => $a): ?>
                                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($a['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Syveco = scrape + heuristics; JSON Webhook = POST to your n8n (or other) endpoint.</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="supplier_id">Supplier (from Suppliers Manager)</label></th>
                        <td>
                            <?php if ($suppliers): ?>
                                <select id="supplier_id" name="supplier_id">
                                    <option value=""><?php echo esc_html('— None —'); ?></option>
                                    <?php foreach ($suppliers as $s):
                                        $label = $s->post_title;
                                        if ($s->gv_margin !== '') $label .= ' ('.number_format((float)$s->gv_margin, 2).'%)';
                                    ?>
                                        <option value="<?php echo esc_attr($s->ID); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    Processes all products where <code><?php echo esc_html(self::supplier_meta_key()); ?></code> equals the selected supplier.
                                    &nbsp;<a href="<?php echo esc_url($supplier_list_url); ?>">Manage suppliers</a>
                                </p>
                            <?php else: ?>
                                <em>No <code>gv_supplier</code> posts found. Create suppliers first (menu: WooCommerce → Suppliers).</em>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="sku_list">Supplier SKUs / Article References</label></th>
                        <td>
                            <textarea id="sku_list" rows="6" placeholder="One per line…"></textarea>
                            <p class="description">Optional. If filled, we’ll process exactly these products (by <code>_gv_proc_supplier_sku</code> or Woo SKU), regardless of the dropdown.</p>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="batch_size">Batch size</label></th>
                        <td><input type="number" id="batch_size" value="<?php echo esc_attr($batch_size); ?>" min="1" max="200"></td>
                    </tr>
                    <tr>
                        <th><label for="rate_ms">Rate limit (ms)</label></th>
                        <td><input type="number" id="rate_ms" value="<?php echo esc_attr($rate_ms); ?>" min="0" step="50"></td>
                    </tr>
                    <tr class="gv-webhook">
                        <th><label for="webhook">JSON Webhook URL</label></th>
                        <td><input type="url" id="webhook" value="<?php echo esc_attr($webhook); ?>" placeholder="https://n8n.example.com/webhook/abc123"></td>
                    </tr>
                    <tr>
                        <th>Options</th>
                        <td>
                            <label><input type="checkbox" id="dry_run" checked> Dry-run (don’t save; preview only)</label><br>
                            <label><input type="checkbox" id="csv_fail" checked> Enable “Failures CSV”</label><br>
                            <label><input type="checkbox" id="csv_preview" checked> Enable “Preview CSV” (would-be updates)</label>
                        </td>
                    </tr>
                </table>

                <p>
                    <button class="button button-primary" id="gv-start">Start Enrichment</button>
                    <button class="button" id="gv-stop" disabled>Stop</button>
                </p>

                <div id="gv-progress" style="display:none">
                    <p><strong>Status:</strong> <span id="gv-status">Preparing…</span></p>
                    <progress id="gv-bar" max="100" value="0" style="width:420px;"></progress>
                    <div style="margin-top:8px;">
                        <button type="button" id="btn-dl-fail" class="button" disabled>Download Failures CSV</button>
                        <button type="button" id="btn-dl-preview" class="button" disabled>Download Preview CSV</button>
                    </div>
                    <pre id="gv-log" style="max-height:280px; overflow:auto;"></pre>
                </div>
            </form>
        </div>
        <?php
    }

    /** ---------------- AJAX ---------------- */
    public function ajax_start() {
        check_ajax_referer(self::NONCE, 'nonce');
        if (!current_user_can(self::CAP)) wp_send_json_error('cap');

		$posted_webhook = isset($_POST['webhook'])
			? $_POST['webhook']
			: ( $_POST['webhook_url'] ?? '' );

		update_option(self::OPT.'_webhook', esc_url_raw($posted_webhook));
		
        //update_option(self::OPT.'_webhook', esc_url_raw($_POST['webhook'] ?? ''));
        update_option(self::OPT.'_batch',   intval($_POST['batch_size'] ?? 20));
        update_option(self::OPT.'_rate',    intval($_POST['rate_ms'] ?? 800));

        $adapter_id  = sanitize_text_field($_POST['adapter'] ?? '');
        $supplier_id = isset($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : 0; // numeric from dropdown
        $sku_list    = $this->normalize_lines($_POST['sku_list'] ?? '');
        $dry_run     = !empty($_POST['dry_run']) && $_POST['dry_run'] === '1';

        $queue = $this->build_queue($supplier_id, $sku_list);
        if (!$queue) wp_send_json_error(['message' => 'No products found to process.']);

        $state = [
            'adapter'     => $adapter_id,
            'supplier_id' => $supplier_id, // int
            'queue'       => array_values($queue),
            'done'        => [],
            'errors'      => [],
            'dry_run'     => $dry_run,
        ];
        wp_send_json_success(['state' => $state, 'total' => count($queue)]);
    }

    public function ajax_step() {
        check_ajax_referer(self::NONCE, 'nonce');
        if (!current_user_can(self::CAP)) wp_send_json_error('cap');

        $state      = json_decode(stripslashes($_POST['state'] ?? '{}'), true);
        $batch_size = intval($_POST['batch_size'] ?? 20);

        if (empty($state['queue'])) {
            wp_send_json_success(['state' => $state, 'progress' => 100, 'tick' => []]);
        }

        $adapters = apply_filters('gv_supplier_adapters', []);
        $adapter  = $adapters[$state['adapter']] ?? null;
        if (!$adapter) wp_send_json_error(['message' => 'Unknown adapter']);

        $tick = [];
        for ($i = 0; $i < $batch_size && !empty($state['queue']); $i++) {
            $pid = array_shift($state['queue']);
            $res = $this->process_one($pid, $adapter, (int)$state['supplier_id'], !empty($state['dry_run']));
            if ($res['ok']) $state['done'][] = $pid; else $state['errors'][] = ['product_id'=>$pid,'error'=>$res['error'],'ref'=>$res['ref']??''];
            $tick[] = $res;
        }

        $total    = count($state['done']) + count($state['errors']) + count($state['queue']);
        $progress = $total ? round(100 * (count($state['done']) + count($state['errors'])) / $total) : 100;

        wp_send_json_success(['state' => $state, 'progress' => $progress, 'tick' => $tick]);
    }

    /** ---------------- Core ---------------- */
    private function build_queue($supplier_id, $sku_list) {
        $ids = [];
        if ($sku_list) {
            foreach ($sku_list as $sku) {
                $p = $this->find_product_by_supplier_sku($sku);
                if ($p) $ids[] = $p;
            }
        } elseif ($supplier_id > 0) {
            $meta_key = self::supplier_meta_key();
            $q = new WP_Query([
                'post_type'      => ['product','product_variation'],
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => [[ 'key'=>$meta_key, 'value'=>$supplier_id, 'compare'=>'=' ]],
            ]);
            $ids = $q->posts;
        }
        return array_unique(array_map('intval', $ids));
    }

    private function normalize_lines($text) {
        $lines = preg_split('/\R+/', (string)$text);
        return array_values(array_filter(array_map('trim', $lines)));
    }

    private function find_product_by_supplier_sku($sku) {
        global $wpdb;
        $sku = sanitize_text_field($sku);
        // Supplier SKU first
        $pid = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
            '_gv_proc_supplier_sku', $sku
        ));
        if ($pid) return (int)$pid;
        // Woo SKU fallback
        $p = function_exists('wc_get_product_id_by_sku') ? wc_get_product_id_by_sku($sku) : 0;
        return $p ? (int)$p : 0;
    }

    private function process_one($product_id, $adapter, $supplier_id, $dry_run=false) {
        $product = function_exists('wc_get_product') ? wc_get_product($product_id) : null;
        if (!$product) return ['ok'=>false,'product_id'=>$product_id,'error'=>'Product not found'];

        $ref = get_post_meta($product_id, '_gv_proc_supplier_sku', true);
        if (!$ref) $ref = $product->get_sku();
        if (!$ref) return ['ok'=>false,'product_id'=>$product_id,'error'=>'No reference (supplier_sku or sku)'];

        try {
            $data = call_user_func($adapter['callback'], [
                'product_id'  => $product_id,
                'supplier_id' => $supplier_id, // int
                'ref'         => $ref,
                'webhook'     => esc_url_raw(get_option(self::OPT.'_webhook','')),
            ]);
            if (!is_array($data) || empty($data)) return ['ok'=>false,'product_id'=>$product_id,'error'=>'No data from adapter','ref'=>$ref];

            $normalized = [
                '_gv_proc_model'        => $data['model']       ?? '',
                '_gv_proc_description'  => $data['description'] ?? '',
                '_gv_proc_brand'        => $data['brand']       ?? '',
                '_gv_proc_tags'         => is_array($data['tags']) ? implode(', ', $data['tags']) : ($data['tags'] ?? ''),
                '_gv_proc_size'         => $data['size']        ?? '',
                '_gv_proc_size2'        => $data['size2']       ?? '',
                '_gv_proc_size3'        => $data['size3']       ?? '',
                '_gv_proc_size4'        => $data['size4']       ?? '',
                '_gv_enrich_source_url' => $data['source_url']  ?? '',
            ];

            $changes = [];
            foreach ($normalized as $k=>$v) {
                if ($v === '') continue;
                $old = get_post_meta($product_id, $k, true);
                if ($old !== $v) $changes[$k] = $v;
            }

            if (!$dry_run) {
                foreach ($changes as $k=>$v) update_post_meta($product_id, $k, wc_clean($v));
                update_post_meta($product_id, '_gv_enrich_last', current_time('mysql'));
            }

            return [
                'ok'         => true,
                'product_id' => $product_id,
                'ref'        => $ref,
                'updated'    => array_keys($changes),
                'changes'    => $changes,
                'source'     => $normalized['_gv_enrich_source_url'],
                'dry'        => $dry_run ? 1 : 0,
            ];
        } catch (\Throwable $e) {
            return ['ok'=>false,'product_id'=>$product_id,'error'=>$e->getMessage(),'ref'=>$ref];
        }
    }

    /** ---------------- Adapters ---------------- */
    public function register_adapters($adapters) {
        // Syveco (direct fetch + heuristics)
        $adapters['syveco'] = [
            'label'    => 'Syveco (direct fetch)',
            'callback' => function($args){
                $ref = sanitize_text_field($args['ref']);
                $search_url = 'https://www.syveco.com/en/catalogsearch/result/?q=' . rawurlencode($ref);
                $out = [
                    'model'=>'','description'=>'','brand'=>'','tags'=>'',
                    'size'=>'','size2'=>'','size3'=>'','size4'=>'',
                    'source_url'=>$search_url,
                ];
                $html = $this->http_get($search_url);
                $purl = $this->parse_syveco_first_result_url($html);
                if ($purl) {
                    $phtml = $this->http_get($purl);
                    $out['source_url'] = $purl;
                    $parsed = $this->parse_syveco_product_page($phtml);
                    foreach ($parsed as $k=>$v) { if (!empty($v)) $out[$k]=$v; }
                }
                $this->syveco_heuristics($ref, $out);
                return $out;
            },
        ];

        // JSON Webhook (n8n, etc.)
        $adapters['json_webhook'] = [
            'label'    => 'Generic JSON Webhook (POST)',
            'callback' => function($args){
                $url = esc_url_raw($args['webhook']);
                if (!$url) throw new \Exception('Webhook URL not configured');
                $resp = wp_remote_post($url, [
                    'timeout'=>30,
                    'headers'=>['Content-Type'=>'application/json'],
                    'body'   => wp_json_encode([
                        'sku'         => sanitize_text_field($args['ref']),
                        'supplier_id' => (int)$args['supplier_id'],
                        'product_id'  => (int)$args['product_id'],
                    ]),
                ]);
                if (is_wp_error($resp)) throw new \Exception($resp->get_error_message());
                $code = wp_remote_retrieve_response_code($resp);
                $body = wp_remote_retrieve_body($resp);
                if ($code < 200 || $code >= 300) throw new \Exception("Webhook HTTP $code: $body");
                $data = json_decode($body, true);
                if (!is_array($data)) throw new \Exception('Invalid JSON from webhook');
                return [
                    'model'       => $data['model']       ?? '',
                    'description' => $data['description'] ?? '',
                    'brand'       => $data['brand']       ?? '',
                    'tags'        => $data['tags']        ?? '',
                    'size'        => $data['size']        ?? '',
                    'size2'       => $data['size2']       ?? '',
                    'size3'       => $data['size3']       ?? '',
                    'size4'       => $data['size4']       ?? '',
                    'source_url'  => $data['source_url']  ?? '',
                ];
            },
        ];
        return $adapters;
    }

    /** ---------------- Syveco helpers ---------------- */
    private function http_get($url) {
        $resp = wp_remote_get($url, [
            'timeout'=>30,
            'headers'=>[
                'User-Agent'=>'GV-Enricher/1.0 (+https://geminivalve.nl)',
                'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);
        if (is_wp_error($resp)) return '';
        if (wp_remote_retrieve_response_code($resp) !== 200) return '';
        return wp_remote_retrieve_body($resp);
    }
    private function parse_syveco_first_result_url($html) {
        if (!$html) return '';
        if (preg_match('#<a[^>]+class="[^"]*product-item-link[^"]*"[^>]+href="([^"]+)"#i', $html, $m)) {
            return esc_url_raw(html_entity_decode($m[1]));
        }
        if (preg_match('#<li[^>]*class="[^"]*product-item[^"]*"[^>]*>.*?<a[^>]+href="([^"]+)"#is', $html, $m)) {
            return esc_url_raw(html_entity_decode($m[1]));
        }
        return '';
    }
    private function parse_syveco_product_page($html) {
        $out = ['model'=>'','description'=>'','brand'=>'','tags'=>'','size'=>'','size2'=>'','size3'=>'','size4'=>''];
        if (!$html) return $out;

        if (preg_match('#<div[^>]+class="product[^"]*attribute[^"]*description[^"]*"[^>]*>(.*?)</div>#is', $html, $m)) {
            $desc = trim(wp_strip_all_tags($m[1]));
            if ($desc) $out['description'] = $desc;
        }
        $attrs = [];
        if (preg_match_all('#<tr[^>]*>\s*<th[^>]*>(.*?)</th>\s*<td[^>]*>(.*?)</td>\s*</tr>#is', $html, $rows)) {
            foreach ($rows[1] as $i=>$k) {
                $kk = strtoupper(trim(wp_strip_all_tags($k)));
                $vv = trim(wp_strip_all_tags($rows[2][$i]));
                if ($kk) $attrs[$kk]=$vv;
            }
        }
        foreach (['MODEL','REFERENCE','SKU'] as $k) {
            if (!$out['model'] && !empty($attrs[$k])) $out['model'] = $attrs[$k];
        }
        if (!$out['brand'] && !empty($attrs['BRAND'])) $out['brand'] = $attrs['BRAND'];
        foreach (['SIZE','DIAMETER','DN','THREAD','CONNECTION'] as $k) {
            if (!empty($attrs[$k])) {
                foreach (['size','size2','size3','size4'] as $slot) {
                    if ($out[$slot]==='') { $out[$slot]=$attrs[$k]; break; }
                }
            }
        }
        if (preg_match_all('#<li[^>]*class="item"[^>]*>\s*<a[^>]*>(.*?)</a>#is', $html, $mm)) {
            $crumbs = array_unique(array_map(function($x){ return sanitize_text_field(trim(wp_strip_all_tags($x))); }, $mm[1]));
            $crumbs = array_filter($crumbs, fn($x)=>$x && strlen($x)<=32);
            if ($crumbs) $out['tags'] = implode(', ', $crumbs);
        }
        if (!$out['brand'] && preg_match('#<meta[^>]+property="og:brand"[^>]+content="([^"]+)"#i', $html, $m)) {
            $out['brand'] = sanitize_text_field($m[1]);
        }
        return $out;
    }
    private function syveco_heuristics($ref, array &$out) {
        $R = strtoupper($ref);
        $endsWithG = substr($R, -1) === 'G';
        if (preg_match('/^00[12]\d{3}[GN]$/', $R)) {
            $finish = $endsWithG ? 'Galvanised' : 'Black';
            if (preg_match('/^001/', $R)) {
                $out['brand']       = $out['brand'] ?: 'AFY';
                $out['model']       = $out['model'] ?: 'AFY 1 — 90° long sweep bend MF';
                $out['description'] = $out['description'] ?: "AFY 1, 90° long sweep bend, male/female, {$finish}, BSP";
                $out['tags']        = $out['tags'] ?: 'malleable cast iron, 90° elbow, long sweep, MF, BSP, '.strtolower($finish);
                $out['size2']       = $out['size2'] ?: 'MF';
                $out['size3']       = $out['size3'] ?: 'BSP';
            } elseif (preg_match('/^002/', $R)) {
                $out['brand']       = $out['brand'] ?: 'AFY';
                $out['model']       = $out['model'] ?: 'AFY 2 — 90° long sweep bend FF';
                $out['description'] = $out['description'] ?: "AFY 2, 90° long sweep bend, female/female, {$finish}, BSP";
                $out['tags']        = $out['tags'] ?: 'malleable cast iron, 90° elbow, long sweep, FF, BSP, '.strtolower($finish);
                $out['size2']       = $out['size2'] ?: 'FF';
                $out['size3']       = $out['size3'] ?: 'BSP';
            }
        }
        if (!$out['brand'] && preg_match('/(SPID[\'’]?O|ECOP|JETINOX|JE|JC)\b/i', $R)) {
            $out['brand'] = 'SPID\'O';
        }
    }

    /** ---------------- Inline CSS/JS ---------------- */
    public function inline_css() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'woocommerce_page_gv-supplier-enrichment') return;
        ?>
        <style>
            #gv-log { font:12px/1.45 Consolas, Menlo, Monaco, monospace; background:#111; color:#9f9; padding:10px; }
            #gv-log .err { color:#f99; }
            #gv-log .ok  { color:#9f9; }
            #btn-dl-fail[disabled], #btn-dl-preview[disabled] { opacity:.5; cursor:not-allowed; }
        </style>
        <?php
    }

    public function inline_js() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'woocommerce_page_gv-supplier-enrichment') return;
        $nonce = wp_create_nonce(self::NONCE);
        $ajax  = admin_url('admin-ajax.php');
        ?>
        <script>
        (function($){
            let running=false, state=null, timer=null;
            let failures=[], previews=[];

            function log(line, cls){
                const $log = $('#gv-log');
                const span = $('<span>').text(line+(line.endsWith('\n')?'':'\n'));
                if (cls) span.addClass(cls);
                $log.append(span);
                $log.scrollTop($log[0].scrollHeight);
            }
            function setStatus(s){ $('#gv-status').text(s); }
            function setProgress(p){ $('#gv-bar').val(p); }

            function nextStep(){
                if (!running || !state) return;
                const batch = parseInt($('#batch_size').val()||'20',10);
                $.post('<?php echo esc_js($ajax); ?>', {
                    action: 'gv_enrich_step',
                    nonce:  '<?php echo esc_js($nonce); ?>',
                    state:  JSON.stringify(state),
                    batch_size: batch
                }).done(function(resp){
                    if (!resp.success){ setStatus('Error'); log('ERROR: '+(resp.data&&resp.data.message||'Unknown'),'err'); running=false; return; }
                    state = resp.data.state;
                    setProgress(resp.data.progress);
                    setStatus('Progress: '+resp.data.progress+'%');

                    (resp.data.tick||[]).forEach(function(r){
                        if (r.ok){
                            const flag = r.dry ? 'DRY-RUN' : 'SAVED';
                            log('✓ ['+r.product_id+'] '+flag+' '+(r.updated||[]).join(', ')+(r.source?' — '+r.source:''),'ok');
                            previews.push({
                                product_id: r.product_id,
                                ref: (r.ref||''),
                                source: (r.source||''),
                                updated_keys: (r.updated||[]).join('|'),
                                changes_json: JSON.stringify(r.changes||{})
                            });
                        } else {
                            log('✗ ['+r.product_id+'] '+(r.ref?('ref='+r.ref+' '):'')+r.error,'err');
                            failures.push({product_id:r.product_id, ref:r.ref||'', error:r.error||''});
                        }
                    });

                    if (!state.queue || state.queue.length===0){
                        running=false;
                        $('#gv-stop').prop('disabled',true);
                        setStatus('Done');
                        if ($('#csv_fail').is(':checked') && failures.length) $('#btn-dl-fail').prop('disabled',false);
                        if ($('#csv_preview').is(':checked') && previews.length) $('#btn-dl-preview').prop('disabled',false);
                    } else {
                        setTimeout(nextStep, parseInt($('#rate_ms').val()||'800',10));
                    }
                }).fail(function(){ setStatus('AJAX error'); running=false; });
            }

            function start(){
                if (running) return;
                failures=[]; previews=[];
                $('#gv-progress').show();
                $('#gv-log').text('');
                $('#btn-dl-fail,#btn-dl-preview').prop('disabled', true);
                setStatus('Preparing…'); setProgress(0);

                $.post('<?php echo esc_js($ajax); ?>', {
                    action: 'gv_enrich_start',
                    nonce:  '<?php echo esc_js($nonce); ?>',
                    adapter: $('#adapter').val(),
                    supplier_id: $('#supplier_id').val(), // dropdown (numeric ID or empty)
                    sku_list: $('#sku_list').val(),
                    batch_size: $('#batch_size').val(),
                    rate_ms: $('#rate_ms').val(),
                    webhook_url: $('#webhook').val(),
                    dry_run: $('#dry_run').is(':checked') ? '1' : '0'
                }).done(function(resp){
                    if (!resp.success){ setStatus('Error'); log('ERROR: '+(resp.data&&resp.data.message||'Unknown'),'err'); return; }
                    state = resp.data.state;
                    setStatus('Queued '+resp.data.total+' product(s).');
                    running=true;
                    $('#gv-stop').prop('disabled', false);
                    nextStep();
                }).fail(function(){ setStatus('AJAX error'); });
            }

            function stop(){ running=false; if (timer) clearTimeout(timer); $('#gv-stop').prop('disabled', true); setStatus('Stopped'); }

            function downloadCSV(rows, filename){
                if (!rows || !rows.length) return;
                const header = Object.keys(rows[0]);
                const esc = v => ('"'+String(v).replaceAll('"','""')+'"');
                const lines = [header.map(esc).join(',')].concat(rows.map(r=>header.map(k=>esc(r[k]??'')).join(',')));
                const blob = new Blob([lines.join('\r\n')], {type:'text/csv;charset=utf-8;'});
                const url  = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url; a.download = filename; document.body.appendChild(a); a.click(); a.remove();
                setTimeout(()=>URL.revokeObjectURL(url), 500);
            }

            $(document).on('click','#gv-start', start);
            $(document).on('click','#gv-stop',  stop);
            $(document).on('click','#btn-dl-fail', function(){ downloadCSV(failures, 'gv_enrichment_failures.csv'); });
            $(document).on('click','#btn-dl-preview', function(){ downloadCSV(previews, 'gv_enrichment_preview.csv'); });
        })(jQuery);
        </script>
        <?php
    }
}
}
new GV_Supplier_Enrichment_Snippet();

/** Register adapters via filter in other snippets if needed:
add_filter('gv_supplier_adapters', function($adapters){
    $adapters['my_supplier'] = ['label'=>'My Supplier','callback'=>function($args){ return [...]; }];
    return $adapters;
}, 1);
*/




/* ============================================================
 * ADD-ON: Supplier-aware, dynamic enrichment (AI/API)
 * ============================================================ */

/** ---------- 0) OpenAI helper (reuses your global key) ---------- */
if (!function_exists('gve_openai_chat_json')) {
    function gve_openai_chat_json(array $args) : ?array {
        // If your finder helper exists, prefer it
        if (function_exists('gvpf_openai_chat_json')) {
            return gvpf_openai_chat_json($args);
        }
        $api_key = trim((string)get_option('gv_openai_api_key',''));
        if ($api_key==='') return null;
        $org_id  = trim((string)get_option('gv_openai_org_id',''));
        $model   = $args['model'] ?? 'gpt-4o-mini';

        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $payload = [
            'model'       => $model,
            'temperature' => $args['temperature'] ?? 0.2,
            'messages'    => [
                [ 'role'=>'system', 'content'=>$args['system'] ?? 'Return only JSON.' ],
                [ 'role'=>'user',   'content'=>$args['user']   ?? '{}' ],
            ],
            'max_tokens'  => $args['max_tokens'] ?? 1200,
        ];
        $headers = [
            'Authorization' => 'Bearer '.$api_key,
            'Content-Type'  => 'application/json',
        ];
        if ($org_id) $headers['OpenAI-Organization'] = $org_id;

        $res = wp_remote_post($endpoint, ['headers'=>$headers,'timeout'=>30,'body'=>wp_json_encode($payload)]);
        if (is_wp_error($res)) return null;
        $code = wp_remote_retrieve_response_code($res);
        if ($code < 200 || $code >= 300) return null;

        $body = json_decode(wp_remote_retrieve_body($res), true);
        $txt  = isset($body['choices'][0]['message']['content']) ? trim($body['choices'][0]['message']['content']) : '';
        if ($txt==='' ) return null;
        if (preg_match('/\{.*\}/s', $txt, $m)) $txt = $m[0]; // strip prose if any
        $json = json_decode($txt, true);
        return is_array($json) ? $json : null;
    }
}

/** ---------- 1) Supplier settings (metabox) ---------- */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'gv_supplier_enrichment',
        __('Enrichment (AI/API)', 'gv'),
        function ($post) {
            wp_nonce_field('gv_supplier_enrichment_save','gv_supplier_enrichment_nonce');

            $get = function($k,$d=''){ $v=get_post_meta($post->ID,$k,true); return $v!==''?$v:$d; };

            $method     = $get('_gv_sup_method','html_ai');      // html_ai | json_webhook
            $search_tpl = $get('_gv_sup_search_url_tpl','https://example.com/search?q={q}');
            $prod_tpl   = $get('_gv_sup_product_url_tpl','');     // optional direct template
            $link_rx    = $get('_gv_sup_result_link_regex','#<a[^>]+class="[^"]*product-item-link[^"]*"[^>]+href="([^"]+)"#i');
            $headers_js = $get('_gv_sup_headers_json','{"User-Agent":"GV-Enricher/1.0"}');
            $ai_hints   = $get('_gv_sup_ai_hints','');
            $ai_model   = $get('_gv_sup_ai_model','gpt-4o-mini');
            $size_keys  = $get('_gv_sup_ai_size_keys','SIZE,DIAMETER,DN,THREAD,CONNECTION');
            $webhook    = $get('_gv_sup_webhook_url','');

            echo '<table class="form-table"><tbody>';

            echo '<tr><th>'.esc_html__('Method','gv').'</th><td>';
            echo '<select name="_gv_sup_method">';
            foreach (['html_ai'=>'HTML + ChatGPT','json_webhook'=>'JSON Webhook'] as $k=>$lbl) {
                echo '<option value="'.$k.'" '.selected($method,$k,false).'>'.esc_html($lbl).'</option>';
            }
            echo '</select> <span class="description">'.esc_html__('Pick how this supplier should be enriched.','gv').'</span></td></tr>';

            echo '<tr><th>'.esc_html__('Search URL template','gv').'</th><td>';
            echo '<input type="url" class="regular-text" name="_gv_sup_search_url_tpl" value="'.esc_attr($search_tpl).'" />';
            echo '<p class="description">'.esc_html__('Use {q} where the article/reference should go.','gv').'</p></td></tr>';

            echo '<tr><th>'.esc_html__('Product URL template (optional)','gv').'</th><td>';
            echo '<input type="url" class="regular-text" name="_gv_sup_product_url_tpl" value="'.esc_attr($prod_tpl).'" />';
            echo '<p class="description">'.esc_html__('If set, we will try this first (e.g., https://site/product/{q}).','gv').'</p></td></tr>';

            echo '<tr><th>'.esc_html__('Result link regex','gv').'</th><td>';
            echo '<input type="text" class="regular-text" name="_gv_sup_result_link_regex" value="'.esc_attr($link_rx).'" />';
            echo '<p class="description">'.esc_html__('Regex with one capture group for the first product link.','gv').'</p></td></tr>';

            echo '<tr><th>'.esc_html__('Extra HTTP headers (JSON)','gv').'</th><td>';
            echo '<textarea name="_gv_sup_headers_json" rows="3" class="large-text">'.esc_textarea($headers_js).'</textarea>';
            echo '</td></tr>';

            echo '<tr><th>'.esc_html__('AI model','gv').'</th><td>';
            echo '<input type="text" name="_gv_sup_ai_model" value="'.esc_attr($ai_model).'" />';
            echo ' <span class="description">'.esc_html__('e.g., gpt-4o-mini','gv').'</span></td></tr>';

            echo '<tr><th>'.esc_html__('AI instructions / hints','gv').'</th><td>';
            echo '<textarea name="_gv_sup_ai_hints" rows="4" class="large-text">'.esc_textarea($ai_hints).'</textarea>';
            echo '<p class="description">'.esc_html__('Anything specific to this supplier’s page layout, field names, or language.','gv').'</p></td></tr>';

            echo '<tr><th>'.esc_html__('AI size keys (CSV)','gv').'</th><td>';
            echo '<input type="text" class="regular-text" name="_gv_sup_ai_size_keys" value="'.esc_attr($size_keys).'" />';
            echo '<p class="description">'.esc_html__('Attribute names likely to represent sizes; help AI map into Size..Size4.','gv').'</p></td></tr>';

            echo '<tr><th>'.esc_html__('Webhook URL (if JSON Webhook)','gv').'</th><td>';
            echo '<input type="url" class="regular-text" name="_gv_sup_webhook_url" value="'.esc_attr($webhook).'" />';
            echo '</td></tr>';

            echo '</tbody></table>';
        },
        'gv_supplier',
        'normal',
        'default'
    );
});

add_action('save_post_gv_supplier', function ($post_id) {
    if (!isset($_POST['gv_supplier_enrichment_nonce']) || !wp_verify_nonce($_POST['gv_supplier_enrichment_nonce'],'gv_supplier_enrichment_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post',$post_id)) return;

	
    $fields = [
        '_gv_sup_method'           => 'sanitize_text_field',
        '_gv_sup_search_url_tpl'   => 'esc_url_raw',
        '_gv_sup_product_url_tpl'  => 'esc_url_raw',
        '_gv_sup_result_link_regex'=> 'wp_kses_post',
        '_gv_sup_headers_json'     => function($v){ return wp_kses_post($v); },
        '_gv_sup_ai_hints'         => function($v){ return wp_kses_post($v); },
        '_gv_sup_ai_model'         => 'sanitize_text_field',
        '_gv_sup_ai_size_keys'     => 'sanitize_text_field',
        '_gv_sup_webhook_url'      => 'esc_url_raw',
    ];
    foreach ($fields as $k=>$cb) {
        if (!isset($_POST[$k])) continue;
        $raw = wp_unslash($_POST[$k]);
        $val = is_callable($cb) ? call_user_func($cb,$raw) : sanitize_text_field($raw);
        update_post_meta($post_id, $k, $val);
    }
});

/** ---------- 2) Utility HTTP + HTML helpers ---------- */
if (!function_exists('gve_http_get')) {
    function gve_http_get($url, $headers_json='') {
        $headers = ['User-Agent'=>'GV-Enricher/1.1 (+https://geminivalve.nl)','Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'];
        if ($headers_json) {
            $extra = json_decode($headers_json, true);
            if (is_array($extra)) $headers = array_merge($headers, $extra);
        }
        $res = wp_remote_get($url, ['timeout'=>30,'headers'=>$headers]);
        if (is_wp_error($res)) return '';
        if (wp_remote_retrieve_response_code($res)!==200) return '';
        return wp_remote_retrieve_body($res);
    }
}
if (!function_exists('gve_compact_html')) {
    function gve_compact_html($html, $max=18000) {
        if (!$html) return '';
        $html = preg_replace('#<script[\s\S]*?</script>#i', ' ', $html);
        $html = preg_replace('#<style[\s\S]*?</style>#i', ' ', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        if (strlen($html) > $max) $html = substr($html, 0, $max);
        return $html;
    }
}

/** ---------- 3) The dynamic adapter: Auto (per Supplier settings) ---------- */
add_filter('gv_supplier_adapters', function($adapters){
    $adapters['auto_by_supplier'] = [
        'label'    => 'Auto (per Supplier settings)',
        'callback' => function($args){
            $supplier_id = (int)($args['supplier_id'] ?? 0);
            $ref         = sanitize_text_field($args['ref'] ?? '');
            if ($supplier_id <= 0) throw new \Exception('No supplier selected.');
            if ($ref === '')        throw new \Exception('No reference/SKU to look up.');

            // Load supplier config
            $method     = get_post_meta($supplier_id, '_gv_sup_method', true) ?: 'html_ai';
            $search_tpl = get_post_meta($supplier_id, '_gv_sup_search_url_tpl', true);
            $prod_tpl   = get_post_meta($supplier_id, '_gv_sup_product_url_tpl', true);
            $link_rx    = get_post_meta($supplier_id, '_gv_sup_result_link_regex', true);
            $headers_js = get_post_meta($supplier_id, '_gv_sup_headers_json', true);
            $ai_hints   = get_post_meta($supplier_id, '_gv_sup_ai_hints', true);
            $ai_model   = get_post_meta($supplier_id, '_gv_sup_ai_model', true) ?: 'gpt-4o-mini';
            $size_keys  = get_post_meta($supplier_id, '_gv_sup_ai_size_keys', true) ?: 'SIZE,DIAMETER,DN,THREAD,CONNECTION';
            $webhook    = get_post_meta($supplier_id, '_gv_sup_webhook_url', true);

            // Path A: JSON Webhook defined at supplier level
            if ($method === 'json_webhook') {
                if (!$webhook) throw new \Exception('Supplier has no webhook URL configured.');
                $resp = wp_remote_post($webhook, [
                    'timeout'=>30,
                    'headers'=>['Content-Type'=>'application/json'],
                    'body'=>wp_json_encode(['sku'=>$ref,'supplier_id'=>$supplier_id,'product_id'=>(int)($args['product_id']??0)]),
                ]);
                if (is_wp_error($resp)) throw new \Exception($resp->get_error_message());
                $code = wp_remote_retrieve_response_code($resp);
                $body = json_decode(wp_remote_retrieve_body($resp), true);
                if ($code<200 || $code>=300 || !is_array($body)) throw new \Exception('Invalid webhook response');
                return [
                    'model'       => $body['model']       ?? '',
                    'description' => $body['description'] ?? '',
                    'brand'       => $body['brand']       ?? '',
                    'tags'        => $body['tags']        ?? '',
                    'size'        => $body['size']        ?? '',
                    'size2'       => $body['size2']       ?? '',
                    'size3'       => $body['size3']       ?? '',
                    'size4'       => $body['size4']       ?? '',
                    'source_url'  => $body['source_url']  ?? '',
                ];
            }

            // Path B: HTML + ChatGPT (generic, supplier-configured)
            // 1) Build candidate URLs
            $url_from_tpl = ($prod_tpl && strpos($prod_tpl,'{q}')!==false) ? str_replace('{q}', rawurlencode($ref), $prod_tpl) : '';
            $search_url   = ($search_tpl && strpos($search_tpl,'{q}')!==false) ? str_replace('{q}', rawurlencode($ref), $search_tpl) : '';

            $product_url = '';
            if ($url_from_tpl) {
                $product_url = $url_from_tpl;
            } elseif ($search_url) {
                $shtml = gve_http_get($search_url, $headers_js);
                if ($shtml) {
                    $rx = $link_rx ?: '#<a[^>]+href="([^"]+)"[^>]*class="[^"]*(?:product|result)[^"]*"#i';
                    if (preg_match($rx, $shtml, $m)) {
                        $product_url = esc_url_raw(html_entity_decode($m[1]));
                        if ($product_url && !parse_url($product_url, PHP_URL_SCHEME)) {
                            // Make relative URL absolute if needed
                            $p = wp_parse_url($search_url);
                            $base = $p['scheme'].'://'.$p['host'];
                            if (!empty($p['port'])) $base .= ':'.$p['port'];
                            if ($product_url && $product_url[0] === '/') $product_url = $base.$product_url;
                        }
                    }
                }
            }
            $source_url = $product_url ?: $search_url;

            // 2) Fetch product page
            $phtml = $product_url ? gve_http_get($product_url, $headers_js) : '';
            if (!$phtml && !$search_url) throw new \Exception('No URL could be built (configure search or product template).');

            // 3) Prepare AI prompt
            $compact = gve_compact_html($phtml ?: gve_http_get($search_url, $headers_js));
            if (!$compact) throw new \Exception('No HTML fetched to parse.');
            $supplier_name = get_the_title($supplier_id) ?: 'Supplier';
            $size_keys_arr = array_values(array_filter(array_map('trim', explode(',',$size_keys))));
            $size_keys_arr = array_slice($size_keys_arr, 0, 12);

            $system = "You extract product facts from messy HTML for industrial supplies. 
Return ONLY strict JSON with keys:
model (string), description (string), brand (string), tags (array of up to 8 short tags), 
size (string), size2 (string), size3 (string), size4 (string), 
source_url (string).
Rules: 
- Prefer fields labelled with ".implode('/', $size_keys_arr)." for sizes; map the most relevant four values into size..size4.
- If multiple size-like values exist (e.g., DN and Thread), put the most buyer-relevant first.
- Keep units as shown (e.g., DN25, 1/2\", BSP).
- Use short, human-readable model if present; otherwise reference/SKU.
- Tags: use breadcrumbs or category labels; avoid stopwords.";

            if ($ai_hints) $system .= "\nSupplier hints:\n".$ai_hints;

            $user = wp_json_encode([
                'supplier'  => $supplier_name,
                'reference' => $ref,
                'page_html' => $compact,
                'source_url'=> $source_url,
            ], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

            $json = gve_openai_chat_json([
                'system'     => $system,
                'user'       => $user,
                'model'      => $ai_model,
                'temperature'=> 0.1,
                'max_tokens' => 900,
            ]);
            if (!is_array($json)) $json = [];

            // 4) Normalize + fallback
            $out = [
                'model'       => (string)($json['model'] ?? ''),
                'description' => (string)($json['description'] ?? ''),
                'brand'       => (string)($json['brand'] ?? ''),
                'tags'        => $json['tags'] ?? '',
                'size'        => (string)($json['size'] ?? ''),
                'size2'       => (string)($json['size2'] ?? ''),
                'size3'       => (string)($json['size3'] ?? ''),
                'size4'       => (string)($json['size4'] ?? ''),
                'source_url'  => (string)($json['source_url'] ?? $source_url),
            ];
            // Ensure tags CSV if array
            if (is_array($out['tags'])) $out['tags'] = implode(', ', array_slice(array_filter(array_map('sanitize_text_field',$out['tags'])),0,8));

            return $out;
        },
    ];
    return $adapters;
}, 10, 1);


/* ============ FIX: reliably save Supplier Enrichment fields ============ */

/** 1) Register meta keys for gv_supplier (REST-safe, single string) */
add_action('init', function () {
    if ( ! post_type_exists('gv_supplier') ) return;

    $keys = [
        '_gv_sup_method',
        '_gv_sup_search_url_tpl',
        '_gv_sup_product_url_tpl',
        '_gv_sup_result_link_regex',
        '_gv_sup_headers_json',
        '_gv_sup_ai_hints',
        '_gv_sup_ai_model',
        '_gv_sup_ai_size_keys',
        '_gv_sup_webhook_url',
    ];
    foreach ($keys as $k) {
        register_post_meta('gv_supplier', $k, [
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => false, // metabox uses classic submit
            'auth_callback' => function() { return current_user_can('manage_woocommerce'); },
        ]);
    }
}, 9);

/** 2) Save handler (robust + non-destructive sanitization) */
add_action('save_post_gv_supplier', function ($post_id, $post, $update) {
    // Bail on autosave/revision
    if ( wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) ) return;
    if ( ! current_user_can('edit_post', $post_id) ) return;

    // Accept either: full edit form (nonce present) or REST/editor update (no nonce),
    // but never on GET requests.
    $is_post = (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST');
    if ( ! $is_post ) return;

    $nonce_ok = isset($_POST['gv_supplier_enrichment_nonce'])
        && wp_verify_nonce($_POST['gv_supplier_enrichment_nonce'], 'gv_supplier_enrichment_save');

    // If it’s not the edit screen submit, we still allow saving when fields are present
    // (e.g., some editors/plugins submit without loading metabox HTML).
    if ( ! $nonce_ok ) {
        $has_any_field = false;
        foreach (['_gv_sup_method','_gv_sup_search_url_tpl','_gv_sup_product_url_tpl','_gv_sup_result_link_regex',
                  '_gv_sup_headers_json','_gv_sup_ai_hints','_gv_sup_ai_model','_gv_sup_ai_size_keys','_gv_sup_webhook_url'] as $k) {
            if (array_key_exists($k, $_POST)) { $has_any_field = true; break; }
        }
        if ( ! $has_any_field ) return;
    }

    // Gentle sanitizers (don’t break JSON/regex)
    $get = function($key, $type='text') {
        if (!array_key_exists($key, $_POST)) return null;
        $raw = wp_unslash($_POST[$key]);

        switch ($type) {
            case 'url':
                return esc_url_raw($raw);
            case 'json':
                // Keep as-is but trim; optionally validate JSON if you like
                $val = is_string($raw) ? trim($raw) : '';
                return $val;
            case 'textarea':
                // Keep line breaks; strip tags
                return sanitize_textarea_field($raw);
            default:
                return sanitize_text_field($raw);
        }
    };

    $map = [
        '_gv_sup_method'            => ['type'=>'text'],
        '_gv_sup_search_url_tpl'    => ['type'=>'url'],
        '_gv_sup_product_url_tpl'   => ['type'=>'url'],
        '_gv_sup_result_link_regex' => ['type'=>'text'],   // allow regex characters
        '_gv_sup_headers_json'      => ['type'=>'json'],   // don’t kses() JSON
        '_gv_sup_ai_hints'          => ['type'=>'textarea'],
        '_gv_sup_ai_model'          => ['type'=>'text'],
        '_gv_sup_ai_size_keys'      => ['type'=>'text'],
        '_gv_sup_webhook_url'       => ['type'=>'url'],
    ];

    foreach ($map as $key => $cfg) {
        $val = $get($key, $cfg['type']);
        if ($val === null) continue;            // field not present in this submit
        update_post_meta($post_id, $key, $val); // store (single)
    }

    // (Optional) debug log — toggle off by default
    if ( false && function_exists('error_log') ) {
        error_log('[gv_supplier save] post='.$post_id.' saved enrichment fields.');
    }
}, 10, 3);


// ===== Snippet #131: Products - make title product link in PDF documents =====
// Scope: global | Priority: 10

/**
 * PDF Invoices & Packing Slips (WP Overnight) — make the item name clickable (all docs).
 * Works with Simple / Premium templates and Customizer layouts.
 */
add_filter('wpo_wcpdf_order_item_data', function ( $data, $order, $document_type ) {
    // $data contains name, quantity, totals AND a 'product' object when applicable.
    if ( empty( $data['product'] ) || ! is_a( $data['product'], 'WC_Product' ) ) {
        return $data; // fees, shipping, or custom lines without a product
    }

    $product = $data['product'];

    // Get the correct permalink (parent for variations is fine — get_permalink() handles it)
    $url = method_exists( $product, 'get_permalink' ) ? $product->get_permalink() : get_permalink( $product->get_id() );
    if ( ! $url || empty( $data['name'] ) ) {
        return $data;
    }

    // Make the name clickable; absolute URLs remain clickable in Dompdf/mPDF
    $data['name'] = sprintf(
        '<a href="%s">%s</a>',
        esc_url( $url ),
        wp_kses_post( $data['name'] ) // keep any existing formatting from bundles, etc.
    );

    return $data;
}, 20, 3 );

add_action('wpo_wcpdf_after_item_meta', function( $document_type, $item, $order ) {
    if ( ! is_callable( [ $item, 'get_product' ] ) ) return;
    $product = $item->get_product();
    if ( ! $product ) return;

    $url = method_exists( $product, 'get_permalink' ) ? $product->get_permalink() : get_permalink( $product->get_id() );
    if ( ! $url ) return;

    echo '<div style="font-size:9pt; margin-top:2px; line-height:1.2;">'
       . '<a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a>'
       . '</div>';
}, 10, 3 );


// ===== Snippet #132: Products - Add HS Code and Country of Origin to products =====
// Scope: admin | Priority: 10

/**
 * Plugin Name: GV - WooCommerce Product Shipping Fields
 * Description: Adds "HS Code" and "Country of Origin" fields to the product Shipping tab.
 * Author: Gemini Valve
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'woocommerce_product_options_shipping', function () {
    echo '<div class="options_group">';

    // HS Code (text)
    woocommerce_wp_text_input( array(
        'id'                => '_gv_hs_code',
        'label'             => __( 'HS Code', 'gv' ),
        'desc_tip'          => true,
        'description'       => __( 'Harmonized System code (digits only, e.g., 848180 for valves).', 'gv' ),
        'type'              => 'text',
        'placeholder'       => 'e.g. 848180',
        'class'             => 'short',
        'custom_attributes' => array(
            'pattern' => '[0-9]*',
            'inputmode' => 'numeric',
        ),
    ) );

    // Country of Origin (dropdown from WooCommerce countries)
    $countries = function_exists( 'WC' ) ? WC()->countries->get_countries() : array();
    woocommerce_wp_select( array(
        'id'          => '_gv_country_of_origin',
        'label'       => __( 'Country of Origin', 'gv' ),
        'desc_tip'    => true,
        'description' => __( 'Select the manufacturing country of origin.', 'gv' ),
        'options'     => array( '' => __( '— Select country —', 'gv' ) ) + $countries,
        'class'       => 'wc-enhanced-select',
    ) );

    echo '</div>';
} );

// Load saved values into the fields
add_action( 'woocommerce_product_options_shipping', function () {
    global $post;

    $hs   = get_post_meta( $post->ID, '_gv_hs_code', true );
    $orig = get_post_meta( $post->ID, '_gv_country_of_origin', true );

    // Pre-fill fields (WooCommerce helpers read from $_POST on submit only, so we echo script to set values in admin)
    ?>
    <script type="text/javascript">
      (function($){
        $('#_gv_hs_code').val(<?php echo json_encode( $hs ); ?>);
        $('#_gv_country_of_origin').val(<?php echo json_encode( $orig ); ?>).trigger('change');
      })(jQuery);
    </script>
    <?php
}, 99 );

// Save values on product save
add_action( 'woocommerce_admin_process_product_object', function ( $product ) {
    $hs   = isset($_POST['_gv_hs_code']) ? preg_replace('/\D+/', '', wp_unslash($_POST['_gv_hs_code'])) : '';
    $orig = isset($_POST['_gv_country_of_origin']) ? sanitize_text_field( wp_unslash($_POST['_gv_country_of_origin']) ) : '';

    $product->update_meta_data( '_gv_hs_code', $hs );
    $product->update_meta_data( '_gv_country_of_origin', $orig );
} );


// ===== Snippet #133: Products - Bulk inject products from supplier =====
// Scope: admin | Priority: 10


/**
 * Plugin Name: GV - Bulk Edit: Inject Products (under Bulk Edit menu)
 * Description: Adds Bulk Edit → Inject products. Paste a table to create/update products. Resolves Image URLs to _thumbnail_id.
 * Author: Gemini Valve
 * Version: 1.0.1
 */

if ( ! defined('ABSPATH') ) exit;

/** ===== Helpers: find "Bulk Edit" parent slug (override via filter) ===== */
function gv_bi_get_bulk_parent_slug() {
    // Allow hard override if you know the slug already.
    $slug = apply_filters('gv_bi_bulk_parent_slug', '');
    if ( $slug ) return $slug;

    // Try to discover a top-level "Bulk Edit" menu item.
    global $menu;
    if ( is_array($menu) ) {
        foreach ( $menu as $m ) {
            // $m: [0]=name, [1]=cap, [2]=slug, [3]=page_title, [4]=classes, [5]=icon, [6]=position
            $name = isset($m[0]) ? wp_strip_all_tags($m[0]) : '';
            if ( stripos($name, 'Bulk Edit') !== false ) {
                return $m[2]; // parent slug
            }
        }
    }
    return ''; // not found
}

function gv_bi_to_percent_fraction($v): float {
    // Accepts "30", "30%", "0.30", "0,30"
    $s = trim((string)$v);
    if ($s === '') return -1.0;
    $s = str_replace('%', '', $s);
    $s = gv_bi_to_decimal($s); // your decimal normalizer
    if ($s === '' || !is_numeric($s)) return -1.0;
    $f = (float)$s;
    // If user entered "30" treat as 30%
    if ($f > 1.0) $f = $f / 100.0;
    return $f; // 0.30 means 30%
}


/** ===== Admin menu ===== */
add_action('admin_menu', function () {
    $parent = gv_bi_get_bulk_parent_slug();

    if ( $parent ) {
        // Add under your “Bulk Edit” menu
        add_submenu_page(
            $parent,
            __('Bulk Edit — Inject products', 'gv'),
            __('Inject products', 'gv'),
            'manage_woocommerce',
            'gv-bulk-inject',
            'gv_bulk_inject_render_page'
        );
    } else {
        // Fallback under Products (and show a notice)
        add_submenu_page(
            'edit.php?post_type=product',
            __('Bulk Edit — Inject products', 'gv'),
            __('Inject products', 'gv'),
            'manage_woocommerce',
            'gv-bulk-inject',
            'gv_bulk_inject_render_page'
        );

        add_action('admin_notices', function () {
            if ( isset($_GET['page']) && $_GET['page'] === 'gv-bulk-inject' ) {
                echo '<div class="notice notice-warning"><p>'
                   . esc_html__('Heads up: Couldn’t find a “Bulk Edit” top-level menu. Placed this page under Products → Inject products instead. You can force the parent via filter gv_bi_bulk_parent_slug.', 'gv')
                   . '</p></div>';
            }
        });
    }
}, 25);

/** ===== Page UI & processor (unchanged) ===== */
function gv_bulk_inject_render_page() {
    if ( ! current_user_can('manage_woocommerce') ) { wp_die(__('No permissions', 'gv')); }

    $did_process = false;
    $results     = array();

    if ( isset($_POST['gv_bi_nonce']) && wp_verify_nonce($_POST['gv_bi_nonce'], 'gv_bi_run') ) {
        $raw              = wp_unslash($_POST['gv_bi_table'] ?? '');
        $status           = sanitize_text_field($_POST['gv_bi_status'] ?? 'draft');
        $update_existing  = ! empty($_POST['gv_bi_update']);
        $results          = gv_bi_process_table($raw, $status, $update_existing);
        $did_process      = true;
    }

    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Bulk Edit — Inject products', 'gv'); ?></h1>

      <?php if ( $did_process ) : ?>
        <div class="notice notice-success"><p>
          <?php
          printf(
            esc_html__('%d rows processed • %d created • %d updated • %d skipped.', 'gv'),
            intval($results['rows_total']),
            intval($results['created']),
            intval($results['updated']),
            intval($results['skipped'])
          );
          ?>
        </p></div>
      <?php endif; ?>

      <form method="post">
        <?php wp_nonce_field('gv_bi_run', 'gv_bi_nonce'); ?>

        <p><?php esc_html_e('Paste a table (TAB, semicolon, or comma delimited). First row must be headers.', 'gv'); ?></p>
        <p><strong><?php esc_html_e('Supported headers (case-insensitive):', 'gv'); ?></strong><br>
          <code>SKU, Description, Short Description, Long Description, Image, Datasheet, Supplier, Supplier SKU,Procurement price, Gencod, Country of origin, HS Code, Net Weight, DN size</code>

        </p>

        <textarea name="gv_bi_table" rows="14" style="width:100%;font-family:Menlo,Consolas,monospace;"></textarea>

        <p style="margin-top:10px;">
          <label><input type="checkbox" name="gv_bi_update" checked> <?php esc_html_e('Update existing products by SKU (create if not found)', 'gv'); ?></label>
        </p>
        <p>
          <label><?php esc_html_e('New product status:', 'gv'); ?>
            <select name="gv_bi_status">
              <option value="draft"><?php esc_html_e('draft', 'gv'); ?></option>
              <option value="publish"><?php esc_html_e('publish', 'gv'); ?></option>
              <option value="pending"><?php esc_html_e('pending', 'gv'); ?></option>
            </select>
          </label>
        </p>

        <p><button class="button button-primary" type="submit"><?php esc_html_e('Inject', 'gv'); ?></button></p>
      </form>

      <?php if ( $did_process ) : ?>
        <h2><?php esc_html_e('Log', 'gv'); ?></h2>
        <ol>
          <?php foreach ( $results['log'] as $line ) : ?>
            <li><?php echo esc_html($line); ?></li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
    </div>
    <?php
}

/* ---------- Processor & utilities (same as before) ---------- */
function gv_bi_process_table(string $raw, string $status, bool $update_existing): array {
    if ( ! class_exists('WC_Product_Simple') ) {
        return array('rows_total'=>0,'created'=>0,'updated'=>0,'skipped'=>0,'log'=>array('WooCommerce not active.'));
    }
    $rows  = gv_bi_parse_table($raw);
    $log   = array();
    $stats = array('rows_total'=>max(count($rows)-1,0),'created'=>0,'updated'=>0,'skipped'=>0,'log'=>&$log);
    if ( empty($rows) ) { $log[] = 'No rows detected.'; return $stats; }

    $header = array_map('trim', $rows[0]);
    $map    = gv_bi_header_map($header);

    for ( $i=1; $i<count($rows); $i++ ) {
        $r = gv_bi_row_to_assoc($rows[$i], $map);
		$sku    = trim($r['sku'] ?? '');
		$title  = trim($r['description'] ?? '');
		$short  = isset($r['short_desc']) ? wp_kses_post($r['short_desc']) : '';
		$long   = isset($r['long_desc'])  ? wp_kses_post($r['long_desc'])  : '';

        if ( $sku === '' || $title === '' ) { $stats['skipped']++; $log[] = "Row {$i}: missing SKU or Description — skipped."; continue; }

        $product_id = wc_get_product_id_by_sku($sku);

        if ( ! $product_id ) {
            $product_id = wp_insert_post(array(
				'post_type'    => 'product',
				'post_status'  => $status,
				'post_title'   => $title,
				'post_excerpt' => $short,      // Woo short description
				'post_content' => $long,       // Woo long description
			), true);

            if ( is_wp_error($product_id) ) { $stats['skipped']++; $log[] = "Row {$i} [SKU {$sku}]: create error — ".$product_id->get_error_message(); continue; }
            $created = true;
        } else {
            if ( ! $update_existing ) { $stats['skipped']++; $log[] = "Row {$i} [SKU {$sku}]: exists, updates disabled — skipped."; continue; }
            if ( $title !== '' ) { wp_update_post(array('ID'=>$product_id,'post_title'=>$title)); }
            $created = false;
        }

        $product = wc_get_product($product_id);
        if ( ! $product ) { $stats['skipped']++; $log[] = "Row {$i} [SKU {$sku}]: cannot load product after insert — skipped."; continue; }
        if ( $product->get_type() !== 'simple' ) { $product = new WC_Product_Simple($product_id); }

        try { $product->set_sku($sku); } catch ( Exception $e ) {}

        if ( ! empty($r['image']) ) {
            $thumb_id = gv_bi_resolve_attachment_id($r['image']);
            if ( $thumb_id ) { set_post_thumbnail($product_id, $thumb_id); }
            else { $log[] = "Row {$i} [SKU {$sku}]: could not resolve Image to attachment ID."; }
        }

        $meta_map = array(
            '_gv_datasheet_url'      => $r['datasheet']            ?? '',
            '_gv_proc_supplier_id'   => gv_bi_to_int($r['supplier'] ?? ''),
            '_gv_proc_supplier_sku'  => $r['supplier_sku']         ?? '',
            '_gv_proc_cost_price'    => gv_bi_to_decimal($r['procurement_price'] ?? ''),
            '_global_unique_id'      => $r['gencod']               ?? '',
            '_gv_country_of_origin'  => $r['country_of_origin']    ?? '',
            '_gv_hs_code'            => preg_replace('/\D+/', '', (string)($r['hs_code'] ?? '')),
            '_weight'                => gv_bi_to_decimal($r['net_weight'] ?? ''),
        );
        foreach ( $meta_map as $k => $v ) {
            if ( $v !== '' && $v !== null ) update_post_meta($product_id, $k, $v);
        }

        if ( ! empty($r['dn_size']) ) {
            $attrs = (array) $product->get_attributes();
            $attr  = new WC_Product_Attribute();
            $attr->set_id(0);
            $attr->set_name('DN size');
            $attr->set_options( array( (string) $r['dn_size'] ) );
            $attr->set_visible(true);
            $attr->set_variation(false);
            $attrs['dn_size_custom'] = $attr;
            $product->set_attributes($attrs);
        }
		
		// --- Pricing from supplier margin ---
			$cost_raw     = $r['procurement_price'] ?? '';
			$cost         = gv_bi_to_decimal($cost_raw);
			$supplier_id  = (int) gv_bi_to_int($r['supplier'] ?? '');
			$margin_raw   = ($supplier_id > 0) ? get_post_meta($supplier_id, '_gv_margin_percent', true) : '';
			$margin_frac  = gv_bi_to_percent_fraction($margin_raw);

			if ($cost !== '' && is_numeric($cost) && $supplier_id > 0 && $margin_frac >= 0 && $margin_frac < 0.99) {
				$cost_f  = (float)$cost;
				// Assume Woo prices are entered EX VAT; cost is EX VAT.
				$price   = $cost_f / (1.0 - $margin_frac);

				// Format with Woo decimals and set as regular price
				$price_f = wc_format_decimal($price, wc_get_price_decimals());

				// Only set if we actually computed something sane
				if ($price_f > 0) {
					if ( method_exists($product, 'set_regular_price') ) {
						$product->set_regular_price($price_f);
						// If no sale price is set, also set current price
						if ( ! $product->get_sale_price() ) {
							$product->set_price($price_f);
						}
					} else {
						// Fallback for very old Woo
						update_post_meta($product_id, '_regular_price', $price_f);
						if ( '' === get_post_meta($product_id, '_sale_price', true) ) {
							update_post_meta($product_id, '_price', $price_f);
						}
					}

					$log[] = "Row {$i} [SKU {$sku}]: price set from supplier {$supplier_id} margin {$margin_frac} (cost {$cost_f} → regular {$price_f}).";
				}
			} else {
				if ($supplier_id > 0) {
					$log[] = "Row {$i} [SKU {$sku}]: could not derive price (cost='{$cost_raw}', margin='{$margin_raw}').";
				} else {
					$log[] = "Row {$i} [SKU {$sku}]: no supplier id, skipped margin pricing.";
				}
			}

		

        $product->save();

        if ( $created ) { $stats['created']++;  $log[] = "Row {$i} [SKU {$sku}]: created (ID {$product_id})."; }
        else            { $stats['updated']++;  $log[] = "Row {$i} [SKU {$sku}]: updated (ID {$product_id})."; }
    }
    return $stats;
}

function gv_bi_resolve_attachment_id(string $val): int {
    $val = trim($val);
    if ( $val === '' ) return 0;
    if ( ctype_digit($val) ) return absint($val);
    $id = attachment_url_to_postid($val);
    if ( $id ) return $id;
    $path = wp_parse_url($val, PHP_URL_PATH);
    if ( $path ) {
        $slug = trim(basename(untrailingslashit($path)));
        $p = get_page_by_path($slug, OBJECT, 'attachment');
        if ( $p && ! is_wp_error($p) ) return (int) $p->ID;
    }
    return 0;
}

function gv_bi_parse_table(string $raw): array {
    $raw = trim($raw);
    if ( $raw === '' ) return array();
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $first = $lines[0] ?? '';
    $delim = "\t";
    if ( substr_count($first, "\t") ) $delim = "\t";
    elseif ( substr_count($first, ';') ) $delim = ';';
    elseif ( substr_count($first, ',') ) $delim = ',';
    $rows = array();
    foreach ( $lines as $ln ) {
        $parts = array_map('trim', $delim === "\t" ? explode("\t", $ln) : str_getcsv($ln, $delim) );
        $rows[] = $parts;
    }
    return $rows;
}

function gv_bi_header_map(array $header): array {
    $norm = array();
    foreach ($header as $i => $h) {
        $k = strtolower(trim($h));
        $k = preg_replace('/\s+/', ' ', $k);
        $key = match ($k) {
            'sku'                           => 'sku',
            'description', 'title', 'name'  => 'description',     // product name
            'short description', 'excerpt'  => 'short_desc',      // Woo short description
            'long description', 'content'   => 'long_desc',       // Woo long description
            'image'                         => 'image',
            'datasheet'                     => 'datasheet',
            'supplier'                      => 'supplier',
            'supplier sku'                  => 'supplier_sku',
            'procurement price'             => 'procurement_price',
            'gencod', 'ean', 'barcode'      => 'gencod',
            'country of origin'             => 'country_of_origin',
            'hs code'                       => 'hs_code',
            'net weight', 'weight'          => 'net_weight',
            'dn size'                       => 'dn_size',
            default                         => 'col_' . $i,
        };
        $norm[$i] = $key;
    }
    return $norm;
}


function gv_bi_row_to_assoc(array $row, array $map): array {
    $out = array();
    foreach ($row as $i => $v) {
        $key = $map[$i] ?? null;
        if ( $key ) $out[$key] = $v;
    }
    return $out;
}

function gv_bi_to_decimal($v): string {
    $s = trim((string)$v);
    if ($s === '') return '';
    if ( preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $s) ) { $s = str_replace('.', '', $s); $s = str_replace(',', '.', $s); }
    elseif ( strpos($s, ',') !== false && strpos($s, '.') === false ) { $s = str_replace(',', '.', $s); }
    return $s;
}
function gv_bi_to_int($v): string { return preg_replace('/\D+/', '', (string)$v); }


// ===== Snippet #134: Menu - Add product description AI tool under bulk Edit =====
// Scope: admin | Priority: 10

/** ===== Find (or create) the "Bulk Edit" parent ===== */
if ( ! function_exists('gv_bi_get_bulk_parent_slug') ) {
	function gv_bi_get_bulk_parent_slug() {
		// Allow hard override if you already know the slug
		$slug = apply_filters('gv_bi_bulk_parent_slug', '');
		if ( $slug ) return $slug;

		// Try to discover a top-level "Bulk Edit" menu item.
		global $menu;
		if ( is_array($menu) ) {
			foreach ( $menu as $m ) {
				// $m: [0]=name, [1]=cap, [2]=slug, [3]=page_title, [4]=classes, [5]=icon, [6]=position
				$name = isset($m[0]) ? wp_strip_all_tags($m[0]) : '';
				$slug = isset($m[2]) ? $m[2] : '';
				if ( $name && stripos($name, 'Bulk Edit') !== false && $slug ) {
					return $slug;
				}
			}
		}
		return '';
	}
}

/** ===== Register menu ===== */
add_action( 'admin_menu', function () {

	$parent = gv_bi_get_bulk_parent_slug();

	// If no Bulk Edit exists yet, create a lightweight top-level parent.
	if ( ! $parent ) {
		$parent = 'gv-bulk-edit-root';
		add_menu_page(
			'Bulk Edit',
			'Bulk Edit',
			'manage_woocommerce',
			$parent,
			'__return_null',
			'dashicons-filter',
			56 // near WooCommerce
		);
	}

	// Now add your submenu under "Bulk Edit"
	add_submenu_page(
		$parent,
		'Bulk Edit - Missing Descr.',
		'Prdct: Missing Desc.',
		'manage_woocommerce',
		'gv-bulk-edit-missing-desc',
		'gv_render_missing_desc_page_gvkeys_supplierpost'
	);
}, 10);


// ===== Snippet #135: Product - Add discount to admin side based on customer role when orders are created/saved =====
// Scope: admin | Priority: 10

/* ─────────────────────────────────────────────────────────────
   ADMIN: apply role discount on order edits (AJAX + full save)
   ───────────────────────────────────────────────────────────── */

function gv_get_user_discount_percent( $user_id ) {
    $role_discounts = gv_get_role_discounts();
    $user = get_user_by( 'id', (int) $user_id );
    if ( ! $user ) return 0;
    foreach ( (array) $user->roles as $role ) {
        if ( isset( $role_discounts[ $role ] ) ) {
            return (float) $role_discounts[ $role ];
        }
    }
    return 0;
}

function gv_get_base_price_for_admin( WC_Product $product ) {
    $sale    = (float) $product->get_sale_price();
    $regular = (float) $product->get_regular_price();
    if ( $sale > 0 && $sale < $regular ) return $sale;
    $fallback = (float) $product->get_price();
    return $regular > 0 ? $regular : $fallback;
}

function gv_get_discounted_price_for_user( WC_Product $product, $user_id ) {
    $percent = gv_get_user_discount_percent( $user_id );
    if ( $percent <= 0 ) return false;
    $base = gv_get_base_price_for_admin( $product );
    if ( $base <= 0 ) return false;
    return round( $base * ( 1 - ( $percent / 100 ) ), wc_get_price_decimals() );
}

/** Core applier used by all hooks. */
function gv_apply_role_discount_to_order( WC_Order $order ) {
    $customer_id = $order->get_customer_id();
    if ( ! $customer_id ) return false;

    $discount_percent = gv_get_user_discount_percent( $customer_id );
    if ( $discount_percent <= 0 ) return false;

    $changed = false;

    foreach ( $order->get_items( 'line_item' ) as $item ) {
        $product = $item->get_product();
        if ( ! $product instanceof WC_Product ) continue;

        $unit = gv_get_discounted_price_for_user( $product, $customer_id );
        if ( $unit === false ) continue;

        $qty = max( 1, (float) $item->get_quantity() );
        $item->set_subtotal( $unit * $qty );
        $item->set_total(    $unit * $qty );

        $changed = true;
    }

    if ( $changed ) {
        $order->calculate_taxes();
        $order->calculate_totals( true );
        $order->save();
    }
    return $changed;
}

/** Full post save (classic + HPOS safe). */
add_action( 'save_post_shop_order', function( $post_id ) {
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;

    // Allow per-save bypass
    if ( isset($_POST['_gv_skip_role_discount']) && $_POST['_gv_skip_role_discount'] ) return;

    $order = wc_get_order( $post_id );
    if ( ! $order ) return;

    gv_apply_role_discount_to_order( $order );
}, 20 );

/**
 * Admin AJAX flows:
 * - When items are added via the “Add item(s)” modal.
 * - When items are saved/updated from the items table.
 */
add_action( 'woocommerce_ajax_add_order_item', function() {
    if ( empty( $_POST['order_id'] ) ) return;
    $order = wc_get_order( absint( $_POST['order_id'] ) );
    if ( ! $order ) return;
    gv_apply_role_discount_to_order( $order );
}, 20 );

add_action( 'woocommerce_saved_order_items', function( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) return;
    gv_apply_role_discount_to_order( $order );
}, 20, 1 );

/** (Optional) UI toggle to skip applying discount on a full save. */
add_action( 'woocommerce_admin_order_data_after_order_details', function( $order ){
    echo '<div class="address"><p><label>';
    echo '<input type="checkbox" name="_gv_skip_role_discount" value="1" /> ';
    echo esc_html__( 'Skip applying role discount on save (this time)', 'gv' );
    echo '</label></p></div>';
});


// ===== Snippet #136: Products - Create composite products =====
// Scope: global | Priority: 10


/**
 * Plugin Name: GV – Composite Simple Products (Qty, Totals, Links + Options)
 * Description: Composite support on Simple products with per-child qty, computed fields, component links meta, options page, and dev filters.
 * Author: Gemini Valve
 * Version: 1.3.0
 */
if ( ! defined('ABSPATH') ) exit;

class GV_Composite_Simple_Products {
    /* Meta keys */
    const META_CHILDREN = '_gv_composite_children';     // JSON: [{id,qty}]
    const META_IS_COMP  = '_gv_is_composite';           // yes|no
    const META_LINKS    = '_gv_comp_child_links';       // JSON: [{id,title,qty,edit,view}]

    /* Options */
    const OPT_KEY       = 'gv_comp_settings';

    /* Nonce + AJAX */
    const NONCE         = 'gv_comp_nonce';
    const AJAX_REBUILD  = 'gv_comp_rebuild';
    const AJAX_INFO     = 'gv_comp_product_info';
    const AJAX_GET_LINKS= 'gv_comp_get_links';

    public function __construct() {
        add_action('add_meta_boxes',                [ $this, 'add_meta_box' ]);
        add_action('save_post_product',             [ $this, 'save_meta_box' ], 20, 2);
        add_action('admin_enqueue_scripts',         [ $this, 'admin_assets' ]);
        add_action('admin_print_footer_scripts',    [ $this, 'print_admin_js' ]);

        add_action('wp_ajax_' . self::AJAX_REBUILD,   [ $this, 'ajax_rebuild' ]);
        add_action('wp_ajax_' . self::AJAX_INFO,      [ $this, 'ajax_product_info' ]);
        add_action('wp_ajax_' . self::AJAX_GET_LINKS, [ $this, 'ajax_get_links' ]);

        // Options screen
        add_action('admin_menu', [ $this, 'add_options_page' ]);
        add_action('admin_init', [ $this, 'maybe_save_options' ]);

        // List column + filter
        add_filter('manage_edit-product_columns',        [ $this, 'add_list_column' ]);
        add_action('manage_product_posts_custom_column', [ $this, 'render_list_column' ], 10, 2 );
        add_action('restrict_manage_posts',              [ $this, 'add_admin_filter' ] );
        add_action('pre_get_posts',                      [ $this, 'apply_admin_filter' ] );
    }

    /* -------------------- Options -------------------- */
    private function default_settings() {
        $defaults = [
            // UI toggles
            'ui_show_regular'      => 1,
            'ui_show_sale'         => 1,
            'ui_show_cost'         => 1,
            'ui_show_line_totals'  => 1,
            'ui_show_links_box'    => 1,

            // Pricing / overwrite
            'parent_price_source'  => 'regular', // 'regular' or 'sale_if_available'
            'overwrite_content'    => 0,
            'overwrite_price'      => 0,         // regular + price
            'overwrite_cost'       => 0,
            'overwrite_sku'        => 0,
        ];
        /** Allow overrides */
        return apply_filters('gv_comp_default_settings', $defaults);
    }
    private function get_settings() {
        $saved = get_option(self::OPT_KEY, []);
        if (!is_array($saved)) $saved = [];
        $settings = array_merge($this->default_settings(), $saved);
        /** Final override hook */
        return apply_filters('gv_comp_settings', $settings);
    }

    public function add_options_page() {
        add_submenu_page(
            'woocommerce',
            __('Composite Options','gv'),
            __('Composite Options','gv'),
            'manage_woocommerce',
            'gv-comp-options',
            [ $this, 'render_options_page' ]
        );
    }
    public function render_options_page() {
        if ( ! current_user_can('manage_woocommerce') ) return;
        $s = $this->get_settings();
        ?>
        <div class="wrap">
          <h1><?php esc_html_e('Composite Options','gv'); ?></h1>
          <form method="post">
            <?php wp_nonce_field('gv_comp_save_options','gv_comp_save_options'); ?>

            <h2 class="title"><?php esc_html_e('UI Columns','gv'); ?></h2>
            <table class="form-table">
              <tr><th><?php esc_html_e('Show Regular price','gv'); ?></th>
                  <td><label><input type="checkbox" name="ui_show_regular" value="1" <?php checked($s['ui_show_regular'],1); ?>> <?php esc_html_e('Enabled','gv'); ?></label></td></tr>
              <tr><th><?php esc_html_e('Show Sale price','gv'); ?></th>
                  <td><label><input type="checkbox" name="ui_show_sale" value="1" <?php checked($s['ui_show_sale'],1); ?>> <?php esc_html_e('Enabled','gv'); ?></label></td></tr>
              <tr><th><?php esc_html_e('Show Cost price','gv'); ?></th>
                  <td><label><input type="checkbox" name="ui_show_cost" value="1" <?php checked($s['ui_show_cost'],1); ?>> <?php esc_html_e('Enabled','gv'); ?></label></td></tr>
              <tr><th><?php esc_html_e('Show line totals','gv'); ?></th>
                  <td><label><input type="checkbox" name="ui_show_line_totals" value="1" <?php checked($s['ui_show_line_totals'],1); ?>> <?php esc_html_e('Enabled','gv'); ?></label></td></tr>
              <tr><th><?php esc_html_e('Show “Component Links” box','gv'); ?></th>
                  <td><label><input type="checkbox" name="ui_show_links_box" value="1" <?php checked($s['ui_show_links_box'],1); ?>> <?php esc_html_e('Enabled','gv'); ?></label></td></tr>
            </table>

            <h2 class="title"><?php esc_html_e('Compute / Apply','gv'); ?></h2>
            <table class="form-table">
              <tr>
                <th><?php esc_html_e('Parent price source','gv'); ?></th>
                <td>
                  <label><input type="radio" name="parent_price_source" value="regular" <?php checked($s['parent_price_source'],'regular'); ?>> <?php esc_html_e('Sum of Regular prices','gv'); ?></label><br>
                  <label><input type="radio" name="parent_price_source" value="sale_if_available" <?php checked($s['parent_price_source'],'sale_if_available'); ?>> <?php esc_html_e('Sum of Sale if set (else Regular)','gv'); ?></label>
                </td>
              </tr>
              <tr><th><?php esc_html_e('Overwrite parent content on rebuild','gv'); ?></th>
                  <td><label><input type="checkbox" name="overwrite_content" value="1" <?php checked($s['overwrite_content'],1); ?>> <?php esc_html_e('Always overwrite content','gv'); ?></label></td></tr>
              <tr><th><?php esc_html_e('Overwrite parent price on rebuild','gv'); ?></th>
                  <td><label><input type="checkbox" name="overwrite_price" value="1" <?php checked($s['overwrite_price'],1); ?>> <?php esc_html_e('Always overwrite _regular_price and _price','gv'); ?></label></td></tr>
              <tr><th><?php esc_html_e('Overwrite parent cost on rebuild','gv'); ?></th>
                  <td><label><input type="checkbox" name="overwrite_cost" value="1" <?php checked($s['overwrite_cost'],1); ?>> <?php esc_html_e('Always overwrite cost','gv'); ?></label></td></tr>
              <tr><th><?php esc_html_e('Overwrite parent SKU on rebuild','gv'); ?></th>
                  <td><label><input type="checkbox" name="overwrite_sku" value="1" <?php checked($s['overwrite_sku'],1); ?>> <?php esc_html_e('Always overwrite SKU (ensures uniqueness)','gv'); ?></label></td></tr>
            </table>

            <?php submit_button( __('Save changes','gv') ); ?>
          </form>
        </div>
        <?php
    }
    public function maybe_save_options() {
        if ( ! isset($_POST['gv_comp_save_options']) ) return;
        if ( ! current_user_can('manage_woocommerce') ) return;
        if ( ! wp_verify_nonce($_POST['gv_comp_save_options'], 'gv_comp_save_options') ) return;

        $in = [
            'ui_show_regular'      => isset($_POST['ui_show_regular']) ? 1 : 0,
            'ui_show_sale'         => isset($_POST['ui_show_sale']) ? 1 : 0,
            'ui_show_cost'         => isset($_POST['ui_show_cost']) ? 1 : 0,
            'ui_show_line_totals'  => isset($_POST['ui_show_line_totals']) ? 1 : 0,
            'ui_show_links_box'    => isset($_POST['ui_show_links_box']) ? 1 : 0,
            'parent_price_source'  => ( $_POST['parent_price_source'] ?? 'regular' ) === 'sale_if_available' ? 'sale_if_available' : 'regular',
            'overwrite_content'    => isset($_POST['overwrite_content']) ? 1 : 0,
            'overwrite_price'      => isset($_POST['overwrite_price']) ? 1 : 0,
            'overwrite_cost'       => isset($_POST['overwrite_cost']) ? 1 : 0,
            'overwrite_sku'        => isset($_POST['overwrite_sku']) ? 1 : 0,
        ];
        update_option(self::OPT_KEY, $in);
        wp_safe_redirect( add_query_arg(['page'=>'gv-comp-options','updated'=>'1'], admin_url('admin.php?page=gv-comp-options')) );
        exit;
    }

    /* -------------------- Assets -------------------- */
    public function admin_assets($hook){
        if ($hook !== 'post.php' && $hook !== 'post-new.php' && $hook !== 'woocommerce_page_gv-comp-options') return;
        wp_enqueue_script('jquery');
        if ( wp_script_is('selectWoo','registered') ) wp_enqueue_script('selectWoo'); else wp_enqueue_script('select2');
        wp_enqueue_script('wc-enhanced-select');
        wp_enqueue_script('woocommerce_admin');
        wp_enqueue_style('woocommerce_admin_styles');

        $css = '.gv-comp-table th,.gv-comp-table td{vertical-align:middle}'
             . '.gv-comp-table .small-text{width:80px}'
             . '.gv-comp-info{white-space:nowrap}'
             . '.gv-comp-info a{text-decoration:none}'
             . '.gv-comp-line-totals{margin-top:2px}'
             . '.gv-comp-status{margin-left:8px;opacity:.8}';
        wp_add_inline_style('woocommerce_admin_styles', $css);
    }

    public function print_admin_js(){
        $s = $this->get_settings(); ?>
<script>
jQuery(function($){
  // Config from PHP options
  var GVCOMP = {
    showRegular: <?php echo $s['ui_show_regular'] ? 'true' : 'false'; ?>,
    showSale: <?php echo $s['ui_show_sale'] ? 'true' : 'false'; ?>,
    showCost: <?php echo $s['ui_show_cost'] ? 'true' : 'false'; ?>,
    showLineTotals: <?php echo $s['ui_show_line_totals'] ? 'true' : 'false'; ?>
  };

  function initWooSelects(){ $(document.body).trigger('wc-enhanced-select-init'); }
  initWooSelects();

  // Add row
  $(document).on('click', '#gv-comp-add-row', function(){
    var idx = $('#gv-comp-rows .gv-comp-row').length;
    var tpl = document.getElementById('gv-comp-row-proto');
    if (!tpl) { console.error('gv-comp-row-proto missing'); return; }
    var html = tpl.innerHTML.replace(/__INDEX__/g, String(idx));
    var $row = $(html);
    $('#gv-comp-rows').append($row);
    applyUiToggles($row);
    initWooSelects();
    refreshRowInfo($row);
  });

  // Remove row + reindex
  $(document).on('click', '.gv-comp-remove-row', function(){
    $(this).closest('tr').remove();
    $('#gv-comp-rows .gv-comp-row').each(function(i, tr){
      $(tr).attr('data-index', i);
      $(tr).find('select, input').each(function(){
        var name = $(this).attr('name');
        if (name) $(this).attr('name', name.replace(/gv_comp_rows\[[^\]]+\]/, 'gv_comp_rows['+i+']'));
      });
    });
    recomputeTotals();
  });

  function getNonce(){ return $('input[name="<?php echo esc_js(self::NONCE); ?>"]').val(); }

  function numberToPrice(n){
    return (typeof n === 'number')
      ? n.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})
      : '—';
  }

  function refreshRowInfo($row){
    var pid = parseInt($row.find('select.wc-product-search').val() || 0, 10);
    var $reg   = $row.find('.gv-comp-regular');
    var $sale  = $row.find('.gv-comp-sale');
    var $cost  = $row.find('.gv-comp-cost');
    var $links = $row.find('.gv-comp-links');

    $row.data({ regular_num:0, sale_num:0, cost_num:0 });

    if (!pid){
      $reg.text('—'); $sale.text('—'); $cost.text('—'); $links.html('');
      setLineTotals($row); recomputeTotals(); return;
    }

    $.post(ajaxurl, {
      action: '<?php echo esc_js(self::AJAX_INFO); ?>',
      nonce:  getNonce(),
      product_id: pid
    }, function(resp){
      if (!resp || !resp.success) {
        $reg.text('—'); $sale.text('—'); $cost.text('—'); $links.html('');
        setLineTotals($row); recomputeTotals(); return;
      }
      var d = resp.data || {};
      $reg.html(d.regular_html || d.regular || '—');
      $sale.html(d.sale_html || d.sale || '—');
      $cost.html(d.cost_html || d.cost || '—');

      var linkHTML = '';
      if (d.edit) linkHTML += '<a href="'+d.edit+'" target="_blank">Edit</a>';
      if (d.link) linkHTML += (linkHTML ? ' | ' : '') + '<a href="'+d.link+'" target="_blank">View</a>';
      $links.html(linkHTML);

      $row.data({
        regular_num: parseFloat(d.regular_num || 0) || 0,
        sale_num:    parseFloat(d.sale_num    || 0) || 0,
        cost_num:    parseFloat(d.cost_num    || 0) || 0
      });

      setLineTotals($row);
      recomputeTotals();
    });
  }

  function setLineTotals($row){
    var qty = parseFloat($row.find('input[type="number"]').val() || 1) || 1;
    var r   = ($row.data('regular_num') || 0) * qty;
    var s   = ($row.data('sale_num')    || 0) * qty;
    var c   = ($row.data('cost_num')    || 0) * qty;

    $row.find('.gv-comp-regular-line').text(numberToPrice(r));
    $row.find('.gv-comp-sale-line').text(numberToPrice(s));
    $row.find('.gv-comp-cost-line').text(numberToPrice(c));
  }

  function recomputeTotals(){
    var treg = 0, tsale = 0, tcost = 0;
    $('#gv-comp-rows .gv-comp-row').each(function(){
      var $row = $(this);
      var qty  = parseFloat($row.find('input[type="number"]').val() || 1) || 1;
      treg += ( $row.data('regular_num') || 0 ) * qty;
      tsale += ( $row.data('sale_num')    || 0 ) * qty;
      tcost += ( $row.data('cost_num')    || 0 ) * qty;
    });
    $('.gv-comp-total-regular').text(numberToPrice(treg));
    $('.gv-comp-total-sale').text(numberToPrice(tsale));
    $('.gv-comp-total-cost').text(numberToPrice(tcost));
  }

  // UI toggles from options
  function applyUiToggles($ctx){
    // Spans
    if (!GVCOMP.showRegular){ $ctx.find('.gv-comp-regular, .gv-comp-regular-line, .gv-total-regular').closest('span,td,th,div').css('display','none'); }
    if (!GVCOMP.showSale){    $ctx.find('.gv-comp-sale, .gv-comp-sale-line, .gv-total-sale').closest('span,td,th,div').css('display','none'); }
    if (!GVCOMP.showCost){    $ctx.find('.gv-comp-cost, .gv-comp-cost-line, .gv-total-cost').closest('span,td,th,div').css('display','none'); }
    if (!GVCOMP.showLineTotals){ $ctx.find('.gv-comp-line-totals').hide(); }
  }

  // React to changes
  $(document).on('change', '#gv-comp-rows select.wc-product-search', function(){ refreshRowInfo($(this).closest('tr')); });
  $(document).on('input change', '#gv-comp-rows input[type="number"]', function(){
    var $row = $(this).closest('tr'); setLineTotals($row); recomputeTotals();
  });

  // Rebuild button
  $(document).on('click', '#gv-comp-rebuild-btn', function(){
    var $btn   = $(this);
    var postId = parseInt($btn.data('post') || 0, 10);
    var force  = $('#gv-comp-force').is(':checked');
    var rows   = [];

    $('#gv-comp-rows .gv-comp-row').each(function(){
      var $tr  = $(this);
      var id   = parseInt($tr.find('select.wc-product-search').val() || 0, 10);
      var qty  = parseInt($tr.find('input[type="number"]').val() || 1, 10);
      if (id) rows.push({id:id, qty: (qty>0?qty:1)});
    });

    $('#gv-comp-status').text('Rebuilding…');

    $.post(ajaxurl, {
      action: '<?php echo esc_js(self::AJAX_REBUILD); ?>',
      nonce:  getNonce(),
      post_id: postId,
      rows: rows,
      force: force ? 1 : 0
    }, function(resp){
//      if (!resp || !resp.success){ $('#gv-comp-status').text('Failed.'); return; }
//      $('#gv-comp-status').text(resp.data && resp.data.msg ? resp.data.msg : 'Done.');
//      $('#gv-comp-rows .gv-comp-row').each(function(){ refreshRowInfo($(this)); });
//      
		if (!resp || !resp.success){ $('#gv-comp-status').text('Failed.'); return; }
		$('#gv-comp-status').text(resp.data && resp.data.msg ? resp.data.msg : 'Done.');
		// Refresh per-row display & totals
		$('#gv-comp-rows .gv-comp-row').each(function(){ refreshRowInfo($(this)); });
		// Update the “Component Links” box directly from the rebuild response
		if (resp.data && resp.data.links_html){
		  $('#gv-comp-links-wrap').html(resp.data.links_html);
		}
		
		if ($('#gv-comp-links-wrap').length && resp.data && resp.data.links_html){
		  $('#gv-comp-links-wrap').html(resp.data.links_html);
		}


		/*
      $.post(ajaxurl, {
        action: '<?php echo esc_js(self::AJAX_GET_LINKS); ?>',
        nonce:  getNonce(),
        post_id: postId
      }, function(r2){
        if (r2 && r2.success && r2.data && r2.data.html){ $('#gv-comp-links-wrap').html(r2.data.html); }
      });
		*/
    });
  });

  // Initial
  $('#gv-comp-rows .gv-comp-row').each(function(){ applyUiToggles($(this)); refreshRowInfo($(this)); });
  applyUiToggles($(document));
});
</script>
<?php }

    /* -------------------- Meta box UI -------------------- */
    public function add_meta_box() {
        add_meta_box('gv_comp_mb', __('Composite Components','gv'), [ $this, 'render_meta_box' ], 'product', 'normal', 'default');
    }

    public function render_meta_box($post){
        if ( ! current_user_can('manage_woocommerce') ) { echo '<p>'.esc_html__('No permission','gv').'</p>'; return; }
        $s = $this->get_settings();

        wp_nonce_field(self::NONCE, self::NONCE);
        $children = $this->get_children_meta($post->ID);
        echo '<p class="description">'.esc_html__('Select component products and set quantity per component. Only published simple products are supported.','gv').'</p>';

        echo '<table class="widefat gv-comp-table"><thead><tr>';
        echo '<th style="width:50%;">'.esc_html__('Component Product','gv').'</th>';
        echo '<th style="width:10%;">'.esc_html__('Qty','gv').'</th>';
        echo '<th style="width:10%;">'.esc_html__('Remove','gv').'</th>';
        echo '<th style="width:30%;">'.esc_html__('Info (Reg / Sale / Cost + line totals)','gv').'</th>';
        echo '</tr></thead><tbody id="gv-comp-rows">';

        if ( empty($children) ) $children = [ [ 'id'=>0, 'qty'=>1 ] ];
        foreach ( $children as $row_idx => $row ) $this->render_row($row_idx, $row);

        echo '<tfoot><tr>';
        echo '<th colspan="3" style="text-align:right;">'.esc_html__('Totals (qty × price):','gv').'</th>';
        echo '<th><span class="gv-comp-total-regular gv-total-regular">—</span> / <span class="gv-comp-total-sale gv-total-sale">—</span> / <span class="gv-comp-total-cost gv-total-cost">—</span></th>';
        echo '</tr></tfoot>';

        echo '</tbody></table>';

        echo '<p><button type="button" class="button" id="gv-comp-add-row">'.esc_html__('Add Component','gv').'</button></p>';

        echo '<p style="margin-top:16px;">';
        echo '<label><input type="checkbox" id="gv-comp-force" /> '.esc_html__('Force overwrite (ignore “update when empty”)','gv').'</label> ';
        echo '<button type="button" class="button button-primary" id="gv-comp-rebuild-btn" data-post="'.esc_attr($post->ID).'">'.esc_html__('Rebuild Composite Fields','gv').'</button>';
        echo ' <span id="gv-comp-status" class="gv-comp-status"></span>';
        echo '</p>';

        echo '<template id="gv-comp-row-proto">'; $this->render_row('__INDEX__', [ 'id'=>0, 'qty'=>1 ], true ); echo '</template>';

        if ( $s['ui_show_links_box'] ) {
            echo '<div id="gv-comp-links-wrap" style="margin-top:12px">'.$this->render_links_html($post->ID).'</div>';
        }
    }

    private function render_row($index, $row, $is_template=false){
        $pid = absint($row['id'] ?? 0);
        $qty = max(1, absint($row['qty'] ?? 1));
        $row_id_attr = $is_template ? '__INDEX__' : (int)$index;

        $selected_title = '';
        if ($pid) {
            $p = wc_get_product($pid);
            if ($p && $p->get_status()==='publish' && $p->is_type('simple')) $selected_title = $p->get_formatted_name();
        }

        echo '<tr class="gv-comp-row" data-index="'.esc_attr($row_id_attr).'">';
        echo '<td>';
        echo '<select class="wc-product-search" style="width:100%;" '
            .'name="gv_comp_rows['.esc_attr($row_id_attr).'][id]" '
            .'data-placeholder="'.esc_attr__('Search for a product…','woocommerce').'" '
            .'data-allow_clear="true" '
            .'data-ajax_url="'.esc_url(admin_url('admin-ajax.php')).'" '
            .'data-action="woocommerce_json_search_products_and_variations" '
            .'data-security="'.esc_attr(wp_create_nonce('search-products')).'" '
            .'data-exclude_type="variable,grouped,external">';
        if ($pid && $selected_title) echo '<option value="'.esc_attr($pid).'" selected="selected">'.esc_html($selected_title).'</option>';
        echo '</select>';
        echo '</td>';

        echo '<td><input type="number" min="1" class="small-text" name="gv_comp_rows['.esc_attr($row_id_attr).'][qty]" value="'.esc_attr($qty).'" /></td>';
        echo '<td><button type="button" class="button gv-comp-remove-row">&times;</button></td>';

        echo '<td class="gv-comp-info">';
        echo '<span class="gv-comp-regular" title="Regular">—</span> / ';
        echo '<span class="gv-comp-sale" title="Sale">—</span> / ';
        echo '<span class="gv-comp-cost" title="Cost">—</span>';
        echo '<div class="gv-comp-line-totals" style="opacity:.8;font-size:11px">= <span class="gv-comp-regular-line">—</span> / <span class="gv-comp-sale-line">—</span> / <span class="gv-comp-cost-line">—</span></div>';
        echo ' <span class="gv-comp-links"></span>';
        echo '</td>';

        echo '</tr>';
    }

    /* -------------------- Save & AJAX -------------------- */
    public function save_meta_box($post_id, $post){
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( ! current_user_can('manage_woocommerce') ) return;
        if ( ! isset($_POST[self::NONCE]) || ! wp_verify_nonce($_POST[self::NONCE], self::NONCE) ) return;

        $rows = $this->sanitize_rows($_POST['gv_comp_rows'] ?? []);
        $this->save_children_meta($post_id, $rows);
        update_post_meta($post_id, self::META_IS_COMP, empty($rows) ? 'no' : 'yes');

        if ( ! empty($rows) ) $this->compute_and_apply($post_id, $rows, false);
    }

    public function ajax_rebuild(){
        if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error();
        $nonce = $_POST['nonce'] ?? '';
        if ( ! wp_verify_nonce($nonce, self::NONCE) ) wp_send_json_error();

        $post_id = absint($_POST['post_id'] ?? 0);
        if ( ! $post_id || get_post_type($post_id) !== 'product' ) wp_send_json_error();

        $rows = $this->sanitize_rows($_POST['rows'] ?? []);
        if ( empty($rows) ) { $rows = $this->get_children_meta($post_id); } else { $this->save_children_meta($post_id, $rows); }

        update_post_meta($post_id, self::META_IS_COMP, empty($rows) ? 'no' : 'yes');
        $force = ! empty($_POST['force']);
//       $res   = $this->compute_and_apply($post_id, $rows, (bool)$force);
//        wp_send_json_success([ 'msg'=>$res['message'], 'applied'=>$res['applied'] ]);
		$res = $this->compute_and_apply( $post_id, $rows, (bool) $force );

		wp_send_json_success( [
			'msg'         => $res['message'],
			'applied'     => $res['applied'],
			'links_html'  => $res['links_html'] ?? '',   // NEW
		] );

    }

    public function ajax_product_info(){
        if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error();
        $nonce = $_POST['nonce'] ?? '';
        if ( ! wp_verify_nonce($nonce, self::NONCE) ) wp_send_json_error();

        $pid = absint($_POST['product_id'] ?? 0);
        if ( ! $pid ) wp_send_json_error();

        $p = wc_get_product($pid);
        if ( ! $p || $p->get_status() !== 'publish' || ! $p->is_type('simple') ) wp_send_json_error();

        $regular = $p->get_regular_price();
        $sale    = $p->get_sale_price();
        $cost    = get_post_meta($pid, '_gv_proc_cost_price', true);

        $edit = get_edit_post_link($pid, '');
        $link = get_permalink($pid);

        wp_send_json_success([
            'regular'      => ($regular !== '' ? (string)$regular : ''),
            'regular_html' => ($regular !== '' ? wc_price((float)$regular) : '—'),
            'regular_num'  => ($regular !== '' ? (float)$regular : 0.0),

            'sale'         => ($sale !== '' ? (string)$sale : ''),
            'sale_html'    => ($sale !== '' ? wc_price((float)$sale) : '—'),
            'sale_num'     => ($sale !== '' ? (float)$sale : 0.0),

            'cost'         => ($cost !== '' ? (string)$cost : ''),
            'cost_html'    => ($cost !== '' ? wc_price((float)$cost) : '—'),
            'cost_num'     => ($cost !== '' ? (float)$cost : 0.0),

            'edit'         => $edit,
            'link'         => $link,
        ]);
    }

    public function ajax_get_links(){
        if ( ! current_user_can('manage_woocommerce') ) wp_send_json_error();
        $nonce = $_POST['nonce'] ?? '';
        if ( ! wp_verify_nonce($nonce, self::NONCE) ) wp_send_json_error();
        $post_id = absint($_POST['post_id'] ?? 0);
        if ( ! $post_id || get_post_type($post_id) !== 'product' ) wp_send_json_error();
        wp_send_json_success([ 'html' => $this->render_links_html($post_id) ]);
    }

    /* -------------------- Compute & helpers -------------------- */
    private function compute_and_apply($parent_id, array $rows, bool $force){
        $s = $this->get_settings();
        $children = $this->filter_children_products($rows);

        $sum_regular = 0.0;
        $sum_sale    = 0.0;
        $sum_cost    = 0.0;
        $sku_parts   = [];
        $desc_parts  = [];
        $link_items  = [];

        foreach ($children as $c){
            $prod = $c['product']; $qty  = $c['qty']; $pid  = $prod->get_id();

            $reg  = (float) get_post_meta($pid, '_regular_price', true);
            $sal  = (float) $prod->get_sale_price();
            $cst  = (float) get_post_meta($pid, '_gv_proc_cost_price', true);

            $sum_regular += max(0,$reg) * $qty;
            $sum_sale    += max(0,$sal) * $qty;
            $sum_cost    += max(0,$cst) * $qty;

            $sku = (string) get_post_meta($pid, '_sku', true);
            if ($sku) $sku_parts[] = $qty>1 ? ($sku.'x'.$qty) : $sku;

            $content = trim( wp_strip_all_tags( (string) get_post_field('post_content', $pid) ) );
            if ($content !== '') $desc_parts[] = $content;

            $link_items[] = [
                'id'=>$pid,'title'=>$prod->get_name(),'qty'=>(int)$qty,
                'edit'=>get_edit_post_link($pid,''),'view'=>get_permalink($pid),
            ];
        }

        // Decide parent price from option
        $parent_price_total = ($s['parent_price_source'] === 'sale_if_available' && $sum_sale > 0)
            ? $sum_sale
            : $sum_regular;

        $computed = [
            'post_content'        => implode("\n\n", $desc_parts),
            '_regular_price'      => wc_format_decimal($parent_price_total),
            '_price'              => wc_format_decimal($parent_price_total),
            '_gv_proc_cost_price' => wc_format_decimal($sum_cost),
            '_sku'                => implode('-', $sku_parts),
            '_upsell_ids'         => wp_list_pluck($children, 'id'),
        ];

        $applied = [];

        // Content
        $current_content = (string) get_post_field('post_content', $parent_id);
        if ( $s['overwrite_content'] || $force || $current_content === '' ) {
            wp_update_post([ 'ID'=>$parent_id, 'post_content'=>$computed['post_content'] ]);
            $applied['post_content'] = true;
        }

        // Prices
        if ( $s['overwrite_price'] || $force || '' === (string)get_post_meta($parent_id,'_regular_price',true) ) {
            update_post_meta($parent_id,'_regular_price',$computed['_regular_price']);
            update_post_meta($parent_id,'_price',$computed['_price']);
            $applied['_regular_price'] = $applied['_price'] = true;
        }

        // Cost
        if ( $s['overwrite_cost'] || $force || '' === (string)get_post_meta($parent_id,'_gv_proc_cost_price',true) ) {
            update_post_meta($parent_id,'_gv_proc_cost_price',$computed['_gv_proc_cost_price']);
            $applied['_gv_proc_cost_price'] = true;
        }

        // SKU
        $new_sku = $computed['_sku'];
        if ( $new_sku !== '' ) {
            $cur = (string) get_post_meta($parent_id,'_sku',true);
            if ( $s['overwrite_sku'] || $force || $cur === '' ) {
                $unique_sku = $this->ensure_unique_sku($new_sku, $parent_id);
                update_post_meta($parent_id,'_sku',$unique_sku);
                $applied['_sku'] = true;
            }
        }

        // Upsells + links meta
        update_post_meta($parent_id,'_upsell_ids', array_map('absint', $computed['_upsell_ids']));
        $applied['_upsell_ids'] = true;

		/*
        update_post_meta($parent_id, self::META_LINKS, wp_json_encode($link_items));
		// Make sure fresh meta is visible immediately in subsequent reads
		clean_post_cache( $parent_id );
		*/
		// Store as native array (WP will serialize). More reliable than JSON here.
		update_post_meta( $parent_id, self::META_LINKS, $link_items );

		// Make sure any caches are fresh
		clean_post_cache( $parent_id );
		
        $applied[self::META_LINKS] = true;

        // Log
        update_post_meta($parent_id,'_gv_composite_last_build',[
            'time'=>current_time('mysql'),
            'force'=>$force?1:0,
            'settings'=>$this->get_settings(),
            'children'=>array_map(fn($c)=>['id'=>$c['id'],'qty'=>$c['qty']], $children),
            'links'=>$link_items,
            'applied'=>$applied,
        ]);

		/*
        return [
            'applied'=>$applied,
            'message'=>'Composite rebuilt: '.count($children).' components, parent price='.$computed['_price'].', cost='.$computed['_gv_proc_cost_price'],
        ];
		*/
		
		return [
		'applied'     => $applied,
		'message'     => 'Composite rebuilt: ' . count( $children ) . ' components, parent price=' . $computed['_price'] . ', cost=' . $computed['_gv_proc_cost_price'],
		'links_html'  => $this->render_links_html( $parent_id ), // NEW
		];

		
    }

    private function render_links_html($post_id){
        $s = $this->get_settings();
        if ( ! $s['ui_show_links_box'] ) return '';
        $links = $this->get_child_links_meta($post_id);
        ob_start();
        echo '<strong>'.esc_html__('Component Links','gv').'</strong><br>';
        if ( empty($links) ) {
            echo '<em>'.esc_html__('No links yet — save or click “Rebuild Composite Fields”.','gv').'</em>';
        } else {
            echo '<ul style="margin:.5em 0 0 1.2em; list-style:disc;">';
            foreach ($links as $lnk) {
                $title = isset($lnk['title']) ? $lnk['title'] : ('#'.(int)($lnk['id'] ?? 0));
                $qty   = (int)($lnk['qty'] ?? 1);
                $edit  = isset($lnk['edit']) ? esc_url($lnk['edit']) : '';
                $view  = isset($lnk['view']) ? esc_url($lnk['view']) : '';
                echo '<li>'.esc_html($title).' × '.esc_html((string)$qty).' — ';
                if ($edit) echo '<a href="'.$edit.'" target="_blank">'.esc_html__('Edit','gv').'</a>';
                if ($edit && $view) echo ' | ';
                if ($view) echo '<a href="'.$view.'" target="_blank">'.esc_html__('View','gv').'</a>';
                echo '</li>';
            }
            echo '</ul>';
        }
        return ob_get_clean();
    }

    /* -------------------- Misc helpers -------------------- */
    private function maybe_update_meta($post_id,$key,$value,$force,array &$applied){
        $cur = get_post_meta($post_id,$key,true);
        if ( $force || $cur === '' ) { update_post_meta($post_id,$key,$value); $applied[$key]=true; }
    }
    private function ensure_unique_sku($base,$product_id){
        $sku = $base; $i=2;
        while ($sku && ($found = wc_get_product_id_by_sku($sku)) && (int)$found !== (int)$product_id) {
            $sku = $base.'-'.$i; $i++; if ($i>9999) break;
        }
        return $sku;
    }
    private function get_children_meta($post_id){
        $json = get_post_meta($post_id,self::META_CHILDREN,true);
        $arr = $json ? json_decode($json,true) : [];
        $out = [];
        foreach ((array)$arr as $row){
            $id = absint($row['id'] ?? 0);
            $qty= max(1, absint($row['qty'] ?? 1));
            if ($id) $out[] = ['id'=>$id,'qty'=>$qty];
        }
        return $out;
    }
    private function save_children_meta($post_id,array $rows){
        if ( empty($rows) ) { delete_post_meta($post_id,self::META_CHILDREN); return; }
        update_post_meta($post_id,self::META_CHILDREN, wp_json_encode(array_values($rows)));
    }
	private function get_child_links_meta( $post_id ) {
		$val = get_post_meta( $post_id, self::META_LINKS, true );

		// If another version saved arrays, $val is already an array
		if ( is_array( $val ) ) {
			return $val;
		}

		// If saved as JSON string, decode
		if ( is_string( $val ) && $val !== '' ) {
			$arr = json_decode( $val, true );
			return is_array( $arr ) ? $arr : [];
		}

		return [];
	}

	// Normalizes the posted component rows: [{id, qty}] -> ints, qty >= 1
	private function sanitize_rows( $rows ) {
		$out = [];
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$id  = isset( $row['id'] )  ? absint( $row['id'] )  : 0;
				$qty = isset( $row['qty'] ) ? absint( $row['qty'] ) : 1;
				if ( $id ) {
					$out[] = [
						'id'  => $id,
						'qty' => max( 1, $qty ),
					];
				}
			}
		}
		return $out;
	}

    private function filter_children_products(array $rows){
        $out = [];
        foreach ($rows as $row){
            $pid = absint($row['id']);
            $qty = max(1, absint($row['qty']));
            $prod = wc_get_product($pid);
            if ( ! $prod ) continue;
            if ( $prod->get_status() !== 'publish' ) continue;
            if ( ! $prod->is_type('simple') ) continue;
            $out[] = ['id'=>$pid,'qty'=>$qty,'product'=>$prod];
        }
        return $out;
    }

    /* -------------------- List column + filter -------------------- */
    public function add_list_column($cols){ $cols['gv_is_composite']=__('Composite','gv'); return $cols; }
    public function render_list_column($col,$post_id){
        if ($col!=='gv_is_composite') return;
        echo get_post_meta($post_id,self::META_IS_COMP,true)==='yes' ? 'Yes' : '—';
    }
    public function add_admin_filter(){
        global $typenow; if ($typenow!=='product') return;
        $cur = isset($_GET['gv_comp_filter']) ? sanitize_text_field($_GET['gv_comp_filter']) : '';
        echo '<select name="gv_comp_filter" id="gv_comp_filter" class="postform">';
        echo '<option value="">'.esc_html__('Composite: All','gv').'</option>';
        echo '<option value="yes" '.selected($cur,'yes',false).'>'.esc_html__('Composite: Yes','gv').'</option>';
        echo '<option value="no"  '.selected($cur,'no',false).'>'.esc_html__('Composite: No','gv').'</option>';
        echo '</select>';
    }
    public function apply_admin_filter($q){
        if ( is_admin() && $q->is_main_query() && 'product' === ( $_GET['post_type'] ?? '' ) ) {
            $v = isset($_GET['gv_comp_filter']) ? sanitize_text_field($_GET['gv_comp_filter']) : '';
            if ($v==='yes'){
                $q->set('meta_query', $this->merge_meta_query($q->get('meta_query'), [ [ 'key'=>self::META_IS_COMP, 'value'=>'yes' ] ] ));
            } elseif ($v==='no'){
                $q->set('meta_query', $this->merge_meta_query($q->get('meta_query'), [
                    [ 'key'=>self::META_IS_COMP, 'compare'=>'NOT EXISTS' ],
                    [ 'relation'=>'OR',
                      [ 'key'=>self::META_IS_COMP, 'value'=>'no' ],
                      [ 'key'=>self::META_IS_COMP, 'compare'=>'NOT EXISTS' ],
                    ],
                ]));
            }
        }
    }
    private function merge_meta_query($existing,$added){
        if (!is_array($existing) || empty($existing)) return $added;
        return [ 'relation'=>'AND', $existing, $added ];
    }
}

/* Bootstrap */
add_action('plugins_loaded', function(){
    if ( class_exists('WooCommerce') ) new GV_Composite_Simple_Products();
});


// ===== Snippet #137: Products - Add composite products to quotes helper =====
// Scope: admin | Priority: 10

/**
 * Compact component list for PDFs:
 * • <SKU> × <qty>  (SKU is a link to the product URL)
 */
function gv_comp_links_html_for_pdf( $product_id ) {
    if ( ! $product_id ) return '';

    // Read links meta (array or legacy JSON)
    $links = get_post_meta( $product_id, '_gv_comp_child_links', true );
    if ( is_string( $links ) && $links !== '' ) {
        $decoded = json_decode( $links, true );
        if ( is_array( $decoded ) ) { $links = $decoded; }
    }
    if ( ! is_array( $links ) || empty( $links ) ) return '';

    // Optional: only for composite parents
    if ( get_post_meta( $product_id, '_gv_is_composite', true ) !== 'yes' ) return '';

    ob_start(); ?>
    <div style="margin:2px 0 0; font-size:8pt;">
          
            <?php foreach ( $links as $lnk ) :
                $pid   = isset($lnk['id']) ? (int) $lnk['id'] : 0;
                $qty   = isset($lnk['qty']) ? (int) $lnk['qty'] : 1;
                $view  = ! empty($lnk['view']) ? esc_url($lnk['view']) : '';
                $sku   = $pid ? get_post_meta( $pid, '_sku', true ) : '';
                $title = isset($lnk['title']) ? wp_strip_all_tags($lnk['title']) : ($sku ?: '#'.$pid);

                // Fallback: if no SKU, show title as text
                $text  = $sku !== '' ? $sku : $title;
            ?>
                    <?php echo $qty; ?>x<?php if ( $view ) : ?><a href="<?php echo $view; ?>" title="<?php echo esc_attr($title); ?>"><?php echo esc_html($text); ?></a> <?php else : ?> <?php echo esc_html($text); ?><?php endif; ?>              
            <?php endforeach; ?>
   
    </div>
    <?php
    return ob_get_clean();
}


function gv_comp_column_links( $array, $document ) {
    // 1) Always prefer the item’s product_id for meta reads
    $pid = 0;
    if ( ! empty( $array['item'] ) && $array['item'] instanceof WC_Order_Item_Product ) {
        $pid = $array['item']->get_product_id();
    }
    // 2) If you attached this as a “standalone” custom column:
    return gv_comp_links_html_for_pdf( $pid, /*debug*/ false );

    // 3) If you attached this to the **Product** column instead, use this form:
    // return $array['name'] . gv_comp_links_html_for_pdf( $pid, false );
}


// ===== Snippet #138: Shop - Products per brand =====
// Scope: global | Priority: 10


/**
 * GV – Brand Products Page (auto brand + smart filters + categories, editor-safe)
 * Shortcode:
 *   [gv_brand_products brand="trutorq" show_filter="yes" show_brand="no" show_category="yes" auto_filters="yes" columns="4" per_page="12" cat_nav="links"]
 */

if ( ! defined('ABSPATH') ) exit;

if ( ! class_exists('GV_Brand_Products_Page') ) :

class GV_Brand_Products_Page {
    const DEFAULT_PER_PAGE = 12;
    const ID_POOL_LIMIT    = 2000; // cap for analysis pool

    /* ──────────────────────────────────────────────────────────────────── */
    /* Taxonomy helpers                                                     */
    /* ──────────────────────────────────────────────────────────────────── */

    /** Return active brand taxonomy slug. Fallback to pa_brand if present. */
    public static function get_brand_taxonomy() {
        foreach ( ['product_brand','pwb-brand','yith_product_brand','product_brands'] as $tax ) {
            if ( taxonomy_exists($tax) ) return $tax;
        }
        return taxonomy_exists('pa_brand') ? 'pa_brand' : '';
    }

    /** If we’re on a brand archive, return its slug; else ''. */
    private static function detect_current_brand_slug() {
        $tax = self::get_brand_taxonomy();
        if ( $tax && is_tax($tax) ) {
            $t = get_queried_object();
            return ($t && ! is_wp_error($t) && isset($t->slug)) ? $t->slug : '';
        }
        return '';
    }

    /** Build brand tax_query from atts + auto-detect + brand_in. */
    private static function build_brand_tax_query( $atts ) {
        $tax_query = [];
        $tax = self::get_brand_taxonomy();
        if ( ! $tax ) return $tax_query;

        $brand = $atts['brand'] ?: self::detect_current_brand_slug();

        if ( ! empty($brand) ) {
            $tax_query[] = [
                'taxonomy' => $tax,
                'field'    => is_numeric($brand) ? 'term_id' : 'slug',
                'terms'    => $brand,
            ];
        }

        if ( ! empty($atts['brand_in']) ) {
            $tax_query[] = [
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['brand_in'])),
            ];
        }

        return $tax_query;
    }

    /** Build product_cat tax_query from atts + GET + archive. */
    private static function build_category_tax_query( $atts ) {
        $tax_query = [];

        // explicit GET override
        if ( isset($_GET['gv_cat']) && $_GET['gv_cat'] !== '' ) {
            $atts['category'] = sanitize_text_field( wp_unslash($_GET['gv_cat']) );
        }

        $cat = $atts['category'];
        if ( empty($cat) && is_product_category() ) {
            $t = get_queried_object();
            if ( $t && ! is_wp_error($t) && isset($t->slug) ) $cat = $t->slug;
        }

        if ( ! empty($cat) ) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field'    => is_numeric($cat) ? 'term_id' : 'slug',
                'terms'    => $cat,
            ];
        }

        if ( ! empty($atts['category_in']) ) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array_map('trim', explode(',', $atts['category_in'])),
            ];
        }

        return $tax_query;
    }

    /* ──────────────────────────────────────────────────────────────────── */
    /* Pool analysis (for auto-filters)                                     */
    /* ──────────────────────────────────────────────────────────────────── */

    /** Gather product IDs for analysis based on baseline tax_query (brand/category). */
    private static function get_product_pool_ids( $baseline_tax_query, $limit=self::ID_POOL_LIMIT ) {
        return array_map('intval', get_posts([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'fields'         => 'ids',
            'posts_per_page' => $limit,
            'no_found_rows'  => true,
            'tax_query'      => $baseline_tax_query ? array_merge(['relation'=>'AND'], $baseline_tax_query) : [],
            'orderby'        => 'ID',
            'order'          => 'DESC',
        ]));
    }

    /** From product IDs, compute price min/max, attribute usage (pa_*), and categories. */
    private static function analyze_pool_for_filters( $ids ) {
        $r = [
            'price_min' => null,
            'price_max' => null,
            'attrs'     => [],   // taxonomy => [ term_slug => count ]
            'cats'      => [],   // product_cat => count
        ];
        if ( empty($ids) ) return $r;

        $min = null; $max = null;

        foreach ( $ids as $id ) {
            $p = wc_get_product($id);
            if ( ! $p ) continue;

            // price
            $raw = $p->get_price();
            if ( $raw !== '' ) {
                $val = (float) $raw;
                $min = is_null($min) ? $val : min($min,$val);
                $max = is_null($max) ? $val : max($max,$val);
            }

            // attributes (taxonomy-based only)
            foreach ( $p->get_attributes() as $attr ) {
                if ( ! $attr->is_taxonomy() ) continue;
                $tax = $attr->get_name();
                if ( strpos($tax,'pa_') !== 0 ) continue;
                $terms = wc_get_product_terms($id, $tax, ['fields'=>'all']);
                if ( empty($terms) ) continue;
                $r['attrs'][$tax] ??= [];
                foreach ($terms as $t) {
                    $slug = $t->slug;
                    $r['attrs'][$tax][$slug] = ($r['attrs'][$tax][$slug] ?? 0) + 1;
                }
            }

            // categories
            $cat_terms = get_the_terms( $id, 'product_cat' );
            if ( $cat_terms && ! is_wp_error($cat_terms) ) {
                foreach ( $cat_terms as $ct ) {
                    $slug = $ct->slug;
                    $r['cats'][$slug] = ($r['cats'][$slug] ?? 0) + 1;
                }
            }
        }

        $r['price_min'] = $min; $r['price_max'] = $max;

        foreach ( $r['attrs'] as $tax => $counts ) {
            arsort($counts);
            $r['attrs'][$tax] = array_slice($counts, 0, 30, true);
        }

        arsort($r['cats']);
        $r['cats'] = array_slice($r['cats'], 0, 50, true);

        return $r;
    }

    /** Get human label for a term slug. */
    private static function term_name_by_slug( $tax, $slug ) {
        $t = get_term_by('slug',$slug,$tax);
        return ($t && ! is_wp_error($t)) ? $t->name : $slug;
    }

    /* ──────────────────────────────────────────────────────────────────── */
    /* Filter UI                                                             */
    /* ──────────────────────────────────────────────────────────────────── */

    private static function render_filters_ui( $atts, $analysis ) {
        // Don’t echo during REST save (prevents invalid JSON on Gutenberg save)
        if ( defined('REST_REQUEST') && REST_REQUEST ) return;

        $brand_tax = self::get_brand_taxonomy();
        $selected_brand = isset($_GET['gv_brand']) ? sanitize_text_field( wp_unslash($_GET['gv_brand']) ) : ($atts['brand'] ?? '');

        echo '<form method="get" class="gv-brand-filter" style="margin:1rem 0; padding:.75rem; border:1px solid #eee; border-radius:8px;">';

        // Brand selector (optional)
        if ( $atts['show_filter'] === 'yes' && $atts['show_brand'] !== 'no' && $brand_tax ) {
            $terms = get_terms(['taxonomy'=>$brand_tax,'hide_empty'=>true]);
            if ( ! is_wp_error($terms) && $terms ) {
                echo '<label style="margin-right:.5rem;font-weight:600;">Brand:</label>';
                echo '<select name="gv_brand" style="min-width:180px; margin-right:1rem;" onchange="this.form.submit()">';
                echo '<option value="">All brands</option>';
                foreach ($terms as $t) {
                    printf('<option value="%s"%s>%s</option>',
                        esc_attr($t->slug),
                        selected($selected_brand,$t->slug,false),
                        esc_html($t->name));
                }
                echo '</select>';
            }
        }

        // Category selector (from current pool)
        if ( $atts['show_category'] !== 'no' && ! empty($analysis['cats']) ) {
            $current_cat = isset($_GET['gv_cat']) ? sanitize_text_field( wp_unslash($_GET['gv_cat']) ) : ( $atts['category'] ?? '' );
            echo '<label style="margin:0 .5rem 0 1rem; font-weight:600;">Category:</label>';
            echo '<select name="gv_cat" style="min-width:200px; margin-right:1rem;" onchange="this.form.submit()">';
            echo '<option value="">All categories</option>';
            $i = 0;
            foreach ( $analysis['cats'] as $slug => $count ) {
                if ( ++$i > 30 ) break;
                $name = self::term_name_by_slug( 'product_cat', $slug );
                printf('<option value="%s"%s>%s (%d)</option>',
                    esc_attr($slug),
                    selected($current_cat,$slug,false),
                    esc_html($name),
                    (int) $count
                );
            }
            echo '</select>';
        }

        // Optional compact category navigator (links)
        if ( $atts['cat_nav'] === 'links' && ! empty( $analysis['cats'] ) ) {
            echo '<div class="gv-cat-nav" style="display:inline-block; margin-left:1rem;">';
            echo '<span style="font-weight:600; margin-right:.5rem;">Browse:</span>';
            $i = 0;
            foreach ( $analysis['cats'] as $slug => $count ) {
                if ( ++$i > 12 ) break;
                $label = esc_html( self::term_name_by_slug('product_cat', $slug) );
                $url = esc_url( add_query_arg( array_merge( $_GET, ['gv_cat'=>$slug, 'pg'=>1] ) ) );
                echo '<a href="'.$url.'" style="margin-right:.5rem;">'.$label.'</a>';
            }
            echo '</div>';
        }

        // Keyword
        $q = isset($_GET['gv_q']) ? sanitize_text_field( wp_unslash($_GET['gv_q']) ) : '';


		
        echo '<label style="margin-right:.5rem; font-weight:600;">Search:</label>';
        printf('<input type="text" name="gv_q" value="%s" placeholder="Keyword…" style="min-width:200px; margin-right:1rem;" />', esc_attr($q));

        // In stock
        $in_stock = isset($_GET['in_stock']) && $_GET['in_stock']==='1';
        echo '<label style="margin-right:.5rem; font-weight:600;">In stock</label>';
        printf('<input type="checkbox" name="in_stock" value="1" %s style="vertical-align:middle; margin-right:1rem;" />', checked($in_stock,true,false));

        // Price range
        $min = $analysis['price_min']; $max = $analysis['price_max'];
        $cur_min = isset($_GET['price_min']) ? (float) $_GET['price_min'] : '';
        $cur_max = isset($_GET['price_max']) ? (float) $_GET['price_max'] : '';
        if ( $min !== null && $max !== null && $min < $max ){
            echo '<label style="margin:0 .5rem 0 1rem; font-weight:600;">Price:</label>';
            printf('<input type="number" step="0.01" name="price_min" placeholder="min %.2f" value="%s" style="width:110px; margin-right:.5rem;" />',$min,esc_attr($cur_min));
            printf('<input type="number" step="0.01" name="price_max" placeholder="max %.2f" value="%s" style="width:110px; margin-right:1rem;" />',$max,esc_attr($cur_max));
        }

        // Dynamic attributes
        if ( ! empty($analysis['attrs']) ) {
            $ranked = [];
            foreach ($analysis['attrs'] as $tax=>$counts) $ranked[$tax]=array_sum($counts);
            arsort($ranked);
            foreach ( array_slice(array_keys($ranked),0,5) as $tax ){
                $label = wc_attribute_label($tax);
                echo '<div style="display:inline-block; margin-left:1rem;">';
                echo '<span style="font-weight:600; margin-right:.5rem;">'.esc_html($label).':</span>';
                $selected = isset($_GET['attr'][$tax]) ? array_map('sanitize_text_field',(array) wp_unslash($_GET['attr'][$tax])) : [];
                foreach ( array_slice($analysis['attrs'][$tax],0,12,true) as $term_slug=>$count ){
                    $checked = in_array($term_slug,$selected,true) ? 'checked' : '';
                    printf('<label style="margin-right:.5rem;"><input type="checkbox" name="attr[%1$s][]" value="%2$s" %3$s /> %4$s</label>',
                        esc_attr($tax),esc_attr($term_slug),$checked,esc_html(self::term_name_by_slug($tax,$term_slug)).' ('.(int)$count.')');
                }
                echo '</div>';
            }
        }

        echo '<button type="submit" class="button" style="margin-left:1rem;">Apply</button>';
        $base = esc_url( remove_query_arg( ['gv_q','gv_brand','gv_cat','in_stock','price_min','price_max','attr','pg'] ) );

        echo ' <a class="button button-secondary" href="'.$base.'">Reset</a>';
        echo '</form>';
    }

    /* ──────────────────────────────────────────────────────────────────── */
    /* Apply GET filters to query                                           */
    /* ──────────────────────────────────────────────────────────────────── */

    private static function apply_request_filters_to_args( &$args, &$tax_query, &$meta_query ) {
        // Keyword
        if ( isset($_GET['gv_q']) && $_GET['gv_q'] !== '' ) {
			$args['s'] = sanitize_text_field( wp_unslash($_GET['gv_q']) ); // alleen in de secundaire query
		}


        // Stock
        if ( isset($_GET['in_stock']) && $_GET['in_stock'] === '1' ) {
            $meta_query[] = ['key'=>'_stock_status','value'=>'instock','compare'=>'='];
        }

        // Price
        $min = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? (float) $_GET['price_min'] : null;
        $max = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? (float) $_GET['price_max'] : null;
        if ( $min !== null || $max !== null ) {
            if ( $min !== null && $max !== null && $min > $max ) { $t=$min; $min=$max; $max=$t; }
            $meta_query[] = $min !== null && $max !== null ? ['key'=>'_price','value'=>[$min,$max],'type'=>'NUMERIC','compare'=>'BETWEEN']
                         : ( $min !== null ? ['key'=>'_price','value'=>$min,'type'=>'NUMERIC','compare'=>'>=']
                                           : ['key'=>'_price','value'=>$max,'type'=>'NUMERIC','compare'=>'<='] );
        }

        // Attributes
        if ( isset($_GET['attr']) && is_array($_GET['attr']) ) {
            foreach ( $_GET['attr'] as $tax=>$slugs ) {
                $tax = sanitize_text_field($tax);
                if ( ! taxonomy_exists($tax) ) continue;
                $terms = array_filter( array_map('sanitize_text_field', (array) wp_unslash($slugs) ) );
                if ( $terms ) $tax_query[] = ['taxonomy'=>$tax,'field'=>'slug','terms'=>$terms,'operator'=>'IN'];
            }
        }

        // Category via GET
        if ( isset($_GET['gv_cat']) && $_GET['gv_cat'] !== '' ) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => [ sanitize_text_field( wp_unslash($_GET['gv_cat']) ) ],
                'operator' => 'IN',
            ];
        }
    }

    /* ──────────────────────────────────────────────────────────────────── */
    /* Shortcode                                                             */
    /* ──────────────────────────────────────────────────────────────────── */

    public static function shortcode_brand_products( $atts ) {
        if ( ! class_exists('WooCommerce') ) return '<p>WooCommerce is required.</p>';

        $atts = shortcode_atts([
            'brand'        => '',
            'brand_in'     => '',
            'per_page'     => self::DEFAULT_PER_PAGE,
            'columns'      => 4,
            'orderby'      => 'date',
            'order'        => 'DESC',
            'paginate'     => 'yes',
            'show_filter'  => 'yes',
            'hide_title'   => 'no',
            'auto_filters' => 'yes',
            'show_brand'   => 'yes',  // NEW: hide brand selector
            // Category options
            'category'      => '',
            'category_in'   => '',
            'show_category' => 'yes',
            'cat_nav'       => 'no',  // set to "links" for compact nav
        ], $atts, 'gv_brand_products' );

        // Allow brand override from GET
        if ( isset($_GET['gv_brand']) && $_GET['gv_brand'] !== '' ) {
            $atts['brand'] = sanitize_text_field( wp_unslash($_GET['gv_brand']) );
        }

        // Start buffer early to avoid leaking into REST responses
        ob_start();

        // Editor/REST placeholder
        if ( defined('REST_REQUEST') && REST_REQUEST ) {
            echo '<div class="gv-brand-products-placeholder">Brand products will render on the front-end.</div>';
            return ob_get_clean();
        }

        // Pagination
        $paged = max(1, get_query_var('paged') ?: ( get_query_var('page') ?: ( isset($_GET['pg']) ? (int) $_GET['pg'] : 1 ) ));

        // Baseline tax query: brand + category
        $tax_query  = self::build_brand_tax_query( $atts );
        $cat_tax    = self::build_category_tax_query( $atts );
        if ( $cat_tax ) $tax_query = array_merge( $tax_query, $cat_tax );

        $meta_query = WC()->query->get_meta_query();

        // Base args
        $args = [
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
            'orderby'             => sanitize_key($atts['orderby']),
            'order'               => ( strtoupper($atts['order']) === 'ASC' ? 'ASC' : 'DESC' ),
            'posts_per_page'      => (int) $atts['per_page'],
            'paged'               => $paged,
        ];

        // Analysis for UI
        $analysis = ['price_min'=>null,'price_max'=>null,'attrs'=>[],'cats'=>[]];
        if ( $atts['auto_filters'] === 'yes' ) {
            $pool_ids = self::get_product_pool_ids( $tax_query );
            $analysis = self::analyze_pool_for_filters( $pool_ids );
        }

        // Render filters UI
        if ( $atts['auto_filters'] === 'yes' || $atts['show_filter'] === 'yes' ) {
            self::render_filters_ui( $atts, $analysis );
        }

        // Apply GET filters
        self::apply_request_filters_to_args( $args, $tax_query, $meta_query );
        if ( $tax_query )  $args['tax_query']  = array_merge(['relation'=>'AND'], $tax_query);
        if ( $meta_query ) $args['meta_query'] = $meta_query;

        // Query + output
        $q = new WP_Query($args);

        if ( $atts['hide_title'] !== 'yes' ) {
            $heading = self::current_brand_heading_from( $atts );
            if ( $heading ) echo '<h2 class="gv-brand-heading" style="margin-top:.5rem;">'.esc_html($heading).'</h2>';
        }

        echo '<div class="woocommerce gv-brand-grid columns-'.(int)$atts['columns'].'">';
        if ( $q->have_posts() ) {
            woocommerce_product_loop_start();
            while ( $q->have_posts() ) { $q->the_post(); wc_get_template_part('content','product'); }
            woocommerce_product_loop_end();
        } else {
            wc_no_products_found();
        }
        echo '</div>';

        if ( $atts['paginate'] === 'yes' && $q->max_num_pages > 1 ) {
            echo '<div class="gv-brand-pagination">';
            echo paginate_links([
                'base'    => esc_url_raw( add_query_arg('pg','%#%') ),
                'current' => max(1,$paged),
                'total'   => $q->max_num_pages,
                'prev_text'=>'&laquo;','next_text'=>'&raquo;',
            ]);
            echo '</div>';
        }

        wp_reset_postdata();
        return ob_get_clean();
    }

    private static function current_brand_heading_from( $atts ) {
        $tax = self::get_brand_taxonomy();
        if ( ! $tax ) return '';
        $b = isset($_GET['gv_brand']) && $_GET['gv_brand']!=='' ? sanitize_text_field( wp_unslash($_GET['gv_brand']) )
            : ($atts['brand'] ?: self::detect_current_brand_slug());
        if ( ! $b ) return '';
        $t = is_numeric($b) ? get_term_by('id',(int)$b,$tax) : get_term_by('slug',$b,$tax);
        return ($t && ! is_wp_error($t)) ? $t->name : '';
    }
}

endif;

/* Register shortcode on init so it’s available before content renders */
add_action('init', function(){
    if ( ! function_exists('shortcode_exists') || ! shortcode_exists('gv_brand_products') ) {
        add_shortcode('gv_brand_products', ['GV_Brand_Products_Page','shortcode_brand_products']);
    }
});


// ===== Snippet #139: PDF Quote - Remove pre-payment needed line =====
// Scope: global | Priority: 10

// Verwijder "A prepayment of the order is required." uit QUOTE-PDFs
add_filter( 'wpo_wcpdf_pdf_html', function( $html, $document_type, $document ) {
    $type = ( is_object($document) && method_exists($document, 'get_type') ) ? $document->get_type() : $document_type;
    if ($type === 'quote') {
        // hele <p> weghalen als die bestaat
        $html = preg_replace('#<p[^>]*>\s*A prepayment of the order is required\.?\s*</p>#i', '', $html);
        // fallback: losse zin weghalen
        $html = preg_replace('#\s*A prepayment of the order is required\.?\s*#i', ' ', $html);
    }
    return $html;
}, 999, 3 );

// Voor oudere/nieuwere pluginversies ook dit filter meenemen:
add_filter( 'wpo_wcpdf_document_html', function( $html, $document_type, $document ) {
    if ($document_type === 'quote') {
        $html = preg_replace('#<p[^>]*>\s*A prepayment of the order is required\.?\s*</p>#i', '', $html);
        $html = preg_replace('#\s*A prepayment of the order is required\.?\s*#i', ' ', $html);
    }
    return $html;
}, 999, 3 );

