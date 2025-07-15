<?php if ( !empty($result) ): ?>
	
	<?php foreach ($result as $key => $value): ?>
		
		<tr class="item">
		    <th scope="row" class="py-3 ps-4 border-bottom">
		        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
		            <input class="form-check-input checkbox-item" type="checkbox" name="ids[]" value="<?php _e( $value->ids )?>">
		        </div>
		    </th>
		    <td class="border-bottom">
		    	<div class="d-flex align-items-center py-3">
		    		<div class="symbol symbol-50px overflow-hidden me-3">
						<a href="<?php _e( get_module_url('index/update/'.$value->ids) )?>" data-remove-other-active="true" data-active="bg-light-primary" data-result="html" data-content="main-wrapper" data-history="<?php _e( get_module_url('index/update/'.$value->ids) )?>">
							<div class="symbol-label b-r-10">
								<div style="background-image: url('<?php _e($value->img)?>'); background-size: cover; background-position: center;" class="w-100 h-125 rounded mb-2 b-r-10"></div>
							</div>
						</a>
					</div>
					<div class="d-flex flex-column mw-500">
						<a href="<?php _e( get_module_url('index/update/'.$value->ids) )?>" class="text-gray-800 text-hover-primary fw-6 text-over" data-remove-other-active="true" data-active="bg-light-primary" data-result="html" data-content="main-wrapper" data-history="<?php _e( get_module_url('index/update/'.$value->ids) )?>" data-call-after="Core.calendar();"><?php _ec( $value->title )?></a>
			        	<span class="text-gray-400 text-over"><?php _ec( $value->desc )?></span>
					</div>
		    	</div>
		    </td>
		    <td class="border-bottom">
		    	<?php
		    		switch ($value->status) {
		    			case 1:
		    				$status = '<span class="badge badge-light-success fw-4 fs-12 p-6">'.__("Active").'</span>';
		    				break;

		    			default:
		    				$status = '<span class="badge badge-light-danger fw-4 fs-12 p-6">'.__("Banned").'</span>';
		    				break;
		    		}

		    	?>

		    	<?php _ec( $status )?>
		    </td>
		    <td class="border-bottom"><?php _e( datetime_show( $value->created ) )?></td>
		</tr>

	<?php endforeach ?>

<?php endif ?>
