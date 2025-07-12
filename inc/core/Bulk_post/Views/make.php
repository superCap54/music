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
    #configureModal, #schedulingModal {
        backdrop-filter: blur(4px);
    }
    #configureModal .neumorphic, #schedulingModal .neumorphic {
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

    /* ================ 新增下拉菜单样式 ================ */
    .options-dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .dropdown-content {
        position: absolute;
        background-color: white;
        min-width: 180px;
        box-shadow: 0px 8px 16px rgba(0,0,0,0.1);
        z-index: 10;
        border-radius: 8px;
        overflow: hidden;
        top: 100%;
        right: 0;
        margin-top: 5px;
        display: none;
        transform-origin: top right;
        transform: scale(0.95);
        opacity: 0;
        transition: all 0.2s ease;
    }
    .dropdown-content.show {
        display: block;
        transform: scale(1);
        opacity: 1;
    }
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        color: #4a5568;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .dropdown-item:hover {
        background-color: rgba(74, 144, 226, 0.1);
        color: #4A90E2;
    }
    .dropdown-item i {
        margin-right: 8px;
        font-size: 16px;
    }

    /* ================ 新增调度模态框样式 ================ */
    .schedule-type-btn {
        transition: all 0.2s ease;
    }
    .schedule-type-btn.bg-primary {
        background-color: #4A90E2 !important;
        color: white !important;
    }
    .schedule-option {
        display: none;
    }
    .schedule-option.active {
        display: block;
    }
    .schedule-day {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #f1f5f9;
        cursor: pointer;
        margin: 2px;
        transition: all 0.2s;
    }
    .schedule-day.selected {
        background-color: #4A90E2;
        color: white;
    }
    .schedule-day:hover {
        background-color: #e2e8f0;
    }
    .schedule-day.selected:hover {
        background-color: #3a7bd5;
    }
    .time-input-container {
        position: relative;
    }
    .time-input-container i {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #718096;
        pointer-events: none;
    }

    /* ================ 响应式调整 ================ */
    @media (max-width: 640px) {
        .schedule-type-btn {
            padding: 8px 4px;
            font-size: 12px;
        }
        .dropdown-content {
            min-width: 140px;
        }
        .dropdown-item {
            padding: 8px 12px;
            font-size: 13px;
        }
    }
    /* 新增调度模态框样式 */
    .schedule-select-wrapper {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .schedule-select {
        appearance: none;
        background-color: white;
        border: none;
        padding: 0.75rem 2.5rem 0.75rem 1rem;
        width: 100%;
        border-radius: 0.5rem;
        box-shadow: inset 3px 3px 6px rgba(0, 0, 0, 0.06), inset -3px -3px 6px rgba(255, 255, 255, 0.8);
        font-size: 0.875rem;
        color: #4a5568;
        cursor: pointer;
    }
    .schedule-select-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #718096;
    }
    .schedule-option-group {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    .schedule-option-group.active {
        display: block;
    }
    .days-selector {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    .day-pill {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem;
        border-radius: 0.5rem;
        background-color: #f1f5f9;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.75rem;
        font-weight: 500;
    }
    .day-pill.selected {
        background-color: #4A90E2;
        color: white;
        box-shadow: 0 2px 4px rgba(74, 144, 226, 0.3);
    }
    .datetime-input {
        position: relative;
    }
    .datetime-input i {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #718096;
        pointer-events: none;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* 模态框美化 */
    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    .modal-body {
        padding: 1.5rem;
    }
    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }
    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1a202c;
    }
    .modal-subtitle {
        font-size: 0.875rem;
        color: #718096;
        margin-bottom: 1.5rem;
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
                        <!-- 修改为下拉按钮 -->
                        <div class="options-dropdown">
                            <button class="dropdown-toggle neumorphic-button !rounded-button px-3 py-1 text-sm text-primary whitespace-nowrap flex items-center">
                                <i class="ri-settings-3-line mr-1"></i>Options
                            </button>
                            <div class="dropdown-content">
                                <div class="dropdown-item configure-option" data-content='<?php echo $workflow['config_schema']; ?>'>
                                    <i class="ri-settings-3-line"></i>Configure
                                </div>
                                <div class="dropdown-item scheduling-option" data-workflow-id="<?php echo $workflow['workflow_id']; ?>">
                                    <i class="ri-calendar-event-line"></i>Scheduling
                                </div>
                            </div>
                        </div>
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
<div id="configureModal" class="fixed inset-0 bg-opacity-30 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-[90%] max-w-2xl mx-4 relative transform transition-all neumorphic">
        <button class="absolute right-4 top-4 w-8 h-8 flex items-center justify-center neumorphic-button rounded-full" onclick="closeConfigModal()">
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
<!-- 替换Schedule模态框内容 -->
<div id="schedulingModal" class="fixed inset-0 bg-opacity-30 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl w-[90%] max-w-md mx-4 relative transform transition-all neumorphic">
        <div class="modal-header">
            <h3 class="modal-title">Schedule Workflow</h3>
            <p class="modal-subtitle">Set up when this workflow should run automatically</p>

            <button class="absolute right-4 top-4 w-8 h-8 flex items-center justify-center neumorphic-button rounded-full" onclick="closeSchedulingModal()">
                <i class="ri-close-line text-gray-700"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="schedule-select-wrapper">
                <select id="scheduleTypeSelect" class="schedule-select">
                    <option value="daily">Every Day</option>
                    <option value="weekly">Days of the Week</option>
                    <option value="once">Once</option>
                </select>
                <i class="ri-arrow-down-s-line schedule-select-icon"></i>
            </div>

            <!-- Daily Schedule -->
            <div id="dailySchedule" class="schedule-option-group active">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Run at</label>
                    <div class="time-input-container">
                        <input type="time" class="neumorphic-inset w-full px-4 py-2 rounded-lg" id="dailyTime">
                    </div>
                </div>
            </div>

            <!-- Weekly Schedule -->
            <div id="weeklySchedule" class="schedule-option-group">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Select days</label>
                    <div class="days-selector">
                        <div class="day-pill" data-day="mon">Mon</div>
                        <div class="day-pill" data-day="tue">Tue</div>
                        <div class="day-pill" data-day="wed">Wed</div>
                        <div class="day-pill" data-day="thu">Thu</div>
                        <div class="day-pill" data-day="fri">Fri</div>
                        <div class="day-pill" data-day="sat">Sat</div>
                        <div class="day-pill" data-day="sun">Sun</div>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Run at</label>
                    <div class="time-input-container">
                        <input type="time" class="neumorphic-inset w-full px-4 py-2 rounded-lg" id="weeklyTime">
                    </div>
                </div>
            </div>

            <!-- Once Schedule -->
            <div id="onceSchedule" class="schedule-option-group">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-medium mb-2">Run on</label>
                    <div class="datetime-input">
                        <input type="datetime-local" class="neumorphic-inset w-full px-4 py-2 rounded-lg" id="onceDateTime">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button onclick="closeSchedulingModal()" class="neumorphic-button !rounded-button px-5 py-2 text-gray-600">Cancel</button>
            <button onclick="saveSchedule()" class="neumorphic-button !rounded-button px-5 py-2 bg-primary text-white">Save Schedule</button>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==================== 初始化函数 ====================
        initWorkflowToggles();
        initWorkflowFilters();
        initDropdownMenus();
        initScheduleModal();
    });

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
                    card.style.display = filter === 'all' || status === filter ? 'block' : 'none';
                });
            });
        });

        // 默认显示所有
        document.querySelector('.filter-btn[data-filter="all"]').click();
    }

    // ==================== 下拉菜单交互 ====================
    function initDropdownMenus() {
        document.addEventListener('click', function(e) {
            // 下拉菜单切换
            if (e.target.closest('.dropdown-toggle')) {
                const toggle = e.target.closest('.dropdown-toggle');
                const dropdownMenu = toggle.nextElementSibling;

                // 关闭其他下拉菜单
                document.querySelectorAll('.dropdown-content').forEach(menu => {
                    if (menu !== dropdownMenu) menu.classList.remove('show');
                });

                dropdownMenu.classList.toggle('show');
                e.stopPropagation();
            }
            // 点击下拉选项
            else if (e.target.closest('.configure-option')) {
                const option = e.target.closest('.configure-option');
                const configData = JSON.parse(option.getAttribute('data-content'));
                openConfigModal(configData);
                option.closest('.dropdown-content').classList.remove('show');
            }
            else if (e.target.closest('.scheduling-option')) {
                const option = e.target.closest('.scheduling-option');
                const workflowId = option.getAttribute('data-workflow-id');
                openSchedulingModal(workflowId);
                option.closest('.dropdown-content').classList.remove('show');
            }
            // 点击外部关闭所有下拉菜单
            else {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });
    }

    // ==================== 调度模态框初始化 ====================
    function initScheduleModal() {
        // Schedule类型选择
        const scheduleTypeSelect = document.getElementById('scheduleTypeSelect');
        if (scheduleTypeSelect) {
            scheduleTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;

                // 隐藏所有选项
                document.querySelectorAll('.schedule-option-group').forEach(group => {
                    group.classList.remove('active');
                });

                // 显示选中的选项
                document.getElementById(`${selectedType}Schedule`).classList.add('active');
            });
        }

        // 星期选择
        document.querySelectorAll('.day-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                this.classList.toggle('selected');
            });
        });
    }

    // ==================== 工作流状态更新 ====================
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
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    // 恢复原来的状态
                    const toggle = document.querySelector(`#toggle${workflowId}`);
                    if (toggle) {
                        toggle.checked = !isActive;
                        const card = toggle.closest('.neumorphic');
                        if (card) {
                            card.style.borderLeft = isActive ? 'none' : '3px solid rgba(74, 144, 226, 0.6)';
                        }
                    }
                    alert('Failed to update workflow status: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating workflow status. Please try again.');
            });
    }

    // ==================== 配置模态框 ====================
    function openConfigModal(configData) {
        const modal = document.getElementById('configureModal');
        const dynamicConfigContainer = document.getElementById('dynamicConfigContainer');

        // 清空容器
        dynamicConfigContainer.innerHTML = '';

        // 动态生成表单控件
        configData.forEach((field) => {
            const fieldWrapper = document.createElement('div');
            fieldWrapper.className = 'mb-4';

            // 创建标签
            const label = document.createElement('label');
            label.className = 'block text-sm font-medium text-gray-600 mb-1';
            label.textContent = field.name;

            // 根据类型创建不同的输入控件
            let input;
            if (field.type === 'text' || field.type === 'number') {
                input = document.createElement('input');
                input.type = field.type;
                input.className = 'neumorphic-inset w-full px-4 py-2 rounded-lg';
                input.placeholder = `Enter ${field.name}`;
                input.value = field.value || '';
            }
            else if (field.type === 'textarea') {
                input = document.createElement('textarea');
                input.className = 'neumorphic-inset w-full px-4 py-2 rounded-lg';
                input.placeholder = `Enter ${field.name}`;
                input.value = field.value || '';
                input.rows = 3;
            }
            else if (field.type === 'select') {
                input = document.createElement('select');
                input.className = 'neumorphic-inset w-full px-4 py-2 rounded-lg';

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
            }
            else if (field.type === 'checkbox') {
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
            }
            else {
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
    }

    function closeConfigModal() {
        const modal = document.getElementById('configureModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function saveConfig() {
        // 这里添加保存配置的逻辑
        console.log('Saving configuration...');
        closeConfigModal();
    }

    // ==================== 调度模态框 ====================
    function openSchedulingModal(workflowId) {
        const modal = document.getElementById('schedulingModal');
        modal.setAttribute('data-workflow-id', workflowId);
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // 重置所有选择
        document.querySelectorAll('.day-pill').forEach(pill => {
            pill.classList.remove('selected');
        });

        // 设置当前时间为默认值
        const now = new Date();
        const formattedTime = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
        document.getElementById('dailyTime').value = formattedTime;
        document.getElementById('weeklyTime').value = formattedTime;

        // 设置日期时间输入框的默认值（当前时间+1小时）
        now.setHours(now.getHours() + 1);
        const formattedDateTime = now.toISOString().slice(0, 16);
        document.getElementById('onceDateTime').value = formattedDateTime;

        // 重置为每日选项
        document.getElementById('scheduleTypeSelect').value = 'daily';
        document.querySelectorAll('.schedule-option-group').forEach(group => {
            group.classList.remove('active');
        });
        document.getElementById('dailySchedule').classList.add('active');
    }

    function closeSchedulingModal() {
        const modal = document.getElementById('schedulingModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function saveSchedule() {
        const modal = document.getElementById('schedulingModal');
        const workflowId = modal.getAttribute('data-workflow-id');
        const scheduleType = document.getElementById('scheduleTypeSelect').value;

        let scheduleData = {
            workflow_id: workflowId,
            type: scheduleType
        };

        switch(scheduleType) {
            case 'daily':
                scheduleData.time = document.getElementById('dailyTime').value;
                break;

            case 'weekly':
                scheduleData.days = [];
                document.querySelectorAll('.day-pill.selected').forEach(pill => {
                    scheduleData.days.push(pill.getAttribute('data-day'));
                });
                scheduleData.time = document.getElementById('weeklyTime').value;
                break;

            case 'once':
                scheduleData.datetime = document.getElementById('onceDateTime').value;
                break;
        }

        // 发送AJAX请求
        fetch('/workflow/save_schedule', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo csrf_token(); ?>'
            },
            body: JSON.stringify(scheduleData)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeSchedulingModal();
                    alert('Schedule saved successfully!');
                } else {
                    alert('Failed to save schedule: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving schedule.');
            });
    }
</script>