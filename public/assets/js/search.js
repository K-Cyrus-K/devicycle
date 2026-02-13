document.addEventListener("DOMContentLoaded", () => {
  // ---------------------- DOM要素の取得 ----------------------
  const searchBar = document.getElementById("search-bar");
  const resultsList = document.getElementById("results-list");

  // ダイアログ関連
  const addDeviceDialog = document.getElementById("addDeviceDialog");
  const addDeviceForm = document.getElementById("add-device-form");

  // ---------------------- 検索機能 ----------------------
  let timeout = null;
  let highlightedIndex = -1;

  if (searchBar && resultsList) {
    searchBar.addEventListener("input", (e) => {
      clearTimeout(timeout);
      const keyword = e.target.value.trim();
      highlightedIndex = -1; // Reset on new input

      if (keyword.length === 0) {
        clearResults();
        return;
      }

      resultsList.classList.remove("hidden");
      resultsList.innerHTML =
        '<div class="px-4 py-3 text-gray-400 text-sm">検索中...</div>';

      timeout = setTimeout(() => {
        performSearch(keyword);
      }, 300);
    });

    searchBar.addEventListener("keydown", (e) => {
      const items = resultsList.querySelectorAll(".search-result-item");
      if (items.length === 0) return;

      if (e.key === "ArrowDown") {
        e.preventDefault();
        highlightedIndex = (highlightedIndex + 1) % items.length;
        updateHighlight(items);
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        highlightedIndex = (highlightedIndex - 1 + items.length) % items.length;
        updateHighlight(items);
      } else if (e.key === "Enter") {
        e.preventDefault();
        if (highlightedIndex > -1 && items[highlightedIndex]) {
          items[highlightedIndex].click();
        }
      } else if (e.key === "Escape") {
        searchBar.value = "";
        clearResults();
      }
    });

    document.addEventListener("click", (e) => {
      if (e.target !== searchBar && !resultsList.contains(e.target)) {
        resultsList.classList.add("hidden");
      }
    });
  }

  function updateHighlight(items) {
    items.forEach((item, index) => {
      if (index === highlightedIndex) {
        item.classList.add("bg-blue-100", "rounded-2xl");
        item.scrollIntoView({ block: "nearest", inline: "start" });
      } else {
        item.classList.remove("bg-blue-100", "rounded-2xl");
      }
    });
  }

  function performSearch(keyword) {
    fetch(BASE_URL + "actions/search.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ keyword: keyword }),
    })
      .then((response) => {
        if (!response.ok) throw new Error("Network response was not ok");
        return response.json();
      })
      .then((data) => {
        renderSearchResults(data);
      })
      .catch((error) => {
        console.error("Search Fetch error:", error);
        resultsList.innerHTML =
          '<div class="px-4 py-3 text-red-500 text-sm">エラーが発生しました</div>';
      });
  }

  function renderSearchResults(data) {
    resultsList.innerHTML = "";
    resultsList.classList.remove("hidden");
    highlightedIndex = -1; // 新しい結果でインデックスをリセット

    if (data.length === 0) {
      const itemDiv = document.createElement("div");
      itemDiv.textContent = "検索結果が見つかりませんでした";
      itemDiv.className = "px-4 py-3 text-slate-500 text-sm";
      resultsList.appendChild(itemDiv);
      return;
    }

    data.forEach((device, index) => {
      const brandName = device.bname || device.brand_name || "";

      const itemDiv = document.createElement("div");
      itemDiv.className =
        "search-result-item px-4 py-3 cursor-pointer hover:bg-blue-50 hover:rounded-2xl text-slate-700 border-b border-slate-100 last:border-0";

      const bnameDiv = document.createElement("div");
      bnameDiv.className = "font-bold text-[#0071D3] text-sm";
      bnameDiv.textContent = brandName;

      const infoDiv = document.createElement("div");
      infoDiv.className = "font-medium text-slate-800";
      infoDiv.textContent = `${brandName} ${device.dname} `;

      const yearSpan = document.createElement("span");
      yearSpan.className = "text-slate-400 text-sm font-normal";
      if (device.launched_year && device.launched_year !== "0000") {
        yearSpan.textContent = `(${device.launched_year})`;
      } else {
        yearSpan.textContent = "(-)";
      }

      itemDiv.appendChild(bnameDiv);
      itemDiv.appendChild(infoDiv);
      infoDiv.appendChild(yearSpan);

      itemDiv.addEventListener("click", () => {
        showDeviceDialog(device);
        clearResults();
      });

      itemDiv.addEventListener("mouseover", () => {
        highlightedIndex = index;
        updateHighlight(resultsList.querySelectorAll(".search-result-item"));
      });

      resultsList.appendChild(itemDiv);
    });
  }

  function clearResults() {
    resultsList.innerHTML = "";
    resultsList.classList.add("hidden");
    highlightedIndex = -1; // インデックスをリセット
  }
  // ---------------------- ダイアログ表示 ----------------------
  window.showDeviceDialog = function(device) {
    if (!addDeviceDialog) return;
    const deviceBrand = device.bname ? device.bname : "Device";

    setTextContent("dialog-device-bname", deviceBrand || "");
    setTextContent("dialog-device-name", deviceBrand + " " + device.dname);

    // スペックリンクの設定
    const specLink = document.getElementById("dialog-spec-link");
    if (specLink) {
      const query = `${deviceBrand} ${device.dname} スペック`.trim();
      specLink.href = `https://www.google.co.jp/search?q=${encodeURIComponent(query)}`;
    }

    // ウィッシュリストボタンの設定
    const wishlistBtn = document.getElementById("toggle-wishlist-btn");
    const wishlistBtnText = document.getElementById("wishlist-btn-text");
    if (wishlistBtn && device.did) {
      // 初期状態の確認
      fetch(BASE_URL + `actions/check_wishlist.php?device_id=${device.did}`)
        .then(res => res.json())
        .then(data => {
          updateWishlistBtnUI(data.in_wishlist);
        });

      wishlistBtn.onclick = (e) => {
        e.preventDefault();
        fetch(BASE_URL + "actions/toggle_wishlist.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ device_id: device.did })
        })
          .then(res => res.json())
          .then(data => {
            if (data.status === "success") {
              updateWishlistBtnUI(data.action === "added");
            } else {
              alert(data.message);
            }
          });
      };
    }

    function updateWishlistBtnUI(isInWishlist) {
      if (!wishlistBtn || !wishlistBtnText) return;
      const icon = wishlistBtn.querySelector("i");
      if (isInWishlist) {
        wishlistBtn.classList.replace("bg-rose-50", "bg-rose-500");
        wishlistBtn.classList.replace("text-rose-500", "text-white");
        wishlistBtnText.textContent = "ウィッシュリスト入り";
        icon.classList.replace("fa-regular", "fa-solid");
      } else {
        wishlistBtn.classList.replace("bg-rose-500", "bg-rose-50");
        wishlistBtn.classList.replace("text-white", "text-rose-500");
        wishlistBtnText.textContent = "欲しい！";
        icon.classList.replace("fa-solid", "fa-regular");
      }
    }

    setTextContent(
      "dialog-device-year",
      device.launched_year && device.launched_year !== "0000"
        ? `発売年：${device.launched_year}年`
        : "発売年：データなし",
    );

    const imgPath = document.getElementById("dialog-device-img");
    if (imgPath) {
      if (device.image_path && device.image_path !== "") {
        imgPath.src = device.image_path;
      } else {
        imgPath.src = `https://placehold.jp/45/3d4070/191919/300x300.png?text=${deviceBrand}%0A${device.dname}%0A${device.launched_year}&css=%7B%22border-radius%22%3A%2216px%22%2C%22background%22%3A%22%20%23EFF6FF%22%2C%22background%22%3A%22%20linear-gradient(180deg%2C%20rgba(239%2C%20246%2C%20255%2C%201)%200%25%2C%20rgba(211%2C%20233%2C%20255%2C%201)%2050%25%2C%20rgba(215%2C%20215%2C%20255%2C%201)%20100%25)%22%7D`;
      }
    }

    // IDなどをセット
    setValue("hidden-device-id", device.did);
    setValue("hidden-device-name", device.dname);
    setValue("hidden-device-bname", device.bname);
    setValue("hidden-device-year", device.launched_year);

    // ---------------------- 評価表示 (左パネル) ----------------------
    // 注: add-device-rating-container はユーザー入力用なので、ここではリセットします（リセット処理で対応）。
    // ここではグローバル平均テキストのみを表示します。
    const ratingValText = document.getElementById("add-global-rating");

    if (ratingValText) {
      const avgRating = parseFloat(device.avg_rating) || 0;
      const count = parseInt(device.rating_count) || 0; // count が渡されることを確認、またはデフォルトで 0
      const formattedRating = avgRating > 0 ? avgRating.toFixed(1) : "-";
      // 注: fuzzy_search_devices SP は現在 rating_count を返します。
      const formattedCount = avgRating > 0 ? ` (${count})` : "";
      ratingValText.textContent = `${formattedRating}${formattedCount}`;
    }

    // ---------------------- 統計情報表示 (寿命・故障理由) ----------------------
    const statsContainer = document.getElementById("device-stats-info");
    const lifespanEl = document.getElementById("stat-avg-lifespan");
    const commonReasonEl = document.getElementById("stat-common-reason");

    if (statsContainer) {
      const avgLifespan = parseInt(device.avg_lifespan);
      const commonReason = device.common_failure_reason;

      if ((avgLifespan && avgLifespan > 0) || commonReason) {
        // 平均寿命の表示
        if (lifespanEl) {
          if (avgLifespan && avgLifespan > 0) {
            const years = Math.floor(avgLifespan / 365);
            const days = avgLifespan % 365;
            let lifespanStr = "";
            if (years > 0) lifespanStr += `${years}年`;
            if (days > 0 || years === 0) lifespanStr += `${days}日`;
            lifespanEl.textContent = lifespanStr;
            lifespanEl.parentElement.classList.remove("hidden");
          } else {
            lifespanEl.parentElement.classList.add("hidden");
          }
        }

        // 故障理由の表示
        if (commonReasonEl) {
          if (commonReason) {
            commonReasonEl.textContent = commonReason;
            commonReasonEl.parentElement.classList.remove("hidden");
          } else {
            commonReasonEl.parentElement.classList.add("hidden");
          }
        }
        statsContainer.classList.remove("hidden");
      } else {
        statsContainer.classList.add("hidden");
      }
    }

    // ---------------------- 市場価格取得ロジック ----------------------
    const priceContainer = document.getElementById("device-market-price");
    const priceNewEl = document.getElementById("price-new");
    const priceUsedEl = document.getElementById("price-used");
    const priceDateEl = document.getElementById("price-date");
    const yahooLinkEl = document.getElementById("yahoo-link");
    const refreshBtn = document.getElementById("refresh-price-btn");
    let lastFetchTime = 0; // 追加：最終取得時刻

    const fetchPrice = (force = false) => {
      const query = `${device.bname || ""} ${device.dname}`.trim();
      if (!device.did || !query) return;

      // レートリミットチェック (60秒)
      const now = Date.now();
      if (force && now - lastFetchTime < 60000) {
        const remaining = Math.ceil((60000 - (now - lastFetchTime)) / 1000);
        alert(`更新は1分間に1回までです。あと ${remaining}秒 待ってください。`);
        return;
      }

      // 関数内で最新のボタン要素を取得するように変更
      const currentRefreshBtn = document.getElementById("refresh-price-btn");

      if (force && currentRefreshBtn) {
        const icon = currentRefreshBtn.querySelector("i");
        if (icon) icon.classList.add("fa-spin");
        currentRefreshBtn.disabled = true;
      }

      fetch(
        BASE_URL +
          `actions/get_device_price.php?device_id=${device.did}&query=${encodeURIComponent(query)}${force ? "&force=1" : ""}`,
      )
        .then((res) => res.json())
        .then((data) => {
          if (data.error) {
            console.error("Price fetch error:", data.error);
            return;
          }

          if (data.data) {
            if (force) lastFetchTime = Date.now(); // 強制更新時のみ時刻を更新
            const p = data.data;
            if (priceNewEl)
              priceNewEl.textContent = p.avg_new_price
                ? `¥${Number(p.avg_new_price).toLocaleString()}`
                : "-";
            if (priceUsedEl)
              priceUsedEl.textContent = p.avg_used_price
                ? `¥${Number(p.avg_used_price).toLocaleString()}`
                : "-";
            if (priceDateEl) {
              const datePart = p.last_checked ? p.last_checked.split(' ')[0] : "-";
              priceDateEl.textContent = `取得日: ${datePart}`;
            }

            if (yahooLinkEl) {
              yahooLinkEl.href = `https://shopping.yahoo.co.jp/search?p=${encodeURIComponent(query)}`;
            }

            priceContainer.classList.remove("hidden");
          }
        })
        .catch((err) => console.error("Price API error:", err))
        .finally(() => {
          const currentRefreshBtnAfter = document.getElementById("refresh-price-btn");
          if (currentRefreshBtnAfter) {
            const icon = currentRefreshBtnAfter.querySelector("i");
            if (icon) icon.classList.remove("fa-spin");
            currentRefreshBtnAfter.disabled = false;
          }
        });
    };

    if (priceContainer) {
      priceContainer.classList.add("hidden"); // 初期状態は非表示
      if (priceNewEl) priceNewEl.textContent = "-";
      if (priceUsedEl) priceUsedEl.textContent = "-";
      if (priceDateEl) priceDateEl.textContent = "取得日: -";

      fetchPrice(false); // 初回取得
    }

    if (refreshBtn) {
      refreshBtn.onclick = (e) => {
        e.preventDefault();
        fetchPrice(true);
      };
    }

    // フォームのリセットとUI初期化
    if (addDeviceForm) {
      addDeviceForm.reset();

      // 評価入力（ラジオボタン）をリセット - 評価はフォームの外にあります（左パネル）
      const ratingContainer = document.getElementById(
        "add-device-rating-container",
      );
      if (ratingContainer) {
        const ratingRadios = ratingContainer.querySelectorAll(
          'input[name="rating"]',
        );
        ratingRadios.forEach((r) => (r.checked = false));
      }

      // フォームリセット後、日付入力欄コンテナを確実に隠す（初期状態は active のため）
      const unusableContainer = document.getElementById(
        "unusable-date-container",
      );
      if (unusableContainer) {
        unusableContainer.classList.add("hidden");
      }
      const soldPriceContainer = document.getElementById(
        "sold-price-container",
      );
      if (soldPriceContainer) {
        soldPriceContainer.classList.add("hidden");
      }
    }

    // ダイアログを表示
    addDeviceDialog.showModal();
    document.body.style.overflow = "hidden";
  }

  function setTextContent(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  }
  function setValue(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value || "";
  }
});
