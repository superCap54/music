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

    /* 折线图颜色调整 */
    .chart-line-primary {
        border-color: #4361ee !important; /* 更鲜明的蓝色 */
        background-color: rgba(67, 97, 238, 0.1) !important;
    }

    .chart-line-secondary {
        border-color: #3a0ca3 !important; /* 深蓝色 */
        background-color: rgba(58, 12, 163, 0.1) !important;
    }

    /* 环形图颜色调整 */
    .chart-doughnut {
        --chart-colors: #4361ee, #3a0ca3, #7209b7, #f72585, #4cc9f0;
    }
</style>
<div class="container-fluid py-4">
    <!-- 页面标题和筛选区 -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="input-group">
                <select class="form-select" id="timeRange" name="timeRange">
                    <option value="12">最近12个月</option>
                    <option value="6">最近6个月</option>
                    <option value="3">最近3个月</option>
                    <option value="1">最近1个月</option>
                    <option value="all" selected>全部时间</option>
                </select>
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-filter me-1"></i>筛选
                </button>
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
                    <h5 class="card-title">国家/地区收入排行</h5>
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
            labels: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
            datasets: [{
                label: '播放量',
                data: [12000, 19000, 15000, 18000, 15600, 21000, 23000, 24500, 19500, 22000, 24000, 26000],
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.3,
                yAxisID: 'y'
            }, {
                label: '收入(USD)',
                data: [450, 680, 520, 640, 580, 720, 780, 820, 650, 750, 820, 890],
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
                        text: '播放量'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: '收入(USD)'
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
            labels: ['美国', '英国', '德国', '日本', '法国', '其他'],
            datasets: [{
                data: [42.5, 21.3, 12.7, 8.4, 6.2, 8.9],
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
</script>