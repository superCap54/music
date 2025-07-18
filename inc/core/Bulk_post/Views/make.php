<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
<style>
    :where([class^="ri-"])::before { content: "\f3c2"; }
    .configure-btn {
        font-size: 0.875rem;
        height: 32px;
        transition: all 0.2s ease;
    }
    .configure-btn:hover {
        background-color: rgba(74, 144, 226, 0.1);
    }
    body {
        font-family: 'Inter', sans-serif;
        background-color: #ffffff;
    }
    .neumorphic {
        background: #ffffff;
        box-shadow: 8px 8px 15px rgba(0, 0, 0, 0.08), -8px -8px 15px rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
    }
    .neumorphic:hover {
        box-shadow: 6px 6px 12px rgba(0, 0, 0, 0.06), -6px -6px 12px rgba(255, 255, 255, 0.8);
    }
    .neumorphic:active {
        box-shadow: inset 4px 4px 8px rgba(0, 0, 0, 0.06), inset -4px -4px 8px rgba(255, 255, 255, 0.8);
    }
    .neumorphic-inset {
        background: #ffffff;
        box-shadow: inset 4px 4px 8px rgba(0, 0, 0, 0.06), inset -4px -4px 8px rgba(255, 255, 255, 0.8);
    }
    .neumorphic-button {
        background: #ffffff;
        box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.08), -4px -4px 8px rgba(255, 255, 255, 0.8);
        transition: all 0.2s ease;
    }
    .neumorphic-button:hover {
        box-shadow: 3px 3px 6px rgba(0, 0, 0, 0.06), -3px -3px 6px rgba(255, 255, 255, 0.8);
    }
    .neumorphic-button:active {
        box-shadow: inset 3px 3px 5px rgba(0, 0, 0, 0.06), inset -3px -3px 5px rgba(255, 255, 255, 0.8);
    }
    .toggle-checkbox:checked + .toggle-switch {
        background-color: rgba(74, 144, 226, 0.6);
    }
    .toggle-checkbox:checked + .toggle-switch::after {
        transform: translateX(22px);
        background-color: #4A90E2;
    }
    .tooltip-trigger:hover .tooltip {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }
    .flowchart-area {
        min-height: 180px;
        border: 2px dashed rgba(74, 144, 226, 0.3);
        transition: all 0.3s ease;
    }
    .flowchart-area:hover {
        border-color: rgba(74, 144, 226, 0.6);
    }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .workflow-card {
        transition: all 0.3s ease;
        transform-origin: top;
    }
    .workflow-card.hidden {
        opacity: 0;
        transform: scale(0.95);
        height: 0;
        padding: 0;
        margin: 0;
        overflow: hidden;
        border: none;
    }
    /* 添加模态框样式 */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    .modal-container {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        width: 90%;
        max-width: 400px;
        padding: 24px;
        transform: translateY(20px);
        transition: all 0.3s ease;
    }
    .modal-overlay.active .modal-container {
        transform: translateY(0);
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    .modal-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
    }
    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #6b7280;
    }
    .modal-body {
        margin-bottom: 24px;
    }
    .modal-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    .modal-input:focus {
        outline: none;
        border-color: #4a90e2;
    }
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
    .modal-btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    .modal-btn-cancel {
        background-color: #f3f4f6;
        color: #4b5563;
        border: none;
    }
    .modal-btn-cancel:hover {
        background-color: #e5e7eb;
    }
    .modal-btn-save {
        background-color: #4a90e2;
        color: white;
        border: none;
    }
    .modal-btn-save:hover {
        background-color: #3b82f6;
    }
    .modal-btn-save:disabled {
        background-color: #93c5fd;
        cursor: not-allowed;
    }
</style>
<div class="container mx-auto px-4 py-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($workflows as $workflow){ ?>
            <div class="neumorphic rounded-2xl workflow-card" style="padding: 1.5rem;"
                 data-remark="<?= htmlspecialchars($workflow['remark'] ?? '') ?>">
                <div class="flex justify-between items-start" style="margin-bottom: 1rem;">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 flex items-center justify-center">
                            <i class="ri-youtube-fill text-[#FF0000]"></i>
                        </div>
                        <h4 class="font-medium text-gray-800"><?php echo $workflow['name']; ?></h4>
                    </div>
                </div>
                <p class="text-gray-600 text-sm" style="margin-bottom: 1rem;"><?php echo $workflow['description']; ?></p>
                <div class="neumorphic-inset flowchart-area rounded-lg flex items-center justify-center" style="padding:1rem;margin-bottom:1.25rem;">
                    <?php if($workflow['image_url']){ ?>
                        " alt="<?php echo $workflow['name']; ?>" width="286px" height="196px">
                    <?php }else{ ?>
                        <div class="text-center">
                            <div class="flex justify-center space-x-4" style="margin-bottom: 0.5rem;">
                                <div class="flex items-center justify-center neumorphic-button rounded-full" style="width: 3rem;height: 3rem;">
                                    <i class="ri-article-line text-primary text-xl"></i>
                                </div>
                            </div>
                            <div class="flex justify-center" style="margin-bottom: 0.5rem;">
                                <div class="flex items-center justify-center" style="width: 1.5rem;height: 1.5rem;">
                                    <i class="ri-arrow-down-line text-gray-400"></i>
                                </div>
                            </div>
                            <div class="flex justify-center" style="margin-bottom: 0.5rem;">
                                <div class="flex items-center justify-center neumorphic-button rounded-full" style="width: 3rem;height: 3rem;">
                                    <i class="ri-scissors-line text-primary text-xl"></i>
                                </div>
                            </div>
                            <div class="flex justify-center" style="margin-bottom: 0.5rem;">
                                <div class="flex items-center justify-center" style="width: 1.5rem;height: 1.5rem;">
                                    <i class="ri-arrow-down-line text-gray-400"></i>
                                </div>
                            </div>
                            <div class="flex justify-center">
                                <div class="flex items-center justify-center neumorphic-button rounded-full" style="width: 3rem;height: 3rem;">
                                    <i class="ri-youtube-line text-primary text-xl"></i>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button class="neumorphic-button !rounded-button px-3 py-1 text-sm text-primary whitespace-nowrap flex items-center add-workflow-btn"
                                data-workflow-id="<?php echo $workflow['workflow_id']; ?>">
                            <i class="ri-add-line mr-1"></i>Add
                        </button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<!-- 在文档的body部分添加模态框HTML -->
<div class="modal-overlay" id="workflowModal">
    <div class="modal-container neumorphic">
        <div class="modal-header">
            <h3 class="modal-title">Name Your Workflow</h3>
            <button class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body">
            <input type="text" class="modal-input" id="workflowName" placeholder="Enter workflow name">
        </div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn-cancel" id="modalCancel">Cancel</button>
            <button class="modal-btn modal-btn-save" id="modalSave" disabled>Save</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==================== 初始化函数 ====================
        initWorkflowToggles();
        initWorkflowFilters();
        initAddButtons();
        initModal();
    });

    // ==================== 初始化模态框 ====================
    function initModal() {
        const modal = document.getElementById('workflowModal');
        const modalClose = document.getElementById('modalClose');
        const modalCancel = document.getElementById('modalCancel');
        const modalSave = document.getElementById('modalSave');
        const workflowName = document.getElementById('workflowName');

        let currentWorkflowId = null;

        // 输入框变化时启用/禁用保存按钮
        workflowName.addEventListener('input', function() {
            modalSave.disabled = this.value.trim() === '';
        });

        // 关闭模态框
        function closeModal() {
            modal.classList.remove('active');
            workflowName.value = '';
            modalSave.disabled = true;
            currentWorkflowId = null;
        }

        // 关闭按钮事件
        modalClose.addEventListener('click', closeModal);
        modalCancel.addEventListener('click', closeModal);

        // 保存按钮事件
        modalSave.addEventListener('click', function() {
            if (currentWorkflowId && workflowName.value.trim()) {
                addWorkflow(currentWorkflowId, workflowName.value.trim());
                closeModal();
            }
        });

        // 全局函数：打开模态框
        window.openWorkflowModal = function(workflowId) {
            currentWorkflowId = workflowId;
            modal.classList.add('active');
            workflowName.focus();
        };
    }

    // ==================== 工作流状态切换 ====================
    function initWorkflowToggles() {
        const toggles = document.querySelectorAll('.toggle-checkbox');

        toggles.forEach(toggle => {
            // 初始化状态
            if (toggle.checked) {
                const card = toggle.closest('.neumorphic');
                card.style.borderLeft = '3px solid rgba(74, 144, 226, 0.6)';
            }

            // 状态变化事件
            toggle.addEventListener('change', function() {
                const workflowId = this.getAttribute('data-workflow-id');
                const isActive = this.checked;
                const card = this.closest('.neumorphic');

                // 更新UI
                card.style.borderLeft = isActive ? '3px solid rgba(74, 144, 226, 0.6)' : 'none';

                // 发送AJAX请求
                updateWorkflowStatus(workflowId, isActive);
            });
        });
    }

    // ==================== 工作流筛选 ====================
    function initWorkflowFilters() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const workflowCards = document.querySelectorAll('.workflow-card');

        // Only proceed if we have filter buttons and workflow cards
        if (filterButtons.length === 0 || workflowCards.length === 0) return;

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // Update button styles
                filterButtons.forEach(btn => {
                    btn.classList.remove('text-gray-800');
                    btn.classList.add('text-gray-500');
                });
                this.classList.remove('text-gray-500');
                this.classList.add('text-gray-800');

                // Filter cards
                workflowCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    card.style.display = filter === 'all' || status === filter ? 'block' : 'none';
                });
            });
        });

        // Find the "all" filter button and click it if it exists
        const allFilterButton = document.querySelector('.filter-btn[data-filter="all"]');
        if (allFilterButton) {
            allFilterButton.click();
        }
    }

    // ==================== 添加工作流按钮 ====================
    function initAddButtons() {
        document.querySelectorAll('.add-workflow-btn').forEach(button => {
            button.addEventListener('click', function() {
                const workflowId = this.getAttribute('data-workflow-id');
                openWorkflowModal(workflowId);
            });
        });
    }

    // ==================== 添加工作流 ====================
    function addWorkflow(workflowId, workflowName) {
        const button = document.querySelector(`.add-workflow-btn[data-workflow-id="${workflowId}"]`);
        const originalText = button.innerHTML;

        // 显示加载状态
        button.innerHTML = '<i class="ri-loader-4-line animate-spin mr-1"></i>Adding...';
        button.disabled = true;

        // 创建 URL 编码的表单数据
        const formData = new URLSearchParams();
        formData.append('workflow_id', workflowId);
        formData.append('workflow_name', workflowName);

        fetch('/bulk_post/add_workflow', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
            },
            body: formData
        })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {  // 检查 status 而不是 success
                    // 直接跳转到用户工作流页面
                    window.location.href = '/bulk_post/index';
                } else {
                    throw new Error(data.message || 'Failed to add workflow');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding workflow: ' + error.message);
            })
            .finally(() => {
                // 恢复按钮状态
                button.innerHTML = originalText;
                button.disabled = false;
            });
    }

    // 辅助函数：显示成功消息
    function showSuccessMessage(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center';
        toast.innerHTML = `
            <i class="ri-checkbox-circle-line mr-2"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);

        // 3秒后自动消失
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-300');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>