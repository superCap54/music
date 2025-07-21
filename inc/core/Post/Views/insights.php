<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
<script src="https://cdn.tailwindcss.com/3.4.16"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
<style>
    :where([class^="ri-"])::before { content: "\f3c2"; }
    body {
        font-family: 'Inter', sans-serif;
    }
    h1, h2, h3, h4, h5, h6 {
        font-family: 'Space Grotesk', sans-serif;
    }
    .card {
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s ease;
        position: relative;
    }
    .card:hover {
        box-shadow: 0 0 15px rgba(0, 180, 255, 0.3);
        border: 1px solid rgba(0, 180, 255, 0.2);
    }
    .metric-value {
        font-family: 'Space Grotesk', sans-serif;
    }
    .glow-effect {
        position: relative;
    }
    .glow-effect::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: inherit;
        box-shadow: 0 0 15px rgba(0, 180, 255, 0);
        transition: box-shadow 0.3s ease;
        pointer-events: none;
    }
    .glow-effect:hover::after {
        box-shadow: 0 0 15px rgba(0, 180, 255, 0.5);
    }
    .table-row {
        transition: all 0.2s ease;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    .table-row:hover {
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 0 10px rgba(0, 180, 255, 0.2);
    }
    input[type="text"], input[type="search"], select {
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(26, 26, 26, 0.8);
        color: #333333;
        transition: all 0.3s ease;
    }
    input[type="text"]:focus, input[type="search"]:focus, select:focus {
        border-color: #00b4ff;
        box-shadow: 0 0 0 2px rgba(0, 180, 255, 0.2);
        outline: none;
    }
    .rounded-button{
        border-radius:8px;
    }
    .\!rounded-button{
        border-radius:8px;
    }
    button {
        transition: all 0.2s ease;
    }
    button:hover {
        transform: translateY(-1px);
    }
    button:active {
        transform: translateY(1px);
    }
    .custom-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    input:checked + .switch-slider {
        background-color: #00b4ff;
    }
    input:checked + .switch-slider:before {
        transform: translateX(22px);
    }
    #prevBtn, #nextBtn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }
    #prevBtn:disabled, #nextBtn:disabled {
        background: #64748b;
        cursor: not-allowed;
    }
</style>

<div class="row">
    <div class="col mb-4">
        <div class="border rounded b-r-10 bg-white position-relative">
            <div class="p-20 position-relative zIndex-2 p-b-0 d-flex">
                <div class="bg-light-success w-60 h-60 text-success m-auto d-flex align-items-center justify-content-center fs-30 b-r-10">
                    <i class="fad fa-badge-check"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class=""><span class="fs-28 fw-9 text-success me-1"><?php _ec( short_number($total_succeed) )?></span> <span class="fw-6 text-gray-700"><?php _e("Succeed")?></span></div>
                </div>
            </div>
            <div id="post_by_status_succeed_chart" class="h-120 b-0 w-100"></div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="border rounded b-r-10 bg-white position-relative">
            <div class="p-20 position-relative zIndex-2 p-b-0 d-flex">
                <div class="bg-light-danger w-60 h-60 text-danger m-auto d-flex align-items-center justify-content-center fs-30 b-r-10">
                    <i class="fad fa-exclamation"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class=""><span class="fs-28 fw-9 text-danger me-1"><?php _ec( short_number($total_failed) )?></span> <span class="fw-6 text-gray-700"><?php _e("Failed")?></span></div>
                </div>
            </div>
            <div id="post_by_status_failed_chart" class="h-120 b-0 w-100"></div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="border rounded b-r-10 bg-white position-relative">
            <div class="p-20 position-relative zIndex-2 p-b-0 d-flex">
                <div class="bg-light-primary w-60 h-60 text-primary m-auto d-flex align-items-center justify-content-center fs-30 b-r-10">
                    <i class="fad fa-calendar-check"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <div class=""><span class="fs-28 fw-9 text-primary me-1"><?php _ec( short_number($total_post) )?></span> <span class="fw-6 text-gray-700"><?php _e("Total")?></span></div>
                </div>
            </div>
            <div id="post_by_status_total_chart" class="h-120 b-0 w-100"></div>
        </div>
    </div>
</div>

<div class="card b-r-6 mb-4 border" style="display: none;">
    <div class="card-header">
        <div class="card-title">
            <?php _e("Report post by status")?>
        </div>
    </div>
    <div class="card-body">
        <div id="post_by_status_chart"></div>
        <h3 class="text-center"></h3>
    </div>
</div>

<div class="row" style="display: none;">
    <div class="col-md-6 mb-4">
        <div class="card h-100 mb-4 border">
            <div class="card-header">
                <div class="card-title">
                    <span class="me-2"><?php _e("Report post by type")?></span>
                </div>
            </div>
            <div class="card-body">
                <div id="post_by_type_chart"></div>
                <div class="card border b-r-10 border">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="border-bottom">
                                <tr>
                                    <th scope="col" class="text-center text-gray-500 fw-6"></th>
                                    <th scope="col" class="text-center text-gray-500 fw-4 fs-12"><?php _e("Media")?></th>
                                    <th scope="col" class="text-center text-gray-500 fw-4 fs-12"><?php _e("Link")?></th>
                                    <th scope="col" class="text-center text-gray-500 fw-4 fs-12"><?php _e("Text")?></th>
                                </tr>
                                </thead>
                                <tbody class="text-gray-700">
                                <tr>
                                    <td class="text-dark p-10 text-gray-500 fw-4 fs-12"><?php _e("Total post")?></td>
                                    <td class="text-dark p-10 text-center fw-6"><?php _ec($total_media_succeed)?></td>
                                    <td class="text-dark p-10 text-center fw-6"><?php _ec($total_link_succeed)?></td>
                                    <td class="text-dark p-10 text-center fw-6"><?php _ec($total_text_succeed)?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100 mb-4 border">
            <div class="card-header">
                <div class="card-title">
                    <span class="me-2"><?php _e("Recent publications")?></span>
                </div>
            </div>
            <div class="card-body py-0 px-4">
                <div class="schedules-main overflow-auto row mt-4 mh-600 h-100">
                    <div class="schedule-list h-100">
                        <?php if (!empty($recent_posts)): ?>
                            <?php foreach ($recent_posts as $key => $value): ?>

                            <?php
                            $data = json_decode($value->data);
                            ?>
                            <div class="card border px-0 item mb-4">

                                <?php if ($value->status == 1){ ?>
                                    <div class="ribbon ribbon-triangle ribbon-top-start border-primary rounded">
                                        <div class="ribbon-icon mn-t-22 mn-l-22">
                                            <i class="fs-20 fas fa-circle-notch fa-spin fs-2"></i>
                                        </div>
                                    </div>

                                    <div class="border-primary border-top-dashed border-1"></div>
                                <?php }else if($value->status == 3){ ?>
                                    <div class="ribbon ribbon-triangle ribbon-top-start border-success rounded">
                                        <div class="ribbon-icon mn-t-22 mn-l-22">
                                            <i class="fs-20 fad fa-check-double fs-2"></i>
                                        </div>
                                    </div>

                                    <div class="border-success border-top-dashed border-1"></div>
                                <?php }else if($value->status == 4){ ?>
                                    <div class="ribbon ribbon-triangle ribbon-top-start border-danger rounded">
                                        <div class="ribbon-icon mn-t-22 mn-l-22">
                                            <i class="fs-20 fad fa-exclamation-circle fs-2"></i>
                                        </div>
                                    </div>

                                    <div class="border-danger border-top-dashed border-1"></div>
                                <?php } ?>

                                <div class="card-header px-4 border-0">

                                    <div class="card-title fw-normal fs-12">

                                        <div class="d-flex flex-stack">
                                            <div class="symbol symbol-45px me-3">
                                                <img src="<?php _ec( get_file_url($value->avatar) )?>" class="align-self-center rounded-circle border" alt="">
                                            </div>
                                            <div class="d-flex align-items-center flex-row-fluid flex-wrap">
                                                <div class="flex-grow-1 me-2 text-over-all">
                                                    <a href="<?php _ec( $value->url )?>" target="_blank" class="text-gray-800 text-hover-primary fs-14 fw-bold"><i class="<?php _ec( $value->icon )?>" style="color: <?php _ec( $value->color )?>;"></i> <?php _ec( $value->name )?></a>
                                                    <span class="text-muted fw-semibold d-block fs-12"><i class="fal fa-calendar-alt"></i> <?php _ec( datetime_show($value->time_post) )?></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <div class="card-body p-20">

                                    <div class="d-flex">
                                        <div class="symbol symbol-100px me-3 overflow-hidden w-99 border rounded">

                                            <?php if($value->type == "media"){?>
                                                <?php if (!empty($data->medias)): ?>
                                                    <div class="owl-carousel owl-theme">
                                                        <?php foreach ($data->medias as $index => $media): ?>

                                                            <?php if ( is_image($media) ): ?>
                                                                <div class="item w-100 h-99" style="background-image: url('<?php _ec( get_file_url($media) )?>');"></div>
                                                            <?php else: ?>
                                                                <div class="item w-100 h-99">
                                                                    <video  autoplay muted>
                                                                        <source src="<?php _ec( get_file_url($media) )?>" type="video/mp4">
                                                                    </video>
                                                                </div>
                                                            <?php endif ?>

                                                        <?php endforeach ?>
                                                    </div>
                                                <?php endif ?>

                                            <?php }elseif($value->type == "link"){?>
                                                <a href="<?php _ec( $data->link )?>" target="_blank" class="d-flex align-items-center justify-content-center w-99 h-99 fs-30 bg-light-primary"><i class="fal fa-link"></i></a>
                                            <?php }else{?>
                                                <div class="d-flex align-items-center justify-content-center w-99 h-99 fs-30 text-primary bg-light-primary"><i class="fal fa-align-center"></i></div>
                                            <?php }?>

                                        </div>
                                        <div class="d-flex flex-row-fluid flex-wrap">
                                            <div class="flex-grow-1 me-2">
							                        <span class="text-gray-600 d-block h-99 overflow-auto">
							                            <?php _ec( nl2br($data->caption) )?>
							                        </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <?php if ( $value->status == 3 ): ?>

                                    <?php  $data = json_decode($value->result); ?>

                                    <div class="card-footer bg-light-success text-success py-3 px-4 d-flex justify-content-between">
                                        <span class="me-2"><?php _e("Post successed")?></span> <a href="<?php _e( $data->url )?>" class="text-dark text-hover-primary" target="_blank"><i class="fad fa-eye"></i> <?php _e("View post")?></a>
                                    </div>
                                <?php endif ?>

                                <?php if ( $value->status == 4 ): ?>

                                    <?php  $error = json_decode($value->result); ?>

                                    <div class="card-footer bg-light-danger text-danger py-3 px-4">
                                        <?php _e($error->message)?>
                                    </div>
                                <?php endif ?>


                            </div>

                        <?php endforeach ?>
                            <script type="text/javascript">
                                $(function(){
                                    Layout.carousel();
                                });
                            </script>
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex justify-content-center align-items-center">
                                <div class="text-center px-4">
                                    <img class="mh-190 mb-4" alt="" src="<?php _e( get_theme_url() ) ?>Assets/img/empty2.png">
                                    <div>
                                        <a class="btn btn-primary btn-sm b-r-30" href="<?php _e( base_url('post') )?>" >
                                            <i class="fad fa-plus"></i> <?php _ec("Create post")?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card bg-white px-4 shadow-none border b-r-10">
    <div class="card-header px-0 border-bottom-0">
        <div class="card-title fw-5 fs-19 text-gray-800">
            <span class="me-2"><i class="fad fa-paper-plane me-2" style="color: #ff0000;"></i> <?php _e("Total Data")?></span>
        </div>
    </div>
</div>
<main class="container mx-auto px-4 py-8">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Plays Card -->
        <div class="card rounded-lg glow-effect" style="padding: 1.5rem">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-400 text-sm font-medium"><?php _e("total_plays")?></h3>
                    <p class="metric-value text-3xl font-semibold mt-1"><?php _ec($dashboardData['views'])?></p>
                </div>
                <div class="rounded-full bg-[rgba(0,180,255,0.1)] flex items-center justify-center text-primary" style="width: 2.5rem; height: 2.5rem;">
                    <i class="ri-play-circle-line text-xl"></i>
                </div>
            </div>
        </div>
        <!-- Total Earnings Card -->
        <div class="card rounded-lg glow-effect" style="padding: 1.5rem">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-400 text-sm font-medium"><?php _e("total_earnings")?></h3>
                    <p class="metric-value text-3xl font-semibold mt-1">$ <?php _ec($dashboardData['earnings'])?></p>
                </div>
                <div class="rounded-full bg-[rgba(100,255,218,0.1)] flex items-center justify-center text-[#64ffda]" style="width: 2.5rem; height: 2.5rem;">
                    <i class="ri-money-dollar-circle-line text-xl"></i>
                </div>
            </div>
        </div>
        <!-- Active Songs Card -->
        <div class="card rounded-lg glow-effect" style="padding: 1.5rem">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-400 text-sm font-medium"><?php _e("active_songs")?></h3>
                    <p class="metric-value text-3xl font-semibold mt-1"><?php _ec(count($songsList)); ?></p>
                </div>
                <div class="rounded-full bg-[rgba(255,0,255,0.1)] flex items-center justify-center text-[#ff00ff]" style="width: 2.5rem; height: 2.5rem;">
                    <i class="ri-music-2-line text-xl"></i>
                </div>
            </div>
        </div>
        <!-- Countries Reached Card -->
        <div class="card rounded-lg glow-effect" style="padding: 1.5rem">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-gray-400 text-sm font-medium"><?php _e("countries_reached")?></h3>
                    <p class="metric-value text-3xl font-semibold mt-1"><?php _ec($dashboardData['countriesReached'])?></p>
                </div>
                <div class="rounded-full bg-[rgba(255,122,0,0.1)] flex items-center justify-center text-[#ff7a00]" style="width: 2.5rem; height: 2.5rem;">
                    <i class="ri-global-line text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    <?php if($monthlyData){ ?>
        <section class="mb-8">
            <div class="mb-8 flex justify-between items-center">
                <h2 class= "text-2xl font-medium"><?php _e('Monthly Performance Trends') ?></h2>
            </div>
            <!-- 卡片容器 -->
            <div class="relative">
                <div class="performance-grid-container overflow-x-auto">
                    <div class="performance-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 min-w-max flex space-x-4">
                        <?php foreach($monthlyData as $index => $month){ ?>
                            <div class="card backdrop-blur-sm border rounded-lg min-w-[200px] m-1" style="padding: 1rem;">
                                <div class=" text-gray-400 mb-3"><?php _ec($month['month'])?></div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex items-center justify-center" style="width: 1rem; height: 1rem;">
                                                <i class="ri-play-circle-line text-primary "></i>
                                            </div>
                                            <span class="text-gray-800">Plays</span>
                                        </div>
                                        <span class="font-semibold"><?php _ec($month['views'])?></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div class="flex items-center justify-center" style="width: 1rem; height: 1rem;color: #64ffda;">
                                                <i class="ri-money-dollar-circle-line text-secondary text-sm"></i>
                                            </div>
                                            <span class="text-gray-800">Earnings</span>
                                        </div>
                                        <span class="font-semibold">$<?php _ec($month['earnings'])?></span>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center text-green-400 text-xs">
                                    <?php if ($month['growth_rate'] !== null): ?>
                                        <span class="<?= $month['growth_rate']['earnings_growth'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?php  if($month['growth_rate']['earnings_growth'] >= 0): ?>
                                            <i class="ri-arrow-up-line mr-1"></i>
                                        <?php else: ?>
                                        <i class="ri-arrow-down-line mr-1"></i>
                                        <?php endif; ?>
                                        <?= round($month['growth_rate']['earnings_growth'],2) ?>%
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>
    <?php } ?>

    <?php if($songsDataList){ ?>
        <!-- Filter Controls -->
        <div class="mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-medium"><?php _e("Song Performance"); ?></h2>
        </div>
        <div class="mb-8 rounded-lg border border-[rgba(255,255,255,0.05)]" style="padding: 1rem; padding-bottom: 0;">
            <?php
                if (false): //先写死不显示
            ?>
            <div class="flex flex-wrap items-end gap-4 mb-8">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm text-gray-400 mb-2"><?php _e("Platform"); ?></label>
                    <div class="relative">
                        <select class="w-full border border-[rgba(255,255,255,0.1)] rounded-button px-4 appearance-none bg-[rgba(255,255,255,0.8)]" style="padding-right: 2rem;height:2.5rem;">
                            <option value="all">All Platforms</option>
                            <option value="youtube">YouTube</option>
                            <option value="tiktok">TikTok</option>
                            <option value="facebook">Facebook & Ins</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="ri-arrow-down-s-line text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm text-gray-400 mb-2"><?php _e("Song"); ?></label>
                    <div class="relative">
                        <input type="search" placeholder="Search by song title..." class="w-full border border-[rgba(255,255,255,0.1)] rounded-button pl-10" style="height:2.5rem;">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-sm text-gray-400 mb-2"><?php _e("Date"); ?></label>
                    <div class="relative">
                        <select class="w-full bg-[rgba(255,255,255,0.8)] border border-[rgba(255,255,255,0.1)] rounded-button px-4 py-2 appearance-none" style="padding-right: 2rem;height:2.5rem;">
                            <option value="all">All Month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="ri-arrow-down-s-line text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <button class="bg-primary hover:bg-opacity-90 px-8 rounded-button flex items-center justify-center whitespace-nowrap" style="height: 2.5rem;color: #ffffff;">
                    <?php _e("Search"); ?>
                </button>
            </div>
            <?php endif; ?>
            <!-- Song Data Table -->
            <div class="mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="text-left text-gray-400 text-sm">
                            <th style="padding: 1rem;" class=" whitespace-nowrap">Song</th>
                            <th style="padding: 1rem;" class=" whitespace-nowrap">Platform</th>
                            <th style="padding: 1rem;" class=" whitespace-nowrap">Promotion Period</th>
                            <th style="padding: 1rem;" class=" whitespace-nowrap">Total Plays</th>
                            <th style="padding: 1rem;" class=" whitespace-nowrap">Top Countries</th>
                            <th style="padding: 1rem;" class=" whitespace-nowrap">Earnings</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($songsDataList as $index => $songDataItem){ ?>
                            <tr class="table-row" data-index="<?php _ec($index) ?>" style="display: <?php if ($index < 5){ echo "table-row"; }else{ echo 'none';} ?>">
                                <td class="" style="padding: 1rem;">
                                    <div class="flex items-center">
                                        <div class="rounded bg-[#2a2a2a] flex items-center justify-center mr-3" style="height: 3rem; width: 3rem;">
                                            <img src="<?php _ec($songDataItem['imgSrc'])?>" class="w-full h-full object-cover" alt="<?php _ec($songDataItem['title'])?>">
                                        </div>
                                        <div>
                                            <p class= font-medium"><?php _ec($songDataItem['title'])?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="" style="padding: 1rem;">
                                    <div class="flex items-center">
                                        <?php if($songDataItem['icon'] != ""){ ?>
                                            <div class="w-6 h-6 flex items-center justify-center mr-2 text-[#FF0000]">
                                                <i class="ri-<?php echo $songDataItem['icon']; ?>-fill"></i>
                                            </div>
                                        <?php } ?>
                                        <span class=><?php _ec($songDataItem['platform'])?></span>
                                    </div>
                                </td>
                                <td class=" text-gray-800" style="padding: 1rem;"><?php _ec($songDataItem['date'])?></td style="padding: 1rem;">
                                <td class=" font-medium" style="padding: 1rem;"><?php _ec($songDataItem['views'])?></td style="padding: 1rem;">
                                <td class=" font-medium" style="padding: 1rem;"><?php _ec($songDataItem['topCountry'])?></td style="padding: 1rem;">
                                <td class=" font-medium text-green-400" style="padding: 1rem;">$<?php _ec($songDataItem['earns'])?></td style="padding: 1rem;">
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class=" border-t border-[rgba(255,255,255,0.05)] flex justify-between items-center" style="padding: 1rem;">
                    <div class="text-sm text-gray-400" id="pageInfo">Showing 1 of 5 songs</div>
                    <div class="flex space-x-2">
                        <button id="prevBtn" disabled><?php echo _e('previous'); ?></button>
                        <button id="nextBtn"><?php echo _e('next'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <div class="mb-8">
        <div class="mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-medium"><?php echo _e('Licensed Songs'); ?></h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
            <?php if (!empty($songsList)): ?>
            <?php foreach ($songsList as $songItem){ ?>
                <div style="box-shadow: 5px 5px 5px rgba(0,0,0,0.05)" class="group relative bg-[rgba(245,245,245,0.8)] backdrop-blur-sm border border-[rgba(255,255,255,0.05)] rounded-lg overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:border-[rgba(0,180,255,0.2)] hover:shadow-[0_0_15px_rgba(0,180,255,0.2)]">
                    <div class="relative aspect-square">
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-[rgba(0,0,0,0.8)] z-10"></div>
                        <img src="<?php _ec($songItem['imgSrc'])?>" class="w-full h-full object-cover" alt="<?php _ec($songItem['title'])?>">
                        <a href="<?php _ec($songItem['audioSrc'])?>" target="_blank">
                            <div class="absolute inset-0 bg-[rgba(0,180,255,0.1)] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center z-20">
                                <i class="ri-play-circle-line text-4xl"></i>
                            </div>
                        </a>
                    </div>
                    <div class="" style="padding: 1rem;">
                        <h4 class="font-medium mb-1" style="font-size: medium;"><?php _ec($songItem['title'])?></h4>
                        <p class="text-gray-400 text-sm mb-3"><?php _ec($songItem['upc'])?></p>
                        <div class="flex items-center gap-2 mb-3">
                            <div class="flex items-center justify-center">
                                <i class="ri-tiktok-fill text-sm" style="font-size: 20px; color: #000000"></i>
                            </div>
                            <div class="flex items-center justify-center">
                                <i class="ri-youtube-fill text-sm" style="font-size: 20px; color: #FF0000"></i>
                            </div>
                            <div class="flex items-center justify-center">
                                <i class="ri-instagram-fill text-sm" style="font-size: 20px; color: #FA57C1"></i>
                            </div>
                            <div class="flex items-center justify-center">
                                <i class="ri-facebook-fill text-sm" style="font-size: 20px; color: #00b4ff"></i>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400 text-xs"></span>
                            <a href="<?php _ec($songItem['audioSrc'])?>" download="<?php _ec($songItem['upc']).$songItem['title'].".".$songItem['fileExtension']; ?>">
                                <button class="bg-primary hover:bg-opacity-90 text-sm px-4 py-1.5 rounded-button flex items-center whitespace-nowrap text-white">
                                    <i class="ri-rocket-line mr-1"></i>
                                    <?php echo _e("Download"); ?>
                                </button>
                            </a>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php else: ?>
                <div class="col-span-full py-12 text-center">
                    <div class="inline-block p-10 bg-white/80 backdrop-blur-sm rounded-lg border border-gray-200">
                        <i class="ri-alert-line text-4xl text-yellow-500 mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-800 mb-2"><?php _e("No Licensed Music")?></h3>
                        <p class="text-gray-600"><?php _e("Please wait for administrator authorization")?></p>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>
</main>
<?php

$str_post_succeed = $post_succeed;
$str_post_succeed = str_replace("[", "", $str_post_succeed);
$str_post_succeed = str_replace("]", "", $str_post_succeed);
$arr_post_succeed = explode(",", $str_post_succeed);


$str_post_failed = $post_failed;
$str_post_failed = str_replace("[", "", $str_post_failed);
$str_post_failed = str_replace("]", "", $str_post_failed);
$arr_post_failed = explode(",", $str_post_failed );

$arr_post_total = [];

foreach ($arr_post_succeed as $key => $value) {
    $arr_post_total[] = $value + $arr_post_failed[$key];
}

$post_total = implode(",", $arr_post_total);
$post_total = "[".$post_total."]";
?>

<script type="text/javascript">
    $(function(){
        Core.chart({
            id: 'post_by_status_succeed_chart',
            categories: <?php _ec( $date )?>,
            legend: false,
            stacking: false,
            xvisible: false,
            yvisible: false,
            margin: [0,0,0,0],
            data: [{
                type: 'areaspline',
                name: '<?php _e("Post succeed")?>',
                lineColor: 'rgba(80, 205, 127, 1)',
                color: {
                    linearGradient : {
                        x1: 1,
                        y1: 0,
                        x2: 0,
                        y2: 0
                    },
                    stops : [
                        [0, 'rgba(80, 205, 127, 0.7)'],
                        [1, 'rgba(80, 205, 127, 0)'],
                    ]
                },
                fillColor : {
                    linearGradient : {
                        x1: 1,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops : [
                        [0, 'rgba(80, 205, 127, 0.7)'],
                        [1, 'rgba(80, 205, 127, 0)'],
                    ]
                },
                marker: {
                    enabled: false
                },
                data: <?php _ec( $post_succeed )?>,
            }]
        });

        Core.chart({
            id: 'post_by_status_failed_chart',
            categories: <?php _ec( $date )?>,
            legend: false,
            stacking: false,
            xvisible: false,
            yvisible: false,
            margin: [0,0,0,0],
            data: [{
                type: 'areaspline',
                name: '<?php _e("Post failed")?>',
                lineColor: 'rgba(241, 65, 108, 1)',
                color: {
                    linearGradient : {
                        x1: 1,
                        y1: 0,
                        x2: 0,
                        y2: 0
                    },
                    stops : [
                        [0, 'rgba(241, 65, 108, 0.7)'],
                        [1, 'rgba(241, 65, 108, 0)'],
                    ]
                },
                fillColor : {
                    linearGradient : {
                        x1: 1,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops : [
                        [0, 'rgba(241, 65, 108, 0.7)'],
                        [1, 'rgba(241, 65, 108, 0)'],
                    ]
                },
                marker: {
                    enabled: false
                },
                data: <?php _ec( $post_failed )?>,
            }]
        });

        Core.chart({
            id: 'post_by_status_total_chart',
            categories: <?php _ec( $date )?>,
            legend: false,
            stacking: false,
            xvisible: false,
            yvisible: false,
            margin: [0,0,0,0],
            data: [{
                type: 'areaspline',
                name: '<?php _e("Post failed")?>',
                lineColor: 'rgba(0, 148, 247, 1)',
                color: {
                    linearGradient : {
                        x1: 1,
                        y1: 0,
                        x2: 0,
                        y2: 0
                    },
                    stops : [
                        [0, 'rgba(0, 148, 247, 0.7)'],
                        [1, 'rgba(0, 148, 247, 0)'],
                    ]
                },
                fillColor : {
                    linearGradient : {
                        x1: 1,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops : [
                        [0, 'rgba(0, 148, 247, 0.7)'],
                        [1, 'rgba(0, 148, 247, 0)'],
                    ]
                },
                marker: {
                    enabled: false
                },
                data: <?php _ec( $post_total )?>,
            }]
        });

        Core.column_chart({
            id: 'post_by_status_chart',
            categories: <?php _ec( $date )?>,
            legend: true,
            stacking: true,
            xvisible: true,
            yvisible: true,
            data: [{
                type: 'column',
                name: '<?php _e("Post succeed")?>',
                lineColor: 'rgba(80, 205, 127, 1)',
                color: 'rgba(80, 205, 127, 1)',
                marker: {
                    enabled: false
                },
                data: <?php _ec( $post_succeed )?>,
            },
                {
                    type: 'column',
                    name: '<?php _e("Post failed")?>',
                    lineColor: 'rgba(241, 65, 108, 1)',
                    color: 'rgba(241, 65, 108, 1)',
                    marker: {
                        enabled: false
                    },
                    data: <?php _ec( $post_failed )?>,
                }]
        });

        Core.chart({
            id: 'post_by_type_chart',
            categories: '',
            legend: true,
            data: [{
                type: 'pie',
                name: '<?php _e("Percent")?>',
                data: [{
                    name: '<?php _e("Media")?>',
                    y: <?php _ec( $percent_media_succeed )?>,
                    color: 'rgba(80, 205, 127, 0.7)',
                },
                    {
                        name: '<?php _e("Link")?>',
                        y: <?php _ec( $percent_link_succeed )?>,
                        color: 'rgba(241, 65, 108, 0.7)',
                    },
                    {
                        name: '<?php _e("Text")?>',
                        y: <?php _ec( $percent_text_succeed )?>,
                        color: 'rgba(0, 148, 247, 0.7)',
                    }],
                size: 250,
                innerSize: '60%',
                showInLegend: true,
                dataLabels: {
                    enabled: false
                }
            }]
        });

        const prevBtn = $("#prevBtn");
        const nextBtn = $("#nextBtn");
        // 调试输出按钮元素
        console.log('prevBtn:', prevBtn);
        console.log('nextBtn:', nextBtn);
        if (prevBtn && nextBtn) {
            const rows = $('.table-row');
            const pageInfoElement = $('#pageInfo');
            const totalRows = rows.length;
            const perPage = 5;
            let currentPage = 1;
            const totalPages = Math.ceil(totalRows / perPage);
            // 初始化显示
            updateDisplay();
            // 上一页按钮
            $("#prevBtn").click(function(){
                if (currentPage > 1) {
                    currentPage--;
                    updateDisplay();
                }
            })
            $("#nextBtn").click(function(){
                if (currentPage < totalPages) {
                    currentPage++;
                    updateDisplay();
                }
            })
            function updateDisplay() {
                // 计算显示范围
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;
                // 更新行显示状态
                $('.table-row').each(function(index) {
                    $(this).css('display', (index >= start && index < end) ? 'table-row' : 'none');
                });
                pageInfoElement.text(`Showing ${start + 1}-${Math.min(end, totalRows)} of ${totalRows}`);
                // 更新按钮状态
                document.getElementById('prevBtn').disabled = currentPage === 1;
                document.getElementById('nextBtn').disabled = currentPage === totalPages;
            }
        } else {
            console.error('分页按钮元素未找到');
        }
    });
</script>