<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI-Powered Music Platform</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config = {
            theme: {
                extend: {
                    colors: {primary: '#6366f1', secondary: '#a855f7'},
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap"
          rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }

        body {
            background-color: #0f0f0f;
            color: #fff;
            font-family: 'Inter', sans-serif;
        }

        .heading-font {
            font-family: 'Space Grotesk', sans-serif;
        }

        .hero-bg {
            background: linear-gradient(135deg, rgba(15, 15, 15, 0.9) 0%, rgba(15, 15, 15, 0.7) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero-bg::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background-image: url('https://readdy.ai/api/search-image?query=dark%20futuristic%20music%20studio%20with%20neon%20blue%20and%20purple%20lights%2C%20flowing%20waveforms%2C%20abstract%20digital%20audio%20visualization%2C%20high-tech%20music%20production%20environment%20with%20subtle%20glowing%20elements%2C%20dark%20atmosphere%2C%20cinematic%20lighting&width=1920&height=1080&seq=1&orientation=landscape');
            background-size: cover;
            background-position: center;
            opacity: 0.6;
            z-index: -1;
        }

        .glow-button {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .glow-button::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, rgba(99, 102, 241, 0) 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .glow-button:hover::after {
            opacity: 1;
        }

        .glow-button-secondary {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .glow-button-secondary::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.3) 0%, rgba(168, 85, 247, 0) 70%);
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .glow-button-secondary:hover::after {
            opacity: 1;
        }

        .feature-card {
            background: rgba(26, 26, 26, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3), 0 0 15px rgba(99, 102, 241, 0.2);
        }

        .waveform {
            position: relative;
            height: 60px;
            width: 100%;
            background: rgba(26, 26, 26, 0.4);
            border-radius: 8px;
            overflow: hidden;
        }

        .waveform::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg,
            rgba(99, 102, 241, 0.1) 0%,
            rgba(168, 85, 247, 0.1) 50%,
            rgba(99, 102, 241, 0.1) 100%);
            background-size: 200% 100%;
            animation: waveMove 15s linear infinite;
        }

        @keyframes waveMove {
            0% {
                background-position: 0% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        .song-card {
            background: rgba(26, 26, 26, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .song-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3), 0 0 15px rgba(99, 102, 241, 0.2);
        }

        .glass-card {
            background: rgba(26, 26, 26, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .about-bg {
            position: relative;
            overflow: hidden;
        }

        .about-bg::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(168, 85, 247, 0.05) 100%);
            animation: gradientRotate 20s linear infinite;
            z-index: -1;
        }

        @keyframes gradientRotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        input[type="range"] {
            -webkit-appearance: none;
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            outline: none;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            background: #6366f1;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
        }

        .custom-checkbox {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .custom-checkbox.checked {
            background: #6366f1;
            border-color: #6366f1;
        }

        .custom-checkbox.checked::after {
            content: "";
            position: absolute;
            top: 4px;
            left: 7px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-switch::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .custom-switch.active {
            background: #6366f1;
        }

        .custom-switch.active::after {
            transform: translateX(24px);
        }
    </style>
</head>
<body class="min-h-screen">
<header class="bg-[#1a1a1a] fixed w-full z-50">
    <div class="container mx-auto px-6 py-4 flex items-center justify-between">
        <a href="/">
            <img alt="Logo" src="<?php _ec( get_option("website_logo_color", base_url("assets/img/logo-color.svg")) )?>" class="logo-big" style="height: 40px;">
        </a>
        <nav class="hidden md:flex items-center space-x-8">
            <a href="#features" class="text-gray-300 hover:text-white transition-colors">Features</a>
            <a href="#studio" class="text-gray-300 hover:text-white transition-colors">AI Studio</a>
            <a href="#promote" class="text-gray-300 hover:text-white transition-colors">Promote</a>
            <a href="#affiliate" class="text-gray-300 hover:text-white transition-colors">Affiliate</a>
            <a href="#about" class="text-gray-300 hover:text-white transition-colors">About</a>
        </nav>
        <div class="flex items-center space-x-4">
            <a href="<?php _ec(base_url("login")) ?>"
               class="text-gray-300 hover:text-white transition-colors"><?php _e("Login") ?></a>
            <a href="<?php _ec(base_url("signup")) ?>"
               class="bg-primary text-white px-5 py-2 !rounded-button whitespace-nowrap glow-button"><?php _e("Sign Up") ?></a>
            <button class="md:hidden w-10 h-10 flex items-center justify-center text-white">
                <i class="ri-menu-line ri-lg"></i>
            </button>
        </div>
    </div>
</header>
<section class="pt-32 pb-20">
    <div class="mih-1000">
        <?php _ec($content) ?>
    </div>
</section>
<footer class="bg-[#0f0f0f] py-16 border-t border-gray-800">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <div class="col-span-1 md:col-span-1">
                <a href="#" class="text-3xl font-['Pacifico'] text-white mb-4 inline-block">logo</a>
                <p class="text-gray-400 mb-6">Transform your voice into fully produced music with our AI-powered
                    platform.</p>
                <div class="flex space-x-4">
                    <a href="#"
                       class="w-10 h-10 !rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                        <i class="ri-twitter-x-line"></i>
                    </a>
                    <a href="#"
                       class="w-10 h-10 !rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                        <i class="ri-instagram-line"></i>
                    </a>
                    <a href="#"
                       class="w-10 h-10 !rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                        <i class="ri-tiktok-line"></i>
                    </a>
                    <a href="#"
                       class="w-10 h-10 !rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:text-white transition-colors">
                        <i class="ri-youtube-line"></i>
                    </a>
                </div>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Product</h4>
                <ul class="space-y-2">
                    <li><a href="#features" class="text-gray-400 hover:text-white transition-colors">Features</a></li>
                    <li><a href="#studio" class="text-gray-400 hover:text-white transition-colors">AI Studio</a></li>
                    <li><a href="#promote" class="text-gray-400 hover:text-white transition-colors">Promotion</a></li>
                    <li><a href="#affiliate" class="text-gray-400 hover:text-white transition-colors">Affiliate
                            Program</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Pricing</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Company</h4>
                <ul class="space-y-2">
                    <li><a href="#about" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Careers</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Press</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Legal</h4>
                <ul class="space-y-2">
                    <li><a href="/terms_of_service" class="text-gray-400 hover:text-white transition-colors">Terms of Service</a></li>
                    <li><a href="/privacy_policy" class="text-gray-400 hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Cookie Policy</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Copyright</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 mb-4 md:mb-0">© 2025 AI Music Platform. All rights reserved.</p>
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <i class="ri-visa-fill ri-lg text-gray-400 mr-2"></i>
                    <i class="ri-mastercard-fill ri-lg text-gray-400 mr-2"></i>
                    <i class="ri-paypal-fill ri-lg text-gray-400 mr-2"></i>
                    <i class="ri-apple-fill ri-lg text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>
</footer>
<script id="tempoSlider">
    document.addEventListener('DOMContentLoaded', function () {
        const tempoSlider = document.getElementById('tempoSlider');
        const tempoValue = document.getElementById('tempoValue');
        if (tempoSlider && tempoValue) {
            tempoSlider.addEventListener('input', function () {
                tempoValue.textContent = this.value;
            });
        }
    });
</script>
<script id="customCheckbox">
    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('termsCheckbox');
        if (checkbox) {
            checkbox.addEventListener('click', function () {
                this.classList.toggle('checked');
            });
        }
    });
</script>
<script id="customSwitch">
    document.addEventListener('DOMContentLoaded', function () {
        const switches = document.querySelectorAll('.custom-switch');
        switches.forEach(switchEl => {
            switchEl.addEventListener('click', function () {
                this.classList.toggle('active');
            });
        });
    });
</script>
</body>
</html>