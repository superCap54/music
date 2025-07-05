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
    .tooltip {
        visibility: hidden;
        position: absolute;
        background-color: rgba(255, 255, 255, 0.9);
        color: #2C3E50;
        padding: 6px 12px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        font-size: 12px;
        white-space: nowrap;
        opacity: 0;
        transform: translateY(5px);
        transition: opacity 0.3s, transform 0.3s, visibility 0.3s;
        z-index: 10;
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
        <div class="neumorphic-button !rounded-button px-4 py-2 text-gray-800 whitespace-nowrap">All Workflows</div>
        <div class="neumorphic-button !rounded-button px-4 py-2 text-gray-500 whitespace-nowrap">Active</div>
        <div class="neumorphic-button !rounded-button px-4 py-2 text-gray-500 whitespace-nowrap">Paused</div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Workflow Card 1 -->
        <div class="neumorphic rounded-2xl" style="padding: 1.5rem;">
            <div class="flex justify-between items-start" style="margin-bottom: 1rem;">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 flex items-center justify-center">
                        <i class="ri-youtube-fill text-[#FF0000]"></i>
                    </div>
                    <h4 class="font-medium text-gray-800">YouTube Weekly Digest</h4>
                </div>
            </div>
            <p class="text-gray-600 text-sm" style="margin-bottom: 1rem;">Automatically publishes weekly content digests to YouTube channel.</p>
            <div class="neumorphic-inset flowchart-area rounded-lg flex items-center justify-center" style="padding:1rem;margin-bottom:1.25rem;">
                <div class="text-center">
                    <div class="flex justify-center space-x-4" style="margin-bottom: 0.5rem;">
                        <div class="flex items-center justify-center neumorphic-button rounded-full" style="width: 3rem;height: 3rem;">
                            <i class="ri-article-line text-primary text-xl"></i>
                        </div>
                        <div class="flex items-center justify-center neumorphic-button rounded-full" style="width: 3rem;height: 3rem;">
                            <i class="ri-video-line text-primary text-xl"></i>
                        </div>
                    </div>
                    <div class="flex justify-center" style="margin-bottom: 0.5rem;">
                        <div class="flex items-center justify-center" style="width: 1.5rem;height: 1.5rem;">
                            <i class="ri-arrow-down-line text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex justify-center" style="margin-bottom: 0.5rem;">
                        <div class="flex items-center justify-center neumorphic-button rounded-full" style="width: 3rem;height: 3rem;">
                            <i class="ri-chat-4-line text-primary text-xl"></i>
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
            </div>
            <div class="flex justify-between items-center">
                <div class="flex space-x-2">
                    <button class="configure-btn neumorphic-button !rounded-button px-3 py-1 text-sm text-primary whitespace-nowrap">Configure</button>
                </div>
                <div>
                    <input type="checkbox" id="toggle1" class="toggle-checkbox hidden">
                    <label for="toggle1" class="toggle-switch block cursor-pointer"></label>
                </div>
            </div>
        </div>
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
        <div class="p-6">
            <div class="aspect-video rounded-xl overflow-hidden mb-6 neumorphic-inset">
                <img src="https://readdy.ai/api/search-image?query=3D%20render%20of%20a%20modern%20automation%20workflow%20process%20visualization%20with%20connected%20nodes%20and%20flowing%20data%20streams%2C%20clean%20minimal%20design%20with%20soft%20lighting%20and%20subtle%20gradients%2C%20professional%20enterprise%20software%20interface&width=800&height=450&seq=workflow1&orientation=landscape" class="w-full h-full object-cover" alt="Workflow Process">
            </div>
            <div class="text-center">
                <button class="neumorphic-button !rounded-button px-8 py-3 text-white bg-primary hover:bg-opacity-90 transition-colors" onclick="closeModal()">
                    Confirm Configuration
                </button>
            </div>
        </div>
    </div>
</div>
<script id="modalInteraction">
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('configureModal');
        const configureBtns = document.querySelectorAll('.configure-btn');
        configureBtns.forEach(btn => {
            btn.addEventListener('click', function() {
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
</script>