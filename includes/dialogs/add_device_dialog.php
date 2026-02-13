<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ログインユーザーIDの取得 (未ログイン時は空)
$uid = isset($_SESSION['login_user']) ? $_SESSION['login_user']['uid'] : '';
?>

<dialog id="addDeviceDialog"
    class="w-[85%] min-[950px]:w-full max-w-4xl max-h-[90vh] rounded-3xl p-0 backdrop:bg-black/60 m-auto shadow-2xl overflow-y-auto bg-white text-slate-800">

    <div class="grid grid-cols-1 md:grid-cols-2">
        <div class="bg-slate-50 flex flex-col items-center justify-center p-8 border-r border-slate-100">
            <div
                class="w-full aspect-square bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center overflow-hidden mb-6 p-4">
                <img id="dialog-device-img" src="" alt="Device Image" class="w-full h-full object-contain">
            </div>
            <div class="text-center w-full">
                <span id="dialog-device-bname"
                    class="text-xs font-bold bg-blue-100 text-[#0071D3] px-3 py-1 rounded-full mb-2 inline-block">Brand</span>
                <h3 id="dialog-device-name" class="text-2xl font-bold text-slate-900 mb-1">Device Name</h3>

                <!-- スペック確認リンク -->
                <div class="mb-4 flex justify-center gap-2">
                    <a id="dialog-spec-link" href="#" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-[#0071D3] text-xs font-bold transition-all border border-transparent hover:border-blue-200">
                        <i class="fa-brands fa-google text-[10px]"></i>
                        <span>スペックを確認</span>
                    </a>
                    <?php if (!empty($uid)): ?>
                        <button type="button" id="toggle-wishlist-btn"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 text-xs font-bold transition-all border border-rose-100 hover:border-rose-200">
                            <i class="fa-regular fa-heart"></i>
                            <span id="wishlist-btn-text">欲しい！</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- 評価入力 (星) -->
                <div class="flex flex-col items-center mb-2">
                    <?php if (!empty($uid)): ?>
                        <div class="rating" id="add-device-rating-container">
                            <input type="radio" id="add-star-5" name="rating" value="5">
                            <label for="add-star-5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path pathLength="360"
                                        d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                    </path>
                                </svg>
                            </label>
                            <input type="radio" id="add-star-4" name="rating" value="4">
                            <label for="add-star-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path pathLength="360"
                                        d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                    </path>
                                </svg>
                            </label>
                            <input type="radio" id="add-star-3" name="rating" value="3">
                            <label for="add-star-3">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path pathLength="360"
                                        d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                    </path>
                                </svg>
                            </label>
                            <input type="radio" id="add-star-2" name="rating" value="2">
                            <label for="add-star-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path pathLength="360"
                                        d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                    </path>
                                </svg>
                            </label>
                            <input type="radio" id="add-star-1" name="rating" value="1">
                            <label for="add-star-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path pathLength="360"
                                        d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                    </path>
                                </svg>
                            </label>
                        </div>
                    <?php endif ?>
                    <div class="text-xs text-slate-400">
                        ユーザ平均：<span id="add-global-rating" class="font-bold text-slate-600">-</span>
                    </div>
                </div>

                <p id="dialog-device-year" class="text-slate-500 text-sm">Release Year</p>

                <!-- デバイス統計情報 (寿命・故障理由) -->
                <div id="device-stats-info"
                    class="w-full mt-4 p-4 bg-blue-50/50 rounded-2xl border border-blue-100 hidden transition-all duration-300 text-left">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-chart-line text-[#0071D3] text-xs"></i>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">信頼性データ</h4>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500">平均使用期間</span>
                            <span id="stat-avg-lifespan" class="text-sm font-bold text-slate-700">-</span>
                        </div>
                        <div class="pt-2 border-t border-blue-100/50">
                            <span class="text-[10px] text-slate-400 block mb-1">主な故障理由</span>
                            <span id="stat-common-reason"
                                class="text-xs font-medium text-slate-600 leading-relaxed">-</span>
                        </div>
                    </div>
                </div>

                <!-- 市場価格情報 -->
                <div id="device-market-price"
                    class="w-full mt-6 p-4 bg-white rounded-2xl shadow-sm border border-slate-100 hidden transition-all duration-300 text-left">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">市場価格相場</h4>
                        <?php if (!empty($uid)): ?>
                            <button type="button" id="refresh-price-btn"
                                class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-[#0071D3] transition-all"
                                title="価格を更新">
                                <i class="fa-solid fa-rotate text-[10px]"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm mb-3">
                        <div class="bg-slate-50 p-2 rounded-lg">
                            <p class="text-[10px] text-slate-400 mb-1">新品平均</p>
                            <p id="price-new" class="font-bold text-slate-700 text-lg">-</p>
                        </div>
                        <div class="bg-slate-50 p-2 rounded-lg">
                            <p class="text-[10px] text-slate-400 mb-1">中古平均</p>
                            <p id="price-used" class="font-bold text-slate-700 text-lg">-</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mb-2">
                        <p id="price-date" class="text-[10px] text-slate-300">取得日: -</p>
                        <a id="yahoo-link" href="#" target="_blank"
                            class="text-xs font-bold text-[#0071D3] hover:text-[#005bb5] flex items-center gap-1 transition-colors">
                            <span>商品ページ</span>
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>
                    <!-- Yahoo! JAPAN Web Services アトリビューション開始 -->
                    <div class="text-center pt-2 border-t border-slate-50">
                        <span class="text-[10px] text-slate-400"><a href="https://developer.yahoo.co.jp/sitemap/"
                                class="hover:underline">Webサービス by Yahoo! JAPAN</a></span>
                    </div>
                    <!-- Yahoo! JAPAN Web Services アトリビューション終了 -->
                </div>
            </div>
        </div>

        <div class="p-8 md:p-10 relative">
            <?php if (empty($uid)): ?>
                <!-- 未ログイン時のリマインダー表示 -->
                <div
                    class="absolute inset-0 z-50 bg-white/60 backdrop-blur-[2px] flex flex-col items-center justify-center p-8 text-center rounded-r-3xl">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-6 text-[#0071D3]">
                        <i class="fa-solid fa-user-lock text-3xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3">ログインが必要です</h2>
                    <p class="text-slate-600 mb-8 leading-relaxed">
                        デバイスをマイページに追加して管理するには、<br>
                        ログインまたは新規会員登録が必要です。
                    </p>
                    <div class="flex flex-col w-full gap-3">
                        <button type="button"
                            onclick="document.getElementById('addDeviceDialog').close(); openFlipDialog('#authDialog', 'login')"
                            class="w-full bg-[#0071D3] hover:bg-[#005bb5] text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-blue-500/20">
                            ログインする
                        </button>
                        <button type="button"
                            onclick="document.getElementById('addDeviceDialog').close(); openFlipDialog('#authDialog', 'signup')"
                            class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl transition-all">
                            新規アカウント作成
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-slate-900">マイページに追加</h2>
                <button type="button" id="close-add-dialog-btn"
                    class="text-slate-400 hover:text-slate-800 transition-colors w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <form id="add-device-form" class="space-y-5">
                <input type="hidden" name="user_id" value="<?php echo h($uid); ?>">
                <input type="hidden" name="device_id" id="hidden-device-id">
                <input type="hidden" name="brand" id="hidden-device-bname">
                <input type="hidden" name="device_name" id="hidden-device-name">
                <input type="hidden" name="release_year" id="hidden-device-year">

                <!-- 購入日 -->

                <div class="space-y-5">

                    <div>

                        <label for="purchase_date" class="block text-sm font-bold text-slate-600 mb-1">購入日 <span
                                class="text-red-500">*</span></label>

                        <div class="relative date-input-wrapper">

                            <input type="date" name="purchase_date" id="purchase_date" required
                                class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 pr-24 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium appearance-none cursor-pointer relative z-10">

                            <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-3 z-20">

                                <button type="button"
                                    class="today-btn text-xs font-bold text-[#0071D3] bg-blue-100 hover:bg-blue-200 px-3 py-1.5 rounded-full transition-colors cursor-pointer whitespace-nowrap">

                                    今日

                                </button>

                            </div>

                        </div>

                    </div>

                    <div>

                        <label for="purchase_price" class="block text-sm font-bold text-slate-600 mb-1">購入価格
                            (税込)</label>

                        <div class="relative">

                            <input type="number" name="purchase_price" id="purchase_price" placeholder="例：100000"
                                min="0"
                                class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 pr-10 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium">

                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">円</span>

                        </div>

                    </div>

                </div>

                <!-- 保証終了日 -->
                <div>
                    <label for="warranty_end_date" class="block text-sm font-bold text-slate-600 mb-1">保証終了日</label>
                    <div class="relative">
                        <input type="date" name="warranty_end_date" id="warranty_end_date"
                            class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium appearance-none cursor-pointer relative z-10">
                    </div>
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" data-years="1"
                            class="warranty-shortcut-btn text-xs font-bold text-[#0071D3] bg-blue-100 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors cursor-pointer whitespace-nowrap">
                            1年
                        </button>
                        <button type="button" data-years="2"
                            class="warranty-shortcut-btn text-xs font-bold text-[#0071D3] bg-blue-100 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors cursor-pointer whitespace-nowrap">
                            2年
                        </button>
                    </div>
                </div>
                <!-- 返品期間（日数） -->
                <div>
                    <label for="return_days" class="block text-sm font-bold text-slate-600 mb-1">返品期間（日数）</label>
                    <div class="relative">
                        <input type="number" name="return_days" id="return_days" placeholder="例：30日" min="0"
                            class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium appearance-none cursor-text relative z-10">
                    </div>
                    <div class="flex justify-end gap-2 mt-2">
                        <button type="button" data-days="7"
                            class="return-shortcut-btn text-xs font-bold text-[#0071D3] bg-blue-100 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors cursor-pointer whitespace-nowrap">
                            7日
                        </button>
                        <button type="button" data-days="14"
                            class="return-shortcut-btn text-xs font-bold text-[#0071D3] bg-blue-100 hover:bg-blue-200 px-4 py-2 rounded-lg transition-colors cursor-pointer whitespace-nowrap">
                            14日
                        </button>
                    </div>
                </div>

                <!-- 現在の状態 -->
                <div>
                    <label for="device_status" class="block text-sm font-bold text-slate-600 mb-1">現在の状態 <span
                            class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="device_status" id="device_status" required
                            class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 pr-10 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium appearance-none cursor-pointer relative z-10">
                            <option value="active" selected>使用中 (Active)</option>
                            <option value="broken">故障 (Broken)</option>
                            <option value="sold">売却済 (Sold)</option>
                            <option value="storage">保管中 (In Storage)</option>
                        </select>
                        <i
                            class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none custom-icon"></i>
                    </div>
                </div>

                <!-- 手放した日/故障日 -->
                <div id="unusable-date-container" class="hidden transition-all duration-300 space-y-4">
                    <div>
                        <label for="unusable_date" class="block text-sm font-bold text-slate-600 mb-1">手放した日 /
                            故障した日</label>
                        <div class="relative date-input-wrapper">
                            <input type="date" name="unusable_date" id="unusable_date"
                                class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 pr-24 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium appearance-none cursor-pointer relative z-10">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2 z-20">
                                <button type="button"
                                    class="today-btn text-xs font-bold text-[#0071D3] bg-blue-100 hover:bg-blue-200 px-3 py-1.5 rounded-full transition-colors cursor-pointer whitespace-nowrap">
                                    今日
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="unusable_reason" class="block text-sm font-bold text-slate-600 mb-1">理由
                            (故障・売却・譲渡など)</label>
                        <div class="relative">
                            <input type="text" name="unusable_reason" id="unusable_reason"
                                placeholder="例：画面が割れた、バッテリー劣化、メルカリで売却..."
                                class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium">
                        </div>
                    </div>

                    <div id="sold-price-container" class="hidden">
                        <label for="sold_price" class="block text-sm font-bold text-slate-600 mb-1">売却価格</label>
                        <div class="relative">
                            <input type="number" name="sold_price" id="sold_price" placeholder="例：50000" min="0"
                                class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium">
                        </div>
                    </div>
                </div>

                <!-- レシート/保証書ファイル -->
                <div>
                    <label
                        class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">関連ファイル</label>
                    <div id="image-preview-container" class="grid grid-cols-2 gap-4">
                        <!-- JSでプレビューが挿入される -->
                    </div>
                    <button type="button" id="add-image-btn"
                        class="mt-4 w-full border-2 border-dashed border-blue-100 rounded-2xl bg-blue-50/30 text-blue-500 font-black py-5 px-6 hover:bg-blue-50 hover:border-blue-300 transition-all flex flex-col items-center justify-center gap-2 group">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl group-hover:scale-110 transition-transform"></i>
                        <span class="text-sm">ファイルを追加する</span>
                    </button>
                    <input type="file" id="image-upload-input" multiple accept=".jpg,.jpeg,.png,.gif,.pdf"
                        class="hidden">
                    <p class="text-[10px] text-slate-400 font-bold mt-3 ml-1">※JPG, PNG, PDF形式に対応 (最大 2MB)</p>
                </div>

                <div>
                    <label for="comment" class="block text-sm font-bold text-slate-600 mb-1">メモ・コメント</label>
                    <textarea name="comment" id="comment" rows="3" placeholder="故障の症状や、気に入っている点など..."
                        class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium resize-none"></textarea>
                </div>

                <!-- 公開設定 -->
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-[#0071D3]">
                            <i class="fa-solid fa-globe text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-700">全体公開設定</p>
                            <p class="text-[10px] text-slate-400 font-medium">殿堂入りページに表示されます</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_public" value="1" class="sr-only peer" checked>
                        <div
                            class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0071D3]">
                        </div>
                    </label>
                </div>

                <div class="pt-4">
                    <button type="submit" id="submit-device-btn"
                        class="w-full bg-[#0071D3] hover:bg-[#005bb5] text-white font-bold py-3 px-6 rounded-xl shadow-lg hover:shadow-blue-500/30 transition-all transform active:scale-[0.98] flex justify-center items-center gap-2">
                        <span>追加する</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- 画像プレビュー用ライトボックス -->
    <div class="js-lightbox fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4 cursor-pointer">
        <img src="" alt="Image Preview" class="js-lightbox-image max-w-full max-h-full object-contain cursor-default">
        <button type="button"
            class="js-close-lightbox-btn absolute top-4 right-4 text-white/80 hover:text-white transition-colors text-4xl leading-none">&times;</button>
    </div>
</dialog>