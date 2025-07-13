<div class="container">
    <div class="card border b-r-10">
        <div class="card-header">
            <h3 class="card-title">Import DistroKid TSV File</h3>
        </div>
        <div class="card-body">
            <!-- TSV文件上传表单 -->
            <form action="<?php _ec( get_module_url("import_tsv") )?>" method="post" enctype="multipart/form-data" class="mb-4">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label><?php _e("Select TSV File")?></label>
                            <input type="file" name="tsv_file" class="form-control" accept=".tsv, text/tab-separated-values" required>
                            <small class="text-muted"><?php _e("Upload DistroKid royalty report in TSV format")?></small>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <button type="submit" class="btn btn-primary"><?php _e("Import")?></button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- 已导入文件列表 -->
            <div class="table-responsive">
                <!-- 已导入文件列表 -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th width="50">#</th>
                            <th><?php _e("TSV File Name")?></th>
                            <th><?php _e("Records Count")?></th>
                            <th><?php _e("Last Updated")?></th>
                            <th><?php _e("Actions")?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($tsv_files)): ?>
                            <?php $count = 1; ?>
                            <?php foreach ($tsv_files as $file): ?>
                                <tr>
                                    <td style="vertical-align: middle;"><?php echo $count++; ?></td>
                                    <td style="vertical-align: middle;"><?php echo htmlspecialchars($file->distrokid_tsv_file_name); ?></td>
                                    <td style="vertical-align: middle;"><?php echo $file->records_count; ?></td>
                                    <td style="vertical-align: middle;"><?php echo date('Y-m-d H:i:s', strtotime($file->last_updated)); ?></td>
                                    <td style="vertical-align: middle;">
                                        <a href="<?php echo get_module_url('delete_tsv/' . $file->tsv_md5); ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php _e("Are you sure you want to delete this TSV file and all its records?"); ?>')">
                                            <?php _e("Delete"); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted"><?php _e("No imported TSV files found")?></td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        // 文件上传验证
        $('input[type="file"]').change(function(){
            var file = this.files[0];
            if(file){
                var ext = file.name.split('.').pop().toLowerCase();
                if(ext != 'tsv'){
                    alert('<?php _e("Please upload a TSV file")?>');
                    $(this).val('');
                }
            }
        });
        // 表单提交防重复
        $('form').on('submit', function(e){
            var $btn = $(this).find('button[type="submit"]');
            // 禁用按钮并添加加载状态
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm" role="status"></span> <?php _e("Processing...") ?>');
        });
    });
</script>