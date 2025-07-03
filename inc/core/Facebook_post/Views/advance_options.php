<div class="mb-0">
    <label for="website_description" class="form-label"><?php _e("Post type")?></label>
    <div class="row">
        <div class="col-12">
            <div class="form-check form-check-inline mb-2 d-flex">
                <input class="form-check-input miw-23" type="radio" name="advance_options[fb_post_type]" id="fb_default" value="default" checked>
                <label class="form-check-label" for="fb_default">
                    <?php _e("Media/Link/Text")?><br/>
                    <p class="fs-10"><?php _e("Support all login method")?></p>
                </label>
            </div>
        </div>
        <?php if (get_option("facebook_profile_cookie_status", 1) || get_option("facebook_page_cookie_status", 1) || get_option("facebook_group_cookie_status", 1)): ?>
        <div class="col-12">
            <div class="form-check form-check-inline mb-2 d-flex">
                <input class="form-check-input miw-23" type="radio" name="advance_options[fb_post_type]" id="fb_story" value="story">
                <label class="form-check-label" for="fb_story">
                    <?php _e("Stories")?><br/>
                    <p class="fs-10"><?php _e("Support post to pages and profiles with login method cookie")?></p>
                </label>
            </div>
        </div>
        <?php endif ?>
        <div class="col-12">
            <div class="form-check form-check-inline mb-2 d-flex">
                <input class="form-check-input miw-23" type="radio" name="advance_options[fb_post_type]" id="fb_reel" value="reels">
                <label class="form-check-label" for="fb_reel">
                    <?php _e("Reels")?><br/>
                    <p class="fs-10"><?php _e("Support post to pages with login method official")?></p>
            </label>
            </div>
        </div>
    </div>
</div>
<?php if (get_option("facebook_profile_cookie_status", 1) || get_option("facebook_page_cookie_status", 1) || get_option("facebook_group_cookie_status", 1)): ?>
<div class="mt-3">
    <label for="fb_story_link" class="form-label"><?php _e("Story link")?></label>
    <div class="input-group input-group-solid bg-white border">
        <input type="text" class="form-control" name="advance_options[fb_story_link]" placeholder="Enter link">
        <span class="input-group-text"><i class="fal fa-search-location fs-18"></i></span>
    </div>
</div>
<?php endif ?>