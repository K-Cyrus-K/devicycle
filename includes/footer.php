<?php
require_once "functions.php";
ini_set("display_errors", true);
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/dialog.js" defer></script>

<footer class="bg-[#F5F5F7] text-[#181818] pt-16 pb-10 px-10 tracking-wide w-full border-t border-gray-200">
	<div class="max-w-screen-xl mx-auto">
		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12 items-start">

			<div>
				<a href="./" class="block mb-6">
					<h2 class="text-3xl font-bold text-[#13182e]"><?php echo getTitle(""); ?></h2>
				</a>

				<ul class="flex space-x-6 mt-6">
					<li>
						<a href="mailto:kwanhonkei2027@gmail.com"
							class="text-[#13182e] hover:text-[#0071D3] transition-colors">
							<i class="fa-regular fa-envelope text-3xl"></i>
						</a>
					</li>
					<li>

						<i class="fa-solid fa-briefcase text-3xl text-gray-400"></i>

					</li>
					<li>
						<a href="https://github.com/K-Cyrus-K"
							class="text-[#13182e] hover:text-[#0071D3] transition-colors">
							<i class="fa-brands fa-square-github text-3xl"></i>
						</a>
					</li>
				</ul>
			</div>

			<div>
				<h4 class="text-lg mb-6 font-bold text-[#13182e]">リンク</h4>
				<ul class="space-y-4">
					<li>
						<a href="./" class="text-gray-600 hover:text-[#0071D3] font-medium transition-colors">ホーム</a>
					</li>
					<li>
						<a href="./hall_of_fame.php"
							class="text-gray-600 hover:text-[#0071D3] font-medium transition-colors">殿堂入り</a>
					</li>
				</ul>
			</div>

			<div>
				<h4 class="text-lg mb-6 font-bold text-[#13182e]">Information</h4>
				<ul class="space-y-4">
					<li>
						<a href="./"
							class="text-gray-600 hover:text-[#0071D3] font-medium transition-colors">このサイトについて</a>
					</li>
					<li>
						<a href="./terms.php"
							class="text-gray-600 hover:text-[#0071D3] font-medium transition-colors">利用規約</a>
					</li>
					<li class="pt-4">
						<p><a href="http://www.freepik.com" class="text-gray-400 hover:text-gray-600 text-xs">Designed
								by rawpixel.com / Freepik</a></p>
					</li>
				</ul>
			</div>
		</div>

		<div class="text-center mt-12 pt-8 border-t border-gray-300 text-gray-500 text-sm">
			&copy; <?php echo date("Y"); ?> <a href="./"><?php echo getTitle(""); ?></a> All rights reserved
		</div>
	</div>

	<!-- ダイアログのファイル導入 -->
	<?php include "../includes/dialogs/add_device_dialog.php" ?>
	<?php include "../includes/dialogs/user_device_dialog.php" ?>
</footer>

</body>

</html>