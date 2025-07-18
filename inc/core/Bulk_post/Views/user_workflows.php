<link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
<style>
    :root {
        --primary: #4361ee;
        --primary-light: #eef2ff;
        --danger: #ef4444;
        --success: #22c55e;
        --warning: #f59e0b;
        --dark: #1e293b;
        --light: #f8fafc;
        --gray: #94a3b8;
        --gray-light: #e2e8f0;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: #f1f5f9;
        color: var(--dark);
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header h1 {
        font-size: 28px;
        font-weight: 600;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: #3a56d4;
    }

    .btn-outline {
        background-color: transparent;
        border: 1px solid var(--gray-light);
        color: var(--dark);
    }

    .btn-outline:hover {
        background-color: var(--gray-light);
    }

    .workflows-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .workflows-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid var(--gray-light);
    }

    .workflows-header h2 {
        font-size: 18px;
        font-weight: 600;
    }

    .search-filter {
        display: flex;
        gap: 10px;
    }

    .search-box {
        padding: 8px 12px;
        border: 1px solid var(--gray-light);
        border-radius: 6px;
        width: 250px;
    }

    .filter-dropdown {
        padding: 8px 12px;
        border: 1px solid var(--gray-light);
        border-radius: 6px;
        background-color: white;
        cursor: pointer;
    }

    .workflows-table {
        width: 100%;
        border-collapse: collapse;
    }

    .workflows-table th {
        text-align: left;
        padding: 12px 20px;
        background-color: var(--primary-light);
        color: var(--primary);
        font-weight: 500;
    }

    .workflows-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--gray-light);
        vertical-align: middle;
    }

    .workflows-table tr:last-child td {
        border-bottom: none;
    }

    .workflows-table tr:hover {
        background-color: #f8fafc;
    }

    .workflow-name {
        font-weight: 500;
        color: var(--dark);
    }

    .workflow-type {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        background-color: var(--primary-light);
        color: var(--primary);
    }

    .workflow-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .status-active {
        background-color: var(--success);
    }

    .status-paused {
        background-color: var(--warning);
    }

    .status-draft {
        background-color: var(--gray);
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        background-color: transparent;
        border: none;
        color: var(--gray);
    }

    .action-btn:hover {
        background-color: var(--gray-light);
        color: var(--dark);
    }

    .action-btn.edit {
        color: var(--primary);
    }

    .action-btn.delete {
        color: var(--danger);
    }

    .action-btn.run {
        color: var(--success);
    }

    .empty-state {
        padding: 40px 20px;
        text-align: center;
        color: var(--gray);
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        color: var(--gray-light);
    }

    .empty-state h3 {
        font-size: 18px;
        margin-bottom: 8px;
        color: var(--dark);
    }

    .empty-state p {
        margin-bottom: 20px;
    }

    /* 模态框样式 */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background-color: white;
        border-radius: 10px;
        width: 600px;
        max-width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 25px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-header h3 {
        font-size: 20px;
        font-weight: 600;
        color: var(--dark);
    }

    .close-modal {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--gray);
        transition: color 0.2s;
    }

    .close-modal:hover {
        color: var(--dark);
    }

    .modal-body {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-section {
        width: 100%;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: var(--dark);
    }

    .form-input, .form-textarea, .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--gray-light);
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }

    .form-input:focus, .form-textarea:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .hashtags-input {
        border: 1px solid var(--gray-light);
        border-radius: 6px;
        padding: 10px;
        min-height: 60px;
        outline: none;
    }

    .hashtags-input:focus {
        border-color: var(--primary);
    }

    .hint-text {
        color: var(--gray);
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--gray-light);
    }

    /* 动画效果 */
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* 设置模态框特定样式 */
    .weekdays-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 15px;
    }

    .weekday-checkbox {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 8px 12px;
        border-radius: 6px;
        background-color: var(--gray-light);
        cursor: pointer;
        transition: all 0.2s;
    }

    .weekday-checkbox:hover {
        background-color: var(--primary-light);
    }

    .weekday-checkbox input[type="checkbox"] {
        margin: 0;
    }
    .close-settings-modal{
        border: none;
        outline: none;
        background: none;
    }
</style>
<div class="container">
    <div class="workflows-container">
        <div class="workflows-header">
            <h2>All Workflows</h2>
            <div class="search-filter">
                <input type="text" class="search-box" placeholder="Search workflows...">
                <select class="filter-dropdown">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="draft">Draft</option>
                </select>
                <button class="btn" onclick="window.location.href='/bulk_post/add_widget'">
                    <i class="ri-add-line"></i> Add Widget
                </button>
            </div>
        </div>

        <table class="workflows-table">
            <thead>
            <tr>
                <th>Workflow Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Last Run</th>
                <th>Next Run</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($user_workflows)): ?>
            <?php foreach ($user_workflows as $workflow): ?>
                    <tr data-workflow-id="<?php echo $workflow['user_workflow_id']; ?>"
                        data-title="<?php echo htmlspecialchars($workflow['title']); ?>"
                        data-description="<?php echo htmlspecialchars($workflow['descript']); ?>"
                        data-category="<?php echo $workflow['category']; ?>"
                        data-tags="<?php echo htmlspecialchars($workflow['tags']); ?>"
                        data-accounts="<?php echo htmlspecialchars($workflow['accounts']); ?>"
                        data-workflow-name="<?php echo htmlspecialchars($workflow['workflow_name']); ?>"
                        data-status="<?php echo $workflow['is_enabled']; ?>"
                        data-schedule-type="<?php echo $workflow['schedule_type']; ?>"
                        data-schedule-time="<?php echo $workflow['schedule_time']; ?>"
                        data-schedule-days="<?php echo $workflow['schedule_days']; ?>"
                        data-schedule-date="<?php echo $workflow['schedule_date']; ?>">
                <td>
                    <div class="workflow-name"><?php _ec($workflow['workflow_name']); ?></div>
                </td>
                <td><span class="workflow-type"><?php _ec($workflow['name']); ?></span></td>
                <td>
                    <div class="workflow-status">
                        <?php if($workflow['is_enabled'] == 2): ?>
                        <span class="status-badge status-active"></span>
                        <span>Active</span>
                        <?php elseif ($workflow['is_enabled'] == 1): ?>
                        <span class="status-badge status-paused"></span>
                        <span>Paused</span>
                        <?php else: ?>
                        <span class="status-badge status-draft"></span>
                        <span>Draft</span>
                        <?php endif; ?>
                    </div>
                </td>
                <td><?php _ec($workflow['last_run_at']); ?></td>
                <td><?php _ec($workflow['next_run_at']); ?></td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn edit edit-btn" title="Edit">
                            <i class="ri-pencil-line"></i>
                        </button>
                        <button class="action-btn settings" title="Settings">
                            <i class="ri-settings-3-line"></i>
                        </button>
                        <button class="action-btn run" title="Run Now">
                            <i class="ri-play-line"></i>
                        </button>
                        <button class="action-btn delete" title="Delete">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr class="empty-row">
                <td colspan="6">
                    <div style="text-align: center; padding: 40px 0;">
                        <i class="ri-inbox-line" style="font-size: 48px; color: var(--gray-light); margin-bottom: 16px;"></i>
                        <h3 style="color: var(--dark); margin-bottom: 8px;">No Workflows Found / 未找到工作流</h3>
                        <p style="color: var(--gray); margin-bottom: 20px;">
                            You don't have any workflows yet. Create your first workflow to get started.<br>
                            您还没有任何工作流。创建第一个工作流开始使用。
                        </p>
                        <button class="btn btn-primary" onclick="window.location.href='/bulk_post/add_widget'">
                            <i class="ri-add-line"></i> Create Workflow / 创建工作流
                        </button>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 模态框结构 -->
<div class="modal-overlay" id="editWorkflowModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Edit Workflow</h3>
            <button class="close-modal">&times;</button>
        </div>

        <div class="modal-body">
            <div class="space-y-4">
                <?php echo view_cell('\Core\Account_manager\Controllers\Account_manager::widget', ["account_id" => false, "module_permission" => "%s_post"]) ?>
            </div>

            <div class="form-section">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" class="form-input" id="workflowTitle">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-textarea" id="workflowDescription"></textarea>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select class="form-select" id="workflowCategory">
                        <option value="0">Select a category</option>
                        <option value="1">Film &amp; Animation</option>
                        <option value="2">Autos &amp; Vehicles</option>
                        <option value="10">Music</option>
                        <option value="15">Pets &amp; Animals</option>
                        <option value="17">Sports</option>
                        <option value="19">Travel &amp; Events</option>
                        <option value="20">Gaming</option>
                        <option value="22">People &amp; Blogs</option>
                        <option value="23">Comedy</option>
                        <option value="24">Entertainment</option>
                        <option value="25">News &amp; Politics</option>
                        <option value="26">Howto &amp; Style</option>
                        <option value="27">Education</option>
                        <option value="28">Science &amp; Technology</option>
                        <option value="29">Nonprofits &amp; Activism</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Hashtags</label>
                    <div class="hashtags-input" contenteditable="true"></div>
                    <small class="hint-text">Press Enter or Space to add new hashtags</small>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline close-modal">Cancel</button>
            <button class="btn btn-primary save-workflow">Save Changes</button>
        </div>
    </div>
</div>

<!-- 设置模态框 -->
<div class="modal-overlay" id="settingsModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="settingsModalTitle">Workflow Settings</h3>
            <button class="close-settings-modal">&times;</button>
        </div>

        <div class="modal-body">
            <div class="form-section">
                <div class="form-group">
                    <label>Workflow Name</label>
                    <input type="text" class="form-input" id="settingsWorkflowName">
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select class="form-select" id="settingsWorkflowStatus">
                        <option value="2">Active</option>
                        <option value="1">Paused</option>
                        <option value="0">Draft</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Schedule Type</label>
                    <select class="form-select" id="scheduleType">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="once">Once</option>
                    </select>
                </div>

                <!-- Daily Schedule -->
                <div class="form-group daily-schedule">
                    <label>Daily Time (China Time)</label>
                    <input type="time" class="form-input" id="dailyTime">
                </div>

                <!-- Weekly Schedule -->
                <div class="form-group weekly-schedule" style="display: none;">
                    <label>Select Days</label>
                    <div class="weekdays-selector">
                        <label class="weekday-checkbox">
                            <input type="checkbox" name="weekdays" value="1"> Monday
                        </label>
                        <label class="weekday-checkbox">
                            <input type="checkbox" name="weekdays" value="2"> Tuesday
                        </label>
                        <label class="weekday-checkbox">
                            <input type="checkbox" name="weekdays" value="3"> Wednesday
                        </label>
                        <label class="weekday-checkbox">
                            <input type="checkbox" name="weekdays" value="4"> Thursday
                        </label>
                        <label class="weekday-checkbox">
                            <input type="checkbox" name="weekdays" value="5"> Friday
                        </label>
                        <label class="weekday-checkbox">
                            <input type="checkbox" name="weekdays" value="6"> Saturday
                        </label>
                        <label class="weekday-checkbox">
                            <input type="checkbox" name="weekdays" value="0"> Sunday
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Time (China Time)</label>
                        <input type="time" class="form-input" id="weeklyTime">
                    </div>
                </div>

                <!-- Once Schedule -->
                <div class="form-group once-schedule" style="display: none;">
                    <label>Date & Time (China Time)</label>
                    <input type="datetime-local" class="form-input" id="onceDateTime"
                           min="<?php echo date('Y-m-d\TH:i', time() + 8 * 3600); ?>">
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-outline close-settings-modal">Cancel</button>
            <button class="btn btn-primary save-settings">Save Settings</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 全局变量保存当前编辑的行
        let currentEditingRow = null;

        // 获取所有DOM元素
        const editButtons = document.querySelectorAll('.action-btn.edit');
        const runButtons = document.querySelectorAll('.action-btn.run');
        const deleteButtons = document.querySelectorAll('.action-btn.delete');
        const searchBox = document.querySelector('.search-box');
        const filterDropdown = document.querySelector('.filter-dropdown');
        const modal = document.getElementById('editWorkflowModal');
        const saveBtn = document.querySelector('.save-workflow');
        const closeModalBtns = document.querySelectorAll('.close-modal');
        const hashtagsInput = document.querySelector('.hashtags-input');

        // 编辑按钮点击事件
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // 保存当前编辑的行
                currentEditingRow = this.closest('tr');

                // 获取行中的数据
                const workflowId = currentEditingRow.dataset.workflowId;
                const workflowName = currentEditingRow.querySelector('.workflow-name').textContent;
                const title = currentEditingRow.dataset.title || '';
                const description = currentEditingRow.dataset.description || '';
                const category = currentEditingRow.dataset.category || '0';
                const tags = currentEditingRow.dataset.tags || '';
                const accounts = currentEditingRow.dataset.accounts || '[]'; // 获取accounts数据

                // 填充模态框表单
                document.getElementById('modalTitle').textContent = `Edit Workflow: ${workflowName}`;
                document.getElementById('workflowTitle').value = title;
                document.getElementById('workflowDescription').value = description;
                document.getElementById('workflowCategory').value = category;

                // 设置hashtags
                if (hashtagsInput) {
                    hashtagsInput.textContent = tags;
                }

                // 显示模态框
                modal.style.display = 'flex';

                // 自动选中账户逻辑
                try {
                    // 解析accounts JSON数据
                    const accountIds = JSON.parse(accounts);
                    // 先取消所有已选中的checkbox
                    document.querySelectorAll('input[name="accounts[]"]').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    // 移除所有已选项目
                    document.querySelectorAll('div.am-selected-box div.am-selected-list div.am-selected-item').forEach(item => {
                        item.remove();
                    });
                    // 显示空状态提示
                    const emptyElement = document.querySelector('div.am-selected-box div.am-selected-list div.am-selected-empty');
                    if (emptyElement) {
                        emptyElement.style.display = 'block';
                    }
                    // 选中匹配的账户
                    if (Array.isArray(accountIds) && accountIds.length > 0) {
                        accountIds.forEach(accountId => {
                            const checkbox = document.querySelector(`input[name="accounts[]"][value="${accountId}"]`);
                            if (checkbox) {
                                // 向上查找label元素
                                let parent = checkbox.parentElement;
                                while (parent && parent.tagName !== 'LABEL' && parent !== document.body) {
                                    parent = parent.parentElement;
                                }
                                // 如果找到label，模拟点击它
                                if (parent && parent.tagName === 'LABEL') {
                                    parent.click();
                                }
                            }
                        });
                    }
                } catch (e) {
                    console.error('Failed to parse accounts data:', e);
                }
            });
        });

        // 运行按钮点击事件
        runButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const workflowName = this.closest('tr').querySelector('.workflow-name').textContent;
                alert(`Run workflow: ${workflowName}`);
            });
        });

        // 删除按钮点击事件
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                const workflowId = row.dataset.workflowId;
                const workflowName = row.querySelector('.workflow-name').textContent;

                if (confirm(`Are you sure you want to delete "${workflowName}"?`)) {
                    // 显示加载状态
                    const originalHTML = this.innerHTML;
                    this.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>';
                    this.disabled = true;

                    // 发送AJAX请求
                    fetch('/bulk_post/delete_user_workflow', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
                        },
                        body: `user_workflow_id=${encodeURIComponent(workflowId)}`
                    })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                // 删除成功，移除行
                                row.remove();

                                // 检查是否还有行，显示空状态
                                const remainingRows = document.querySelectorAll('.workflows-table tbody tr');
                                if (remainingRows.length === 0) {
                                    // 可以在这里显示空状态提示
                                }
                            } else {
                                throw new Error(data.message || 'Failed to delete workflow');
                            }
                        })
                        .catch(error => {
                            alert('Error: ' + error.message);
                            // 恢复按钮状态
                            this.innerHTML = originalHTML;
                            this.disabled = false;
                        });
                }
            });
        });

        // 保存按钮点击事件
        saveBtn.addEventListener('click', function() {
            if (!currentEditingRow) {
                alert('No workflow selected for editing');
                return;
            }

            // 获取表单数据
            const workflowId = currentEditingRow.dataset.workflowId;
            const title = document.getElementById('workflowTitle').value;
            const description = document.getElementById('workflowDescription').value;
            const category = document.getElementById('workflowCategory').value;
            const tags = hashtagsInput ? hashtagsInput.textContent : '';

            // 获取所有选中的checkbox值
            const selectedAccounts = [];
            document.querySelectorAll('input[name="accounts[]"]:checked').forEach(checkbox => {
                selectedAccounts.push(checkbox.value);
            });

            // 显示加载状态
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>';
            this.disabled = true;

            // 创建FormData对象，更适合文件上传和常规表单提交
            const formData = new FormData();
            formData.append('workflow_id', workflowId);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('category', category);
            formData.append('tags', tags);

            // 添加所有选中的账户
            selectedAccounts.forEach((accountId, index) => {
                formData.append(`accounts[${index}]`, accountId);
            });

            // 发送AJAX请求
            fetch('/bulk_post/update_workflow', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
                },
                body: formData  // 使用FormData而不是JSON
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        // 直接重新加载页面
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Failed to save workflow');
                    }
                })
                .catch(error => {
                    showToast('Error: ' + error.message, 'error');
                })
                .finally(() => {
                    // 恢复按钮状态
                    this.innerHTML = originalHTML;
                    this.disabled = false;
                });
        });

        // 关闭模态框按钮
        closeModalBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                modal.style.display = 'none';
                currentEditingRow = null;
            });
        });

        // 处理hashtags输入
        if (hashtagsInput) {
            hashtagsInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const text = this.textContent.trim();
                    if (text && !text.endsWith('#')) {
                        this.textContent = text + ' #';
                        // 将光标移动到末尾
                        const range = document.createRange();
                        const sel = window.getSelection();
                        range.setStart(this.childNodes[0], this.textContent.length);
                        range.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                }
            });
        }

        // 搜索功能
        searchBox.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.workflows-table tbody tr');

            rows.forEach(row => {
                const name = row.querySelector('.workflow-name').textContent.toLowerCase();
                row.style.display = name.includes(searchTerm) ? '' : 'none';
            });
        });

        // 筛选功能
        filterDropdown.addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const rows = document.querySelectorAll('.workflows-table tbody tr');

            rows.forEach(row => {
                const statusElement = row.querySelector('.workflow-status span:nth-child(2)');
                const status = statusElement ? statusElement.textContent.toLowerCase() : '';
                row.style.display = (filterValue === 'all' || status === filterValue) ? '' : 'none';
            });
        });

        // 显示Toast提示
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    });


    // 获取设置相关DOM元素
    const settingsButtons = document.querySelectorAll('.action-btn.settings');
    const settingsModal = document.getElementById('settingsModal');
    const closeSettingsModalBtns = document.querySelectorAll('.close-settings-modal');
    const saveSettingsBtn = document.querySelector('.save-settings');
    const scheduleTypeSelect = document.getElementById('scheduleType');

    // 设置按钮点击事件
    settingsButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');

            // 从行的data属性获取所有数据
            const workflowId = row.dataset.workflowId;
            const workflowName = row.dataset.workflowName;
            const status = row.dataset.status;
            const scheduleType = row.dataset.scheduleType || 'none';
            const scheduleTime = row.dataset.scheduleTime;
            const scheduleDays = row.dataset.scheduleDays;
            const scheduleDate = row.dataset.scheduleDate;

            // 填充基本信息
            document.getElementById('settingsModalTitle').textContent = `Settings: ${workflowName}`;
            document.getElementById('settingsWorkflowName').value = workflowName;
            document.getElementById('settingsWorkflowStatus').value = status;

            // 设置调度类型
            document.getElementById('scheduleType').value = scheduleType;
            // 触发change事件以显示正确的表单部分
            document.getElementById('scheduleType').dispatchEvent(new Event('change'));

            // 根据调度类型填充数据
            switch(scheduleType) {
                case 'daily':
                    if (scheduleTime) {
                        document.getElementById('dailyTime').value = scheduleTime.substring(0, 5); // 去掉秒数
                    }
                    break;

                case 'weekly':
                    if (scheduleDays) {
                        // 将逗号分隔的字符串转为数组
                        const daysArray = scheduleDays.split(',');
                        // 选中对应的checkbox
                        document.querySelectorAll('input[name="weekdays"]').forEach(checkbox => {
                            checkbox.checked = daysArray.includes(checkbox.value);
                        });
                    }
                    if (scheduleTime) {
                        document.getElementById('weeklyTime').value = scheduleTime.substring(0, 5);
                    }
                    break;

                case 'once':
                    if (scheduleDate) {
                        // 将数据库中的datetime转换为datetime-local输入框需要的格式
                        const date = new Date(scheduleDate);
                        const formattedDate = date.toISOString().slice(0, 16);
                        document.getElementById('onceDateTime').value = formattedDate;
                    }
                    break;
            }

            // 显示模态框
            settingsModal.style.display = 'flex';
        });
    });

    // 计划类型切换事件
    scheduleTypeSelect.addEventListener('change', function() {
        const scheduleType = this.value;

        // 隐藏所有计划类型区域
        document.querySelectorAll('.daily-schedule, .weekly-schedule, .once-schedule').forEach(el => {
            el.style.display = 'none';
        });

        // 显示选中的计划类型区域
        document.querySelector(`.${scheduleType}-schedule`).style.display = 'block';
    });

    // 关闭设置模态框
    closeSettingsModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            settingsModal.style.display = 'none';
        });
    });

    // 保存设置
    // 保存设置
    saveSettingsBtn.addEventListener('click', function() {
        // 获取当前编辑的行
        const row = document.querySelector('tr[data-workflow-id]');
        if (!row) {
            showToast('No workflow selected', 'error');
            return;
        }

        // 获取工作流ID
        const workflowId = row.dataset.workflowId;

        // 收集表单数据
        const workflowName = document.getElementById('settingsWorkflowName').value;
        const status = document.getElementById('settingsWorkflowStatus').value;
        const scheduleType = document.getElementById('scheduleType').value;

        // 验证必填字段
        if (!workflowName) {
            showToast('Workflow name is required', 'error');
            return;
        }

        // 根据计划类型收集数据
        let scheduleData = {};
        let isValid = true;

        switch(scheduleType) {
            case 'daily':
                const dailyTime = document.getElementById('dailyTime').value;
                if (!dailyTime) {
                    showToast('Daily time is required', 'error');
                    isValid = false;
                    break;
                }
                scheduleData = {
                    type: 'daily',
                    time: dailyTime
                };
                break;

            case 'weekly':
                const weeklyTime = document.getElementById('weeklyTime').value;
                const selectedDays = [];
                document.querySelectorAll('input[name="weekdays"]:checked').forEach(checkbox => {
                    selectedDays.push(checkbox.value);
                });

                if (selectedDays.length === 0 || !weeklyTime) {
                    showToast('Please select at least one day and time', 'error');
                    isValid = false;
                    break;
                }

                scheduleData = {
                    type: 'weekly',
                    days: selectedDays,
                    time: weeklyTime
                };
                break;

            case 'once':
                const onceDateTime = document.getElementById('onceDateTime').value;
                if (!onceDateTime) {
                    showToast('Date & time is required', 'error');
                    isValid = false;
                    break;
                }

                // 验证是否选择了未来时间
                const selectedDate = new Date(onceDateTime);
                const now = new Date();
                if (selectedDate <= now) {
                    showToast('Please select a future date & time', 'error');
                    isValid = false;
                    break;
                }

                scheduleData = {
                    type: 'once',
                    datetime: onceDateTime
                };
                break;
        }

        if (!isValid) return;

        // 准备发送的数据
        const postData = {
            workflow_id: workflowId,
            workflow_name: workflowName,
            status: status,
            schedule: scheduleData
        };

        // 显示加载状态
        const originalHTML = this.innerHTML;
        this.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>';
        this.disabled = true;

        // 发送AJAX请求
        fetch('/bulk_post/update_setting', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
            },
            body: new URLSearchParams({
                workflow_id: workflowId,
                workflow_name: workflowName,
                status: status,
                schedule: JSON.stringify(scheduleData) // 将嵌套对象转为JSON字符串
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // 更新行中的数据
                    row.querySelector('.workflow-name').textContent = workflowName;

                    // 更新状态显示
                    const statusBadge = row.querySelector('.status-badge');
                    const statusText = row.querySelector('.workflow-status span:nth-child(2)');

                    // 移除所有状态类
                    statusBadge.classList.remove('status-active', 'status-paused', 'status-draft');

                    // 添加新状态类
                    switch(status) {
                        case '2':
                            statusBadge.classList.add('status-active');
                            statusText.textContent = 'Active';
                            break;
                        case '1':
                            statusBadge.classList.add('status-paused');
                            statusText.textContent = 'Paused';
                            break;
                        case '0':
                            statusBadge.classList.add('status-draft');
                            statusText.textContent = 'Draft';
                            break;
                    }

                    // 关闭模态框
                    settingsModal.style.display = 'none';

                    // 显示成功提示
                    console.log('Settings saved successfully!', 'success');
                } else {
                    throw new Error(data.message || 'Failed to save settings');
                }
            })
            .catch(error => {
                console.log('Error: ' + error.message, 'error');
            })
            .finally(() => {
                // 恢复按钮状态
                this.innerHTML = originalHTML;
                this.disabled = false;
            });
    });
</script>