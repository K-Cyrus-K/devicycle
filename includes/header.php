<?php
require_once "../classes/UserLogic.php";
require_once "functions.php";

// ログイン判定
$logged_in = UserLogic::checkLogin();
$notification_count = 0;
$expiring_devices = [];

if ($logged_in) {
    $login_user = $_SESSION["login_user"];
    require_once "../classes/DeviceLogic.php";
    $expiring_devices = DeviceLogic::getExpiringDevices($login_user['uid']);
    $notification_count = count($expiring_devices);
}

// 現在のファイル名を取得してアクティブリンクを判定
$current_page = basename($_SERVER['SCRIPT_NAME']);

$pageDescription = "Devicycleは、みんなのガジェットデータを集めて、電子機器の平均寿命と脆弱性を共有するサービスです。";

ini_set("display_errors", true);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?></title>

    <link rel="stylesheet" href="./assets/css/header.css">
    <link rel="stylesheet" href="./assets/css/output.css">
    <link rel="stylesheet" href="./assets/css/animations.css">
    <link rel="stylesheet" href="./assets/css/rating.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/js/all.min.js"
        integrity="sha512-6BTOlkauINO65nLhXhthZMtepgJSghyimIalb+crKRPhvhmsCdnIuGcVbR5/aQY2A+260iC1OPy1oCdB6pSSwQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="icon" href="./assets/img/favicon.ico" />
    <script>
        const BASE_URL = "<?php echo BASE_URL; ?>";
    </script>
    <script src="<?php echo BASE_URL; ?>public/assets/js/header.js" defer></script>
</head>

<body class="bg-[#0D0E0E] text-[#D1D1D1] antialiased selection:bg-[#0071D3] selection:text-white">
    <header id="main-header" class="header-dark fixed top-0 left-0 w-full z-[999] px-6 md:px-10 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between">

            <!-- Logo -->
            <div class="flex items-center gap-2">
                <a href="./" class="flex items-center gap-2 group">

                    <span class="text-xl font-black tracking-tighter text-white uppercase">デジクル・DEVICYCLE</span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden xl:block">
                <ul class="flex items-center gap-x-10 text-sm">
                    <li>
                        <a href="./"
                            class="nav-link-modern <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">ホーム</a>
                    </li>
                    <li>
                        <a href="./hall_of_fame.php"
                            class="nav-link-modern <?php echo $current_page == 'hall_of_fame.php' ? 'active' : ''; ?>">殿堂入り</a>
                    </li>
                    <?php if ($logged_in): ?>
                        <li>
                            <a href="./mypage.php"
                                class="nav-link-modern <?php echo $current_page == 'mypage.php' ? 'active' : ''; ?>">マイページ</a>
                        </li>
                        <li>
                            <a href="./mypage.php#wishlist" class="nav-link-modern">ウィッシュリスト</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- User Area -->
            <div class="flex items-center gap-4">
                <?php if ($logged_in): ?>
                    <!-- Notification Bell -->
                    <div class="relative" id="notification-wrapper">
                        <button id="notification-bell"
                            class="w-10 h-10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 rounded-xl transition-all relative">
                            <i class="fa-solid fa-bell"></i>
                            <?php if ($notification_count > 0): ?>
                                <span id="notification-badge"
                                    class="absolute top-2 right-2 w-4 h-4 bg-red-500 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-[#0d0e0e] animate-bounce">
                                    <?php echo $notification_count; ?>
                                </span>
                            <?php endif; ?>
                        </button>

                        <!-- Dropdown (Click-based) -->
                        <div id="notification-dropdown"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-[2rem] shadow-2xl border border-slate-100 overflow-hidden hidden z-[1000] origin-top-right transition-all">
                            <div class="p-5 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">通知中心</h4>
                                <span
                                    class="text-[10px] font-bold bg-blue-100 text-[#0071D3] px-2 py-0.5 rounded-full"><?php echo $notification_count; ?>件</span>
                            </div>
                            <div class="max-h-80 overflow-y-auto custom-scrollbar">
                                <?php if ($notification_count > 0): ?>
                                    <?php foreach ($expiring_devices as $noti): ?>
                                        <div class="p-4 border-b border-slate-50 hover:bg-blue-50/50 transition-colors">
                                            <p class="text-xs font-bold text-slate-800 line-clamp-1">
                                                <?php echo h($noti['device_name']); ?></p>
                                            <div class="flex gap-2 mt-1.5">
                                                <?php if ($noti['return_days_left'] !== null && $noti['return_days_left'] <= 7): ?>
                                                    <p
                                                        class="text-[9px] font-black text-red-500 bg-red-50 px-1.5 py-0.5 rounded border border-red-100">
                                                        返品まで <?php echo $noti['return_days_left']; ?>日
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($noti['warranty_days_left'] !== null && $noti['warranty_days_left'] <= 30): ?>
                                                    <p
                                                        class="text-[9px] font-black text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-100">
                                                        保証まで <?php echo $noti['warranty_days_left']; ?>日
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="p-10 text-center">
                                        <i class="fa-solid fa-bell-slash text-slate-200 text-3xl mb-3"></i>
                                        <p class="text-xs text-slate-400 font-bold">通知はありません</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-2 bg-slate-50 border-t border-slate-100">
                                <button id="stop-session-alert"
                                    class="w-full py-2.5 text-[10px] font-black text-slate-400 hover:text-slate-600 transition-colors uppercase tracking-tighter">
                                    <i class="fa-solid fa-eye-slash mr-1"></i> 今セッションの通知を停止
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:flex flex-col items-end mr-2">
                        <span
                            class="text-[0.6rem] font-bold text-slate-500 uppercase tracking-widest leading-none mb-1">おかえりなさい</span>
                        <span
                            class="text-base font-bold text-slate-300 leading-none"><?php echo h($login_user["uname"]) ?></span>
                    </div>

                    <?php if (UserLogic::isAdmin()): ?>
                        <a href="./admin.php"
                            class="hidden sm:flex items-center gap-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 px-3 py-2 rounded-xl transition-colors border border-amber-500/30">
                            <span class="admin-badge">Admin</span>
                            <i class="fa-solid fa-gauge-high"></i>
                        </a>
                    <?php endif ?>

                    <form action="../actions/logout.php" method="post" class="m-0">
                        <button type="submit" name="logout" value="1"
                            class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-red-500 hover:bg-red-500/10 rounded-xl transition-all"
                            title="ログアウト">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                    </form>
                <?php else: ?>
                    <button id="loginBtn"
                        class="login bg-[#0071D3] hover:bg-[#005bb5] text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-blue-500/20 text-sm">
                        ログイン
                    </button>
                <?php endif ?>

                <!-- Mobile Menu Toggle -->
                <button id="toggleOpen"
                    class="xl:hidden w-10 h-10 flex items-center justify-center text-slate-300 hover:bg-[#333] rounded-xl transition-colors">
                    <i class="fa-solid fa-bars-staggered text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div id="mobileMenuOverlay"
            class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[1000] hidden opacity-0 transition-opacity duration-300">
        </div>

        <!-- Mobile Menu Panel -->
        <div id="mobileMenu" class="xl:hidden">
            <div class="flex justify-between items-center mb-10">
                <span class="font-black text-white tracking-tighter text-xl uppercase">DEVICYCLE</span>
                <button id="toggleClose"
                    class="w-10 h-10 flex items-center justify-center text-slate-400 hover:bg-[#333] rounded-full transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <nav class="flex-1">
                <ul class="space-y-6">
                    <li>
                        <a href="./"
                            class="flex items-center gap-4 text-lg font-bold <?php echo $current_page == 'index.php' ? 'text-[#0071D3]' : 'text-slate-300'; ?> hover:text-white transition-colors">
                            <i class="fa-solid fa-house w-6 text-center"></i> ホーム
                        </a>
                    </li>
                    <li>
                        <a href="./hall_of_fame.php"
                            class="flex items-center gap-4 text-lg font-bold <?php echo $current_page == 'hall_of_fame.php' ? 'text-[#0071D3]' : 'text-slate-300'; ?> hover:text-white transition-colors">
                            <i class="fa-solid fa-crown w-6 text-center"></i> 殿堂入り
                        </a>
                    </li>
                    <?php if ($logged_in): ?>
                        <li>
                            <a href="./mypage.php"
                                class="flex items-center gap-4 text-lg font-bold <?php echo $current_page == 'mypage.php' ? 'text-[#0071D3]' : 'text-slate-300'; ?> hover:text-white transition-colors">
                                <i class="fa-solid fa-user-circle w-6 text-center"></i> マイページ
                            </a>
                        </li>
                        <li>
                            <a href="./mypage.php#wishlist"
                                class="flex items-center gap-4 text-lg font-bold text-slate-300 hover:text-white transition-colors">
                                <i class="fa-solid fa-heart w-6 text-center"></i> ウィッシュリスト
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="mt-auto pt-10 border-t border-[#333]">
                <?php if (!$logged_in): ?>
                    <button
                        class="login w-full bg-[#0071D3] hover:bg-[#005bb5] text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-500/20 transition-all">ログイン
                        / 新規登録</button>
                <?php else: ?>
                    <div class="flex items-center gap-4 p-4 bg-[#222] rounded-2xl mb-6 border border-[#333]">
                        <div
                            class="w-12 h-12 bg-[#0071D3] rounded-full flex items-center justify-center text-white font-black text-lg">
                            <?php echo mb_substr($login_user["uname"], 0, 1) ?>
                        </div>
                        <div class="min-w-0">
                            <p class="text-base font-bold text-white truncate"><?php echo h($login_user["uname"]) ?></p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest">Active Member</p>
                        </div>
                    </div>
                    <form action="../actions/logout.php" method="post">
                        <button type="submit" name="logout" value="1"
                            class="w-full bg-[#333] hover:bg-red-500/20 hover:text-red-500 text-slate-300 font-bold py-4 rounded-2xl transition-all border border-[#444]">
                            ログアウト
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php require_once "../includes/dialogs/auth_dialog.php"; ?>
    </header>

    <!-- Header Spacer -->
    <div class="h-[64px]"></div>