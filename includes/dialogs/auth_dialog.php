<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ログインエラー
$login_err = isset($_SESSION) ? $_SESSION : [];

// 2. 新規登録関連のエラーと入力保持の取得
$signup_error = isset($_SESSION["signup-error"]) ? $_SESSION["signup-error"] : [];
$old_username = isset($_SESSION["signup_inputs"]["username"]) ? $_SESSION["signup_inputs"]["username"] : "";
$old_email = isset($_SESSION["signup_inputs"]["email"]) ? $_SESSION["signup_inputs"]["email"] : "";

// 3. 表示用にデータを取り出した後、セッションをクリア
// (リロード時にエラーが出続けないようにする処理)
if (isset($_SESSION["signup-error"]))
    unset($_SESSION["signup-error"]);
if (isset($_SESSION["signup_inputs"]))
    unset($_SESSION["signup_inputs"]);

// ログインエラーのクリア処理（既存ロジックを踏襲しつつ、登録エラーがない場合のみクリアを検討）
// ※ここでの全クリアはタイミングによって表示が消える可能性があるため、必要に応じて調整してください
if ((isset($_SESSION["msg"]) || isset($_SESSION["email"])) && empty($signup_error)) {
    // 表示前に消すと消えてしまうため、HTML出力後にJS等でフラグを立てるか、
    // 次回遷移時に消すのが一般的ですが、ここでは既存コードの意図を汲んで変数の確保のみ行います
}

ini_set("display_errors", true);
$current_page = $_SERVER['REQUEST_URI'];

// 初期状態の決定: 新規登録エラーがある場合は最初から裏面(登録画面)を表示
$initial_rotation = count($signup_error) > 0 ? "rotate-y-180" : "";

// エラーがある場合に自動でダイアログを開くためのフラグ
$should_open = (isset($login_err["msg"]) || isset($login_err["email"]) || isset($login_err["password"]) || count($signup_error) > 0);
?>

<dialog id="authDialog"
    class="bg-transparent p-0 m-auto backdrop:bg-slate-900/40 shadow-none outline-none overflow-visible">

    <div class="w-[450px] perspective-[1000px] group mx-auto flex flex-col">

        <div
            class="flip-card-inner relative w-full transition-all duration-700 preserve-3d <?php echo $initial_rotation; ?>">

            <!-- 表面 (ログイン) -->
            <div
                class="relative w-full backface-hidden bg-white/80 backdrop-blur-xl p-10 shadow-2xl border border-white/60 rounded-[30px] flex flex-col justify-center z-20 max-h-[85vh] overflow-y-auto custom-scrollbar">

                <button type="button" name="closeBtn"
                    class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100/50 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-all z-10">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">おかえりなさい</h2>
                    <p class="text-slate-500 text-sm mt-1">アカウントにログインしてください</p>
                </div>

                <?php if (isset($login_err["msg"])): ?>
                    <div
                        class="bg-red-50/80 border border-red-100 text-red-500 rounded-2xl p-3 mb-6 text-sm font-bold flex items-center justify-center gap-2 animate-pulse">
                        <i class="fa-solid fa-circle-exclamation"></i><?php echo $login_err["msg"] ?>
                    </div>
                <?php endif ?>

                <form action="../actions/login" method="post" class="space-y-5 text-left">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 ml-3">メールアドレス</label>
                        <?php if (isset($login_err["email"])): ?>
                            <span
                                class="text-red-500 text-xs font-bold float-right mr-2"><?php echo $login_err["email"] ?></span>
                        <?php endif ?>
                        <div class="relative group/input">
                            <i
                                class="fa-regular fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-[#0071D3] transition-colors"></i>
                            <input
                                class="w-full bg-slate-50/50 border border-slate-200 text-slate-800 pl-11 pr-4 py-3.5 rounded-2xl outline-none focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-[#0071D3]/10 transition-all font-medium placeholder:text-slate-300"
                                type="email" name="email" placeholder="example@email.com" required>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 ml-3">パスワード</label>
                        <?php if (isset($login_err["password"])): ?>
                            <span
                                class="text-red-500 text-xs font-bold float-right mr-2"><?php echo $login_err["password"] ?></span>
                        <?php endif ?>
                        <div class="relative group/input">
                            <i
                                class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within/input:text-[#0071D3] transition-colors"></i>
                            <input
                                class="w-full bg-slate-50/50 border border-slate-200 text-slate-800 pl-11 pr-12 py-3.5 rounded-2xl outline-none focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-[#0071D3]/10 transition-all font-medium placeholder:text-slate-300"
                                type="password" name="password" placeholder="••••••••" required>
                            <button type="button"
                                class="js-password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#0071D3] transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button
                        class="w-full bg-gradient-to-r from-[#0071D3] to-[#208ceb] hover:from-[#005bb5] hover:to-[#0071D3] text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 mt-4"
                        type="submit">
                        ログイン
                    </button>
                </form>

                <p class="text-center text-slate-400 mt-8 text-sm">
                    アカウントをお持ちでないですか？
                    <button type="button" onclick="flipDialog('#authDialog', 'signup')"
                        class="text-[#0071D3] font-bold hover:underline decoration-2 underline-offset-4 ml-1">
                        新規登録
                    </button>
                </p>
            </div>

            <!-- 裏面 (新規登録) -->
            <div
                class="absolute inset-x-0 top-0 w-full backface-hidden rotate-y-180 bg-white/90 backdrop-blur-2xl p-8 shadow-2xl border border-white/60 rounded-[35px] overflow-y-auto custom-scrollbar z-10 max-h-[85vh]">

                <button type="button" name="closeBtn"
                    class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100/50 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition-all z-10">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="text-center mb-6">
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">アカウント作成</h2>
                    <p class="text-slate-500 text-xs mt-2">必要な情報を入力してスタートしましょう</p>
                </div>

                <?php if (count($signup_error) > 0): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 text-xs rounded-xl p-3 mb-4">
                        <ul class="list-disc list-inside space-y-1 font-bold">
                            <?php foreach ($signup_error as $error): ?>
                                <li><?php echo h($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif ?>

                <form action="<?php echo BASE_URL; ?>actions/signup.php" method="post" class="space-y-4">
                    <input type="hidden" name="origin_url" value="<?php echo h($current_page); ?>">

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 ml-3">ユーザー名</label>
                        <input type="text" name="username"
                            class="w-full bg-slate-50/50 border border-slate-200 px-4 py-3 rounded-xl outline-none focus:bg-white focus:border-[#0071D3] transition-all font-medium text-slate-800 placeholder:text-slate-300"
                            placeholder="例: 山田 太郎" value="<?php echo h($old_username); ?>" autocomplete="username">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 ml-3">メールアドレス</label>
                        <input type="email" name="email"
                            class="w-full bg-slate-50/50 border border-slate-200 px-4 py-3 rounded-xl outline-none focus:bg-white focus:border-[#0071D3] transition-all font-medium text-slate-800 placeholder:text-slate-300"
                            placeholder="example@email.com" value="<?php echo h($old_email); ?>" autocomplete="email">
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 ml-3">パスワード</label>
                        <div class="relative">
                            <input type="password" name="password"
                                class="w-full bg-slate-50/50 border border-slate-200 pl-4 pr-12 py-3 rounded-xl outline-none focus:bg-white focus:border-[#0071D3] transition-all font-medium text-slate-800 placeholder:text-slate-300"
                                placeholder="8文字以上 (大文字・記号含む)" autocomplete="new-password">
                            <button type="button"
                                class="js-password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#0071D3] transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-400 px-2 pt-1">※ 半角英大文字・小文字・数字・記号必須</p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 ml-3">確認用パスワード</label>
                        <div class="relative">
                            <input type="password" name="password_confirm"
                                class="w-full bg-slate-50/50 border border-slate-200 pl-4 pr-12 py-3 rounded-xl outline-none focus:bg-white focus:border-[#0071D3] transition-all font-medium text-slate-800 placeholder:text-slate-300"
                                placeholder="パスワードを再入力" autocomplete="new-password">
                            <button type="button"
                                class="js-password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#0071D3] transition-colors">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 px-3 py-2 bg-blue-50/50 rounded-xl border border-blue-100">
                        <input type="checkbox" id="terms_agreed" name="terms_agreed" required
                            class="w-5 h-5 rounded-md border-slate-300 text-[#0071D3] focus:ring-[#0071D3]/20 cursor-pointer">
                        <label for="terms_agreed" class="text-xs font-bold text-slate-600 cursor-pointer leading-tight">
                            <a href="./terms.php" target="_blank" class="text-[#0071D3] hover:underline">利用規約</a>に同意します
                        </label>
                    </div>

                    <input type="hidden" name="csrf_token" value="<?php echo h(setToken()); ?>">

                    <div class="pt-2 flex flex-row gap-3">
                        <button type="submit"
                            class="flex-1 bg-gradient-to-r from-[#0071D3] to-[#208ceb] hover:from-[#005bb5] hover:to-[#0071D3] text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5">
                            登録する
                        </button>
                        <button type="reset"
                            class="w-24 bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 font-bold py-3 rounded-xl transition-colors">
                            クリア
                        </button>
                    </div>

                    <div class="text-center mt-4 pt-4 border-t border-slate-100">
                        <p class="text-slate-400 text-sm">
                            すでにアカウントをお持ちですか？
                            <button type="button" onclick="flipDialog('#authDialog', 'login')"
                                class="text-[#0071D3] font-bold hover:underline decoration-2 underline-offset-4 ml-1">
                                ログイン
                            </button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</dialog>

<?php if ($should_open): ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const dialog = document.getElementById('authDialog');
            if (dialog) {
                dialog.showModal();
                document.body.style.overflow = "hidden";
            }
        });
    </script>
<?php endif; ?>

<script>
    /**
     * 指定したダイアログ内のカードを回転させる汎用関数
     * @param {string} dialogSelector - ダイアログのセレクタ (例: '#authDialog')
     * @param {string} face - 表示したい面 ('signup' または 'back' で裏面、それ以外は表面)
     */
    function flipDialog(dialogSelector, face) {
        const container = document.querySelector(dialogSelector);
        if (!container) return;

        const card = container.querySelector('.flip-card-inner');
        if (!card) return;

        // 面の要素を取得 (表面: login, 裏面: signup)
        const loginFace = card.querySelector('.backface-hidden:not(.rotate-y-180)');
        const signupFace = card.querySelector('.backface-hidden.rotate-y-180');

        const maxHeight = window.innerHeight * 0.85;

        if (face === 'signup' || face === 'back') {
            card.classList.add('rotate-y-180');
            if (signupFace) {
                // scrollHeightを使用して、隠れている場合でもコンテンツの高さを取得
                const targetHeight = Math.min(signupFace.scrollHeight, maxHeight);
                card.style.height = targetHeight + 'px';
            }
        } else {
            card.classList.remove('rotate-y-180');
            if (loginFace) {
                const targetHeight = Math.min(loginFace.scrollHeight, maxHeight);
                card.style.height = targetHeight + 'px';
            }
        }
    }

    /**
     * ダイアログを開きつつ、指定した面を初期表示する関数
     * @param {string} dialogSelector - ダイアログのセレクタ
     * @param {string} face - 初期表示したい面 ('login' or 'signup')
     */
    function openFlipDialog(dialogSelector, face = 'login') {
        const dialog = document.querySelector(dialogSelector);
        if (dialog) {
            dialog.showModal();
            // 開いた瞬間に指定の面へセット
            flipDialog(dialogSelector, face);
        }
    }

    // 初回表示時の高さ調整 (エラー表示などで最初から裏面の場合など)
    document.addEventListener("DOMContentLoaded", () => {
        const authDialog = document.getElementById('authDialog');
        if (authDialog && authDialog.open) {
            const isSignup = authDialog.querySelector('.flip-card-inner').classList.contains('rotate-y-180');
            flipDialog('#authDialog', isSignup ? 'signup' : 'login');
        }

        // --- パスワード表示/非表示の切り替えロジック ---
        document.querySelectorAll('.js-password-toggle').forEach(btn => {
            btn.addEventListener('click', function () {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
    });
</script>

<style>
    /* Tailwindの任意の値を補完する回転クラス */
    .rotate-y-180 {
        transform: rotateY(180deg);
    }

    .backface-hidden {
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
    }

    .preserve-3d {
        transform-style: preserve-3d;
    }

    .flip-card-inner {
        transition: transform 0.7s, height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform, height;
    }

    /* ダイアログのデフォルト動作調整 */
    #authDialog {
        overflow: visible;
        /* 3D回転がはみ出ても切れないようにする */
        max-width: none;
        max-height: none;
    }

    /* Chrome/Safari/Edge用スクロールバーのカスタマイズ */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 3px;
    }

    /* ダイアログ表示時のフェードインアニメーション */
    dialog[open] {
        animation: fade-in 0.3s ease-out;
    }

    @keyframes fade-in {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>