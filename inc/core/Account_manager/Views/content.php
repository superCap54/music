<div class="container">
    <div class="row no-gutters px-2 py-5 m-auto">
        <div class="sub-header">
            <?php if( isset($config) ){?>
                <h1 class="d-flex fw-bold my-0 fs-20 mb-5"><i class="<?php _ec( $config['icon'] )?> pe-3" style="color: <?php _ec( $config['color'] )?>;"></i> <?php _e( $config['name'] )?></h1>
            <?php }?>
        </div>

        <?php if( !empty($block_accounts) ){?>
            <?php foreach ($block_accounts as $key => $value): ?>
                <?php _ec( $value['data']['content'] )?>
            <?php endforeach ?>
        <?php }?>

        <div class="col-custom">
            <div class="card mb-4">
                <div class="card-header border-0 pt-3">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark"><i class="fab fa-google-drive" style="color: #36d633;"></i> Google drive</span>
                    </h3>
                </div>
                <div class="card-body pt-3 check-wrap-all">
                    <?php if ($google_drive_status['is_connected']): ?>
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-check-circle me-2"></i> Google Drive 账户已连接
                        </div>
                        <a href="<?= get_module_url('google_logout') ?>" class="btn btn-sm btn-danger google-logout-btn" onclick="return confirm('确定要退出Google Drive吗？您将需要重新授权才能再次使用Google Drive功能')">
                            <i class="fas fa-sign-out-alt me-1"></i> 退出登录
                        </a>
                    <?php else: ?>
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i> 您尚未连接 Google Drive 账户
                        </div>
                        <div class="text-center py-4">
                            <a href="http://localhost/account_manager/google_oauth" class="btn btn-primary btn-lg">
                                <i class="fab fa-google-drive me-2"></i> 连接 Google Drive 账户
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
