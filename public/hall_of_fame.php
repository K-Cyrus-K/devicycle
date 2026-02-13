<?php
session_start();
require_once "../classes/DeviceLogic.php";
require_once "../classes/UserLogic.php";
require_once "../includes/functions.php";

$page = "IN-殿堂";
$title = getTitle($page);

// データの取得
$top_rated = DeviceLogic::getTopRatedDevices(6);
$most_rated = DeviceLogic::getMostRatedDevices(6);
$longest_used = DeviceLogic::getLongestUsedDevices(6);
$failure_stories = DeviceLogic::getInterestingFailures(6);

echo "<!-- DEBUG: Top Rated: " . count($top_rated) . " -->";
echo "<!-- DEBUG: Longest Used: " . count($longest_used) . " -->";
echo "<!-- DEBUG: Failure Stories: " . count($failure_stories) . " -->";
?>

<script src="<?php echo BASE_URL; ?>public/assets/js/search.js" defer></script>
<link rel="stylesheet" href="./assets/css/animations.css">

<?php include "../includes/header.php" ?>

<main class="min-h-screen text-[#1d1d1f] bg-gradient-to-b from-slate-50 via-blue-50 to-blue-100 pb-20">

    <!-- 背景装飾 -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10 opacity-30 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-gold-200/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-blue-200/20 rounded-full blur-[120px]"></div>
    </div>

    <div class="pt-20 pb-16 px-4 text-center">
        <div
            class="inline-block mb-4 px-6 py-2 bg-white/80 backdrop-blur-md rounded-full shadow-sm border border-gold-200/50">
            <span class="text-xs font-black text-amber-600 uppercase tracking-[0.3em]">The Hall of Fame</span>
        </div>
        <h1 class="text-5xl md:text-6xl font-black text-slate-900 mb-6 tracking-tight">
            デジタルの<span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-yellow-600">殿堂</span>
        </h1>
        <p class="text-slate-600 max-w-2xl mx-auto leading-relaxed">
            長く愛され、高く評価された名機たち。<br>
            ユーザーたちの「ガジェット遍歴」から紡がれる、デバイスの物語。
        </p>
    </div>

    <!-- セクション1: 高評価ランキング -->
    <section class="max-w-6xl mx-auto px-6 mb-24">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-600 shadow-sm">
                    <i class="fa-solid fa-crown text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">みんなの推しガジェット</h2>
                    <p class="text-sm text-slate-500 font-medium">ユーザーに愛されている名機たち</p>
                </div>
            </div>
            
            <!-- 切り替えスイッチ -->
            <div class="bg-slate-100 p-1 rounded-xl flex self-start">
                <button onclick="switchRank('top')" id="btn-top-rank" class="px-4 py-2 rounded-lg text-sm font-bold transition-all bg-white text-slate-900 shadow-sm">
                    評価スコア順
                </button>
                <button onclick="switchRank('most')" id="btn-most-rank" class="px-4 py-2 rounded-lg text-sm font-bold transition-all text-slate-500 hover:text-slate-700">
                    評価数順
                </button>
            </div>
        </div>

        <!-- 評価スコア順グリッド -->
        <div id="grid-top-rank" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($top_rated as $index => $device): ?>
                <div
                    class="bg-white/70 backdrop-blur-md rounded-[2.5rem] p-8 border border-white shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 group relative overflow-hidden">
                    <div
                        class="absolute top-6 right-8 text-4xl font-black italic opacity-10 group-hover:opacity-20 transition-opacity">
                        #<?php echo $index + 1; ?>
                    </div>

                    <div class="w-full aspect-square mb-6 flex items-center justify-center relative">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-slate-50 to-transparent rounded-full scale-90 group-hover:scale-100 transition-transform duration-700">
                        </div>
                        <img src="<?php echo h($device['image_path']); ?>" alt="<?php echo h($device['device_name']); ?>"
                            class="w-40 h-48 object-contain drop-shadow-2xl z-10 group-hover:scale-110 transition-transform duration-500">
                    </div>

                    <div class="text-center relative z-10">
                        <span
                            class="text-[10px] font-black bg-blue-100 text-[#0071D3] px-3 py-1 rounded-full uppercase tracking-wider mb-3 inline-block">
                            <?php echo h($device['brand_name']); ?>
                        </span>
                        <h3
                            class="text-xl font-bold text-slate-900 mb-4 line-clamp-1 group-hover:text-[#0071D3] transition-colors">
                            <?php echo h($device['device_name']); ?>
                        </h3>

                        <div class="flex items-center justify-center gap-2 mb-2">
                            <div class="flex text-amber-400">
                                <?php
                                $rating = (float) $device['avg_rating'];
                                for ($j = 1; $j <= 5; $j++): ?>
                                    <i
                                        class="fa-<?php echo ($j <= round($rating)) ? 'solid' : 'regular'; ?> fa-star text-sm"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="font-black text-slate-900"><?php echo number_format($rating, 1); ?></span>
                        </div>
                        <p class="text-xs text-slate-400 font-bold"><?php echo $device['rating_count']; ?> ユーザーが評価</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 評価数順グリッド (初期非表示) -->
        <div id="grid-most-rank" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 hidden">
            <?php foreach ($most_rated as $index => $device): ?>
                <div
                    class="bg-white/70 backdrop-blur-md rounded-[2.5rem] p-8 border border-white shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-500 group relative overflow-hidden">
                    <div
                        class="absolute top-6 right-8 text-4xl font-black italic opacity-10 group-hover:opacity-20 transition-opacity">
                        #<?php echo $index + 1; ?>
                    </div>

                    <div class="w-full aspect-square mb-6 flex items-center justify-center relative">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-slate-50 to-transparent rounded-full scale-90 group-hover:scale-100 transition-transform duration-700">
                        </div>
                        <img src="<?php echo h($device['image_path']); ?>" alt="<?php echo h($device['device_name']); ?>"
                            class="w-40 h-48 object-contain drop-shadow-2xl z-10 group-hover:scale-110 transition-transform duration-500">
                    </div>

                    <div class="text-center relative z-10">
                        <span
                            class="text-[10px] font-black bg-blue-100 text-[#0071D3] px-3 py-1 rounded-full uppercase tracking-wider mb-3 inline-block">
                            <?php echo h($device['brand_name']); ?>
                        </span>
                        <h3
                            class="text-xl font-bold text-slate-900 mb-4 line-clamp-1 group-hover:text-[#0071D3] transition-colors">
                            <?php echo h($device['device_name']); ?>
                        </h3>

                        <div class="flex items-center justify-center gap-2 mb-2">
                            <div class="flex text-amber-400">
                                <?php
                                $rating = (float) $device['avg_rating'];
                                for ($j = 1; $j <= 5; $j++): ?>
                                    <i
                                        class="fa-<?php echo ($j <= round($rating)) ? 'solid' : 'regular'; ?> fa-star text-sm"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="font-black text-slate-900"><?php echo number_format($rating, 1); ?></span>
                        </div>
                        <p class="text-xs text-amber-600 font-bold bg-amber-50 px-3 py-1 rounded-full inline-block">
                            <i class="fa-solid fa-users mr-1"></i><?php echo $device['rating_count']; ?> 評価
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <script>
        function switchRank(type) {
            const topGrid = document.getElementById('grid-top-rank');
            const mostGrid = document.getElementById('grid-most-rank');
            const topBtn = document.getElementById('btn-top-rank');
            const mostBtn = document.getElementById('btn-most-rank');

            if (type === 'top') {
                topGrid.classList.remove('hidden');
                mostGrid.classList.add('hidden');
                topBtn.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
                topBtn.classList.remove('text-slate-500');
                mostBtn.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
                mostBtn.classList.add('text-slate-500');
            } else {
                topGrid.classList.add('hidden');
                mostGrid.classList.remove('hidden');
                mostBtn.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
                mostBtn.classList.remove('text-slate-500');
                topBtn.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
                topBtn.classList.add('text-slate-500');
            }
        }
    </script>

    <!-- セクション2: 最長使用ランキング -->
    <section class="bg-slate-900 text-white py-24 mb-24 overflow-hidden relative">
        <div class="max-w-6xl mx-auto px-6 relative z-10">
            <div class="flex items-center gap-4 mb-12">
                <div
                    class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-white shadow-sm border border-white/10">
                    <i class="fa-solid fa-hourglass-half text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold">伝説の長寿デバイス</h2>
                    <p class="text-sm text-slate-400 font-medium">過酷な使用に耐え、愛され続けている名機たち</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($longest_used as $index => $device): ?>
                    <div
                        class="bg-white/5 border border-white/10 rounded-3xl p-6 flex items-center gap-6 hover:bg-white/10 transition-all group">
                        <div class="w-24 h-24 bg-white/5 rounded-2xl p-2 flex-shrink-0">
                            <img src="<?php echo h($device['image_path']); ?>" alt=""
                                class="w-full h-full object-contain filter brightness-90 group-hover:brightness-110 transition-all">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <span
                                    class="text-[10px] font-bold text-slate-500 uppercase"><?php echo h($device['brand_name']); ?></span>
                                <span
                                    class="text-[10px] font-bold bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded border border-amber-500/30">
                                    TOP <?php echo $index + 1; ?>
                                </span>
                            </div>
                            <h3 class="text-lg font-bold truncate mb-2"><?php echo h($device['device_name']); ?></h3>
                            <div class="flex items-end gap-3">
                                <span
                                    class="text-3xl font-black text-white"><?php echo number_format($device['usage_days']); ?></span>
                                <span class="text-xs text-slate-400 font-bold mb-1.5 uppercase tracking-widest">Days in
                                    use</span>
                            </div>
                            <p class="text-[10px] text-slate-500 font-medium mt-1">Owner:
                                <?php echo h($device['owner_name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 背景の大きなテキスト -->
        <div
            class="absolute bottom-[-10%] right-[-5%] text-[20rem] font-black text-white/[0.02] select-none pointer-events-none italic">
            HISTORY
        </div>
    </section>

    <!-- セクション3: 故障理由ピックアップ -->
    <section class="max-w-6xl mx-auto px-6">
        <div class="flex items-center gap-4 mb-10">
            <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center text-rose-600 shadow-sm">
                <i class="fa-solid fa-heart-crack text-2xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-900">ガジェットの散り際</h2>
                <p class="text-sm text-slate-500 font-medium">デバイスたちが役目を終えたその理由</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($failure_stories as $story): ?>
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                            <i class="fa-solid fa-mobile-screen"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800 line-clamp-1">
                                <?php echo h($story['device_name']); ?></h4>
                            <p class="text-[10px] text-slate-400 font-medium"><?php echo h($story['brand_name']); ?></p>
                        </div>
                    </div>
                    <div
                        class="relative bg-slate-50 rounded-2xl p-4 mb-4 min-h-[100px] flex flex-col justify-center italic">
                        <i class="fa-solid fa-quote-left absolute top-2 left-2 text-slate-200 text-xl"></i>
                        <p class="text-sm text-slate-600 relative z-10 leading-relaxed">
                            <?php echo h($story['unusable_reason']); ?>
                        </p>
                    </div>
                    <div class="flex justify-between items-center text-[10px] font-bold text-slate-400">
                        <span><?php echo h($story['owner_name']); ?> さん</span>
                        <span><?php echo !empty($story['unusable_date']) ? (new DateTime($story['unusable_date']))->format('Y/m/d') : ''; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<style>
    .bg-gold-200\/20 {
        background-color: rgba(251, 191, 36, 0.2);
    }
</style>

<?php include "../includes/footer.php" ?>