<style>
    .mb-10 {
        margin-bottom: 2rem;
    }
</style>
<form class="actionForm" action="<?php _e(get_module_url("auth_music/" . get_data($user, "ids"))) ?>"
      data-call-success="Core.click('users-list');" method="POST">
    <div class="container my-5">
        <div class="mw-750">
            <div class="card card-flush">
                <div class="card-header mt-6">
                    <div class="card-title w-100 m-r-0">
                        <div class="d-flex">
                            <h3 class="fw-bolder">
                                <i class="fad fa-user-music text-primary"></i>
                                <?php _e('Music Licensing for: ') ?>
                                <span class="text-primary"><?php _e(get_data($user, "username")) ?></span>
                            </h3>
                        </div>
                        <div class="d-flex ms-auto">
                            <a href="<?php _e(get_module_url('index/list')) ?>" class="btn btn-light-primary actionItem"
                               data-remove-other-active="true" data-active="bg-light-primary" data-result="html"
                               data-content="main-wrapper" data-history="<?php _e(get_module_url('index/list')) ?>">
                                <i class="fad fa-chevron-left"></i> <?php _e('Back') ?>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- 用户信息概览 -->
                    <div class="d-flex align-items-center mb-10 p-5 bg-light-primary rounded">
                        <div class="d-flex flex-column">
                            <span class="text-muted mt-2">
                                <i class="fad fa-music"></i>
                                <?php _e('Currently licensed: ') ?>
                                <span class="badge badge-light-primary" style="font-size:14px;"><?php _e($licensed_count) ?></span>
                            </span>
                        </div>
                    </div>

                    <!-- 授权操作区域 -->
                    <div class="mb-10">
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <div class="input-group w-250px">
                                <input type="text" class="form-control" id="musicSearch"
                                       placeholder="<?php _e('Search music...') ?>">
                                <button class="btn btn-light" type="button" id="clearSearch">
                                    <i class="fad fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 音乐列表 -->
                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-200 align-middle">
                                <thead>
                                <tr class="text-gray-800 border-bottom-2 border-gray-200">
                                    <th width="50px" class="text-center">
                                        <div class="form-check form-check-sm form-check-custom">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th><?php _e('Music Info') ?></th>
                                    <th><?php _e('Artist') ?></th>
                                    <th><?php _e('Duration') ?></th>
                                    <th><?php _e('License Status') ?></th>
                                    <th><?php _e('Option') ?></th> <!-- 新增Option列 -->
                                </tr>
                                </thead>
                                <tbody id="musicList">
                                <?php if (!empty($musics)): ?>
                                    <?php foreach ($musics as $music): ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php if (!$music['licensed']): ?> <!-- 只有未授权的才显示选择框 -->
                                                    <div class="form-check form-check-sm form-check-custom">
                                                        <input class="form-check-input music-checkbox"
                                                               type="checkbox"
                                                               name="music_ids[]"
                                                               value="<?php _e($music['id']) ?>"
                                                        >
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-50px me-5">
                                                        <img src="<?php _e($music['cover_url']) ?>"
                                                             alt="<?php _e($music['title']) ?>"
                                                             class="h-50 align-self-center">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-dark fw-bold"><?php _e($music['title']) ?></span>
                                                        <span class="text-muted"><?php _e($music['isrc']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php _e($music['artist']) ?></td>
                                            <td><?php _e(gmdate("i:s", $music['duration'])) ?></td>
                                            <td>
                                                <?php if ($music['licensed'] && !empty($music['license_data']->expiry_date)): ?>
                                                    <span class="badge badge-success" style="font-size: 14px;">
                        <?php _e('Licensed: ') ?>
                        <?php _e(date('Y-m-d', (int)$music['license_data']->expiry_date)) ?>
                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-dark"
                                                          style="font-size: 14px;"><?php _e('Not Licensed') ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($music['licensed']): ?>
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger revoke-license"
                                                            data-music-id="<?php _e($music['id']) ?>"
                                                            data-user-id="<?php _e(get_data($user, 'id')) ?>">
                                                        <?php _e('Revoke') ?>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6"
                                            class="text-center text-muted py-10"><?php _e('No music available in library') ?></td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- 分页 -->
                        <nav class="m-t-50 m-b-50 ajax-pagination m-auto text-center">
                            <?php if ($datatable['total_pages'] > 1): ?>
                                <ul class="pagination">
                                    <?php if ($datatable['current_page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="<?php _ec(base_url("users/index/music/") . '/' . get_data($user, "ids") . '/' . ($datatable['current_page'] - 1)) ?>">
                                                &laquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $datatable['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo ($i == $datatable['current_page']) ? 'active' : '' ?>">
                                            <a class="page-link"
                                               href="<?php _ec(base_url("users/index/music/") . '/' . get_data($user, 'ids') . '/' . $i) ?>">
                                                <?php _e($i) ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($datatable['current_page'] < $datatable['total_pages']): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="<?php _ec(base_url("users/index/music/") . '/' . get_data($user, 'ids') . '/' . ($datatable['current_page'] + 1)) ?>">
                                                &raquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </nav>
                    </div>
                    <!-- 授权选项 -->
                    <div class="card bg-light rounded p-5 mb-10">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-5">
                                    <label class="form-label"><?php _e('Expiration Date') ?></label>
                                    <input type="date" name="expiry_date" class="form-control"
                                           value="<?php _e(date('Y-m-d', strtotime('+1 year'))) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><?php _e('Save') ?></button>
                </div>
            </div>
        </div>

    </div>
</form>


<script>
    $(document).ready(function () {
        // 全选/取消全选 - 只针对可见的未授权音乐
        $('#selectAll').change(function () {
            var isChecked = $(this).prop('checked');
            $('#musicList tr:visible').each(function () {
                var checkbox = $(this).find('.music-checkbox');
                if (checkbox.length) {
                    checkbox.prop('checked', isChecked);
                }
            });
        });

        // 实时搜索
        $('#musicSearch').on('keyup', function () {
            var value = $(this).val().toLowerCase();
            $('#musicList tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // 清空搜索
        $('#clearSearch').click(function () {
            $('#musicSearch').val('');
            $('#musicList tr').show();
        });

        $(document).on('click', '.revoke-license', function () {
            var musicId = $(this).data('music-id');
            var userId = $(this).data('user-id');

            $.ajax({
                url: '<?php _e(get_module_url("revoke_license")) ?>',
                type: 'POST',
                data: {
                    music_id: musicId,
                    user_id: userId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {
                        location.reload();
                    } else {
                        Core.notify('error', response.message);
                    }
                },
                error: function () {
                    Core.notify('error', '<?php _e("An error occurred, please try again") ?>');
                }
            });
        }); // 这里之前缺少了这个闭合
    });
</script>