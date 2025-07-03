<div class="container mw-700 py-5">
    <div class="w-100 m-r-0 d-flex align-items-center justify-content-between">
        <h3 class="fw-bolder m-b-0 text-gray-800"><i class="<?php _ec( $config['icon'] )?>" style="color: <?php _ec( $config['color'] )?>;"></i> <?php _e("Instagram OAuth")?></h3>
    </div>

    <?php if ( get_option("instagram_official_status", 1) && get_option("instagram_unofficial_status", 1) ): ?>
    <ul class="nav nav-pills mb-5 m-t-40 bg-light-dark rounded" id="pills-tab">
        <li class="nav-item me-0 wp-50">
            <button class="nav-link bg-active-dark text-active-white text-gray-700 px-4 py-3 w-100 <?php _ec( post("type")==""?"active show":"" )?>" data-bs-toggle="pill" data-bs-target="#oauth_tab_1" type="button" role="tab"><?php _e("Official")?></button>
        </li>
        <li class="nav-item me-0 wp-50">
            <button class="nav-link bg-active-dark text-active-white text-gray-700 px-4 py-3 w-100 <?php _ec( post("type")=="unofficial"?"active show":"" )?>" data-bs-toggle="pill" data-bs-target="#oauth_tab_2" type="button" role="tab"><?php _e("Username & password")?></button>
        </li>
    </ul>
    <?php endif ?>

    <div class="tab-content" id="tab_plans">
        <?php if ( get_option("instagram_official_status", 1) ): ?>
        <div class="tab-pane fade <?php _ec( post("type")==""?"active show":"" )?>  mt-5" id="oauth_tab_1" role="tabpanel">
            <a class="btn w-100 me-2 text-white" style="background-color: <?php _ec( $config['color'] )?>;" href="<?php _ec($oauth_link)?>">
                <i class="<?php _ec( $config['icon'] )?>"></i> 
                <?php _e("Connect with Instagram")?>
            </a>
        </div>
        <?php endif ?>

        <?php if ( get_option("instagram_unofficial_status", 1) ): ?>
        <div class="tab-pane fade <?php _ec( (!get_option("instagram_official_status", 1) || post("type")=="unofficial")?"active show mt-5":"" )?> " id="oauth_tab_2" role="tabpanel">
            <form class="actionForm ig_unofficial_login_form" action="<?php _ec( get_module_url("oauth_unofficial") )?>" method="POST" data-redirect="<?php _ec( get_module_url("index/unofficial") )?>" data-call-after="Instagram_profiles.Checkpoint(result);">
                <div class="card b-r-10">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="ig_username" class="form-label"><?php _e('Instagram username')?></label>
                            <input type="text" class="form-control form-control-solid" id="ig_username" name="ig_username">
                        </div>
                        <div class="mb-3">
                            <label for="ig_password" class="form-label"><?php _e('Instagram password')?></label>
                            <input type="password" class="form-control form-control-solid" id="ig_password" name="ig_password">
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a class="btn btn-light btn-active-light-primary me-2" href="<?php _ec( base_url("account_manager") )?>"><?php _e("Discard")?></a>
                        <button type="submit" class="btn btn-primary"><?php _e("Submit")?></button>
                    </div>
                </div>
            </form>  

            <form class="actionForm ig_unofficial_confirm_form d-none" action="<?php _ec( get_module_url("confirm_security_code") )?>" method="POST" data-redirect="<?php _ec( get_module_url("index/unofficial") )?>" data-call-after="Instagram_profiles.LoginPass(result);">
                <div class="card b-r-10">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="ig_security_code" class="form-label"><?php _e('Security code')?></label>
                            <input type="hidden" class="form-control form-control-solid" id="ig_type_1" name="ig_type">
                            <input type="hidden" class="form-control form-control-solid" id="ig_api_path" name="ig_api_path">
                            <input type="text" class="form-control form-control-solid" id="ig_security_code" name="ig_security_code">
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a class="btn btn-light btn-active-light-primary me-2" href="<?php _ec( base_url("account_manager") )?>"><?php _e("Discard")?></a>
                        <button type="submit" class="btn btn-primary"><?php _e("Submit")?></button>
                    </div>
                </div>
            </form>   

            <form class="actionForm ig_unofficial_two_factor_confirm_form d-none" action="<?php _ec( get_module_url("confirm_security_code") )?>" method="POST" data-redirect="<?php _ec( get_module_url("index/unofficial") )?>" data-call-after="Instagram_profiles.LoginPass(result);">
                <div class="card b-r-10">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="ig_security_code" class="form-label"><?php _e('Security code')?></label>
                            <input type="hidden" class="form-control form-control-solid" id="ig_type_2" name="ig_type">
                            <input type="hidden" class="form-control form-control-solid" id="ig_method" name="ig_method">
                            <input type="hidden" class="form-control form-control-solid" id="ig_identifier" name="ig_identifier">
                            <input type="text" class="form-control form-control-solid" id="ig_security_code" name="ig_security_code">
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a class="btn btn-light btn-active-light-primary me-2" href="<?php _ec( base_url("account_manager") )?>"><?php _e("Discard")?></a>
                        <button type="submit" class="btn btn-primary"><?php _e("Submit")?></button>
                    </div>
                </div>
            </form>      
        </div>
        <?php endif ?>
    </div>
</div>