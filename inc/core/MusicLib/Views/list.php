<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
<style>
    :where([class^="ri-"])::before {
        content: "\f3c2";
    }

    .song-card {
        transition: all 0.3s ease;
    }
    .song-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .play-button {
        transition: all 0.2s ease;
    }
    .play-button:hover {
        transform: scale(1.1);
    }
    .tag {
        font-size: 0.75rem;
        padding: 0.5rem 0.5rem;
        border-radius: 9999px;
        font-weight: 500;
        margin-right: 1rem !important;
    }
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 2rem;
    }
    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        border-radius: 0.375rem;
        text-decoration: none;
        color: #4b5563;
        border: 1px solid #e5e7eb;
        transition: all 0.2s ease;
    }
    .pagination a:hover {
        background-color: #f3f4f6;
    }
    .pagination .active {
        background-color: #6366f1;
        color: white;
        border-color: #6366f1;
    }
    .pagination .disabled {
        color: #9ca3af;
        pointer-events: none;
        background-color: #f3f4f6;
    }
</style>
<div class="container my-5">
    <nav class="border-b border-gray-200 sticky top-0 z-40">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <div class="flex space-x-1 bg-gray-100 rounded-full p-1">
                        <a href="/MusicLib/index" id="songsTab" class="px-6 py-2 rounded-full text-sm font-medium transition-all duration-200 bg-primary text-white whitespace-nowrap !rounded-button" style="cursor:pointer;">
                            Songs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div id="songsPage" class="px-6 py-8">
        <!-- Search and Sort Controls -->
        <div class="flex items-center justify-between mb-8">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <div class="w-5 h-5 flex items-center justify-center">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                </div>
                <input type="text" name="search" id="searchInput"
                       value="<?php echo isset($search) ? htmlspecialchars($search) : ''; ?>"
                       placeholder="Search songs..."
                       class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                       onkeydown="handleSearchKeydown(event)">
            </div>
        </div>
        <!-- Songs List -->
        <div class="space-y-4">
            <?php if (!empty($music_list)): ?>
            <?php foreach ($music_list as $item): ?>
            <div class="song-card bg-white rounded-lg shadow-sm border border-gray-100" style="padding: 1.5rem;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 flex-1">
                        <div class="bg-gradient-to-br from-indigo-400 to-purple-400 rounded-lg flex items-center justify-center" style="width: 3rem;height: 3rem;">
                            <img src="<?php echo $item['cover_url']; ?>" class="w-full h-full object-cover" alt="<?php echo $item['title']; ?>">
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 mb-1"><?php echo $item['title']; ?></div>
                            <p class="text-sm text-gray-600"><?php echo $item['artist']; ?></p>
                        </div>
                        <div class="tag bg-purple-100 text-purple-700"><?php echo $item['genre']; ?></div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-md-center text-gray-500"><?php echo $item['formatted_duration']; ?></span>
                        <div class="flex items-center space-x-2">
                            <a href="<?php echo $item['file_src']; ?>" download="<?php echo $item['title']; ?>"  class="flex items-center justify-center text-gray-400 hover:text-primary transition-colors" onclick="downloadSong(this)" title="Download song" style="width: 2rem;height: 2rem;">
                                <i class="ri-download-line"></i>
                            </a>
                            <a href="<?php echo $item['file_src']; ?>" target="_blank"  class="play-button bg-primary text-white rounded-full flex items-center justify-center hover:bg-primary/90" style="width: 2.5rem;height: 2.5rem;">
                                <div class="w-5 h-5 flex items-center justify-center">
                                    <i class="ri-play-fill"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <!-- 空状态提示 -->
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="text-6xl mb-4">🎵</div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Public Music Library is Empty</h3>
                <p class="text-gray-500 mb-6">No unlicensed songs available at the moment</p>
                <p class="text-gray-500">公共音乐库待添加，暂无歌曲</p>
            </div>
            <?php endif; ?>
        </div>
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
            <div class="text-sm text-gray-600" id="pageInfo">
                Showing <?php echo (($datatable['current_page'] - 1) * $datatable['per_page'] + 1); ?>
                to <?php echo min($datatable['current_page'] * $datatable['per_page'], $datatable['total_items']); ?>
                of <?php echo $datatable['total_items']; ?> songs
            </div>

            <div class="flex items-center space-x-2">
                <a href="/MusicLib/index/<?php echo max(1, $datatable['current_page'] - 1); ?>"
                   class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 <?php echo ($datatable['current_page'] == 1) ? 'text-gray-400 cursor-not-allowed bg-gray-100' : 'text-gray-700 hover:bg-gray-50'; ?>"
                    <?php echo ($datatable['current_page'] == 1) ? 'disabled' : ''; ?>>
                    Previous
                </a>

                <div class="flex space-x-1">
                    <?php
                    // 显示页码按钮
                    $start = max(1, $datatable['current_page'] - 2);
                    $end = min($datatable['total_pages'], $datatable['current_page'] + 2);

                    if ($start > 1) {
                        echo '<a href="/MusicLib/index/1" class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50">1</a>';
                        if ($start > 2) {
                            echo '<span class="px-4 py-2 text-sm font-medium">...</span>';
                        }
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        echo '<a href="/MusicLib/index/'.$i.'" class="px-4 py-2 text-sm font-medium rounded-md border '.($i == $datatable['current_page'] ? 'bg-primary text-white border-primary' : 'border-gray-300 hover:bg-gray-50').'">'.$i.'</a>';
                    }

                    if ($end < $datatable['total_pages']) {
                        if ($end < $datatable['total_pages'] - 1) {
                            echo '<span class="px-4 py-2 text-sm font-medium">...</span>';
                        }
                        echo '<a href="/MusicLib/index/'.$datatable['total_pages'].'" class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50">'.$datatable['total_pages'].'</a>';
                    }
                    ?>
                </div>

                <a href="/MusicLib/index/<?php echo min($datatable['total_pages'], $datatable['current_page'] + 1); ?>"
                   class="px-4 py-2 text-sm font-medium rounded-md border border-gray-300 <?php echo ($datatable['current_page'] == $datatable['total_pages']) ? 'text-gray-400 cursor-not-allowed bg-gray-100' : 'text-gray-700 hover:bg-gray-50'; ?>"
                    <?php echo ($datatable['current_page'] == $datatable['total_pages']) ? 'disabled' : ''; ?>>
                    Next
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    function handleSearchKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const searchTerm = document.getElementById('searchInput').value.trim();
            // 更新URL为 /MusicLib/index/1/searchTerm
            window.location.href = `/MusicLib/index/1/${encodeURIComponent(searchTerm)}`;
        }
    }

    // 更新分页链接以包含搜索词
    document.addEventListener('DOMContentLoaded', function() {
        const searchTerm = "<?php echo isset($search) ? $search : ''; ?>";
        if (searchTerm) {
            const paginationLinks = document.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && !href.includes(searchTerm)) {
                    link.setAttribute('href', href + '/' + encodeURIComponent(searchTerm));
                }
            });
        }
    });
</script>