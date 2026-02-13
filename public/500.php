<?php
require_once "../includes/functions.php";
$page_title = "500 Server Error";
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getTitle($page_title); ?></title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/output.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</head>

<body class="bg-slate-900 min-h-screen text-slate-200 font-sans">

    <main class="min-h-screen flex items-center justify-center p-6 relative overflow-hidden">

        <div class="absolute top-0 left-0 w-full h-1 bg-red-500 z-50"></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-900/20 rounded-full blur-[120px] -z-10">
        </div>

        <div
            class="max-w-lg w-full bg-[#1e293b] rounded-3xl shadow-2xl border border-slate-700 p-10 md:p-14 text-center relative">

            <div class="mb-8 relative inline-block">
                <div class="relative">
                    <i class="fa-solid fa-microchip text-8xl text-slate-700"></i>
                    <i
                        class="fa-solid fa-triangle-exclamation absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-5xl text-red-500/90 drop-shadow-lg"></i>
                </div>
            </div>

            <h1 class="text-6xl font-bold text-white mb-2 tracking-tighter">500</h1>
            <p class="text-xl font-bold text-slate-400 mb-6">System Malfunction</p>
            <p class="text-slate-400 mb-8 leading-relaxed text-sm">
                サーバー内部でエラーが発生しました。<br>
                システムが一時的にオーバーヒートしているようです。<br>
                しばらく時間をおいてから再読み込みしてください。
            </p>

            <div class="flex flex-col gap-4 justify-center">
                <button onclick="location.reload()"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-red-500/20 transition-all transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-rotate-right mr-2"></i> 再読み込み
                </button>
                <a href="<?php echo BASE_URL; ?>public/"
                    class="w-full bg-transparent border border-slate-600 text-slate-400 hover:text-white hover:border-slate-400 font-bold py-3 px-8 rounded-xl transition-all">
                    トップへ戻る
                </a>
            </div>

            <div class="mt-8 text-xs text-slate-600 font-mono">
                Error Code: INTERNAL_SERVER_ERROR
            </div>
        </div>
    </main>

</body>

</html>