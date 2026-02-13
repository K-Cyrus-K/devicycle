<?php
session_start();
require_once "../includes/functions.php";

$page = "利用規約";
$title = getTitle($page);
?>

<?php include "../includes/header.php" ?>

<main class="min-h-screen text-slate-800 bg-slate-50 pb-20">
    <div class="pt-24 pb-12 px-4 bg-white border-b border-slate-200">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl font-black text-slate-900 mb-4">利用規約</h1>
            <p class="text-slate-500 font-medium">最終更新日: 2026年2月12日</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-6 py-16">
        <div
            class="bg-white p-8 md:p-12 rounded-[2.5rem] shadow-sm border border-slate-200 prose prose-slate max-w-none">

            <section class="mb-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                    <span
                        class="w-8 h-8 bg-blue-100 text-[#0071D3] rounded-lg flex items-center justify-center text-sm italic">01</span>
                    第1条（適用）
                </h2>
                <p class="leading-relaxed text-slate-600 mb-4">
                    この利用規約（以下，「本規約」といいます。）は，DEVICYCLE（以下，「本サービス」といいます。）の提供条件及び本サービスの利用に関するユーザーと運営者との間の権利義務関係を定めるものです。
                </p>
            </section>

            <section class="mb-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                    <span
                        class="w-8 h-8 bg-blue-100 text-[#0071D3] rounded-lg flex items-center justify-center text-sm italic">02</span>
                    第2条（ユーザー登録）
                </h2>
                <p class="leading-relaxed text-slate-600 mb-4">
                    1. 本サービスにおいては，登録希望者が本規約に同意の上，当方の定める方法によって利用登録を申請し，当方がこれを承認することによって，利用登録が完了するものとします。
                </p>
                <p class="leading-relaxed text-slate-600 mb-4">
                    2. 当方は，利用登録の申請者に以下の事由があると判断した場合，利用登録の申請を承認しないことがあり，その理由については一切の開示義務を負わないものとします。
                </p>
                <ul class="list-disc pl-6 space-y-2 text-slate-600">
                    <li>利用登録の申請に際して虚偽の事項を届け出た場合</li>
                    <li>本規約に違反したことがある者からの申請である場合</li>
                    <li>その他，当方が利用登録を相当でないと判断した場合</li>
                </ul>
            </section>

            <section class="mb-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                    <span
                        class="w-8 h-8 bg-blue-100 text-[#0071D3] rounded-lg flex items-center justify-center text-sm italic">03</span>
                    第3条（禁止事項）
                </h2>
                <p class="leading-relaxed text-slate-600 mb-4">
                    ユーザーは，本サービスの利用にあたり，以下の行為をしてはなりません。
                </p>
                <ul class="list-disc pl-6 space-y-2 text-slate-600">
                    <li>法令または公序良俗に違反する行為</li>
                    <li>犯罪行為に関連する行為</li>
                    <li>本サービスの内容等，本サービスに含まれる著作権，商標権ほか知的財産権を侵害する行為</li>
                    <li>当方のサーバーまたはネットワークの機能を破壊したり，妨害したりする行為</li>
                    <li>本サービスの運営を妨害するおそれのある行為</li>
                    <li>他のユーザーに関する個人情報等を収集または蓄積する行為</li>
                    <li>不正アクセスをし，またはこれを試みる行為</li>
                    <li>本サービスの他のユーザーまたはその他の第三者に不利益，損害，不快感を与える行為</li>
                    <li>当方のサービスに関連して，反社会的勢力に対して直接または間接に利益を供与する行為</li>
                    <li>その他，当方が不適切と判断する行為</li>
                </ul>
            </section>

            <section class="mb-12">
                <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                    <span
                        class="w-8 h-8 bg-blue-100 text-[#0071D3] rounded-lg flex items-center justify-center text-sm italic">04</span>
                    第4条（免責事項）
                </h2>
                <p class="leading-relaxed text-slate-600 mb-4">
                    1.
                    当方は，本サービスに事実上または法律上の瑕疵（安全性，信頼性，正確性，完全性，有効性，特定の目的への適合性，セキュリティ等に関する欠陥，エラーやバグ，権利侵害等を含みます。）がないことを明示的にも黙示的にも保証しておりません。
                </p>
                <p class="leading-relaxed text-slate-600 mb-4">
                    2. 当方は，本サービスに起因してユーザーに生じたあらゆる損害について一切の責任を負いません。
                </p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                    <span
                        class="w-8 h-8 bg-blue-100 text-[#0071D3] rounded-lg flex items-center justify-center text-sm italic">05</span>
                    第5条（規約の変更）
                </h2>
                <p class="leading-relaxed text-slate-600 mb-4">
                    当方は，必要と判断した場合には，ユーザーに通知することなくいつでも本規約を変更することができるものとします。なお，本規約の変更後，本サービスの利用を開始した場合には，当該ユーザーは変更後の規約に同意したものとみなします。
                </p>
            </section>

        </div>

        <div class="mt-12 text-center">
            <a href="./"
                class="inline-flex items-center gap-2 text-slate-500 hover:text-[#0071D3] font-bold transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                ホームへ戻る
            </a>
        </div>
    </div>
</main>

<?php include "../includes/footer.php" ?>