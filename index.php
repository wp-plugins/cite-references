<?php
/*
  Plugin Name: Citing Option
  Plugin URI: http://dejanseo.com.au
  Description: Citing Option
  Author: Ivan M
  Version: 0.1.2
  Author URI: http://dejanseo.com.au
 */
/* Fire our meta box setup function on the post editor screen. */
add_action( 'load-post.php', 'cite_post_meta_boxes_setup' );
add_action( 'load-post-new.php', 'cite_post_meta_boxes_setup' );


/* Meta box setup function. */
function cite_post_meta_boxes_setup() {

    /* Add meta boxes on the 'add_meta_boxes' hook. */
    add_action( 'add_meta_boxes', 'cite_add_post_boxes' );

    /* Save post meta on the 'save_post' hook. */
    add_action( 'save_post', 'cite_save_post_meta', 10, 2 );
}


/* Create one or more meta boxes to be displayed on the post editor screen. */
function cite_add_post_boxes() {

    add_meta_box(
        'cite-option',          // Unique ID
        esc_html__( 'Citing option', 'example' ),      // Title
        'cite_meta_box',     // Callback function
        'post',                 // Admin page (or post type)
        'side',                 // Context
        'default'                   // Priority
    );
}

/* Display the post meta box. */
function cite_meta_box( $object, $box ) { ?>

    <?php wp_nonce_field( basename( __FILE__ ), 'cite_option_nonce' ); ?>

    <p>
        <label for="cite-option"><?php _e( "Show citing option?", 'example' ); ?></label>
        <br />

               
            <?
            
            if(esc_attr( get_post_meta( $object->ID, 'cite_option', true ) )=="yes"){
                ?>
                   <input type="radio" name="cite-option" id="cite-option" value="yes" checked> Yes |
                   <input type="radio" name="cite-option" id="cite-option" value="no"> No

                <?
            }
            else if(esc_attr( get_post_meta( $object->ID, 'cite_option', true ) )=="no") {
                ?>
                    <input type="radio" name="cite-option" id="cite-option" value="yes">Yes</option>
                    <input type="radio" name="cite-option" id="cite-option" value="no" checked>No</option>
                <?
            }
            else{
                ?>
                    <input type="radio" name="cite-option" id="cite-option" value="yes" checked>Yes</option>
                    <input type="radio" name="cite-option" id="cite-option" value="no">No</option>
                <?
            }
            ?>
    </p>
<?php }


/* Save the meta box's post metadata. */
function cite_save_post_meta( $post_id, $post ) {

    /* Verify the nonce before proceeding. */
    if ( !isset( $_POST['cite_option_nonce'] ) || !wp_verify_nonce( $_POST['cite_option_nonce'], basename( __FILE__ ) ) )
        return $post_id;

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );

    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return $post_id;

    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST['cite-option'] ) ? sanitize_html_class( $_POST['cite-option'] ) : '' );

    /* Get the meta key. */
    $meta_key = 'cite_option';

    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );

    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
        add_post_meta( $post_id, $meta_key, $new_meta_value, true );

    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
        update_post_meta( $post_id, $meta_key, $new_meta_value );

    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
        delete_post_meta( $post_id, $meta_key, $meta_value );
}


/* Filter the post class hook with our custom post class function. */
add_filter( 'post_class', 'cite_option' );

function cite_option( $classes ) {

    /* Get the current post ID. */
    $post_id = get_the_ID();

    /* If we have a post ID, proceed. */
    if ( !empty( $post_id ) ) {

        /* Get the custom post class. */
        $post_class = get_post_meta( $post_id, 'cite_option', true );

        /* If a post class was input, sanitize it and add it to the post class array. */
        if ( !empty( $post_class ) )
            $classes[] = sanitize_html_class( $post_class );
    }

    return $classes;
}


add_filter('the_content', 'add_cite_to_footer_the_content');
function add_cite_to_footer_the_content($content = ''){
    
	
        $post_id = get_the_ID();
        if ( !empty( $post_id ) ) {
            
            $post_class = get_post_meta( $post_id, 'cite_option', true );
            $author_fname = get_the_author_meta('first_name');
            $author_lname = get_the_author_meta('last_name');
            
            $blog_title = get_bloginfo('name');
            
            //echo $content;
            
            if($post_class=="yes" AND $author_fname!="" AND $author_lname!=""){
                
                $post_data = get_post($post_id, ARRAY_A);
                
                $post_author = $post_data['post_author'];
                $post_title  = $post_data['post_title'];
                $post_date  = $post_data['post_date'];
                $cur_date = date("M d, Y");
                $permalink = get_permalink( $post_id );
                
                return $content.'<b><div style="border:1px solid #000000; padding:5px;">Cite this article:</b><br>'.$author_lname.'  '.$author_fname[0].' ('.$post_date.'). '.$post_title.'. <i>'.$blog_title.'</i>. Retrieved: '.$cur_date.', from '.$permalink.'<br></div>';
            }
            else return $content;
        }

}

?>
