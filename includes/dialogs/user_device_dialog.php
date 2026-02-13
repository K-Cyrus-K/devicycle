<dialog class="w-full max-w-4xl rounded-3xl p-0 backdrop:bg-black/60 m-auto shadow-2xl overflow-hidden"
    id="userDeviceDialog">

    <div class="grid grid-cols-1 md:grid-cols-2 bg-white max-h-[90vh] overflow-y-auto">
        <div
            class="bg-slate-50 flex flex-col items-center justify-center p-8 border-b md:border-b-0 md:border-r border-slate-100 text-center">

            <div class="w-full aspect-square bg-white rounded-2xl p-6 shadow-sm flex items-center justify-center mb-6">
                <img id="detail-image" src="" alt="" class="max-w-full max-h-full object-contain">
            </div>

            <span id="detail-brand" class="text-base font-bold text-[#0071D3] mb-2 block"></span>
            <h2 id="detail-name" class="text-4xl font-bold text-slate-900 mb-4"></h2>

            <!-- スペック確認リンク -->
            <div class="mb-6">
                <a id="detail-spec-link" href="#" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-slate-600 hover:bg-blue-50 hover:text-[#0071D3] text-sm font-bold transition-all border border-slate-200 hover:border-blue-200 shadow-sm">
                    <i class="fa-brands fa-google text-xs"></i>
                    <span>スペックを確認</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </div>

            <!-- 評価入力 (星) -->
            <div class="flex flex-col items-center mb-4">
                <?php if (!empty($uid)): ?>
                    <div class="rating rating-lg" id="detail-rating-container">
                        <input type="radio" id="detail-star-5" name="detail-rating" value="5">
                        <label for="detail-star-5">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path pathLength="360"
                                    d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                </path>
                            </svg>
                        </label>
                        <input type="radio" id="detail-star-4" name="detail-rating" value="4">
                        <label for="detail-star-4">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path pathLength="360"
                                    d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                </path>
                            </svg>
                        </label>
                        <input type="radio" id="detail-star-3" name="detail-rating" value="3">
                        <label for="detail-star-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path pathLength="360"
                                    d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                </path>
                            </svg>
                        </label>
                        <input type="radio" id="detail-star-2" name="detail-rating" value="2">
                        <label for="detail-star-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path pathLength="360"
                                    d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                </path>
                            </svg>
                        </label>
                        <input type="radio" id="detail-star-1" name="detail-rating" value="1">
                        <label for="detail-star-1">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path pathLength="360"
                                    d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z">
                                </path>
                            </svg>
                        </label>
                    </div>
                <?php endif ?>
                <div class="text-sm text-slate-400 mt-1">
                    ユーザ平均：<span id="detail-global-rating" class="font-bold text-slate-600">-</span>
                </div>
            </div>

            <p id="detail-launch-year" class="text-slate-500 text-base mb-8">発売年: -</p>

            <!-- デバイス統計情報 (寿命・故障理由) -->
            <div id="user-device-stats-info"
                class="w-full mt-2 mb-8 p-5 bg-blue-50/50 rounded-3xl border border-blue-100 hidden transition-all duration-300 text-left">
                <div class="flex items-center gap-2 mb-4">
                    <i class="fa-solid fa-chart-line text-[#0071D3] text-sm"></i>
                    <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">信頼性データ (ユーザー平均)</h4>
                </div>
                <div class="space-y-4">
                    <div id="user-stat-lifespan-container" class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-500">平均使用期間</span>
                        <span id="user-stat-avg-lifespan" class="text-base font-black text-slate-700">-</span>
                    </div>
                    <div id="user-stat-reason-container" class="pt-3 border-t border-blue-100/50">
                        <span
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1.5">主な故障理由</span>
                        <span id="user-stat-common-reason"
                            class="text-sm font-bold text-slate-600 leading-relaxed">-</span>
                    </div>
                </div>
            </div>

            <!-- 市場価格情報 -->
            <div id="user-device-market-price"
                class="mt-2 p-5 bg-white rounded-3xl shadow-sm border border-slate-100 hidden transition-all duration-300 w-full text-left">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">市場価格相場</h4>
                    <?php if (!empty($uid)): ?>
                        <button type="button" id="user-refresh-price-btn"
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-blue-50 hover:text-[#0071D3] transition-all"
                            title="価格を更新">
                            <i class="fa-solid fa-rotate text-xs"></i>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div class="bg-slate-50 p-3 rounded-2xl">
                        <p class="text-[10px] font-black text-slate-400 mb-1 uppercase">新品平均</p>
                        <p id="user-price-new" class="font-black text-slate-700 text-xl">-</p>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-2xl">
                        <p class="text-[10px] font-black text-slate-400 mb-1 uppercase">中古平均</p>
                        <p id="user-price-used" class="font-black text-slate-700 text-xl">-</p>
                    </div>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <p id="user-price-date" class="text-[10px] font-bold text-slate-300">取得日: -</p>
                    <a id="user-yahoo-link" href="#" target="_blank"
                        class="text-xs font-black text-[#0071D3] hover:text-[#005bb5] flex items-center gap-1 transition-colors">
                        <span>商品ページ</span>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
                <!-- Yahoo! JAPAN Web Services アトリビューション -->
                <div class="text-center pt-3 border-t border-slate-50">
                    <span class="text-[9px] font-bold text-slate-300"><a href="https://developer.yahoo.co.jp/sitemap/"
                            class="hover:underline">Webサービス by Yahoo! JAPAN</a></span>
                </div>
            </div>
        </div>

        <div class="p-8 md:p-10 relative">
            <form id="edit-device-form">
                <input type="hidden" name="entry_id" id="edit-entry-id">
                <input type="hidden" name="device_id" id="edit-device-id">
                <input type="hidden" name="user_id" value="<?php echo h($uid); ?>">

                <button type="button" name="closeBtn"
                    class="absolute top-6 right-6 text-slate-400 hover:text-slate-800 transition-colors w-12 h-12 rounded-full hover:bg-slate-100 flex items-center justify-center z-50">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>

                <div class="space-y-6 text-slate-600 mb-8">
                    <!-- 価値の推移 (推定) + 購入価格の統合 -->
                    <div
                        class="p-6 rounded-[2rem] border border-slate-100 bg-white view-mode shadow-sm relative overflow-hidden">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">価値の推移 (推定)
                                </h4>
                                <p class="text-[10px] text-slate-300">※発売時期と価格に基づく推計</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-black text-[#0071D3] uppercase tracking-widest mb-1">購入価格</p>
                                <p id="detail-purchase-price" class="text-2xl font-black text-slate-900 tracking-tight">
                                    -</p>
                            </div>
                        </div>
                        <div class="h-[220px] w-full">
                            <canvas id="depreciationChart"></canvas>
                        </div>
                    </div>

                    <!-- 日付: 表示と編集 -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">購入日</p>
                            <div class="view-mode bg-slate-50 p-4 rounded-2xl border border-slate-100 flex-1">
                                <p id="detail-purchase-date" class="text-base font-bold text-slate-800"></p>
                            </div>
                            <div class="edit-mode hidden flex-1">
                                <input type="date" name="purchase_date" id="edit-purchase-date"
                                    class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-4 rounded-2xl focus:bg-white focus:border-[#0071D3] outline-none transition-all font-bold">
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <p class="text-xs font-black text-blue-400 uppercase tracking-widest mb-2 ml-1">経過日数</p>
                            <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 view-mode flex-1">
                                <p class="text-base font-black text-[#0071D3]"><span id="detail-days-passed"></span>日
                                </p>
                            </div>
                            <div
                                class="edit-mode hidden flex-1 bg-blue-50/30 p-4 rounded-2xl border border-blue-50 border-dashed flex items-center">
                                <p class="text-sm text-blue-300 font-black">自動計算
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 編集モード用の購入価格入力 (統合されたので、編集時のみ独立して表示) -->
                    <div class="edit-mode hidden">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">購入価格 (税込)</p>
                        <div class="relative">
                            <input type="number" name="purchase_price" id="edit-purchase-price"
                                class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-4 pr-12 rounded-2xl focus:bg-white focus:border-[#0071D3] outline-none transition-all font-bold">
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">円</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">保証終了日</p>
                            <div class="view-mode bg-slate-50 p-4 rounded-2xl border border-slate-100 flex-1">
                                <p id="detail-warranty-date" class="text-base font-bold text-slate-800"></p>
                            </div>
                            <div class="edit-mode hidden flex-1">
                                <input type="date" name="warranty_end_date" id="edit-warranty-date"
                                    class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-4 rounded-2xl focus:bg-white focus:border-[#0071D3] outline-none transition-all font-bold">
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">返品期限日　<span
                                    id="detail-return-alert"
                                    class="text-red-500 text-[0.7rem] font-black ml-2 animate-pulse"></span></p>

                            <div class="view-mode bg-slate-50 p-4 rounded-2xl border border-slate-100 flex-1">
                                <p class="text-base font-bold text-slate-800">
                                    <span id="detail-return-date"></span>

                                </p>
                            </div>
                            <div class="edit-mode hidden flex-1">
                                <input type="date" name="return_due_date" id="edit-return-date"
                                    class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-4 rounded-2xl focus:bg-white focus:border-[#0071D3] outline-none transition-all font-bold">
                            </div>
                        </div>
                    </div>

                    <!-- 売却価格 (売却済の場合のみ表示) -->
                    <div id="edit-sold-price-section" class="flex flex-col hidden">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">売却価格</p>
                        <div class="view-mode bg-slate-50 p-4 rounded-2xl border border-slate-100 flex-1">
                            <p id="detail-sold-price" class="text-lg font-black text-emerald-600">-</p>
                        </div>
                        <div class="edit-mode hidden flex-1">
                            <div class="relative">
                                <input type="number" name="sold_price" id="edit-sold-price"
                                    class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-4 pr-12 rounded-2xl focus:bg-white focus:border-[#0071D3] outline-none transition-all font-bold">
                                <span
                                    class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 font-bold">円</span>
                            </div>
                        </div>
                    </div>

                    <!-- ステータス: 表示と編集 -->
                    <div class="p-5 rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">現在の状態</p>
                        <div class="view-mode">
                            <p class="text-slate-800 font-bold text-lg flex items-center gap-3">
                                <span id="detail-status-indicator" class="w-3 h-3 rounded-full shadow-sm"></span>
                                <span id="detail-status-text"></span>
                            </p>
                        </div>
                        <div class="edit-mode hidden">
                            <select name="status_name" id="edit-status"
                                class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-4 rounded-2xl focus:bg-white focus:border-[#0071D3] outline-none transition-all font-bold appearance-none cursor-pointer">
                                <!-- Options will be populated by JS -->
                            </select>

                            <!-- Unusable Reason Fields -->
                            <div id="edit-unusable-container"
                                class="hidden mt-4 space-y-4 border-t border-slate-50 pt-4">
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">終了日</label>
                                    <div class="relative date-input-wrapper">
                                        <input type="date" name="unusable_date" id="edit-unusable-date"
                                            class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-2xl focus:bg-white focus:border-[#0071D3] outline-none transition-all font-bold text-sm">
                                        <div
                                            class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2 z-20">
                                            <button type="button"
                                                class="today-btn text-xs font-black text-[#0071D3] bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full transition-colors cursor-pointer whitespace-nowrap">
                                                今日
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block ml-1">理由</label>
                                    <textarea name="unusable_reason" id="edit-unusable-reason"
                                        class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-4 rounded-2xl focus:bg-white focus:border-[#0071D3] outline-none transition-all text-sm font-bold resize-none"
                                        rows="2" placeholder="故障内容や売却先など..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div id="detail-unusable-container"
                            class="hidden mt-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-xs font-bold text-slate-400 mb-2 italic">(<span
                                    id="detail-unusable-date"></span> に終了)</p>
                            <p class="text-sm font-bold text-slate-600 leading-relaxed">理由: <span
                                    id="detail-unusable-reason"></span></p>
                        </div>
                    </div>

                    <!-- メモ: 表示と編集 -->
                    <div class="p-5 rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">メモ</p>
                        <div class="view-mode">
                            <p id="detail-notes" class="text-base text-slate-700 whitespace-pre-wrap leading-relaxed">
                            </p>
                        </div>
                        <div class="edit-mode hidden">
                            <textarea name="user_notes" id="edit-notes"
                                class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-4 rounded-2xl focus:bg-white focus:border-[#0071D3] focus:ring-8 focus:ring-blue-500/5 outline-none transition-all font-bold resize-none leading-relaxed"
                                rows="4" placeholder="メモを入力してください..."></textarea>
                        </div>
                    </div>

                    <!-- 公開設定 -->
                    <div
                        class="p-5 rounded-3xl border border-slate-100 bg-white flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100">
                                <i class="fa-solid fa-eye text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-700 uppercase tracking-widest">全体公開</p>
                                <div class="view-mode">
                                    <p id="detail-visibility-text" class="text-xs font-bold"></p>
                                </div>
                            </div>
                        </div>
                        <div class="edit-mode hidden">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_public" id="edit-is-public" value="1"
                                    class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0071D3]">
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 画像 -->
                    <div id="detail-images-container"
                        class="p-5 rounded-3xl border border-slate-100 bg-white shadow-sm">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">関連ファイル</p>
                        <div id="detail-images-gallery" class="grid grid-cols-3 gap-4"></div>

                        <div class="edit-mode hidden mt-6 border-t border-slate-50 pt-6">
                            <label
                                class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 block ml-1">ファイルの追加</label>
                            <input type="file" id="edit-image-upload-input" class="hidden" multiple
                                accept="image/*,.pdf">
                            <button type="button" id="edit-add-image-btn"
                                class="w-full py-5 border-2 border-dashed border-blue-100 rounded-2xl bg-blue-50/30 text-blue-500 font-black hover:bg-blue-50 hover:border-blue-300 transition-all flex flex-col items-center justify-center gap-2 group">
                                <i
                                    class="fa-solid fa-cloud-arrow-up text-3xl group-hover:scale-110 transition-transform"></i>
                                <span class="text-sm">ファイルを追加する</span>
                            </button>
                            <p class="text-[10px] text-slate-400 font-bold mt-3 ml-1">※JPG, PNG, PDF形式に対応 (最大 2MB)</p>
                            <div id="edit-image-preview-container" class="grid grid-cols-2 gap-4 mt-6"></div>
                        </div>
                    </div>
                </div>

                <!-- ボタン・グループ -->
                <div class="view-mode flex gap-4 pt-4">
                    <button type="button" id="edit-device-btn"
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black py-4 rounded-2xl transition-all flex items-center justify-center gap-2 text-base">
                        <i class="fa-solid fa-pen-to-square"></i> 編集する
                    </button>
                </div>
                <div class="edit-mode hidden flex gap-4 pt-4">
                    <button type="submit" id="save-device-btn"
                        class="flex-1 bg-[#0071D3] hover:bg-[#005bb5] text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2 text-base">
                        <i class="fa-solid fa-save"></i> 変更を保存
                    </button>
                    <button type="button" id="cancel-edit-btn"
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black py-4 rounded-2xl transition-all flex items-center justify-center gap-2 text-base">
                        キャンセル
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- ライトボックス -->
    <div class="js-lightbox fixed inset-0 bg-black/90 z-[9999] hidden items-center justify-center p-6 cursor-pointer">
        <img src="" alt="Preview"
            class="js-lightbox-image max-w-full max-h-full object-contain cursor-default drop-shadow-2xl">
        <button type="button"
            class="js-close-lightbox-btn absolute top-6 right-6 text-white/60 hover:text-white transition-colors text-5xl leading-none">&times;</button>
    </div>
</dialog>