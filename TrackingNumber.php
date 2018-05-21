<?php

namespace Code;

class TrackingNumber
{
    //region Singleton
    /** @var Woocommerce */
    private static $instance;

    /** @return Woocommerce */
    public static function getInstance(){
        if (TrackingNumber::$instance == null)
            TrackingNumber::$instance = new TrackingNumber();
        return TrackingNumber::$instance;
    }

    /** @return Woocommerce */
    private function __construct(){

    }
    //endregion

    public function init(){
        //adds meta box in order overview page
        add_action('add_meta_boxes', array(&$this, 'add_order_tracking_meta_box'));
        // Function to handle ajax request.
        add_action('wp_ajax_tracking_update_order', array(&$this, 'tracking_update_order'));
        add_action('wp_ajax_nopriv_tracking_update_carriers', array(&$this, 'tracking_update_order'));
    }

    /**
     * Function to add order tracking details to the order overview page
     *
     */
    function add_order_tracking_meta_box(){
            add_meta_box('woocommerce-tracking-number',
                'Tracking Information',
                array(&$this, 'tracking_meta_box_view'),
                'shop_order',
                'side',
                'high');
    }

    /**
     * Function to display order tracking form on order overview page
     *
     */
    function tracking_meta_box_view(){
        global $post, $wpdb;
        echo '<p class="form-field">';
        woocommerce_wp_text_input(array(
            'id' => 'tracking_number',
            'label' => 'Tracking Number',
            'placeholder' => '',
            'description' => 'Order\'s Tracking Number',
            'class' => '',
            'value' => get_post_meta($post->ID, '_tracking_number', true),
        ));
        $this->admin_tracking_display($post->ID);
    }


    /**
     * Function to display shipping tracking details tracking button.
     * @param $order_id
     */
    function admin_tracking_display($order_id){

        echo '<a id="update-order-button" class="button button-primary" href="#" style="margin-left: 5px;font-size: 12px;padding:0 7px 1px;">Save Tracking Number</a>';
        echo '</p>';
        $inline = '
                <p id="inline-tracker"></p>                
                <script>
                jQuery(document).ready(function(){
                    jQuery("#update-order-button").click(function(){
                     //call the function!
                      var track_id = jQuery("#tracking_number").val();
                      var order_id="'.$order_id.'";
                      if(track_id==""){
                       jQuery("#tracking_number").css("border","1px solid #a00");
                       return false;
                      }else{
                        jQuery("#tracking_number").css("border","1px solid #ccc");
                      }                     
                      jQuery.ajax({
                      url: "'.admin_url( 'admin-ajax.php' ).'?ajax=true&track_id="+track_id+"&order_id="+order_id+"&action=tracking_update_order&admin=true",
                        success: function(result){
                            jQuery("#inline-tracker").text(result);
                        },
                        error: function(error){
                            jQuery("#inline-tracker").text("Error: " + error);
                            jQuery("#tracking_number").val("").css("border","1px solid #a00");
                        }    
                      });                    
                    });
                });          
                </script>
            ';
        echo $inline;
    }

    /**
     * Function to save tracking details
     *
     * @since 1.0.0
     */
    function tracking_update_order(){
        if (array_key_exists('track_id', $_REQUEST)){
            $track_id = $_REQUEST['track_id'];
            $order_id = $_REQUEST['order_id'];
            update_post_meta($order_id, '_tracking_number', $track_id);
            echo 'Tracking Number saved.';
        } else {
            echo 'Please enter Tracking number.';
        }
        die;
    }

}