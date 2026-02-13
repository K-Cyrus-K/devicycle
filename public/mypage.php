<?php
session_start();
require_once "../classes/DeviceLogic.php";
require_once "../classes/UserLogic.php";
require_once "../includes/functions.php";

$page = "マイページ";
$title = getTitle($page);

$logged_in = UserLogic::checkLogin();

if (!$logged_in) {
    $_SESSION["login_err"] = "ユーザ登録、もしくはログインしてからアクセスしてください！";
    header("Location: " . BASE_URL . "public/");
    return;
}

$login_user = $_SESSION["login_user"];
$uid = isset($_SESSION['login_user']) ? $_SESSION['login_user']['uid'] : '';

// ユーザーのデバイス一覧を取得
$my_devices = DeviceLogic::getDevicesByUserId($uid);
$expiring_devices = DeviceLogic::getExpiringDevices($uid);
$wishlist = DeviceLogic::getWishlistByUserId($uid);

// 各ステータスのデバイス数を計算
$all_count = count($my_devices);
$active_count = 0;
$storage_count = 0;
$archive_count = 0;
$public_count = 0;
$private_count = 0;

$total_spent = 0;
$total_current_value = 0;
$total_potential_resale = 0;

foreach ($my_devices as $device) {
    $status_name = h($device['status_name']);
    $purchase_price = (int) ($device['purchase_price'] ?? 0);
    $market_price = (int) ($device['market_price'] ?? 0);
    $sold_price = (int) ($device['sold_price'] ?? 0);
    $lifespan_months = (int) ($device['expected_lifespan_months'] ?? 36);
    $is_public = (int) ($device['is_public'] ?? 1);

    // 公開・非公開のカウント
    if ($is_public === 1) {
        $public_count++;
    } else {
        $private_count++;
    }

    $total_spent += $purchase_price;

    // 現在価値の計算
    $current_val = 0;
    if ($status_name === '売却済') {
        $current_val = $sold_price ?: ($market_price ?: 0);
    } else {
        if ($market_price > 0) {
            $current_val = $market_price;
        } else if ($purchase_price > 0) {
            $p_date = new DateTime($device['purchased_date']);
            $now = new DateTime();
            $diff = $p_date->diff($now);
            $months_passed = ($diff->y * 12) + $diff->m;
            $depreciation = min(0.9, $months_passed / $lifespan_months);
            $current_val = $purchase_price * (1 - $depreciation);
        }
    }
    $total_current_value += $current_val;

    if ($status_name === '使用中' || $status_name === '保管中') {
        $total_potential_resale += ($market_price ?: $current_val);
    }

    if ($status_name === '使用中') {
        $active_count++;
    } elseif ($status_name === '保管中') {
        $storage_count++;
    } elseif ($status_name === '故障' || $status_name === '売却済') {
        $archive_count++;
    }
}
?>

<script src="./assets/js/search.js" defer></script>
<script src="./assets/js/mypage.js" defer></script>
<link rel="stylesheet" href="./assets/css/animations.css">

<?php include "../includes/header.php" ?>

<main class="min-h-screen text-[#1d1d1f] bg-gradient-to-b from-blue-50 via-[#d3e9ff] to-[#d7d7ff] pb-20">
    <style>
        html {
            scroll-behavior: smooth;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-card-entry {
            opacity: 0;
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .filter-btn.active,
        .visibility-filter-btn.active {
            background-color: white;
            color: #0071D3;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>

    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-white/40 rounded-full blur-[100px] -z-10 mix-blend-overlay pointer-events-none"></div>

    <div class="pt-16 pb-12 px-4">
        <div id="search-container" class="relative max-w-xl mx-auto z-50 group">
            <div class="input flex px-5 py-4 rounded-full border-2 border-white/50 shadow-xl bg-white/60 backdrop-blur-md transition-all group-hover:border-[#0071D3] group-hover:bg-white group-hover:shadow-[#0071D3]/20">
                <input type="search" id="search-bar" placeholder="登録・検索したい装置を入力してください…"
                    class="w-full outline-none bg-transparent text-slate-800 text-lg placeholder-slate-500"
                    autocomplete="off">
                <i class="fa-solid fa-magnifying-glass text-[#0071D3] text-xl m-auto"></i>
            </div>
            <div id="results-list" class="bg-white border border-blue-100 shadow-2xl rounded-2xl absolute hidden w-full top-full mt-2 p-2 text-lg z-[100] text-left max-h-80 overflow-y-auto text-slate-700"></div>
        </div>
    </div>

    <div class="px-6 mb-16 relative z-0">
        <div class="max-w-6xl mx-auto">
            <!-- タイトルセクション -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-slate-900 inline-block relative tracking-wide after:content-[''] after:block after:w-16 after:h-1 after:bg-[#0071D3] after:mx-auto after:mt-4 after:rounded-full">
                    登録デバイス一覧
                </h2>
                <p class="text-slate-600 mt-4 text-sm">あなたが管理しているデバイスの状態を確認できます</p>
                <!-- 通知設定トグル -->
                            <div class="mt-6 flex justify-center items-center gap-6">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">アラート表示</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="global-alert-toggle" class="sr-only peer" checked>
                                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0071D3]"></div>
                                    </label>
                                </div>
                                <a href="#wishlist" class="text-xs font-bold text-rose-500 hover:text-rose-600 transition-colors flex items-center gap-1.5 px-4 py-2 bg-rose-50 rounded-full border border-rose-100 hover:shadow-sm">
                                    <i class="fa-solid fa-heart"></i>
                                    <span>ウィッシュリストへ</span>
                                </a>
                            </div>            </div>

            <!-- 期限間近のアラート -->
            <div id="expiring-devices-section" class="<?php echo empty($expiring_devices) ? 'hidden' : 'mb-12 animate-card-entry'; ?>">
                <div class="flex items-center gap-3 mb-4 ml-4">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center text-red-500 shadow-sm">
                        <i class="fa-solid fa-clock-rotate-left text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">期限間近のアラート</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if (!empty($expiring_devices)): ?>
                            <?php foreach ($expiring_devices as $device): ?>
                                    <div onclick="document.querySelector('.js-device-card[data-id=\'<?php echo $device['entry_id']; ?>\']').click()"
                                        class="bg-white/60 backdrop-blur-md border border-red-100 rounded-2xl p-4 flex items-center justify-between hover:bg-white/80 hover:shadow-lg hover:border-red-200 transition-all group cursor-pointer">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-base font-bold text-slate-800 truncate group-hover:text-[#0071D3] transition-colors"><?php echo h($device['device_name']); ?></p>
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                <?php if ($device['return_days_left'] !== null && $device['return_days_left'] <= 7): ?>
                                                        <span class="text-xs font-black text-red-500 bg-red-50 px-2 py-0.5 rounded border border-red-100">
                                                            <i class="fa-solid fa-reply mr-1"></i> 返品まで <?php echo $device['return_days_left']; ?>日
                                                        </span>
                                                <?php endif; ?>
                                                <?php if ($device['warranty_days_left'] !== null && $device['warranty_days_left'] <= 30): ?>
                                                        <span class="text-xs font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100">
                                                            <i class="fa-solid fa-shield-halved mr-1"></i> 保証まで <?php echo $device['warranty_days_left']; ?>日
                                                        </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="w-8 h-8 rounded-full bg-white text-slate-400 group-hover:text-[#0071D3] group-hover:shadow-md transition-all flex items-center justify-center">
                                            <i class="fa-solid fa-chevron-right text-xs"></i>
                                        </div>
                                    </div>
                            <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 資産サマリー -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-12">
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border border-white shadow-xl flex flex-col items-center text-center">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">現在の総資産価値</p>
                    <p class="text-3xl font-black text-slate-900"><span class="text-lg font-bold text-slate-400 mr-1">¥</span><?php echo number_format($total_current_value); ?></p>
                    <div class="mt-3 flex items-center gap-2 text-xs font-bold py-1 px-3 rounded-full bg-green-100 text-green-600 border border-green-200">
                        <i class="fa-solid fa-chart-line"></i> 資産として管理中
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border border-white shadow-xl flex flex-col items-center text-center">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">想定売却価格合計</p>
                    <p class="text-3xl font-black text-[#0071D3]"><span class="text-lg font-bold text-blue-200 mr-1">¥</span><?php echo number_format($total_potential_resale); ?></p>
                    <div class="mt-3 flex items-center gap-2 text-xs font-bold py-1 px-3 rounded-full bg-blue-100 text-blue-600 border border-blue-200">
                        <i class="fa-solid fa-hand-holding-dollar"></i> アップグレード原資
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-md p-6 rounded-3xl border border-white shadow-xl flex flex-col items-center text-center">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">デバイス投資総額</p>
                    <p class="text-3xl font-black text-slate-700"><span class="text-lg font-bold text-slate-300 mr-1">¥</span><?php echo number_format($total_spent); ?></p>
                    <div class="mt-3 flex items-center gap-2 text-xs font-bold py-1 px-3 rounded-full bg-slate-100 text-slate-500 border border-slate-200">
                        <i class="fa-solid fa-receipt"></i> 過去の購入総額
                    </div>
                </div>
            </div>

            <!-- フィルター & ソート -->
            <div class="flex flex-col md:flex-row justify-center items-center gap-4 mb-10">
                <div class="bg-slate-200/50 backdrop-blur-sm p-1.5 rounded-2xl inline-flex shadow-inner border border-white/50 max-w-full overflow-x-auto no-scrollbar">
                    <button class="filter-btn active px-4 md:px-6 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 transition-all duration-300 flex items-center gap-2 whitespace-nowrap" data-filter="all">
                        すべて <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-[#0071D3]"><?php echo $all_count; ?></span>
                    </button>
                    <button class="filter-btn px-4 md:px-6 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 transition-all duration-300 flex items-center gap-2 whitespace-nowrap" data-filter="active">
                        使用中 <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-[#0071D3]"><?php echo $active_count; ?></span>
                    </button>
                    <button class="filter-btn px-4 md:px-6 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 transition-all duration-300 flex items-center gap-2 whitespace-nowrap" data-filter="storage">
                        保管中 <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-600"><?php echo $storage_count; ?></span>
                    </button>
                    <button class="filter-btn px-4 md:px-6 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 transition-all duration-300 flex items-center gap-2 whitespace-nowrap" data-filter="archive">
                        故障・売却 <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600"><?php echo $archive_count; ?></span>
                    </button>
                </div>

                <div class="bg-slate-200/50 backdrop-blur-sm p-1.5 rounded-2xl inline-flex shadow-inner border border-white/50 max-w-full overflow-x-auto no-scrollbar">
                    <button class="visibility-filter-btn active px-4 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 transition-all duration-300 flex items-center gap-2 whitespace-nowrap" data-visibility="all">
                        全表示 <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-[#0071D3]"><?php echo $all_count; ?></span>
                    </button>
                    <button class="visibility-filter-btn px-4 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 transition-all duration-300 flex items-center gap-2 whitespace-nowrap" data-visibility="public">
                        <i class="fa-solid fa-globe text-[10px]"></i> 公開 <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-[#0071D3]"><?php echo $public_count; ?></span>
                    </button>
                    <button class="visibility-filter-btn px-4 py-2.5 rounded-xl text-sm font-bold text-slate-600 hover:text-slate-800 transition-all duration-300 flex items-center gap-2 whitespace-nowrap" data-visibility="private">
                        <i class="fa-solid fa-lock text-[10px]"></i> 非公開 <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600"><?php echo $private_count; ?></span>
                    </button>
                </div>

                <div class="relative min-w-[180px] max-w-[220px]">
                    <select id="device-sort" class="w-full bg-white/80 backdrop-blur-md border-2 border-white shadow-lg px-4 py-2.5 rounded-2xl text-sm font-bold text-slate-700 outline-none focus:border-[#0071D3] transition-all appearance-none cursor-pointer truncate whitespace-nowrap">
                        <option value="reg_desc">登録が新しい順</option>
                        <option value="reg_asc">登録が古い順</option>
                        <option value="purchase_desc">購入日が新しい順</option>
                        <option value="purchase_asc">購入日が古い順</option>
                        <option value="price_desc">価格が高い順</option>
                        <option value="price_asc">価格が安い順</option>
                        <option value="duration_desc">使用期間が長い順</option>
                    </select>
                    <i class="fa-solid fa-sort absolute right-4 top-1/2 -translate-y-1/2 text-[#0071D3] pointer-events-none"></i>
                </div>
            </div>

            <!-- デバイスグリッド -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mx-auto" id="device-grid">
                <?php if (empty($my_devices)): ?>
                        <div class="col-span-full py-20 px-6 text-center bg-white/40 backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/60 animate-card-entry flex flex-col items-center">
                            <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center text-[#0071D3] mb-6 shadow-inner">
                                <i class="fa-solid fa-mobile-screen-button text-4xl opacity-50"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-slate-800 mb-2">管理中のデバイスがありません</h3>
                            <p class="text-slate-500 mb-8 max-w-sm mx-auto">上の検索バーから、あなたが持っている（または持っていた）デバイスを検索して登録してみましょう！</p>
                            <button type="button" onclick="document.getElementById('search-bar').focus();" class="bg-[#0071D3] hover:bg-[#005bb5] text-white font-bold py-3 px-8 rounded-2xl transition-all shadow-lg shadow-blue-500/20 flex items-center gap-2 group">
                                <span>デバイスを検索する</span>
                                <i class="fa-solid fa-magnifying-glass group-hover:scale-110 transition-transform"></i>
                            </button>
                        </div>
                <?php else: ?>
                        <?php
                        $i = 0;
                        foreach ($my_devices as $device):
                            $entry_id = h($device['entry_id']);
                            $device_name = h($device['device_name']);
                            $brand_name = h($device['brand_name']);
                            $image_url = !empty($device['image_url']) ? h($device['image_url']) : 'https://placehold.co/600x400/e2e8f0/64748b?text=No+Image';
                            $status_name = h($device['status_name']);
                            $purchase_date_str = h($device['purchased_date']);
                            $notes = h($device['user_notes']);
                            $display_notes = mb_strimwidth($notes, 0, 60, '...', 'UTF-8');

                            $p_date = new DateTime($purchase_date_str);
                            $end_date = !empty($device['unusable_date']) ? new DateTime($device['unusable_date']) : new DateTime();
                            $days_passed = ($p_date > $end_date) ? 0 : $p_date->diff($end_date)->days;

                            $now_dt = new DateTime();
                            $now_dt->setTime(0, 0, 0);
                            $w_rem = null;
                            if (!empty($device['warranty_end_date'])) {
                                $w_dt = new DateTime($device['warranty_end_date']);
                                $w_dt->setTime(0, 0, 0);
                                $w_rem = ($w_dt >= $now_dt) ? $now_dt->diff($w_dt)->days : -1;
                            }
                            $r_rem = null;
                            if (!empty($device['return_due_date'])) {
                                $r_dt = new DateTime($device['return_due_date']);
                                $r_dt->setTime(0, 0, 0);
                                $r_rem = ($r_dt >= $now_dt) ? $now_dt->diff($r_dt)->days : -1;
                            }

                            $badge_bg = 'bg-green-100 text-green-600';
                            if (strpos($status_name, '故障') !== false)
                                $badge_bg = 'bg-red-100 text-red-600';
                            elseif (strpos($status_name, '売却') !== false)
                                $badge_bg = 'bg-slate-100 text-slate-500';
                            elseif (strpos($status_name, '保管') !== false)
                                $badge_bg = 'bg-amber-100 text-amber-600';

                            $delay = $i * 0.1;
                            $i++;
                            $my_rating = (float) ($device['my_rating'] ?? 0);
                            $global_rating = (float) ($device['global_rating'] ?? 0);
                            $global_rating_count = (int) ($device['global_rating_count'] ?? 0);
                            ?>
                                <div class="js-device-card animate-card-entry group relative w-full h-[320px] bg-[#FCFBFC] rounded-[20px] shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-white/60 flex flex-col items-center justify-center cursor-pointer"
                                    style="animation-delay: <?php echo $delay; ?>s;" data-id="<?php echo $entry_id; ?>"
                                    data-device-id="<?php echo h($device['device_id']); ?>" data-name="<?php echo $device_name; ?>"
                                    data-brand="<?php echo $brand_name; ?>" data-image="<?php echo $image_url; ?>"
                                    data-status="<?php echo $status_name; ?>" data-status-color="<?php echo $badge_bg; ?>"
                                    data-purchase-date="<?php echo $p_date->format('Y/m/d'); ?>"
                                    data-purchase-price="<?php echo h($device['purchase_price']); ?>"
                                    data-sold-price="<?php echo h($device['sold_price']); ?>"
                                    data-is-public="<?php echo h($device['is_public']); ?>"
                                    data-release-year="<?php echo h($device['release_year']); ?>"
                                    data-market-price="<?php echo h($device['market_price']); ?>"
                                    data-market-price-new="<?php echo h($device['market_price_new']); ?>"
                                    data-price-date="<?php echo h($device['price_last_checked']); ?>"
                                    data-expected-lifespan-months="<?php echo h($device['expected_lifespan_months']); ?>"
                                    data-days-passed="<?php echo number_format($days_passed); ?>"
                                    data-unusable-date="<?php echo !empty($device['unusable_date']) ? h((new DateTime($device['unusable_date']))->format('Y/m/d')) : ''; ?>"
                                    data-unusable-reason="<?php echo !empty($device['unusable_reason']) ? h($device['unusable_reason']) : ''; ?>"
                                    data-notes="<?php echo $notes; ?>"
                                    data-warranty-end-date="<?php echo !empty($device['warranty_end_date']) ? h((new DateTime($device['warranty_end_date']))->format('Y/m/d')) : ''; ?>"
                                    data-return-due-date="<?php echo !empty($device['return_due_date']) ? h((new DateTime($device['return_due_date']))->format('Y/m/d')) : ''; ?>"
                                    data-return-days-remaining="<?php echo $r_rem; ?>"
                                    data-images="<?php echo h(json_encode($device['images'] ?? [])); ?>"
                                    data-my-rating="<?php echo $my_rating; ?>" data-global-rating="<?php echo $global_rating; ?>"
                                    data-global-rating-count="<?php echo $global_rating_count; ?>"
                                    data-avg-lifespan="<?php echo h($device['avg_lifespan'] ?? ''); ?>"
                                    data-common-failure-reason="<?php echo h($device['common_failure_reason'] ?? ''); ?>">

                                    <div class="absolute top-4 right-4 z-40 px-2.5 py-1 rounded-lg text-xs font-bold shadow-sm border border-white/50 <?php echo $badge_bg; ?>">
                                        <?php echo $status_name; ?>
                                    </div>
                                    <div class="absolute top-11 right-4 z-40 px-2.5 py-1 rounded-lg text-xs font-black shadow-sm border border-white/50 <?php echo ($device['is_public'] == 1) ? 'bg-blue-500 text-white' : 'bg-slate-200 text-slate-500'; ?>">
                                        <?php echo ($device['is_public'] == 1) ? '公開中' : '非公開'; ?>
                                    </div>
                                    <div class="absolute top-4 left-4 z-40 flex flex-col gap-1">
                                        <?php if ($w_rem !== null && $w_rem >= 0 && $w_rem <= 14): ?>
                                                <div class="px-2.5 py-1 rounded-lg text-xs font-bold bg-yellow-200 text-yellow-800 shadow-sm border border-white/50">
                                                    保証期限間近 (残り <?php echo $w_rem; ?>日)
                                                </div>
                                        <?php endif; ?>
                                        <?php if ($r_rem !== null && $r_rem >= 0 && $r_rem <= 3): ?>
                                                <div class="px-2.5 py-1 rounded-lg text-xs font-bold bg-red-100 text-red-600 shadow-sm border border-white/50">
                                                    返品期限間近 (残り <?php echo $r_rem; ?>日)
                                                </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="absolute top-0 w-full h-full flex flex-col items-center justify-center transition-all duration-500 group-hover:-translate-y-16 group-hover:scale-75 z-20">
                                        <div class="w-48 h-48 relative mb-4">
                                            <img src="<?php echo $image_url; ?>" alt="<?php echo $device_name; ?>" class="w-full h-full object-contain drop-shadow-md">
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-800 text-center px-4 transition-opacity duration-300 group-hover:opacity-0"><?php echo $device_name; ?></h3>
                                        <p class="text-xs text-slate-500 mt-1 transition-opacity duration-300 group-hover:opacity-0"><?php echo $brand_name; ?></p>
                                    </div>
                                    <div class="absolute bottom-0 left-0 w-full h-auto bg-white/95 backdrop-blur-xl p-6 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out z-30 border-t border-blue-50/50 flex flex-col justify-end">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1"><?php echo $brand_name; ?></p>
                                        <h3 class="text-lg font-bold text-slate-800 mb-4 leading-tight line-clamp-1"><?php echo $device_name; ?></h3>
                                        <div class="space-y-2">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                                    <p class="text-xs text-slate-400 font-bold mb-0.5">購入日</p>
                                                    <p class="text-sm font-semibold text-slate-700"><?php echo $p_date->format('Y/m/d'); ?></p>
                                                </div>
                                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                                    <p class="text-xs text-slate-400 font-bold mb-0.5"><?php echo !empty($device['unusable_date']) ? '総使用期間' : '経過日数'; ?></p>
                                                    <p class="text-sm font-bold text-[#0071D3]"><?php echo number_format($days_passed); ?>日</p>
                                                </div>
                                            </div>
                                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 min-h-[50px]">
                                                <p class="text-xs text-slate-400 font-bold mb-1">メモ</p>
                                                <p class="text-xs text-slate-600 leading-relaxed line-clamp-1"><?php echo $display_notes ?: '－'; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="absolute inset-0 bg-[#0071D3] opacity-0 group-hover:opacity-5 transition-opacity duration-500 z-10 pointer-events-none"></div>
                                </div>
                        <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- フィルタリング結果が0件の場合のメッセージ -->
            <div id="no-results-message" class="hidden py-20 px-6 text-center bg-white/30 backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/40 animate-card-entry flex flex-col items-center mt-10">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-4 shadow-inner">
                    <i class="fa-solid fa-filter-circle-xmark text-2xl opacity-50"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-700">該当するデバイスが見つかりません</h3>
                <p class="text-slate-500 text-sm mt-1">フィルターの条件を変更してみてください</p>
            </div>

            <!-- ウィッシュリストセクション -->
            <div id="wishlist" class="mt-24 mb-16 relative">
                <div class="text-center mb-12">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-rose-100 rounded-2xl text-rose-500 shadow-sm border border-rose-200/50 mb-4">
                        <i class="fa-solid fa-heart text-xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-900 block relative tracking-wide after:content-[''] after:block after:w-16 after:h-1 after:bg-rose-400 after:mx-auto after:mt-4 after:rounded-full">
                        ウィッシュリスト
                    </h2>
                    <p class="text-slate-600 mt-4 text-sm">いつか手に入れたい、あなたの気になるデバイス</p>
                </div>

                <?php if (empty($wishlist)): ?>
                    <div class="py-20 px-6 text-center bg-white/40 backdrop-blur-md rounded-[3rem] border-2 border-dashed border-white/60 flex flex-col items-center">
                        <div class="w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center text-rose-300 mb-6 shadow-inner">
                            <i class="fa-solid fa-heart-circle-plus text-3xl opacity-50"></i>
                        </div>
                        <p class="text-slate-500 font-medium">ウィッシュリストは空です。<br>デバイスを検索して「欲しい！」ボタンを押してみましょう。</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mx-auto">
                        <?php foreach ($wishlist as $item): 
                            $image_url = !empty($item['image_path']) ? h($item['image_path']) : 'https://placehold.co/600x400/e2e8f0/64748b?text=No+Image';
                            ?>
                            <div onclick="openAddDialogFromWishlist(<?php echo $item['device_id']; ?>)" class="group relative w-full h-[320px] bg-[#FCFBFC] rounded-[20px] shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden border border-white/60 flex flex-col items-center justify-center cursor-pointer">
                                <!-- 削除ボタン -->
                                <button onclick="event.stopPropagation(); removeFromWishlist(<?php echo $item['device_id']; ?>, this)" class="absolute top-4 right-4 z-40 w-8 h-8 rounded-full bg-white/80 backdrop-blur-sm text-rose-500 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-rose-500 hover:text-white shadow-sm" title="リストから削除">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                
                                <!-- ウィッシュリストバッジ -->
                                <div class="absolute top-4 left-4 z-40 px-3 py-1 rounded-lg text-xs font-black bg-rose-500 text-white shadow-md border border-white/20 uppercase tracking-tighter">
                                    Wishlist
                                </div>

                                <!-- デバイス画像 (通常時) -->
                                <div class="absolute top-0 w-full h-full flex flex-col items-center justify-center transition-all duration-500 group-hover:-translate-y-16 group-hover:scale-75 z-20">
                                    <div class="w-48 h-48 relative mb-4">
                                        <img src="<?php echo $image_url; ?>" alt="" class="w-full h-full object-contain drop-shadow-md">
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-800 text-center px-4 transition-opacity duration-300 group-hover:opacity-0"><?php echo h($item['device_name']); ?></h3>
                                    <p class="text-xs text-slate-500 mt-1 transition-opacity duration-300 group-hover:opacity-0"><?php echo h($item['brand_name']); ?></p>
                                </div>

                                <!-- ホバー時に下から競り上がる詳細情報 -->
                                <div class="absolute bottom-0 left-0 w-full h-auto bg-white/95 backdrop-blur-xl p-6 translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-out z-30 border-t border-rose-50/50 flex flex-col justify-end">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1"><?php echo h($item['brand_name']); ?></p>
                                    <h3 class="text-lg font-bold text-slate-800 mb-4 leading-tight line-clamp-1"><?php echo h($item['device_name']); ?></h3>
                                    
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-center gap-2 bg-slate-50 py-2 rounded-xl border border-slate-100">
                                            <i class="fa-solid fa-star text-amber-400 text-xs"></i>
                                            <span class="text-sm font-black text-slate-700"><?php echo number_format($item['avg_rating'], 1); ?></span>
                                            <span class="text-xs text-slate-400 font-bold">(<?php echo $item['rating_count']; ?> 評価)</span>
                                        </div>
                                        
                                        <button onclick="openAddDialogFromWishlist(<?php echo $item['device_id']; ?>)" class="w-full py-3 rounded-2xl bg-[#0071D3] hover:bg-[#005bb5] text-white font-black text-sm transition-all shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 group/btn">
                                            <span>マイページに登録</span>
                                            <i class="fa-solid fa-plus group-hover/btn:rotate-90 transition-transform"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="absolute inset-0 bg-rose-500 opacity-0 group-hover:opacity-5 transition-opacity duration-500 z-10 pointer-events-none"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>
</main>

<?php include "../includes/footer.php" ?>
