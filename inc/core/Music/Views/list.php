<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center">
        <div class="bd-search position-relative me-auto">
            <h1><i class="<?php _e( $config['icon'] )?>" style="color: <?php _e( $config['color'] )?>;"></i> <?php _e( $config['name'] )?></h1>
        </div>
        <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-file-import me-2"></i><?php _e("Import Music")?>
        </button>
        <a href="/music/import_tsv_index" class="btn btn-outline-secondary me-2">
            <i class="fas fa-file-import me-2"></i><?php _e("Import DistroKid Data")?>
        </a>
<!--        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">-->
<!--            <i class="fas fa-upload me-2"></i>--><?php //_e("Upload Music")?>
<!--        </button>-->
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
        <?php foreach ($music_list as $item): ?>
        <div style="box-shadow: 5px 5px 5px rgba(0,0,0,0.05)" class="group relative bg-[rgba(245,245,245,0.8)] backdrop-blur-sm border border-[rgba(255,255,255,0.05)] rounded-lg overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:border-[rgba(0,180,255,0.2)] hover:shadow-[0_0_15px_rgba(0,180,255,0.2)]">
            <div class="relative aspect-square">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[rgba(0,0,0,0.8)] z-10"></div>
                <img src="<?php echo $item['cover_url']; ?>" class="w-full h-full object-cover" alt="<?php echo $item['title']; ?>">
                <a href="<?php echo $item['file_src']; ?>" target="_blank">
                    <div class="absolute inset-0 bg-[rgba(0,180,255,0.1)] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center z-20">
                        <i class="ri-play-circle-line text-4xl"></i>
                    </div>
                </a>
            </div>
            <div class="" style="padding: 1rem;">
                <h4 class="font-medium mb-1" style="font-size: medium;"><?php echo $item['title']; ?></h4>
                <h5>Artist：<?php echo $item['artist']; ?></h5>
                <p class="text-gray-400 text-sm">isrc:<?php echo $item['isrc']; ?></p>
                <p class="text-gray-400 text-sm">upc:<?php echo $item['upc']; ?></p>
                <div class="flex justify-between items-center">
                    <div class="flex gap-2">
                        <a href="<?php echo $item['file_src']; ?>" download="<?php echo $item['title']; ?>" class="bg-primary hover:bg-opacity-90 text-sm px-4 py-1.5 rounded-button flex items-center whitespace-nowrap text-white">
                            Download
                        </a>
                        <button onclick="deleteMusic('<?php echo $item['id']; ?>', '<?php echo $item['title']; ?>')" class="bg-red-500 hover:bg-opacity-90 text-sm px-4 py-1.5 rounded-button flex items-center whitespace-nowrap text-white">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 上传音乐模态框 -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php _e("Upload New Music")?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="musicUploadForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><?php _e("Artist")?></label>
                                <input type="text" name="artist" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php _e("Title")?></label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php _e("ISRC")?></label>
                                <input type="text" name="isrc" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php _e("UPC")?></label>
                                <input type="text" name="upc" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><?php _e("Cover Image")?></label>
                                <input type="file" name="cover" class="form-control" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php _e("Music File")?></label>
                                <input type="file" name="music_file" class="form-control" accept="audio/*" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php _e("Genre")?></label>
                                <select name="genre" class="form-select">
                                    <option value="Pop">Pop</option>
                                    <option value="Rock">Rock</option>
                                    <option value="Hip Hop">Hip Hop</option>
                                    <option value="Electronic">Electronic</option>
                                    <option value="Classical">Classical</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php _e("Release Date")?></label>
                                <input type="date" name="release_date" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e("Cancel")?></button>
                    <button type="submit" class="btn btn-primary">
                        <span id="submitText"><?php _e("Upload")?></span>
                        <span id="uploadSpinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php _e("Import Music from CSV")?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="musicImportForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php _e("CSV File")?></label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        <div class="form-text"><?php _e("Upload a CSV file containing music data")?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php _e("CSV Template")?></label>
                        <div>
                            <a href="<?php _ec( get_module_url('download_template') )?>" class="text-decoration-none">
                                <i class="fas fa-download me-2"></i><?php _e("Download CSV Template")?>
                            </a>
                        </div>
                        <div class="form-text"><?php _e("Download the template to ensure correct format")?></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e("Cancel")?></button>
                    <button type="submit" class="btn btn-primary">
                        <span id="importSubmitText"><?php _e("Import")?></span>
                        <span id="importSpinner" class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        // 表单提交处理
        $('#musicUploadForm').on('submit', function(e){
            e.preventDefault();

            var form = this;
            var formData = new FormData(form);
            var submitBtn = $(form).find('button[type="submit"]');
            var spinner = $('#uploadSpinner');
            var submitText = $('#submitText');

            // 显示加载状态
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            submitText.text("<?php _e('Uploading...') ?>");

            $.ajax({
                url: "<?php _ec( get_module_url('upload') )?>",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        // 关闭模态框
                        $('#uploadModal').modal('hide');
                        // 显示成功消息
                        Core.show_notification('success', response.message);
                        // 刷新页面
                        window.location.reload();
                    } else {
                        Core.show_notification('error', response.message);
                    }
                },
                error: function(xhr) {
                    Core.show_notification('error', xhr.responseJSON?.message || '<?php _e("Server error occurred") ?>');
                },
                complete: function() {
                    // 恢复按钮状态
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                    submitText.text("<?php _e('Upload') ?>");
                }
            });
        });

        // 重置表单当模态框关闭时
        $('#uploadModal').on('hidden.bs.modal', function () {
            $('#musicUploadForm')[0].reset();
        });


        // 导入表单提交处理
        $('#musicImportForm').on('submit', function(e){
            e.preventDefault();

            var form = this;
            var formData = new FormData(form);
            var submitBtn = $(form).find('button[type="submit"]');
            var spinner = $('#importSpinner');
            var submitText = $('#importSubmitText');

            // 显示加载状态
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            submitText.text("<?php _e('Importing...') ?>");

            $.ajax({
                url: "<?php _ec( get_module_url('import') )?>",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        // 关闭模态框
                        $('#importModal').modal('hide');
                        // 显示成功消息
                        Core.show_notification('success', response.message);
                        // 刷新页面
                        window.location.reload();
                    } else {
                        // 显示错误详情（如果有）
                        if(response.errors) {
                            var errorMsg = response.message + "<br>" + response.errors.join("<br>");
                            Core.show_notification('error', errorMsg, 5000);
                        } else {
                            Core.show_notification('error', response.message);
                        }
                    }
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON?.message || '<?php _e("Server error occurred") ?>';
                    if(xhr.responseJSON?.errors) {
                        errorMsg += "<br>" + xhr.responseJSON.errors.join("<br>");
                    }
                    Core.show_notification('error', errorMsg, 5000);
                },
                complete: function() {
                    // 恢复按钮状态
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                    submitText.text("<?php _e('Import') ?>");
                }
            });
        });

// 重置导入表单当模态框关闭时
        $('#importModal').on('hidden.bs.modal', function () {
            $('#musicImportForm')[0].reset();
        });
    });
    function deleteMusic(id, title) {
        if (confirm('Are you sure you want to delete "' + title + '"?')) {
            const btn = $(`button[onclick="deleteMusic('${id}', '${title}')"]`);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

            $.ajax({
                url: "<?php _ec( get_module_url('delete_music') )?>",
                type: "POST",
                data: {id: id},
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        // 直接移除对应的音乐卡片
                        window.location.reload();
                    } else {
                        Core.show_notification('error', response.message || '<?php _e("Failed to delete music") ?>');
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).html('Delete');
                }
            });
        }
    }
</script>