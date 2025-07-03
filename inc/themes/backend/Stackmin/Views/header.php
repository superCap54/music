<div class="header bg-white align-items-stretch">
    <div class="container-fluid d-flex align-items-stretch justify-content-between h-100">
        <div class="d-flex justify-content-between flex-lg-grow-1">
            <div class="d-flex align-items-stretch ms-2 ms-md-0 ms-lg-0">
                <div class="d-flex align-items-stretch ms-2 ms-md-0 ms-lg-0">
                    <div class="d-flex align-items-center">
                        <div class="d-lg-none d-md-none d-sm-block d-xs-block d-block">
                            <a href="javascript:void(0);" class="btn btn-light-primary px-3 btn-open-sidebar">
                                <i class="fad fa-bars p-r-0 fs-20"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-stretch ms-2 ms-md-0 ms-lg-0">
                    <div class="d-flex align-items-center">
                        <div class="d-lg-none d-md-none d-sm-none d-none">
                            <a href="javascript:void(0);" class="btn btn-light-primary p-l-17 p-r-17 btn-open-sub-sidebar">
                                <i class="fad fa-chevron-right pe-0"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php $platforms = get_platfrom(); ?>
                <?php if (count($platforms) > 1 ): ?>
                <div class="d-flex align-items-stretch ms-2 ms-md-0 ms-lg-0">
                    <div class="d-flex align-items-center">
                        <div class="d-lg-block d-md-block d-sm-block d-block">
                            <form class="auto-submit actionForm" action="<?php _ec( base_url("home/change_platform") )?>" data-redirect="<?php _ec( current_url() )?>">
                                
                                <?php if ( $platforms ): ?>
                                <select class="form-select auto-submit b-r-10 form-select-solid" name="platform" data-control="select2">
                                    <?php foreach ($platforms as $key => $value): ?>
                                        <option data-icon="<?php _ec( $value->icon )?>" data-icon-color="<?php _ec( $value->color )?>" value="<?php _ec( $value->platform_id )?>" <?php _ec( $value->platform_id == PLATFORM?"selected":"" )?>><?php _e( $value->name )?></option>
                                    <?php endforeach ?>
                                </select>
                                <?php endif ?>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif ?>
            </div>
        </div>
        <div class="d-flex align-items-stretch flex-shrink-0 me-1 me-lg-3">
            <?php
                $request = \Config\Services::request();
                $topbars = $request->topbars;
            ?>

            <?php if ( !empty($topbars) ): ?>
                
                <?php foreach ($topbars as $key => $value): ?>
                    <?php _ec( $value['topbar'] )?>
                <?php endforeach ?>

            <?php endif ?>
        </div>
    </div>
</div>