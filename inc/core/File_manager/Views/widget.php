<div class="card border file-manager" data-select-multi="<?php _ec($select_multi)?>">
	<div class="card-header p-r-20 p-l-20">
		<h3 class="card-title"><?php _e("Media")?></h3>
        <div class="card-toolbar">
	        <div class="ps-3 d-lg-none d-md-none d-sm-block d-xs-block d-block">
        		<button type="button" class="btn btn-sm btn-light-danger btn-close-filemanager w-35 h-35 b-r-40 d-flex justify-content-center align-items-center"><i class="fad fa-times pe-0"></i></button>
			</div>
        </div>
	</div>
	<div class="card-body p-0 fm-widget bg-light" data-loading="false" data-result="html" data-result="fm-selected-media .items" data-select-multi="1">
		<div class="fm-content flex-grow-1">
			<div class="fm-progress-bar bg-primary"></div>
			<div class="input-group mb-2 d-none">
                <input type="text" class="form-control ajax-filter fs-12 fw-4 fm-input-folder" name="folder">
                <input type="hidden" class="ajax-filter fm-input-filter" name="filter" value="<?php _ec($type)?>">
            </div>
			<div class="fm-list row px-2 py-4 ajax-load-scroll m-l-0 m-r-0 align-content-start" style="height: 669px;" data-url="<?php _e( base_url("file_manager/load_files/widget") )?>" data-scroll="ajax-load-scroll" data-call-after="File_manager.lazy(); File_manager.checkSelected();">
				<div class="fm-empty text-center fs-90 text-muted h-100 d-flex flex-column align-items-center justify-content-center">
                    <img class="mh-190 mb-4" alt="" src="<?php _e( get_theme_url() ) ?>Assets/img/empty2.png">
				</div>
			</div>
			<div class="ajax-loading text-center bg-primary"></div>
		</div>
	</div>
</div>