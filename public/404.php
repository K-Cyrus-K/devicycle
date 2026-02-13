<?php
require_once "../includes/functions.php";

$page = "404 Not Found";
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo getTitle($page); ?></title>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/output.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</head>

<body class="bg-gradient-to-b from-blue-50 via-[#d3e9ff] to-[#d7d7ff] min-h-screen text-[#1d1d1f] font-sans">

    <main class="min-h-screen flex items-center justify-center p-6 relative overflow-hidden">

        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/40 rounded-full blur-[100px] -z-10 mix-blend-overlay">
        </div>

        <div
            class="max-w-lg w-full bg-white/80 backdrop-blur-md rounded-3xl shadow-2xl border border-white/50 p-10 md:p-14 text-center relative overflow-hidden">


            <!-- 携帯風画面 -->
            <div class="flex justify-center mb-8">
                <div class="relative flex justify-center h-[300px] w-[160px] border-4 border-black rounded-2xl bg-gray-50"
                    style="box-shadow: 5px 5px 2.5px 6px rgb(209, 218, 218)">

                    <span class="border border-black bg-black w-20 h-2 rounded-br-xl rounded-bl-xl"></span>

                    <span class="absolute -right-2 top-14 border-4 border-black h-7 rounded-md"></span>
                    <span class="absolute -right-2 bottom-36 border-4 border-black h-10 rounded-md"></span>

                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="relative inline-block">
                            <i class="fa-solid fa-robot text-7xl text-slate-200"></i>
                            <i
                                class="fa-solid fa-question absolute -top-2 -right-2 text-3xl text-[#0071D3] animate-bounce"></i>
                        </div>
                    </div>
                </div>
            </div>

            <h1 class="text-6xl font-bold text-slate-900 mb-2 tracking-tighter">404</h1>
            <p class="text-xl font-bold text-slate-600 mb-6">Device Not Found</p>

            <p class="text-slate-500 mb-8 leading-relaxed">
                お探しのページは、すでに廃棄されたか、<br>
                別の場所に移動した可能性があります。<br>
                URLをご確認ください。
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo BASE_URL; ?>public/"
                    class="bg-[#0071D3] hover:bg-[#005bb5] text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                    トップへ戻る
                </a>
                <a href="javascript:history.back()"
                    class="bg-white border-2 border-slate-200 text-slate-600 hover:text-slate-800 hover:border-slate-400 font-bold py-3 px-8 rounded-xl transition-all">
                    前のページへ
                </a>
            </div>
        </div>
    </main>

</body>

</html>