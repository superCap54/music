<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
    }

    .stat-card {
        border-left: 4px solid;
        transition: transform 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card i {
        font-size: 2rem;
        opacity: 0.7;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    /* 替换原有图表样式 */
    .chart-container {
        background: #f8f9fa; /* 浅灰色背景 */
        border-radius: 8px;
        padding: 15px;
    }
    .month-picker-container {
        position: relative;
        display: inline-block;
        width: 180px;
    }

    .month-display {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .month-display:hover {
        border-color: #aaa;
    }

    .month-display i {
        margin-left: 8px;
        color: #666;
        transition: transform 0.2s;
    }

    .month-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        z-index: 100;
        display: none;
        margin-top: 5px;
    }

    .dropdown-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
    }

    .current-year {
        font-weight: bold;
    }

    .year-nav {
        background: none;
        border: none;
        cursor: pointer;
        color: #666;
        padding: 4px;
    }

    .year-nav:hover {
        color: #333;
    }

    .month-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 5px;
        padding: 8px;
    }

    .month-option {
        padding: 6px;
        border: none;
        background: none;
        cursor: pointer;
        border-radius: 3px;
        font-size: 13px;
    }

    .month-option:hover {
        background: #f0f0f0;
    }

    .month-option.selected {
        background: #4361ee;
        color: white;
    }

    .dropdown-footer {
        padding: 8px;
        border-top: 1px solid #eee;
        text-align: center;
    }

    .select-all {
        background: none;
        border: none;
        color: #4361ee;
        cursor: pointer;
        font-size: 13px;
    }

    .select-all:hover {
        text-decoration: underline;
    }

    /* 下拉菜单展开时的样式 */
    .month-picker-container.active .month-dropdown {
        display: block;
    }

    .month-picker-container.active .month-display i {
        transform: rotate(180deg);
    }
</style>
<div class="container-fluid py-4">
    <!-- 日历选择器 -->
    <!-- 替换原来的日历选择器部分 -->
    <div class="row mb-4">
        <div class="month-picker-container">
            <div class="month-display" id="monthDisplay">
                <span>全部数据</span>
                <i class="fas fa-caret-down"></i>
            </div>

            <div class="month-dropdown" id="monthDropdown">
                <div class="dropdown-header">
                    <div class="year-nav prev-year"><i class="fas fa-chevron-left"></i></div>
                    <span class="current-year">2025</span>
                    <div class="year-nav next-year"><i class="fas fa-chevron-right"></i></div>
                </div>

                <div class="month-grid">
                    <button class="month-option" data-month="01">Jan</button>
                    <button class="month-option" data-month="02">Feb</button>
                    <button class="month-option" data-month="03">Mar</button>
                    <button class="month-option" data-month="04">Apr</button>
                    <button class="month-option" data-month="05">May</button>
                    <button class="month-option" data-month="06">Jun</button>
                    <button class="month-option" data-month="07">Jul</button>
                    <button class="month-option" data-month="08">Aug</button>
                    <button class="month-option" data-month="09">Sep</button>
                    <button class="month-option" data-month="10">Oct</button>
                    <button class="month-option" data-month="11">Nov</button>
                    <button class="month-option" data-month="12">Dec</button>
                </div>

                <div class="dropdown-footer">
                    <button class="select-all">全部数据</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 数据概览卡片 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">总播放量</h6>
                            <h3 class="mb-0"><?php _ec($dashboardData['views'])?></h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-play-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">总收入</h6>
                            <h3 class="mb-0">$<?php _ec($dashboardData['earnings'])?></h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">覆盖国家</h6>
                            <h3 class="mb-0"><?php _ec($dashboardData['countriesReached'])?></h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted">歌曲数量</h6>
                            <h3 class="mb-0"><?php _ec(count($songsList)); ?></h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-compact-disc"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 收入趋势图表 -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title"><?php _e('Monthly Performance Trends') ?></h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="earningsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title">收入分布</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="earningsDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 音乐作品数据表格 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title"><?php _e("Song Performance"); ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Cover</th>
                                <th>Song</th>
                                <th>Views</th>
                                <th>Earns(USD)</th>
                                <th>Top Countries</th>
                                <th>Platform</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($songsDataList as $songDataItem): ?>
                            <tr>
                                <td>
                                    <div class="rounded bg-[#2a2a2a] flex items-center justify-center" style="height: 3rem; width: 3rem;">
                                        <img src="<?php _ec($songDataItem['imgSrc'])?>" style="width: 100%" alt="<?php _ec($songDataItem['title'])?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?php _ec($songDataItem['title'])?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td><?php _ec($songDataItem['views'])?></td>
                                <td>$<?php _ec($songDataItem['earns'])?></td>
                                <td><?php _ec($songDataItem['topCountry']); ?></td>
                                <td>Youtube</td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 国家/地区收入分析 -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Region Rank</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th><?php _ec('No.'); ?></th>
                                <th><?php _ec('Region'); ?></th>
                                <th><?php _ec('Views'); ?></th>
                                <th><?php _ec('Earnings'); ?></th>
                                <th><?php _ec('Percentage'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($countryEarnings as $countryEarningsItem): ?>
                            <tr>
                                <td><?php _ec($countryEarningsItem['index']); ?></td>
                                <td><?php _ec($countryEarningsItem['country']); ?></td>
                                <td><?php _ec($countryEarningsItem['total_views']); ?></td>
                                <td>$<?php _ec(round($countryEarningsItem['total_earnings'],4)); ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php _ec($countryEarningsItem['percentage']); ?>%"
                                             aria-valuenow="<?php _ec($countryEarningsItem['percentage']); ?>" aria-valuemin="0" aria-valuemax="100"><?php _ec($countryEarningsItem['percentage']); ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript 库 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<script>
    // 月度收入趋势图表
    const earningsCtx = document.getElementById('earningsChart').getContext('2d');
    const earningsChart = new Chart(earningsCtx, {
        type: 'line',
        data: {
            labels: [<?php foreach ($monthlyData as $monthlyDataItem): _ec('"'.$monthlyDataItem['month_name'].'",'); endforeach; ?>],
            datasets: [{
                label: 'Views',
                data: [<?php foreach ($monthlyData as $monthlyDataItem): _ec($monthlyDataItem['views'].','); endforeach; ?>],
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.3,
                yAxisID: 'y'
            }, {
                label: 'Earnings(USD)',
                data: [<?php foreach ($monthlyData as $monthlyDataItem): _ec($monthlyDataItem['earnings'].','); endforeach; ?>],
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.3,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Views'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Earnings(USD)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });

    // 收入分布图表
    const distributionCtx = document.getElementById('earningsDistributionChart').getContext('2d');
    const distributionChart = new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: [<?php foreach ($countryChart as $countryChartItem): _ec('"'.$countryChartItem['country'].'",'); endforeach; ?>],
            datasets: [{
                data: [<?php foreach ($countryChart as $countryChartItem): _ec($countryChartItem['percentage'].','); endforeach; ?>],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `${context.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.month-picker-container');
        const display = document.getElementById('monthDisplay');
        const dropdown = document.getElementById('monthDropdown');
        const yearSpan = document.querySelector('.current-year');
        const prevYearBtn = document.querySelector('.prev-year');
        const nextYearBtn = document.querySelector('.next-year');
        const monthOptions = document.querySelectorAll('.month-option');
        const selectAllBtn = document.querySelector('.select-all');

        let currentYear = new Date().getFullYear();
        let selectedMonth = null;

        // 初始化年份显示
        yearSpan.textContent = currentYear;

        // 切换下拉菜单
        display.addEventListener('click', function(e) {
            e.stopPropagation();
            container.classList.toggle('active');
        });

        // 年份导航
        prevYearBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentYear--;
            yearSpan.textContent = currentYear;
        });

        nextYearBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentYear++;
            yearSpan.textContent = currentYear;
        });

        // 选择月份
        monthOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();

                // 移除之前的选择
                monthOptions.forEach(opt => opt.classList.remove('selected'));

                // 标记当前选择
                this.classList.add('selected');
                selectedMonth = this.dataset.month;

                // 更新显示
                display.querySelector('span').textContent = `${currentYear}-${selectedMonth}`;

                // 关闭下拉菜单
                container.classList.remove('active');

                // 直接跳转到新URL
                window.location.href = `<?php echo base_url('musicdata/index') ?>/${currentYear}-${selectedMonth}`;
            });
        });

        // 选择全部数据
        selectAllBtn.addEventListener('click', function(e) {
            e.stopPropagation();

            // 清除选择
            monthOptions.forEach(opt => opt.classList.remove('selected'));
            selectedMonth = null;

            // 更新显示
            display.querySelector('span').textContent = '全部数据';

            // 关闭下拉菜单
            container.classList.remove('active');

            // 跳转到基础URL
            window.location.href = '<?php echo base_url('musicdata/index') ?>';
        });

        // 点击外部关闭下拉菜单
        document.addEventListener('click', function() {
            container.classList.remove('active');
        });

        // 初始化当前选中的月份（如果有）
        const pathParts = window.location.pathname.split('/');
        const dateParam = pathParts[pathParts.length - 1];

        if (dateParam && /^\d{4}-\d{2}$/.test(dateParam)) {
            const [year, month] = dateParam.split('-');
            currentYear = parseInt(year);
            selectedMonth = month;
            yearSpan.textContent = currentYear;

            // 标记选中的月份
            document.querySelectorAll('.month-option').forEach(option => {
                if (option.dataset.month === month) {
                    option.classList.add('selected');
                    display.querySelector('span').textContent = dateParam;
                }
            });
        }
    });
</script>