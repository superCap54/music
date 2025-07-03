<form class="actionForm" action="<?php _ec( base_url("facebook_post/save_facebook_story_configure") ) ?>" method="POST">
	<div class="card mb-4">
		<div class="card-header">
			<div class="card-title">
				<span class="me-2"><i class="<?php _e( $config['icon'] )?> me-2" style="color: <?php _e( $config['color'] )?>"></i> <?php _e( "Facebook Stories" )?></span>
			</div>
		</div>
		<div class="card-body">
            <div class="mb-3">
                <label for="fb_story_bg" class="form-label"><?php _e('Story Background')?></label>
                <input type="text" class="form-control form-control-solid input-color" id="fb_story_bg" name="fb_story_bg" value="<?php _e( get_team_data("fb_story_bg", "#636e72") )?>">
            </div>
            <div class="mb-3">
                <label for="fb_story_title_bg" class="form-label"><?php _e('Title Background')?></label>
                <input type="text" class="form-control form-control-solid input-color" id="fb_story_title_bg" name="fb_story_title_bg" value="<?php _e( get_team_data("fb_story_title_bg", "#000000") )?>">
            </div>
            <div class="mb-3">
                <label for="fb_story_bg_opacity" class="form-label"><?php _e('Title Background Opacity')?></label>
                <input type="int" class="form-control form-control-solid" id="fb_story_bg_opacity" name="fb_story_bg_opacity" value="<?php _e( get_team_data("fb_story_bg_opacity", 30) )?>">
            </div>
            <div class="mb-3">
                <label for="fb_story_title_color" class="form-label"><?php _e('Title Color')?></label>
                <input type="text" class="form-control form-control-solid input-color" id="fb_story_title_color" name="fb_story_title_color" value="<?php _e( get_team_data("fb_story_title_color", "#FFFFFF") )?>">
            </div>
            <div class="mb-3">
                <label for="fb_story_title_top" class="form-label"><?php _e('Title Top')?></label>
                <input type="int" class="form-control form-control-solid" id="fb_story_title_top" name="fb_story_title_top" value="<?php _e( get_team_data("fb_story_title_top", 125) )?>">
            </div>
            <div class="mb-3">
                <label for="fb_story_title_left" class="form-label"><?php _e('Title Left')?></label>
                <input type="int" class="form-control form-control-solid" id="fb_story_title_left" name="fb_story_title_left" value="<?php _e( get_team_data("fb_story_title_left", 30) )?>">
            </div>
            <div class="mb-3">
                <label for="fb_story_title_width" class="form-label"><?php _e('Title Width')?></label>
                <input type="int" class="form-control form-control-solid" id="fb_story_title_width" name="fb_story_title_width" value="<?php _e( get_team_data("fb_story_title_width", 660) )?>">
            </div>
            <div class="mb-3">
                <label  class="form-label"><?php _e('Title Font Family')?></label>
                <select class="form-control form-control-solid" name="fb_story_title_font_family">
                    <option value="arial" <?php _ec( (get_team_data("fb_story_title_font_family", "notosans") == "arial" )?"selected='true'":"" ) ?>><?php _e("Arial")?></option>
                    <option value="story" <?php _ec( (get_team_data("fb_story_title_font_family", "notosans") == "story" )?"selected='true'":"" ) ?>><?php _e("Story")?></option>
                    <option value="opensans" <?php _ec( (get_team_data("fb_story_title_font_family", "notosans") == "opensans" )?"selected='true'":"" ) ?>><?php _e("Open Sans")?></option>
                    <option value="notosans" <?php _ec( (get_team_data("fb_story_title_font_family", "notosans") == "notosans" )?"selected='true'":"" ) ?>><?php _e("Noto Sans")?></option>
                </select>
            </div>
            <div class="mb-3">
                <label for="fb_story_title_font_size" class="form-label"><?php _e('Title Font Size')?></label>
                <input type="int" class="form-control form-control-solid" id="fb_story_title_font_size" name="fb_story_title_font_size" value="<?php _e( get_team_data("fb_story_title_font_size", 30) )?>">
            </div>
            <div class="mb-4">
                <label class="form-label"><?php _e("Text direction")?></label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fb_story_title_text_direction" <?php _ec( (get_team_data("fb_story_title_text_direction", 1) == 1 || get_team_data("fb_story_title_text_direction", 1) == "")?"checked='true'":"" ) ?> id="fb_story_title_text_direction_1" value="1">
                        <label class="form-check-label" for="fb_story_title_text_direction_1"><?php _e('LTR')?></label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fb_story_title_text_direction" <?php _ec( (get_team_data("fb_story_title_text_direction", 1) == 2 )?"checked='true'":"" ) ?> id="fb_story_title_text_direction_2" value="2">
                        <label class="form-check-label" for="fb_story_title_text_direction_2"><?php _e('RTL')?></label>
                    </div>
                </div>
            </div>
        </div>
      	<div class="card-footer d-flex justify-content-end">
      		<button class="btn btn-primary" data-bs-dismiss="modal"><?php _e("Submit")?></button>
      	</div>
	</div>
</form>