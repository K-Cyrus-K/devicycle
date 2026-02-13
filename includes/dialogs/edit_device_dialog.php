<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ログインユーザーIDの取得 (未ログイン時は空)
$uid = isset($_SESSION['login_user']) ? $_SESSION['login_user']['uid'] : '';
?>

<dialog id="addDeviceDialog"
    class="w-full max-w-4xl rounded-3xl p-0 backdrop:bg-black/60 m-auto shadow-2xl overflow-hidden bg-white text-slate-800">

    <div class="grid grid-cols-1 md:grid-cols-2">
        <div class="bg-slate-50 flex flex-col items-center justify-center p-8 border-r border-slate-100">
            <div
                class="w-full aspect-square bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center overflow-hidden mb-6 p-4">
                <img id="dialog-device-img" src="" alt="Device Image" class="w-full h-full object-contain">
            </div>
            <div class="text-center">
                <span id="dialog-device-bname"
                    class="text-xs font-bold bg-blue-100 text-[#0071D3] px-3 py-1 rounded-full mb-2 inline-block">Brand</span>
                <h3 id="dialog-device-name" class="text-2xl font-bold text-slate-900 mb-1">Device Name</h3>
                <p id="dialog-device-year" class="text-slate-500 text-sm">Release Year</p>
            </div>
        </div>

        <div class="p-8 md:p-10">
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
                <input type="hidden" name="device_name" id="hidden-device-name">
                <input type="hidden" name="brand" id="hidden-device-brand">
                <input type="hidden" name="release_year" id="hidden-device-year">

                <div>
                    <label for="purchase_date" class="block text-sm font-bold text-slate-600 mb-1">購入日 <span
                            class="text-red-500">*</span></label>
                    <input type="date" name="purchase_date" id="purchase_date" required
                        class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium">
                </div>

                <div>
                    <label for="device_status" class="block text-sm font-bold text-slate-600 mb-1">現在の状態 <span
                            class="text-red-500">*</span></label>
                    <div class="relative">
                        <select name="device_status" id="device_status" required
                            class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium appearance-none cursor-pointer">
                            <option value="active" selected>使用中 (Active)</option>
                            <option value="broken">故障 (Broken)</option>
                            <option value="sold">売却済 (Sold)</option>
                            <option value="storage">保管中 (In Storage)</option>
                        </select>
                        <i
                            class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div id="unusable-date-container" class="hidden transition-all duration-300">
                    <label for="unusable_date" class="block text-sm font-bold text-slate-600 mb-1">手放した日 / 故障した日</label>
                    <input type="date" name="unusable_date" id="unusable_date"
                        class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium">
                </div>

                <div>
                    <label for="comment" class="block text-sm font-bold text-slate-600 mb-1">メモ・コメント</label>
                    <textarea name="comment" id="comment" rows="3" placeholder="故障の症状や、気に入っている点など..."
                        class="w-full bg-slate-50 border-2 border-slate-100 text-slate-800 p-3 rounded-xl focus:bg-white focus:border-[#0071D3] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-medium resize-none"></textarea>
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
</dialog>