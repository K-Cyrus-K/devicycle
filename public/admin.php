<?php
session_start();
require_once "../classes/UserLogic.php";
require_once "../classes/DeviceLogic.php";
require_once "../includes/functions.php";

// ログイン判定 & 管理者判定
$logged_in = UserLogic::checkLogin();
if (!$logged_in || !UserLogic::isAdmin()) {
    header("Location: ./index.php");
    exit();
}

$login_user = $_SESSION["login_user"];
$title = "Dashboard | Devicycle";

// 統計データの取得
$stats = DeviceLogic::getDashboardStats();

if (!$stats) {
    // 統計が取得できない場合のフォールバック（初回実行時など）
    $stats = [
        'global' => ['total_users' => 0, 'total_devices' => 0, 'avg_lifespan_days' => 0],
        'lifespan_by_brand' => [],
        'popularity_by_brand' => [],
        'failure_reasons' => []
    ];
}

require_once "../includes/header.php";
?>

<main class="min-h-screen bg-[#0D0E0E] py-12">
    <div class="max-w-7xl mx-auto px-6">

        <!-- ヘッダーセクション -->
        <div class="mb-12">
            <h1 class="text-4xl font-black text-white tracking-tight mb-2">管理者ダッシュボード</h1>
            <p class="text-slate-400">Devicycle 全体の統計データとユーザー管理</p>
        </div>

        <!-- サマリーカード -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-[#181818] border border-[#333] p-8 rounded-[2rem] shadow-xl">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-blue-500/10 rounded-2xl flex items-center justify-center text-blue-500">
                        <i class="fa-solid fa-users text-2xl"></i>
                    </div>
                    <span class="text-slate-400 font-bold">総ユーザー数</span>
                </div>
                <div class="text-4xl font-black text-white">
                    <?php echo number_format($stats['global']['total_users']); ?> <span
                        class="text-sm text-slate-500">人</span>
                </div>
            </div>

            <div class="bg-[#181818] border border-[#333] p-8 rounded-[2rem] shadow-xl">
                <div class="flex items-center gap-4 mb-4">
                    <div
                        class="w-12 h-12 bg-yellow-500/10 rounded-2xl flex items-center justify-center text-yellow-500">
                        <i class="fa-solid fa-mobile-screen text-2xl"></i>
                    </div>
                    <span class="text-slate-400 font-bold">総デバイス数</span>
                </div>
                <div class="text-4xl font-black text-white">
                    <?php echo number_format($stats['global']['total_devices']); ?> <span
                        class="text-sm text-slate-500">台</span>
                </div>
            </div>

            <div class="bg-[#181818] border border-[#333] p-8 rounded-[2rem] shadow-xl">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-green-500/10 rounded-2xl flex items-center justify-center text-green-500">
                        <i class="fa-solid fa-heart-pulse text-2xl"></i>
                    </div>
                    <span class="text-slate-400 font-bold">平均寿命</span>
                </div>
                <div class="text-4xl font-black text-white">
                    <?php
                    $avg_days = $stats['global']['avg_lifespan_days'] ?? 0;
                    echo number_format($avg_days);
                    ?> <span class="text-sm text-slate-500">日</span>
                </div>
            </div>
        </div>

        <!-- チャートセクション -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">

            <!-- ブランド別信頼性 (平均寿命) -->
            <div class="bg-[#181818] border border-[#333] p-8 rounded-[2rem] shadow-xl">
                <h3 class="text-xl font-bold text-white mb-6">ブランド別信頼性 (平均使用日数)</h3>
                <div class="h-[300px]">
                    <canvas id="lifespanChart"></canvas>
                </div>
            </div>

            <!-- ブランド別人気 (登録数) -->
            <div class="bg-[#181818] border border-[#333] p-8 rounded-[2rem] shadow-xl">
                <h3 class="text-xl font-bold text-white mb-6">ブランド別シェア</h3>
                <div class="h-[300px]">
                    <canvas id="popularityChart"></canvas>
                </div>
            </div>

            <!-- 故障理由の割合 -->
            <div class="bg-[#181818] border border-[#333] p-8 rounded-[2rem] shadow-xl">
                <h3 class="text-xl font-bold text-white mb-6">主な故障・廃棄理由</h3>
                <div class="h-[300px]">
                    <canvas id="failureChart"></canvas>
                </div>
            </div>

            <!-- データテーブル (プレースホルダー) -->
            <div class="bg-[#181818] border border-[#333] p-8 rounded-[2rem] shadow-xl overflow-hidden">
                <h3 class="text-xl font-bold text-white mb-6">最新の故障報告</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-slate-500 text-xs uppercase tracking-wider border-b border-[#333]">
                                <th class="pb-4 font-bold">デバイス</th>
                                <th class="pb-4 font-bold">理由</th>
                                <th class="pb-4 font-bold text-right">使用期間</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-300">
                            <!-- ここに最新データを流し込む予定 -->
                            <tr class="border-b border-[#222]">
                                <td class="py-4 font-medium">iPhone 13 Pro</td>
                                <td class="py-4">バッテリー劣化</td>
                                <td class="py-4 text-right">820日</td>
                            </tr>
                            <tr class="border-b border-[#222]">
                                <td class="py-4 font-medium">Galaxy S22</td>
                                <td class="py-4">画面割れ</td>
                                <td class="py-4 text-right">450日</td>
                            </tr>
                            <tr>
                                <td class="py-4 font-medium">Xperia 5 IV</td>
                                <td class="py-4">売却 (買い替え)</td>
                                <td class="py-4 text-right">1,020日</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // データの準備
        const lifespanData = <?php echo json_encode($stats['lifespan_by_brand']); ?>;
        const popularityData = <?php echo json_encode($stats['popularity_by_brand']); ?>;
        const failureData = <?php echo json_encode($stats['failure_reasons']); ?>;

        // 1. 寿命チャート
        new Chart(document.getElementById('lifespanChart'), {
            type: 'bar',
            data: {
                labels: lifespanData.map(d => d.brand_name),
                datasets: [{
                    label: '平均使用日数',
                    data: lifespanData.map(d => d.avg_lifespan),
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#222' }, ticks: { color: '#666' } },
                    x: { grid: { display: false }, ticks: { color: '#666' } }
                }
            }
        });

        // 2. 人気チャート
        new Chart(document.getElementById('popularityChart'), {
            type: 'doughnut',
            data: {
                labels: popularityData.map(d => d.brand_name),
                datasets: [{
                    data: popularityData.map(d => d.device_count),
                    backgroundColor: [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: '#666', padding: 20 } }
                },
                cutout: '70%'
            }
        });

        // 3. 故障理由チャート
        new Chart(document.getElementById('failureChart'), {
            type: 'polarArea',
            data: {
                labels: failureData.map(d => d.unusable_reason),
                datasets: [{
                    data: failureData.map(d => d.reason_count),
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.5)',
                        'rgba(245, 158, 11, 0.5)',
                        'rgba(16, 185, 129, 0.5)',
                        'rgba(59, 130, 246, 0.5)',
                        'rgba(139, 92, 246, 0.5)'
                    ],
                    borderColor: '#181818',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: '#666' } }
                },
                scales: {
                    r: { grid: { color: '#333' }, angleLines: { color: '#333' }, ticks: { display: false } }
                }
            }
        });
    });
</script>

<?php require_once "../includes/footer.php"; ?>