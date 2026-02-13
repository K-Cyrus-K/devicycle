<?php
session_start();
require_once "../includes/functions.php";
require_once "../classes/UserLogic.php";

$page = "ホーム";
$title = getTitle($page);
$logged_in = UserLogic::checkLogin();

?>

<script src="./assets/js/search.js" defer></script>
<link rel="stylesheet" href="./assets/css/feature-cards.css">

<?php include "../includes/header.php" ?>

<main class="min-h-screen text-[#1d1d1f]">
  <section
    class="relative pt-24 pb-40 overflow-visible bg-gradient-to-b from-blue-50 via-[#d3e9ff] to-[#d7d7ff] font-sans">

    <div
      class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[800px] bg-white/40 rounded-full blur-[100px] z-10 mix-blend-overlay">
      test</div>

    <div class="container mx-auto px-6 text-center relative z-10 ">
      <h1 class="text-4xl md:text-6xl font-bold text-slate-900 mb-6 tracking-tight drop-shadow-sm">
        あなたのデジタルデバイス<br>選ぶ時から手放す時まで賢く管理
      </h1>
      <p class="text-lg text-slate-700 mb-10 max-w-2xl mx-auto leading-relaxed text-pretty md:font-medium">
        ただの持ち物リストじゃない。保証管理からリセール価値、コミュニティの評価まで。あなたのデジタル資産を最適化します。
      </p>

      <div id="search-container" class="relative max-w-xl mx-auto group z-50">
        <div
          class="input flex px-5 py-4 rounded-full border-2 border-white/50 shadow-xl  group-hover:border-[#0071D3] backdrop-blur-md transition-all  group-hover:bg-white group-hover:shadow-[#0071D3]/20">
          <input type="search" id="search-bar" placeholder="登録・検索したい装置を入力してください…"
            class="w-full outline-none bg-transparent text-slate-800 text-lg placeholder-slate-500" autocomplete="off">

          <img src="./assets/img/unDraw/undraw_file-search_cbur.svg" alt="" width="70px" class="z-1">
        </div>

        <div id="results-list"
          class="bg-white border border-blue-100 shadow-2xl rounded-2xl absolute hidden w-full top-full mt-2 p-2 text-lg z-[100] text-left max-h-80 overflow-y-auto text-slate-700">
        </div>
      </div>

      <?php if (!$logged_in): ?>
        <a href="#"
          class="inline-block mt-10 text-highlight transition-colors tracking-wide login bg-white/50 px-6 py-2 rounded-full backdrop-blur-sm border border-white/20 hover:bg-white shadow-sm"
          name="login">
          > ログインまたは、無料で会員登録する
        </a>
      <?php endif ?>
    </div>
  </section>

  <section class="py-24 relative z-0" id="introduction-section">
    <div class="container mx-auto px-6 text-center">
      <h2
        class="text-3xl font-bold mb-28 pb-10 text-slate-800 tracking-wide inline-block relative after:content-[''] after:block after:w-24 after:h-1 after:bg-[#0071D3] after:mx-auto after:mt-4 after:rounded-full">
        <?php echo getTitle("") ?>でできること
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 px-4">

        <div class="feature-card group h-[380px]">
          <div class="feature-card-content"></div>

          <div class="relative z-10 p-8 flex flex-col items-center h-full justify-center">
            <div
              class="icon-box w-20 h-20 rounded-2xl flex items-center justify-center mb-6 text-[#0071D3] group-hover:scale-110 transition-transform duration-300">
              <i class="fa-solid fa-search text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-4 tracking-wider">賢く選ぶ<br><span
                class="text-xs text-slate-400 font-normal">CHOOSE WISELY</span></h3>
            <p class="text-slate-500 text-sm leading-relaxed">
              独自の「信頼性スコア」やコミュニティの評価を参考に、<br>後悔しないデバイス選びをサポートします。
            </p>
          </div>
        </div>

        <div class="feature-card group h-[380px]">
          <div class="feature-card-content"></div>

          <div class="relative z-10 p-8 flex flex-col items-center h-full justify-center">
            <div
              class="icon-box w-20 h-20 rounded-2xl flex items-center justify-center mb-6 text-[#0071D3] group-hover:scale-110 transition-transform duration-300">
              <i class="fa-solid fa-pen-to-square text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-4 tracking-wider">管理する<br><span
                class="text-xs text-slate-400 font-normal">MANAGE</span></h3>
            <p class="text-slate-500 text-sm leading-relaxed">
              保証期間、リセール価値、関連ドキュメントを一元管理。<br>あなたのデジタル資産を最適化します。
            </p>
          </div>
        </div>

        <div class="feature-card group h-[380px]">
          <div class="feature-card-content"></div>

          <div class="relative z-10 p-8 flex flex-col items-center h-full justify-center">
            <div
              class="icon-box w-20 h-20 rounded-2xl flex items-center justify-center mb-6 text-[#0071D3] group-hover:scale-110 transition-transform duration-300">
              <i class="fa-solid fa-hand-holding-dollar text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-4 tracking-wider">賢く手放す<br><span
                class="text-xs text-slate-400 font-normal">SELL SMART</span></h3>
            <p class="text-slate-500 text-sm leading-relaxed">
              リアルタイムの市場価値を追跡し、<br>資産価値を最大化する最適なタイミングを逃しません。
            </p>
          </div>
        </div>

      </div>
    </div>
  </section>
</main>

<?php include "../includes/footer.php" ?>