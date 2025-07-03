<?php if (get_option("facebook_profile_cookie_status", 1) || get_option("facebook_profile_official_status", 1)): ?>
<a href="<?php _e( base_url( $config['id']."/oauth" ) )?>" class="btn btn-outline btn-outline-dashed me-2 mb-2 text-start list-btn-add-account">
    <i class="<?php _e( $config['icon'] )?>" style="color: <?php _e( $config['color'] )?>;" ></i> 
    <?php _e("Add Facebook profile")?>
</a>
<?php endif ?>