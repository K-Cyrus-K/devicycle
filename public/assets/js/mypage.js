document.addEventListener("DOMContentLoaded", () => {
  const filterBtns = document.querySelectorAll(".filter-btn");
  const visibilityFilterBtns = document.querySelectorAll(".visibility-filter-btn");
  const cards = Array.from(document.querySelectorAll(".js-device-card"));
  const grid = document.getElementById("device-grid");
  const sortSelect = document.getElementById("device-sort");

  // デバイス状態フィルター
  filterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      filterBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      applyFilterAndSort();
    });
  });

  // 公開状態フィルター
  visibilityFilterBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      visibilityFilterBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");
      applyFilterAndSort();
    });
  });

  // 並べ替えロジック
  if (sortSelect) {
    // sessionStorageから以前のソート順を読み込む
    const savedSort = sessionStorage.getItem("devicycle_sort_order");
    if (savedSort) {
      sortSelect.value = savedSort;
    }

    sortSelect.addEventListener("change", () => {
      // ソート順をsessionStorageに保存
      sessionStorage.setItem("devicycle_sort_order", sortSelect.value);
      applyFilterAndSort();
    });
  }

  // 初期表示の実行
  applyFilterAndSort();

  function applyFilterAndSort() {
    if (!grid) return;

    const filterType =
      document.querySelector(".filter-btn.active")?.dataset.filter || "all";
    const visibilityType =
      document.querySelector(".visibility-filter-btn.active")?.dataset.visibility || "all";
    const sortType = sortSelect ? sortSelect.value : "reg_desc";

    // 1. ソート (省略なしで維持)
    cards.sort((a, b) => {
      let valA, valB;
      switch (sortType) {
        case "reg_desc":
          return parseInt(b.dataset.id) - parseInt(a.dataset.id);
        case "reg_asc":
          return parseInt(a.dataset.id) - parseInt(b.dataset.id);
        case "purchase_desc":
          valA = new Date(a.dataset.purchaseDate).getTime();
          valB = new Date(b.dataset.purchaseDate).getTime();
          return valB - valA;
        case "purchase_asc":
          valA = new Date(a.dataset.purchaseDate).getTime();
          valB = new Date(b.dataset.purchaseDate).getTime();
          return valA - valB;
        case "price_desc":
          valA = parseInt(a.dataset.purchasePrice) || 0;
          valB = parseInt(b.dataset.purchasePrice) || 0;
          return valB - valA;
        case "price_asc":
          valA = parseInt(a.dataset.purchasePrice) || 0;
          valB = parseInt(b.dataset.purchasePrice) || 0;
          return valA - valB;
        case "duration_desc":
          valA = parseInt(a.dataset.daysPassed.replace(/,/g, "")) || 0;
          valB = parseInt(b.dataset.daysPassed.replace(/,/g, "")) || 0;
          return valB - valA;
        default:
          return 0;
      }
    });

        // 2. フィルタリングとDOMへの再配置
        let visibleIndex = 0;
        const noResultsMessage = document.getElementById("no-results-message");
    
        cards.forEach((card) => {
          const status = card.dataset.status || "";
          const isPublic = card.dataset.isPublic === "1";
    
          let isStatusMatch = false;
          let isVisibilityMatch = false;
    
          // ステータス条件の判定
          if (filterType === "all") {
            isStatusMatch = true;
          } else if (filterType === "active") {
            isStatusMatch =
              !status.includes("故障") &&
              !status.includes("売却") &&
              !status.includes("保管");
          } else if (filterType === "storage") {
            isStatusMatch = status.includes("保管");
          } else if (filterType === "archive") {
            isStatusMatch = status.includes("故障") || status.includes("売却済");
          }
    
          // 公開状態条件の判定
          if (visibilityType === "all") {
            isVisibilityMatch = true;
          } else if (visibilityType === "public") {
            isVisibilityMatch = isPublic;
          } else if (visibilityType === "private") {
            isVisibilityMatch = !isPublic;
          }
    
          // 両方の条件に一致する場合のみ表示
          if (isStatusMatch && isVisibilityMatch) {
            card.style.display = "flex";
            card.classList.remove("animate-card-entry");
            void card.offsetWidth;
            card.style.animationDelay = `${visibleIndex * 0.05}s`;
            card.classList.add("animate-card-entry");
            visibleIndex++;
          } else {
            card.style.display = "none";
          }
    
          grid.appendChild(card);
        });
    
        // 結果が0件の場合のメッセージ表示制御
        if (noResultsMessage) {
          if (visibleIndex === 0 && cards.length > 0) {
            noResultsMessage.classList.remove("hidden");
            noResultsMessage.classList.add("flex");
            grid.classList.add("hidden");
          } else {
            noResultsMessage.classList.add("hidden");
            noResultsMessage.classList.remove("flex");
            grid.classList.remove("hidden");
          }
        }
      }
  // --- 通知設定トグルロジック ---
  const globalAlertToggle = document.getElementById("global-alert-toggle");
  const expiringSection = document.getElementById("expiring-devices-section");

  const updateAlertVisibility = () => {
    const isDisabled = sessionStorage.getItem("devicycle_disable_alerts") === "true";
    if (globalAlertToggle) globalAlertToggle.checked = !isDisabled;
    if (expiringSection) {
      if (isDisabled) {
        expiringSection.classList.add("hidden");
      } else if (expiringSection.querySelectorAll(".grid > div").length > 0) {
        // 通知がある場合のみ表示
        expiringSection.classList.remove("hidden");
      }
    }
  };

  if (globalAlertToggle) {
    globalAlertToggle.addEventListener("change", (e) => {
      sessionStorage.setItem("devicycle_disable_alerts", (!e.target.checked).toString());
      updateAlertVisibility();
      // ヘッダー側のバッジも更新するためのイベント
      window.dispatchEvent(new CustomEvent("devicycle_alerts_changed"));
    });
  }

  // ヘッダーからの変更通知を受け取る
  window.addEventListener("devicycle_alerts_changed", updateAlertVisibility);

  // 初期状態の反映
  updateAlertVisibility();

  // 保証日自動入力とショートカットボタン
  const purchaseDateInput = document.getElementById("purchase_date");
  const warrantyEndDateInput = document.getElementById("warranty_end_date");
  const warrantyShortcutBtns = document.querySelectorAll(
    ".warranty-shortcut-btn",
  );

  function updateWarrantyDate(purchaseDateStr, years) {
    if (!purchaseDateStr) {
      warrantyEndDateInput.value = "";
      return;
    }
    const purchaseDate = new Date(purchaseDateStr);
    if (isNaN(purchaseDate.getTime())) {
      warrantyEndDateInput.value = "";
      return;
    }
    purchaseDate.setFullYear(purchaseDate.getFullYear() + years);
    const yyyy = purchaseDate.getFullYear();
    const mm = String(purchaseDate.getMonth() + 1).padStart(2, "0");
    const dd = String(purchaseDate.getDate()).padStart(2, "0");
    warrantyEndDateInput.value = `${yyyy}-${mm}-${dd}`;
  }

  // 購入日入力時の自動設定 (1年後)
  if (purchaseDateInput && warrantyEndDateInput) {
    purchaseDateInput.addEventListener("input", (event) => {
      updateWarrantyDate(event.target.value, 1);
    });
  }

  // 保証ショートカットボタン
  warrantyShortcutBtns.forEach((btn) => {
    btn.addEventListener("click", (event) => {
      const years = parseInt(event.target.dataset.years, 10);
      updateWarrantyDate(purchaseDateInput.value, years);
    });
  });
});

// --- ウィッシュリスト操作 (グローバル関数) ---
function removeFromWishlist(deviceId, btn) {
    if (!confirm("ウィッシュリストから削除しますか？")) return;

    fetch(BASE_URL + "actions/toggle_wishlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ device_id: deviceId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            const card = btn.closest('.bg-white\\/80');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    location.reload();
                }, 300);
            } else {
                location.reload();
            }
        }
    });
}

function openAddDialogFromWishlist(deviceId) {
    fetch(BASE_URL + `actions/get_device_by_id.php?device_id=${deviceId}`)
        .then(res => res.json())
        .then(device => {
            if (device.error) {
                alert("デバイス情報の取得に失敗しました。");
                return;
            }
            if (typeof window.showDeviceDialog === 'function') {
                window.showDeviceDialog(device);
            }
        });
}

