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
    #configureModal {
        backdrop-filter: blur(4px);
    }
    #configureModal .neumorphic {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
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
    .toggle-switch {
        position: relative;
        width: 50px;
        height: 28px;
        background-color: #ffffff;
        border-radius: 14px;
        box-shadow: inset 2px 2px 4px rgba(0, 0, 0, 0.06), inset -2px -2px 4px rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
    }
    .toggle-switch::after {
        content: '';
        position: absolute;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background-color: #E4EBF5;
        box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        top: 3px;
        left: 3px;
        transition: all 0.3s ease;
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
</style>
<div class="container mx-auto px-4 py-6">
<!--    <div class="mb-8 flex justify-between items-center">-->
<!--        <h3 class="text-2xl font-medium text-gray-800">Video Publishing Workflows</h3>-->
<!--        <button class="neumorphic-button !rounded-button flex items-center px-5 py-2.5 text-primary whitespace-nowrap">-->
<!--            <div class="w-5 h-5 flex items-center justify-center mr-2">-->
<!--                <i class="ri-add-line"></i>-->
<!--            </div>-->
<!--            <span>New Workflow</span>-->
<!--        </button>-->
<!--    </div>-->
    <div class="flex mb-6 space-x-4">
        <div class="neumorphic-button !rounded-button px-4 py-2 text-gray-800 whitespace-nowrap filter-btn" data-filter="all">All Workflows</div>
        <div class="neumorphic-button !rounded-button px-4 py-2 text-gray-500 whitespace-nowrap filter-btn" data-filter="active">Active</div>
        <div class="neumorphic-button !rounded-button px-4 py-2 text-gray-500 whitespace-nowrap filter-btn" data-filter="paused">Paused</div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($workflows as $workflow){ ?>
        <div class="neumorphic rounded-2xl workflow-card" style="padding: 1.5rem;" data-status="<?= $workflow['user_is_enabled'] ? 'active' : 'paused' ?>">
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
                    <img src="<?php echo $workflow['image_url']; ?>" alt="<?php echo $workflow['name']; ?>" width="286px" height="196px">
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
                    <button class="configure-btn neumorphic-button !rounded-button px-3 py-1 text-sm text-primary whitespace-nowrap" data-content='<?php echo $workflow['config_schema']; ?>'>Configure</button>
                </div>
                <div>
                    <input type="checkbox"
                        <?php if($workflow['user_is_enabled']){ echo "checked";} ?>
                           id="toggle<?php echo $workflow['workflow_id']; ?>"
                           class="toggle-checkbox hidden"
                           data-workflow-id="<?php echo $workflow['workflow_id']; ?>">
                    <label for="toggle<?php echo $workflow['workflow_id']; ?>" class="toggle-switch block cursor-pointer"></label>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<script id="toggleSwitchInteraction">
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.toggle-checkbox');
        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const card = this.closest('.neumorphic');
                if (this.checked) {
// Workflow is active
                    card.style.borderLeft = '3px solid rgba(74, 144, 226, 0.6)';
                } else {
// Workflow is inactive
                    card.style.borderLeft = 'none';
                }
            });
// Initialize state
            if (toggle.checked) {
                const card = toggle.closest('.neumorphic');
                card.style.borderLeft = '3px solid rgba(74, 144, 226, 0.6)';
            }
        });
    });
</script>
<div id="configureModal" class="fixed inset-0 bg-opacity-30 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-[90%] max-w-2xl mx-4 relative transform transition-all neumorphic">
        <button class="absolute right-4 top-4 w-8 h-8 flex items-center justify-center neumorphic-button rounded-full" onclick="closeModal()">
            <i class="ri-close-line text-gray-700"></i>
        </button>

        <div class="" style="padding: 1.5rem;">
            <!-- 标题 -->
            <h3 class="text-xl font-semibold text-gray-800 mb-6">Workflow Configuration</h3>

            <!-- 账号密码区域 -->
            <div class="mb-8">
                <h4 class="text-md font-medium text-gray-700" style="margin-bottom: 1rem;">Account Settings</h4>
                <div class="space-y-4">
                    <?php echo view_cell('\Core\Account_manager\Controllers\Account_manager::widget', ["account_id" => get_data($post, 'account_id'), "module_permission" => "%s_post"]) ?>
                </div>
            </div>

            <!-- 动态配置区域 -->
            <div>
                <h4 class="text-md font-medium text-gray-700" style="margin-bottom: 1rem;">Workflow Settings</h4>
                <div id="dynamicConfigContainer" class="space-y-4">
                    <!-- 这里将通过JavaScript动态渲染config_schema -->
                </div>
            </div>

            <!-- 操作按钮 -->
            <div class="mt-8 flex justify-end space-x-3">
                <button onclick="closeModal()" class="neumorphic-button !rounded-button px-5 py-2 text-gray-600">Cancel</button>
                <button onclick="saveConfig()" class="neumorphic-button !rounded-button px-5 py-2 bg-primary text-white">Save Configuration</button>
            </div>
        </div>
    </div>
</div>
<script id="modalInteraction">
    document.addEventListener('DOMContentLoaded', function() {
        const configureBtns = document.querySelectorAll('.configure-btn');
        const dynamicConfigContainer = document.getElementById('dynamicConfigContainer');
        const modal = document.getElementById('configureModal');

        configureBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // 获取并解析配置数据
                const configData = JSON.parse(this.getAttribute('data-content'));

                // 清空容器
                dynamicConfigContainer.innerHTML = '';

                // 动态生成表单控件
                configData.forEach((field, index) => {
                    const fieldWrapper = document.createElement('div');

                    // 创建标签
                    const label = document.createElement('label');
                    label.className = 'block text-sm font-medium text-gray-600 mb-1';
                    label.textContent = field.name;

                    // 根据类型创建不同的输入控件
                    let input;
                    if (field.type === 'text') {
                        input = document.createElement('input');
                        input.type = 'text';
                        input.className = 'neumorphic-inset w-full px-4 py-2 rounded-lg';
                        input.placeholder = `Enter ${field.name}`;
                        input.value = field.value || '';
                    } else if (field.type === 'number') {
                        input = document.createElement('input');
                        input.type = 'number';
                        input.className = 'neumorphic-inset w-full px-4 py-2 rounded-lg';
                        input.placeholder = `Enter ${field.name}`;
                        input.value = field.value || '';
                    } else if (field.type === 'textarea') {
                        input = document.createElement('textarea');
                        input.className = 'neumorphic-inset w-full px-4 py-2 rounded-lg';
                        input.placeholder = `Enter ${field.name}`;
                        input.value = field.value || '';
                        input.rows = 3;
                    } else if (field.type === 'select') {
                        input = document.createElement('select');
                        input.className = 'neumorphic-inset w-full px-4 py-2 rounded-lg';

                        // 假设field.options包含选择项
                        if (field.options && Array.isArray(field.options)) {
                            field.options.forEach(option => {
                                const optionElement = document.createElement('option');
                                optionElement.value = option.value;
                                optionElement.textContent = option.label;
                                if (option.value === field.value) {
                                    optionElement.selected = true;
                                }
                                input.appendChild(optionElement);
                            });
                        }
                    } else if (field.type === 'checkbox') {
                        const checkboxWrapper = document.createElement('div');
                        checkboxWrapper.className = 'flex items-center';

                        input = document.createElement('input');
                        input.type = 'checkbox';
                        input.className = 'mr-2';
                        input.checked = field.value || false;

                        const checkboxLabel = document.createElement('label');
                        checkboxLabel.className = 'text-sm text-gray-600';
                        checkboxLabel.textContent = field.name;

                        checkboxWrapper.appendChild(input);
                        checkboxWrapper.appendChild(checkboxLabel);
                        fieldWrapper.appendChild(checkboxWrapper);
                    } else {
                        // 默认文本输入
                        input = document.createElement('input');
                        input.type = 'text';
                        input.className = 'neumorphic-inset w-full px-4 py-2 rounded-lg';
                        input.placeholder = `Enter ${field.name}`;
                        input.value = field.value || '';
                    }

                    // 如果不是复选框，添加标签和输入控件
                    if (field.type !== 'checkbox') {
                        fieldWrapper.appendChild(label);
                        fieldWrapper.appendChild(input);
                    }

                    dynamicConfigContainer.appendChild(fieldWrapper);
                });

                // 显示模态框
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        });
    });
    function closeModal() {
        const modal = document.getElementById('configureModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
    document.addEventListener('DOMContentLoaded', function() {
        // 获取所有相关元素
        const filterButtons = document.querySelectorAll('.filter-btn');
        const workflowCards = document.querySelectorAll('.workflow-card');

        // 为每个筛选按钮添加点击事件
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // 更新按钮样式
                filterButtons.forEach(btn => {
                    btn.classList.remove('text-gray-800');
                    btn.classList.add('text-gray-500');
                });
                this.classList.remove('text-gray-500');
                this.classList.add('text-gray-800');

                // 筛选卡片
                workflowCards.forEach(card => {
                    const status = card.getAttribute('data-status');

                    switch(filter) {
                        case 'all':
                            card.style.display = 'block';
                            break;
                        case 'active':
                            card.style.display = status === 'active' ? 'block' : 'none';
                            break;
                        case 'paused':
                            card.style.display = status === 'paused' ? 'block' : 'none';
                            break;
                    }
                });
            });
        });

        // 初始化显示所有工作流
        document.querySelector('.filter-btn[data-filter="all"]').click();
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggles = document.querySelectorAll('.toggle-checkbox');

        toggles.forEach(toggle => {
            toggle.addEventListener('change', function() {
                const workflowId = this.getAttribute('data-workflow-id');
                const isActive = this.checked;
                const card = this.closest('.neumorphic');

                // 更新UI
                if (isActive) {
                    card.style.borderLeft = '3px solid rgba(74, 144, 226, 0.6)';
                } else {
                    card.style.borderLeft = 'none';
                }

                // 发送AJAX请求
                updateWorkflowStatus(workflowId, isActive);
            });

            // 初始化状态
            if (toggle.checked) {
                const card = toggle.closest('.neumorphic');
                card.style.borderLeft = '3px solid rgba(74, 144, 226, 0.6)';
            }
        });

        function updateWorkflowStatus(workflowId, isActive) {
            fetch('/post/update_workflow_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
                },
                body: JSON.stringify({
                    workflow_id: workflowId,
                    is_active: isActive
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        // 如果服务器返回失败，恢复原来的状态
                        const toggle = document.querySelector(`#toggle${workflowId}`);
                        toggle.checked = !isActive;
                        const card = toggle.closest('.neumorphic');
                        card.style.borderLeft = isActive ? 'none' : '3px solid rgba(74, 144, 226, 0.6)';

                        alert('Failed to update workflow status: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // 恢复原来的状态
                    const toggle = document.querySelector(`#toggle${workflowId}`);
                    toggle.checked = !isActive;
                    const card = toggle.closest('.neumorphic');
                    card.style.borderLeft = isActive ? 'none' : '3px solid rgba(74, 144, 226, 0.6)';

                    alert('An error occurred while updating workflow status. Please try again.');
                });
        }
    });
</script>