<?php 
// TODO: Clean this mess up

// Load the stylesheet
function load_contest_styles() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'contest-styles', $plugin_url . 'css/styles.css' );
}
add_action( 'wp_enqueue_scripts', 'load_contest_styles' );
add_action( 'admin_enqueue_scripts', 'load_contest_styles' );

// Load the necessary scripts for the image uploader.
function load_wp_scripts () {
    wp_enqueue_script('media-upload');
    wp_enqueue_script('thickbox');
    wp_enqueue_script('jquery');
}

function load_wp_styles () {
    wp_enqueue_style('thickbox');
}

add_action('admin_print_scripts', 'load_wp_scripts');
add_action('admin_print_styles', 'load_wp_styles');

function display_uploader() {
    echo "<div class=\"submissions-container\">";
    echo "<h1>My Submission</h1>";
    // Set $args for query to grab all images by the user.
    $args = array(
        "post_type" => "attachment",
        'post_status'    => 'inherit',
        "author" => wp_get_current_user()->ID
    );
    $user_images = new WP_Query($args);
    if ($user_images->have_posts()):
        while ($user_images->have_posts()): 
            $user_images->the_post();
            $approved = get_post_meta(get_the_ID(), "is_approved", true);
            if ($approved === 'true') {
                $status = "<span style='color: green;'>Approved</span>";
            } else if ($approved === 'false') {
                $status = "<span style='color: red;'>Denied</span>";
            } else {
                $status = "<span style='color: yellow;'>Pending</span>";
            }
            $more_info = get_post_meta(get_the_ID(), "more_info", true);
?>
    <div class="submission-img-container">
        <?php echo wp_get_attachment_image(get_the_id()); ?>
    </div>
    <div class="submission-info-container">
    <p>
        <b><?php the_title(); ?></b>
    </p>
    <p>
        <b>Status: </b> <?php echo $status; ?>
    </p>
    <?php if ($more_info): ?>
        <p>
            <b>Additional Information: </b> <?php echo $more_info; ?>
        </p>
    <?php endif; ?>
    <p>
        <a  style="color:red; text-decoration:none;" href="<?php echo get_delete_post_link(); ?>">Delete</a>
    </p>
    </div>
<?php   endwhile;
    else: ?>
<script language="JavaScript">
    jQuery(document).ready(function() {
        jQuery('#upload_image_button').click(function() {
            formfield = jQuery('#upload_image').attr('name');
            tb_show('', 'media-upload.php?type=image&TB_iframe=true');
            return false;
        });
        window.send_to_editor = function(html) {
            imgurl = jQuery('img',html).attr('src');
            jQuery('#upload_image').val(imgurl);
            tb_remove();
            location.reload();
        }
    });
    jQuery(document).bind("DOMNodeRemoved", function(e) {
        if (e.target.id == "TB_window") {
            window.location.reload(false); 
        }
    })
</script>
<tr valign="topa" style="margin-top:50px;">
    <td>Upload Image</td>
    <td><label for="upload_image">
        <input id="upload_image" type="text" size="36" name="upload_image" readonly />
        <input style="cursor: pointer; text-align: center;" id="upload_image_button" type"button" value="Upload Image" />
        <br />Enter a URL or upload an image to submit to the contest.
        </label>
    </td>
</tr>
<?php endif;
echo "</div>";
}

// Add a shortcode to make dropping the submissions in easily.
function display_submissions() {
    $args = array(
        "post_type"    => "attachment",
        'post_status'  => 'inherit',
        'meta_key'     => 'is_approved',
        'meta_value'   => 'true'
    );
    $contest_entries = new WP_Query($args);
    if ($contest_entries->have_posts()): 
        while($contest_entries->have_posts()): $contest_entries->the_post(); ?>
            <div class="submission-img-container">
                <a href="<?php echo get_attachment_link(); ?>"><?php echo wp_get_attachment_image(get_the_id(), 'thumbnail'); ?></a>
            </div>
            <div class="submission-info-container">
                <p>
                    <b>Title: </b> <?php the_title(); ?>
                </p>
                <p>
                    <b>Author: </b> <?php the_author(); ?>
                </p>
            </div>
<?php   endwhile;
    else: ?>
    <p>There are no approved contest entries yet. <a href="/wp-admin/admin.php?page=my-contest-submission">Submit an entry</a></p>
<?php
    endif;
}
add_shortcode( 'contest_submissions', 'display_submissions' );

// Register and include the new admin menu
function register_submissions_menu_page() {
    // Set the values to set up the menu in the admin pages.
    $page_title = "My Contest Submission";
    $menu_title = "My Submission";
    $capability = "upload_files";
    $menu_slug = "my-contest-submission";
    $callback_function = "display_uploader";
    $icon_url = '';
    $position = 30;

    // Pass in the declared values.
    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $callback_function, $icon_url, $position);
}
add_action('admin_menu', 'register_submissions_menu_page');

// Add a new role for the competitors to better manage permissions.
global $wp_roles;
remove_role('competitor');
$result = add_role('competitor', _('Competitor'), array('read' => true, 'upload_files' => true, 'delete_posts' => true));

// Cool function to print out errors to the debug.log file
// located in wp-content/
if (!function_exists('write_log')) {
    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}

//Remove Media Library Tab
function remove_medialibrary_tab($tabs) {
    if ( !current_user_can( 'administrator' ) ) {
        unset($tabs['library']);
        return $tabs;
    } else {
        return $tabs;
    }
}
add_filter('media_upload_tabs','remove_medialibrary_tab');

add_action( 'admin_menu', 'remove_menu_links' );
function remove_menu_links() {
    global $submenu;

    // Remove media menu link for non-admins
    if( !current_user_can('manage_options') )
        remove_menu_page('upload.php');
    
    // Still need to update cap requirements even when hidden
    foreach( $submenu['upload.php'] as $position => $data ) {
        $submenu['upload.php'][$position][1] = 'manage_options';
    }
}

// Add in some more data to manage the visibility of the images on the front-end
function add_form_fields( $form_fields, $post ) {
        $form_fields['is_approved'] = array(
            'label' => 'Approved?',
            'input' => 'html',
            'value' => get_post_meta( $post->ID, 'is_approved', true ),
            'helps' => 'If provided, it will determine if the image is allowed to be counted in the contest or not',
            'html'  => "
                <select name='attachments[{$post->ID}][is_approved]' id='attachments[{$post->ID}][is_approved]'>
                    <option value=''>-- Please select an option --</option>
                    <option value='true' " . (get_post_meta( $post->ID, 'is_approved', true) === 'true' ? " selected" : "") . ">Yes</option>
                    <option value='false' " . (get_post_meta( $post->ID, 'is_approved', true) === 'false' ? " selected" : "") . ">No</option>
                </select>"
        );
        // Give admins the ability to leave some feedback for the submissions.
        $form_fields['more_info'] = array(
            'label' => 'Additional Information',
            'input' => 'textarea',
            'value' => get_post_meta( $post->ID, 'more_info', true ),
            'helps' => 'If provided, this will display a message on the submissions page to the author of the submission only.',
        );
            
        return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'add_form_fields', 10, 2 );

function save_image_data( $post, $attachment ) {
    if( isset( $attachment['is_approved'] ) )
        update_post_meta( $post['ID'], 'is_approved', $attachment['is_approved'] );
    
    if( isset( $attachment['more_info'] ) )
        update_post_meta( $post['ID'], 'more_info', $attachment['more_info'] );

    return $post;
}
add_filter( 'attachment_fields_to_save', 'save_image_data', 10, 2 );