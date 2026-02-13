document.addEventListener("DOMContentLoaded", () => {
  const body = document.querySelector("body");

  // --- 一般的なダイアログ関数 ---
  function setupDialog(dialogId, specificCloseBtnId = null) {
    const dialog = document.getElementById(dialogId);
    if (!dialog || dialog.dataset.initialized) return;
    dialog.dataset.initialized = "true"; // 重複実行防止

    let isProcessingClose = false;

    const handleCloseAttempt = (e) => {
      // ダイアログが既に閉じているか、処理中の場合は無視
      if (!dialog.open || isProcessingClose) return;
      isProcessingClose = true;

      // 連続実行を防ぐためのタイマー（OSやブラウザによる複数イベント発生対策）
      const releaseLock = () => {
        setTimeout(() => {
          isProcessingClose = false;
        }, 100);
      };

      if (dialogId === "authDialog") {
        if (!confirm("入力を破棄して閉じますか？")) {
          if (e && e.cancelable) e.preventDefault();
          releaseLock();
          return false;
        }
      }

      dialog.close();
      releaseLock();
      return true;
    };

    // 背景クリック
    dialog.addEventListener("click", (e) => {
      // ダイアログ自体がクリックされた（＝背景部分）場合のみ処理
      if (e.target === dialog) {
        handleCloseAttempt(e);
      }
    });

    // 閉じるボタン
    const closeButtons = dialog.querySelectorAll("[name='closeBtn']");
    closeButtons.forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.stopPropagation(); // バブリングを防ぐ
        handleCloseAttempt(e);
      });
    });

    if (specificCloseBtnId) {
      const specificBtn = document.getElementById(specificCloseBtnId);
      if (specificBtn) {
        specificBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          handleCloseAttempt(e);
        });
      }
    }

    // ESCキー (cancelイベント)
    dialog.addEventListener("cancel", (e) => {
      const lightbox = dialog.querySelector(".js-lightbox");
      if (
        lightbox &&
        (!lightbox.classList.contains("hidden") ||
          lightbox.classList.contains("flex"))
      ) {
        e.preventDefault();
        e.stopImmediatePropagation();
        lightbox.classList.add("hidden");
        lightbox.classList.remove("flex");
        const img = lightbox.querySelector(".js-lightbox-image");
        if (img) img.src = "";
      } else {
        // 通常のダイアログ閉じ
        handleCloseAttempt(e);
      }
    });

    dialog.addEventListener("close", () => {
      body.style.overflow = "auto";
      const url = new URL(window.location);
      url.searchParams.delete("error");
      window.history.replaceState({}, "", url);
      clearPHPSessionErrors();

      // このダイアログ内で開いているライトボックスを閉じる
      const lightbox = dialog.querySelector(".js-lightbox");
      if (lightbox) {
        lightbox.classList.add("hidden");
        lightbox.classList.remove("flex");
        const img = lightbox.querySelector(".js-lightbox-image");
        if (img) img.src = "";
      }
    });
  }

  function openDialog(dialog) {
    if (!dialog) return;
    dialog.showModal();
    body.style.overflow = "hidden";
  }

  function clearPHPSessionErrors() {
    fetch("../actions/clear_session.php", { method: "POST" }).catch((err) =>
      console.error(err),
    );
  }

  // --- 全ダイアログのセットアップ ---
  setupDialog("authDialog");
  setupDialog("addDeviceDialog", "close-add-dialog-btn");
  setupDialog("userDeviceDialog");

  // =========================================================================
  // ログイン / サインアップダイアログロジック (統合認証ダイアログ)
  // =========================================================================
  const authDialog = document.getElementById("authDialog");

  if (authDialog) {
    const flipCardInner = authDialog.querySelector(".flip-card-inner");

    document.querySelectorAll(".login, [name='login']").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        openDialog(authDialog);
        // flipDialogを使用して高さを調整
        if (typeof flipDialog === "function") {
          flipDialog("#authDialog", "login");
        } else if (flipCardInner) {
          flipCardInner.classList.remove("rotate-y-180");
        }
      });
    });

    document.querySelectorAll(".signup, [name='signup']").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        e.preventDefault();
        openDialog(authDialog);
        // flipDialogを使用して高さを調整
        if (typeof flipDialog === "function") {
          flipDialog("#authDialog", "signup");
        } else if (flipCardInner) {
          flipCardInner.classList.add("rotate-y-180");
        }
      });
    });
  }

  // --- グローバル「今日」ボタンロジック ---
  document.querySelectorAll(".today-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const wrapper = e.target.closest(".date-input-wrapper");
      const input = wrapper
        ? wrapper.querySelector('input[type="date"]')
        : null;
      if (input) {
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, "0");
        const dd = String(today.getDate()).padStart(2, "0");
        input.value = `${yyyy}-${mm}-${dd}`;
      }
    });
  });

  // =========================================================================
  // デバイス追加ダイアログロジック
  // =========================================================================
  const addDeviceDialog = document.getElementById("addDeviceDialog");
  if (addDeviceDialog) {
    // --- ステータス変更ロジック (使用不可の理由を表示/非表示) ---
    const statusSelect = addDeviceDialog.querySelector("#device_status");
    const unusableContainer = addDeviceDialog.querySelector(
      "#unusable-date-container",
    );
    const soldPriceContainer = addDeviceDialog.querySelector(
      "#sold-price-container",
    );

    if (statusSelect && unusableContainer) {
      statusSelect.addEventListener("change", (e) => {
        const status = e.target.value;
        if (status !== "active") {
          unusableContainer.classList.remove("hidden");
        } else {
          unusableContainer.classList.add("hidden");
        }

        if (soldPriceContainer) {
          if (status === "sold") {
            soldPriceContainer.classList.remove("hidden");
          } else {
            soldPriceContainer.classList.add("hidden");
          }
        }
      });
    }

    // --- 新規: ショートカットボタンロジック (保証期間と返品日数) ---

    // 1. 返品日数ショートカット (7日 / 14日)
    addDeviceDialog.querySelectorAll(".return-shortcut-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        const input = addDeviceDialog.querySelector("#return_days");
        if (input) input.value = btn.dataset.days;
      });
    });

    // 2. 保証年数ショートカット (1年 / 2年)
    // 注意: 購入日に基づいて計算します
    addDeviceDialog
      .querySelectorAll(".warranty-shortcut-btn")
      .forEach((btn) => {
        btn.addEventListener("click", () => {
          const purchaseDateInput =
            addDeviceDialog.querySelector("#purchase_date");
          const warrantyInput =
            addDeviceDialog.querySelector("#warranty_end_date");

          if (!purchaseDateInput.value) {
            alert("先に購入日を入力してください。");
            purchaseDateInput.focus();
            return;
          }

          const date = new Date(purchaseDateInput.value);
          const years = parseInt(btn.dataset.years, 10);
          date.setFullYear(date.getFullYear() + years);

          // 日付を YYYY-MM-DD 形式にフォーマット
          const yyyy = date.getFullYear();
          const mm = String(date.getMonth() + 1).padStart(2, "0");
          const dd = String(date.getDate()).padStart(2, "0");

          warrantyInput.value = `${yyyy}-${mm}-${dd}`;
        });
      });

    // --- フォーム送信 ---
    const addDeviceForm = addDeviceDialog.querySelector("#add-device-form");
    if (addDeviceForm) {
      addDeviceForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        fileStore.forEach((file) => formData.append(`images[]`, file));

        // 左パネルから評価を追加
        const ratingContainer = document.getElementById(
          "add-device-rating-container",
        );
        if (ratingContainer) {
          const checkedRating = ratingContainer.querySelector(
            'input[name="rating"]:checked',
          );
          if (checkedRating) {
            formData.append("rating", checkedRating.value);
          }
        }

        fetch(BASE_URL + "actions/add_device.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            alert(data.message);
            if (data.status) {
              addDeviceDialog.close();
              window.location.reload();
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("通信エラーが発生しました。");
          });
      });
    }

    // --- 画像アップロードとプレビュー ---
    const addImageBtn = addDeviceDialog.querySelector("#add-image-btn");
    const imageUploadInput = addDeviceDialog.querySelector(
      "#image-upload-input",
    );
    const imagePreviewContainer = addDeviceDialog.querySelector(
      "#image-preview-container",
    );
    let fileStore = [];

    if (addImageBtn && imageUploadInput) {
      addImageBtn.addEventListener("click", () => imageUploadInput.click());
      imageUploadInput.addEventListener("change", handleFileSelect);
    }

    function handleFileSelect(event) {
      const files = Array.from(event.target.files);
      files.forEach((file) => {
        if (
          !fileStore.some((f) => f.name === file.name && f.size === file.size)
        )
          fileStore.push(file);
      });
      renderPreviews();
      event.target.value = "";
    }

    function renderPreviews() {
      if (!imagePreviewContainer) return;

      while (imagePreviewContainer.firstChild) {
        imagePreviewContainer.removeChild(imagePreviewContainer.firstChild);
      }

      fileStore.forEach((file, index) => {
        const previewWrapper = document.createElement("div");
        previewWrapper.className =
          "flex flex-col relative w-full bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden group hover:shadow-md transition-all";

        // --- 削除ボタン ---
        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className =
          "absolute top-2 right-2 z-10 w-6 h-6 rounded-full bg-slate-900/60 text-white hover:bg-red-500 flex items-center justify-center text-xs transition-colors backdrop-blur-sm";

        const removeIcon = document.createElement("i");
        removeIcon.classList.add("fa-solid", "fa-xmark");
        removeBtn.appendChild(removeIcon);

        removeBtn.dataset.index = index;
        removeBtn.addEventListener("click", (e) => {
          const idx = parseInt(e.currentTarget.dataset.index, 10);
          fileStore.splice(idx, 1);
          renderPreviews();
        });
        // -----------------------------

        const contentContainer = document.createElement("div");
        contentContainer.className = "p-3 flex flex-col gap-3";

        const fileInfoDiv = document.createElement("div");
        fileInfoDiv.className = "w-full pr-6";

        const fileNameLabel = document.createElement("span");
        fileNameLabel.className =
          "block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5";
        fileNameLabel.textContent = "ファイル名";

        const fileNameText = document.createElement("p");
        fileNameText.className = "text-xs font-bold text-slate-700 truncate";
        fileNameText.textContent = file.name;
        fileNameText.title = file.name;

        fileInfoDiv.appendChild(fileNameLabel);
        fileInfoDiv.appendChild(fileNameText);

        const fileReader = new FileReader();
        const isImage = file.type.startsWith("image/");
        let previewEl;

        if (isImage) {
          previewEl = document.createElement("img");
          fileReader.onload = () => {
            previewEl.src = fileReader.result;
          };
          fileReader.readAsDataURL(file);
        } else {
          previewEl = document.createElement("div");
          const pdfIcon = document.createElement("i");
          pdfIcon.classList.add(
            "fa-solid",
            "fa-file-pdf",
            "text-red-500",
            "text-4xl",
          );
          previewEl.appendChild(pdfIcon);
          previewEl.style.cursor = "default";
        }

        previewEl.className =
          "preview-thumbnail w-full aspect-square object-cover rounded-lg bg-slate-100 border border-slate-100 cursor-pointer hover:opacity-90 transition-opacity flex items-center justify-center";

        const typeDiv = document.createElement("div");
        typeDiv.className = "w-full";

        const typeLabel = document.createElement("label");
        typeLabel.className =
          "block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1";
        typeLabel.textContent = "画像の種類";

        const typeSelect = document.createElement("select");
        typeSelect.name = "image_types[]";
        typeSelect.className =
          "w-full text-sm font-bold text-slate-700 bg-blue-50 border border-blue-200 rounded-lg py-2 px-2 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none cursor-pointer transition-all appearance-none";

        const options = [
          { val: "RECEIPT", text: "🧾 レシート" },
          { val: "WARRANTY", text: "🛡️ 保証書" },
          { val: "DEVICE_IMAGE", text: "📷 本体写真" },
          { val: "OTHER", text: "📁 その他" },
        ];

        options.forEach((opt) => {
          const optionEl = document.createElement("option");
          optionEl.value = opt.val;
          optionEl.textContent = opt.text;
          typeSelect.appendChild(optionEl);
        });

        typeDiv.appendChild(typeLabel);
        typeDiv.appendChild(typeSelect);

        contentContainer.appendChild(fileInfoDiv);
        contentContainer.appendChild(previewEl);
        contentContainer.appendChild(typeDiv);

        previewWrapper.appendChild(removeBtn);
        previewWrapper.appendChild(contentContainer);

        imagePreviewContainer.appendChild(previewWrapper);
      });
    }

    // --- "追加"ダイアログのライトボックス ---
    const lightbox = addDeviceDialog.querySelector(".js-lightbox");
    if (lightbox) {
      const lightboxImage = lightbox.querySelector(".js-lightbox-image");
      const closeBtn = lightbox.querySelector(".js-close-lightbox-btn");

      const open = (src) => {
        lightboxImage.src = src;
        lightbox.classList.remove("hidden");
        lightbox.classList.add("flex");
      };
      const close = () => {
        lightbox.classList.add("hidden");
        lightbox.classList.remove("flex");
      };

      if (imagePreviewContainer)
        imagePreviewContainer.addEventListener("click", (e) => {
          if (
            e.target.tagName === "IMG" &&
            e.target.classList.contains("preview-thumbnail")
          ) {
            open(e.target.src);
          }
        });

      closeBtn.addEventListener("click", close);
      lightbox.addEventListener("click", (e) => {
        if (e.target === lightbox) close();
      });
    }
  }

  // =========================================================================
  // ユーザーデバイス詳細ダイアログロジック
  // =========================================================================
  const userDeviceDialog = document.getElementById("userDeviceDialog");
  if (userDeviceDialog) {
    // 評価ロック/ロック解除のヘルパー
    const toggleRatingLock = (isLocked) => {
      const container = userDeviceDialog.querySelector(
        "#detail-rating-container",
      );
      if (container) {
        if (isLocked) {
          container.classList.add("rating-readonly");
        } else {
          container.classList.remove("rating-readonly");
        }
      }
    };

    // --- "詳細"ダイアログのライトボックス ---
    const lightbox = userDeviceDialog.querySelector(".js-lightbox");
    if (lightbox) {
      const lightboxImage = lightbox.querySelector(".js-lightbox-image");
      const closeBtn = lightbox.querySelector(".js-close-lightbox-btn");
      const galleryContainer = userDeviceDialog.querySelector(
        "#detail-images-gallery",
      );
      const mainImage = userDeviceDialog.querySelector("#detail-image");

      const open = (src) => {
        lightboxImage.src = src;
        lightbox.classList.remove("hidden");
        lightbox.classList.add("flex");
      };
      const close = () => {
        lightbox.classList.add("hidden");
        lightbox.classList.remove("flex");
      };

      if (galleryContainer)
        galleryContainer.addEventListener("click", (e) => {
          if (e.target.tagName === "IMG") open(e.target.src);
        });

      if (mainImage)
        mainImage.addEventListener("click", (e) => open(e.target.src));

      closeBtn.addEventListener("click", close);
      lightbox.addEventListener("click", (e) => {
        if (e.target === lightbox) close();
      });
    }

    // --- カードクリック時にダイアログを設定 (イベント委譲) ---
    document.addEventListener("click", (e) => {
      const card = e.target.closest(".js-device-card");
      if (!card) return;

      // まず、カードがクリックされたときは表示モードであることを確認
      toggleEditMode(false);
      toggleRatingLock(true); // デフォルトでロック

      const data = card.dataset;

      // 表示モードの要素を設定
      const detailImage = userDeviceDialog.querySelector("#detail-image");
      if (detailImage) {
        detailImage.src = data.image;
        detailImage.alt = data.name;
      }
      
      const detailBrand = userDeviceDialog.querySelector("#detail-brand");
      if (detailBrand) detailBrand.textContent = data.brand;
      
      const detailBrandView = userDeviceDialog.querySelector("#detail-brand-view");
      if (detailBrandView) detailBrandView.textContent = data.brand;
      
      const detailBrandEdit = userDeviceDialog.querySelector("#detail-brand-edit");
      if (detailBrandEdit) detailBrandEdit.textContent = data.brand; // 編集モード用
      
      const detailName = userDeviceDialog.querySelector("#detail-name");
      if (detailName) detailName.textContent = data.name;

      const detailNameView = userDeviceDialog.querySelector("#detail-name-view");
      if (detailNameView) detailNameView.textContent = data.name;
      
      const detailNameEdit = userDeviceDialog.querySelector("#detail-name-edit");
      if (detailNameEdit) detailNameEdit.textContent = data.name; // 編集モード用
      
      const detailPurchaseDate = userDeviceDialog.querySelector("#detail-purchase-date");
      if (detailPurchaseDate) detailPurchaseDate.textContent = data.purchaseDate || "－";
      
      const detailDaysPassed = userDeviceDialog.querySelector("#detail-days-passed");
      if (detailDaysPassed) detailDaysPassed.textContent = data.daysPassed;
      
      const detailStatusText = userDeviceDialog.querySelector("#detail-status-text");
      if (detailStatusText) detailStatusText.textContent = data.status;
      
      const detailNotes = userDeviceDialog.querySelector("#detail-notes");
      if (detailNotes) detailNotes.textContent = data.notes || "－";
      
      const detailWarrantyDate = userDeviceDialog.querySelector("#detail-warranty-date");
      if (detailWarrantyDate) detailWarrantyDate.textContent = data.warrantyEndDate || "－";
      
      const detailReturnDate = userDeviceDialog.querySelector("#detail-return-date");
      if (detailReturnDate) detailReturnDate.textContent = data.returnDueDate || "－";

      const detailLaunchYear = userDeviceDialog.querySelector("#detail-launch-year");
      if (detailLaunchYear) {
        detailLaunchYear.textContent = data.releaseYear && data.releaseYear !== "0000" 
          ? `発売年：${data.releaseYear}年` 
          : "発売年：データなし";
      }

      // 価格情報の表示設定
      const purchasePrice = parseInt(data.purchasePrice, 10);
      const soldPrice = parseInt(data.soldPrice, 10);
      const marketPrice = parseInt(data.marketPrice, 10);
      const lifespanMonths = parseInt(data.expectedLifespanMonths, 10) || 36;

      const detailPurchasePrice = userDeviceDialog.querySelector("#detail-purchase-price");
      if (detailPurchasePrice) {
        detailPurchasePrice.textContent = !isNaN(purchasePrice) ? `¥${purchasePrice.toLocaleString()}` : "－";
      }

      const soldPriceSection = userDeviceDialog.querySelector("#edit-sold-price-section");
      if (soldPriceSection) {
        if (data.status === "売却済") {
          soldPriceSection.classList.remove("hidden");
          const detailSoldPrice = userDeviceDialog.querySelector("#detail-sold-price");
          if (detailSoldPrice) {
            detailSoldPrice.textContent = !isNaN(soldPrice) ? `¥${soldPrice.toLocaleString()}` : "－";
          }
        } else {
          soldPriceSection.classList.add("hidden");
        }
      }

      // チャートの描画
      renderDepreciationChart(
        purchasePrice,
        marketPrice,
        data.purchaseDate,
        lifespanMonths,
      );

      // --- 評価ロジック ---
      const myRating = Math.round(parseFloat(data.myRating) || 0);
      const globalRating = parseFloat(data.globalRating) || 0;
      const globalCount = parseInt(data.globalRatingCount) || 0;

      const detailRatingContainer = userDeviceDialog.querySelector("#detail-rating-container");
      if (detailRatingContainer) {
        detailRatingContainer.dataset.deviceId = data.deviceId;
        detailRatingContainer.dataset.myRating = myRating;

        // ラジオボタンをリセット
        const radios = detailRatingContainer.querySelectorAll('input[type="radio"]');
        radios.forEach((r) => (r.checked = false));

        if (myRating > 0) {
          const target = detailRatingContainer.querySelector(`input[value="${myRating}"]`);
          if (target) target.checked = true;
        }

        if (!detailRatingContainer.dataset.listenerAdded) {
          detailRatingContainer.addEventListener("change", (e) => {
            const selectedRating = e.target.value;
            const deviceId = detailRatingContainer.dataset.deviceId;
            if (!deviceId) return;

            fetch(BASE_URL + "actions/rate_device.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                device_id: deviceId,
                rating: selectedRating,
              }),
            })
              .then((res) => res.json())
              .then((result) => {
                if (result.status === "success") {
                  const globalVal = userDeviceDialog.querySelector("#detail-global-rating");
                  if (globalVal) {
                    const formattedRating = parseFloat(result.new_avg_rating).toFixed(1);
                    globalVal.textContent = `${formattedRating} (${result.new_rating_count})`;
                  }
                  if (card) {
                    card.dataset.myRating = selectedRating;
                    card.dataset.globalRating = result.new_avg_rating;
                    card.dataset.globalRatingCount = result.new_rating_count;
                  }
                } else {
                  alert("評価の保存に失敗しました: " + result.message);
                }
              })
              .catch((err) => {
                console.error("Rating error:", err);
              });
          });
          detailRatingContainer.dataset.listenerAdded = "true";
        }
      }

      const globalVal = userDeviceDialog.querySelector("#detail-global-rating");
      if (globalVal) {
        const formattedRating = globalRating > 0 ? globalRating.toFixed(1) : "-";
        const formattedCount = globalRating > 0 ? ` (${globalCount})` : "";
        globalVal.textContent = `${formattedRating}${formattedCount}`;
      }

      const editDevId = userDeviceDialog.querySelector("#edit-device-id");
      if (editDevId) editDevId.value = data.deviceId;

      const periodLabel = userDeviceDialog.querySelector("#detail-period-label");
      if (periodLabel) {
        if (data.unusableDate && data.unusableDate.trim() !== "") {
          periodLabel.textContent = "総使用期間";
        } else {
          periodLabel.textContent = "経過日数";
        }
      }

      const returnAlertSpan = userDeviceDialog.querySelector("#detail-return-alert");
      if (returnAlertSpan) {
        const returnDaysRemaining = parseInt(data.returnDaysRemaining, 10);
        if (!isNaN(returnDaysRemaining) && returnDaysRemaining >= 0 && returnDaysRemaining <= 3) {
          returnAlertSpan.textContent = "期限間近！";
        } else {
          returnAlertSpan.textContent = "";
        }
      }

      const indicator = userDeviceDialog.querySelector("#detail-status-indicator");
      if (indicator) {
        indicator.className = "w-2.5 h-2.5 rounded-full shadow-sm";
        const vividColors = {
          "使用中": "bg-emerald-500",
          "故障": "bg-rose-500",
          "売却済": "bg-slate-500",
          "保管中": "bg-amber-500"
        };
        const statusKey = data.status ? data.status.trim() : "";
        if (vividColors[statusKey]) {
          indicator.classList.add(vividColors[statusKey]);
        } else {
          indicator.classList.add("bg-slate-400");
        }
      }

      const unusableContainer = userDeviceDialog.querySelector("#detail-unusable-container");
      if (unusableContainer) {
        if (data.unusableReason || data.unusableDate) {
          unusableContainer.classList.remove("hidden");
          const detailUnusableDate = userDeviceDialog.querySelector("#detail-unusable-date");
          if (detailUnusableDate) detailUnusableDate.textContent = data.unusableDate;
          const detailUnusableReason = userDeviceDialog.querySelector("#detail-unusable-reason");
          if (detailUnusableReason) detailUnusableReason.textContent = data.unusableReason;
        } else {
          unusableContainer.classList.add("hidden");
        }
      }

      // --- 統計情報表示 (寿命・故障理由) ---
      const statsContainer = userDeviceDialog.querySelector("#user-device-stats-info");
      const lifespanEl = userDeviceDialog.querySelector("#user-stat-avg-lifespan");
      const commonReasonEl = userDeviceDialog.querySelector("#user-stat-common-reason");
      const lifespanCont = userDeviceDialog.querySelector("#user-stat-lifespan-container");
      const reasonCont = userDeviceDialog.querySelector("#user-stat-reason-container");

      if (statsContainer) {
        const avgLifespan = parseInt(data.avgLifespan);
        const commonReason = data.commonFailureReason;

        if ((avgLifespan && avgLifespan > 0) || commonReason) {
          // 平均寿命の表示
          if (lifespanEl && lifespanCont) {
            if (avgLifespan && avgLifespan > 0) {
              const years = Math.floor(avgLifespan / 365);
              const days = avgLifespan % 365;
              let lifespanStr = "";
              if (years > 0) lifespanStr += `${years}年`;
              if (days > 0 || years === 0) lifespanStr += `${days}日`;
              lifespanEl.textContent = lifespanStr;
              lifespanCont.classList.remove("hidden");
            } else {
              lifespanCont.classList.add("hidden");
            }
          }

          // 故障理由の表示
          if (commonReasonEl && reasonCont) {
            if (commonReason && commonReason !== 'NULL') {
              commonReasonEl.textContent = commonReason;
              reasonCont.classList.remove("hidden");
            } else {
              reasonCont.classList.add("hidden");
            }
          }
          statsContainer.classList.remove("hidden");
        } else {
          statsContainer.classList.add("hidden");
        }
      }

      const userPriceContainer = userDeviceDialog.querySelector("#user-device-market-price");
      const userPriceNew = userDeviceDialog.querySelector("#user-price-new");
      const userPriceUsed = userDeviceDialog.querySelector("#user-price-used");
      const userPriceDate = userDeviceDialog.querySelector("#user-price-date");
      const userYahooLink = userDeviceDialog.querySelector("#user-yahoo-link");
      const userSpecLink = userDeviceDialog.querySelector("#detail-spec-link");
      
      if (userSpecLink) {
        const query = `${data.brand || ""} ${data.name}`.trim();
        userSpecLink.href = `https://www.google.co.jp/search?q=${encodeURIComponent(query + " スペック")}`;
      }

      const fetchUserPrice = (force = false) => {
        const query = `${data.brand || ""} ${data.name}`.trim();
        const deviceId = data.deviceId;
        if (!deviceId || !query) return;

        const nowLocal = new Date();
        const today = `${nowLocal.getFullYear()}-${String(nowLocal.getMonth() + 1).padStart(2, "0")}-${String(nowLocal.getDate()).padStart(2, "0")}`;
        if (!force && data.priceDate === today) {
          if (userPriceNew) userPriceNew.textContent = data.marketPriceNew ? `¥${Number(data.marketPriceNew).toLocaleString()}` : "-";
          if (userPriceUsed) userPriceUsed.textContent = data.marketPrice ? `¥${Number(data.marketPrice).toLocaleString()}` : "-";
          if (userPriceDate) {
            const datePart = data.priceDate ? data.priceDate.split(' ')[0] : "-";
            userPriceDate.textContent = `取得日: ${datePart}`;
          }
          userPriceContainer.classList.remove("hidden");
          return;
        }

        const now = Date.now();
        const currentRefreshBtn = userDeviceDialog.querySelector("#user-refresh-price-btn");
        if (force && currentRefreshBtn && currentRefreshBtn.dataset.lastFetch) {
          const lastFetch = parseInt(currentRefreshBtn.dataset.lastFetch);
          if (now - lastFetch < 60000) {
            const remaining = Math.ceil((60000 - (now - lastFetch)) / 1000);
            alert(`更新は1分間に1回までです。あと ${remaining}秒 待ってください。`);
            return;
          }
        }

        if (force && currentRefreshBtn) {
          const icon = currentRefreshBtn.querySelector("i");
          if (icon) icon.classList.add("fa-spin");
          currentRefreshBtn.disabled = true;
        }

        fetch(`../actions/get_device_price.php?device_id=${deviceId}&query=${encodeURIComponent(query)}${force ? "&force=1" : ""}`)
          .then((res) => res.json())
          .then((pdata) => {
            if (pdata.data) {
              const refreshedBtn = userDeviceDialog.querySelector("#user-refresh-price-btn");
              if (refreshedBtn) refreshedBtn.dataset.lastFetch = Date.now();
              const p = pdata.data;
              const newPriceStr = p.avg_new_price ? `¥${Number(p.avg_new_price).toLocaleString()}` : "-";
              const usedPriceStr = p.avg_used_price ? `¥${Number(p.avg_used_price).toLocaleString()}` : "-";
              const datePart = p.last_checked ? p.last_checked.split(' ')[0] : "-";
              const dateStr = `取得日: ${datePart}`;

              if (userPriceNew) userPriceNew.textContent = newPriceStr;
              if (userPriceUsed) userPriceUsed.textContent = usedPriceStr;
              if (userPriceDate) userPriceDate.textContent = dateStr;

              const entryId = data.id;
              const activeCard = document.querySelector(`.js-device-card[data-id="${entryId}"]`);
              if (activeCard) {
                activeCard.dataset.marketPrice = p.avg_used_price || "";
                activeCard.dataset.marketPriceNew = p.avg_new_price || "";
                activeCard.dataset.priceDate = p.last_checked || "";
                data.priceDate = p.last_checked;
                data.marketPrice = p.avg_used_price;
                data.marketPriceNew = p.avg_new_price;
              }
              if (userYahooLink) userYahooLink.href = `https://shopping.yahoo.co.jp/search?p=${encodeURIComponent(query)}`;
              userPriceContainer.classList.remove("hidden");
            }
          })
          .catch((err) => console.error("Price API error:", err))
          .finally(() => {
            const refreshedBtnAfter = userDeviceDialog.querySelector("#user-refresh-price-btn");
            if (refreshedBtnAfter) {
              const icon = refreshedBtnAfter.querySelector("i");
              if (icon) icon.classList.remove("fa-spin");
              refreshedBtnAfter.disabled = false;
            }
          });
      };

      if (userPriceContainer) {
        userPriceContainer.classList.add("hidden");
        if (userPriceNew) userPriceNew.textContent = "-";
        if (userPriceUsed) userPriceUsed.textContent = "-";
        fetchUserPrice(false);
      }

      const userRefreshBtn = userDeviceDialog.querySelector("#user-refresh-price-btn");
      if (userRefreshBtn) {
        userRefreshBtn.onclick = (e) => {
          e.preventDefault();
          fetchUserPrice(true);
        };
      }

      const editEntryId = userDeviceDialog.querySelector("#edit-entry-id");
      if (editEntryId) editEntryId.value = data.id;
      
      const editPurchaseDate = userDeviceDialog.querySelector("#edit-purchase-date");
      if (editPurchaseDate) editPurchaseDate.value = data.purchaseDate ? data.purchaseDate.replace(/\//g, "-") : "";
      
      const editPurchasePrice = userDeviceDialog.querySelector("#edit-purchase-price");
      if (editPurchasePrice) editPurchasePrice.value = !isNaN(purchasePrice) ? purchasePrice : "";
      
      const editSoldPrice = userDeviceDialog.querySelector("#edit-sold-price");
      if (editSoldPrice) editSoldPrice.value = !isNaN(soldPrice) ? soldPrice : "";

      const editWarrantyDate = userDeviceDialog.querySelector("#edit-warranty-date");
      if (editWarrantyDate) editWarrantyDate.value = data.warrantyEndDate ? data.warrantyEndDate.replace(/\//g, "-") : "";
      
      const editReturnDate = userDeviceDialog.querySelector("#edit-return-date");
      if (editReturnDate) editReturnDate.value = data.returnDueDate ? data.returnDueDate.replace(/\//g, "-") : "";
      
      const editNotes = userDeviceDialog.querySelector("#edit-notes");
      if (editNotes) editNotes.value = data.notes;
      
      const editUnusableDate = userDeviceDialog.querySelector("#edit-unusable-date");
      if (editUnusableDate) editUnusableDate.value = data.unusableDate ? data.unusableDate.replace(/\//g, "-") : "";
      
      const editUnusableReason = userDeviceDialog.querySelector("#edit-unusable-reason");
      if (editUnusableReason) editUnusableReason.value = data.unusableReason || "";

      const statusSelect = userDeviceDialog.querySelector("#edit-status");
      const unusableEditContainer = userDeviceDialog.querySelector("#edit-unusable-container");
      if (statusSelect) {
        statusSelect.innerHTML = "";
        const statuses = ["使用中", "保管中", "故障", "売却済"];
        statuses.forEach((st) => {
          const opt = document.createElement("option");
          opt.value = st;
          opt.textContent = st;
          if (data.status && data.status.trim() === st) opt.selected = true;
          statusSelect.appendChild(opt);
        });
        if (data.status && data.status.trim() !== "使用中") {
          unusableEditContainer.classList.remove("hidden");
        } else {
          unusableEditContainer.classList.add("hidden");
        }
      }

      const visibilityText = userDeviceDialog.querySelector("#detail-visibility-text");
      const visibilityToggle = userDeviceDialog.querySelector("#edit-is-public");
      const isPublicFlag = parseInt(data.isPublic) === 1;
      if (visibilityText) {
        visibilityText.textContent = isPublicFlag ? "全体公開中" : "非公開";
        visibilityText.className = isPublicFlag ? "text-xs font-bold text-green-500" : "text-xs font-bold text-slate-400";
      }
      if (visibilityToggle) visibilityToggle.checked = isPublicFlag;

      editFileStore = [];
      renderEditPreviews();

      const galleryContainer = userDeviceDialog.querySelector("#detail-images-gallery");
      const imagesContainer = userDeviceDialog.querySelector("#detail-images-container");
      if (galleryContainer) {
        galleryContainer.innerHTML = "";
        let images = [];
        try { if (data.images) images = JSON.parse(data.images); } catch (e) { console.error(e); }
        if (images.length > 0) {
          imagesContainer.classList.remove("hidden");
          images.forEach((image) => {
            const isPdf = image.image_path.toLowerCase().endsWith(".pdf");
            const wrapper = document.createElement("div");
            wrapper.className = "relative group/img";
            if (isPdf) {
              const link = document.createElement("a");
              link.href = image.image_path;
              link.target = "_blank";
                            link.className = "w-full aspect-square bg-slate-50 rounded-lg border-2 border-slate-200 hover:border-red-500 transition-all flex flex-col items-center justify-center gap-1 group";
              link.innerHTML = `<i class="fa-solid fa-file-pdf text-red-500 text-2xl group-hover:scale-110 transition-transform"></i><span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">View PDF</span>`;
              wrapper.appendChild(link);
            } else {
              const img = document.createElement("img");
              img.src = image.image_path;
              img.alt = image.image_type;
                            img.className =
                              "w-full aspect-square object-cover rounded-lg border-2 border-slate-200 hover:border-blue-500 transition-colors cursor-pointer";
              wrapper.appendChild(img);
            }
            const typeLabel = document.createElement("span");
            typeLabel.className = "absolute -top-2 -right-2 px-1.5 py-0.5 bg-slate-800 text-white text-[8px] font-black rounded shadow-sm opacity-0 group-hover/img:opacity-100 transition-opacity z-10 pointer-events-none";
            const typeNames = { 'RECEIPT': 'レシート', 'WARRANTY': '保証書', 'DEVICE_IMAGE': '本体写真', 'OTHER': 'その他' };
            typeLabel.textContent = typeNames[image.image_type] || '不明';
            wrapper.appendChild(typeLabel);
            galleryContainer.appendChild(wrapper);
          });
        } else {
          imagesContainer.classList.remove("hidden");
          galleryContainer.innerHTML = '<p class="text-sm text-slate-500">画像なし</p>';
        }
      }

      openDialog(userDeviceDialog);
    });

    // --- 編集/キャンセルボタンロジック ---
    const editBtn = userDeviceDialog.querySelector("#edit-device-btn");
    const cancelBtn = userDeviceDialog.querySelector("#cancel-edit-btn");

    function toggleEditMode(isEdit) {
      userDeviceDialog
        .querySelectorAll(".view-mode")
        .forEach((el) => el.classList.toggle("hidden", isEdit));
      userDeviceDialog
        .querySelectorAll(".edit-mode")
        .forEach((el) => el.classList.toggle("hidden", !isEdit));
    }

    if (editBtn) {
      editBtn.addEventListener("click", () => {
        toggleEditMode(true);
        toggleRatingLock(false);
      });
    }

    if (cancelBtn) {
      cancelBtn.addEventListener("click", () => {
        toggleEditMode(false);
        toggleRatingLock(true);
      });
    }

    // --- 編集ステータス変更ロジック ---
    const editStatusSelect = userDeviceDialog.querySelector("#edit-status");
    const editUnusableContainer = userDeviceDialog.querySelector(
      "#edit-unusable-container",
    );
    const editUnusableDate = userDeviceDialog.querySelector(
      "#edit-unusable-date",
    );
    const editUnusableReason = userDeviceDialog.querySelector(
      "#edit-unusable-reason",
    );
    const editSoldPriceSection = userDeviceDialog.querySelector(
      "#edit-sold-price-section",
    );

    let previousStatus = "";

    if (editStatusSelect && editUnusableContainer) {
      // ダイアログが開いたとき（またはスクリプトが実行されたとき）に初期の前のステータスを設定
      previousStatus = editStatusSelect.value;

      // フォーカス時に前のステータスを更新
      editStatusSelect.addEventListener("focus", function () {
        previousStatus = this.value;
      });

      editStatusSelect.addEventListener("change", (e) => {
        const currentStatus = e.target.value;

        if (currentStatus === "使用中" && previousStatus !== "使用中") {
          if (
            confirm(
              "ステータスを「使用中」に戻すと、終了日と理由が削除されます。よろしいですか？",
            )
          ) {
            // フィールドをクリアしてコンテナを隠す
            if (editUnusableDate) editUnusableDate.value = "";
            if (editUnusableReason) editUnusableReason.value = "";
            editUnusableContainer.classList.add("hidden");
            if (editSoldPriceSection)
              editSoldPriceSection.classList.add("hidden");
            previousStatus = currentStatus;
          } else {
            // 変更を元に戻す
            e.target.value = previousStatus;
          }
        } else {
          if (currentStatus !== "使用中") {
            editUnusableContainer.classList.remove("hidden");
          } else {
            editUnusableContainer.classList.add("hidden");
          }

          if (editSoldPriceSection) {
            if (currentStatus === "売却済") {
              editSoldPriceSection.classList.remove("hidden");
            } else {
              editSoldPriceSection.classList.add("hidden");
            }
          }
          previousStatus = currentStatus;
        }
      });
    }

    // --- 減価償却チャート描画ロジック ---
    let depreciationChartInstance = null;

    function renderDepreciationChart(
      purchasePrice,
      marketPrice,
      purchasedDate,
      lifespanMonths,
    ) {
      const canvas = document.getElementById("depreciationChart");
      if (!canvas) return;
      const ctx = canvas.getContext("2d");

      if (depreciationChartInstance) {
        depreciationChartInstance.destroy();
      }

      if (!purchasePrice || purchasePrice <= 0) {
        // 価格がない場合はチャートを非表示にするかメッセージを出す
        canvas.parentElement.classList.add("hidden");
        return;
      }
      canvas.parentElement.classList.remove("hidden");

      const labels = [];
      const data = [];
      const pDate = new Date(purchasedDate);

      // 6ヶ月ごとの5つのポイント
      for (let i = 0; i <= 24; i += 6) {
        const d = new Date(pDate);
        d.setMonth(d.getMonth() + i);
        labels.push(`${i}ヶ月後`);

        const monthsPassed = i;
        const depreciation = Math.min(0.9, monthsPassed / lifespanMonths);
        data.push(Math.round(purchasePrice * (1 - depreciation)));
      }

      depreciationChartInstance = new Chart(ctx, {
        type: "line",
        data: {
          labels: labels,
          datasets: [
            {
              label: "推定価値 (¥)",
              data: data,
              borderColor: "#0071D3",
              backgroundColor: "rgba(0, 113, 211, 0.1)",
              fill: true,
              pointRadius: 4,
              pointBackgroundColor: "#0071D3",
              tension: 0.4,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              padding: 12,
              titleFont: { size: 14, weight: 'bold' },
              bodyFont: { size: 13 },
              callbacks: {
                label: function (context) {
                  return "推定価値: ¥" + context.parsed.y.toLocaleString();
                },
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.03)',
              },
              ticks: {
                callback: (value) => "¥" + (value / 10000).toFixed(1) + "万",
                font: { size: 11, weight: 'bold' },
                color: '#94a3b8',
              },
            },
            x: {
              grid: {
                display: false
              },
              ticks: { 
                font: { size: 11, weight: 'bold' },
                color: '#94a3b8',
              },
            },
          },
        },
      });
    }

    // --- 編集画像アップロードロジック ---
    const editAddImageBtn = userDeviceDialog.querySelector(
      "#edit-add-image-btn",
    );
    const editImageUploadInput = userDeviceDialog.querySelector(
      "#edit-image-upload-input",
    );
    const editImagePreviewContainer = userDeviceDialog.querySelector(
      "#edit-image-preview-container",
    );
    let editFileStore = [];

    if (editAddImageBtn && editImageUploadInput) {
      editAddImageBtn.addEventListener("click", () =>
        editImageUploadInput.click(),
      );
      editImageUploadInput.addEventListener("change", handleEditFileSelect);
    }

    function handleEditFileSelect(event) {
      const files = Array.from(event.target.files);
      files.forEach((file) => {
        if (
          !editFileStore.some(
            (f) => f.name === file.name && f.size === file.size,
          )
        )
          editFileStore.push(file);
      });
      renderEditPreviews();
      event.target.value = "";
    }

    function renderEditPreviews() {
      if (!editImagePreviewContainer) return;

      while (editImagePreviewContainer.firstChild) {
        editImagePreviewContainer.removeChild(
          editImagePreviewContainer.firstChild,
        );
      }

      if (editFileStore.length > 0) {
        editImagePreviewContainer.classList.remove("hidden");
      } else {
        editImagePreviewContainer.classList.add("hidden");
      }

      editFileStore.forEach((file, index) => {
        const previewWrapper = document.createElement("div");
        previewWrapper.className =
          "flex flex-col relative w-full bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden group hover:shadow-md transition-all";

        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className =
          "absolute top-2 right-2 z-10 w-6 h-6 rounded-full bg-slate-900/60 text-white hover:bg-red-500 flex items-center justify-center text-xs transition-colors backdrop-blur-sm";

        const removeIcon = document.createElement("i");
        removeIcon.classList.add("fa-solid", "fa-xmark");
        removeBtn.appendChild(removeIcon);

        removeBtn.dataset.index = index;
        removeBtn.addEventListener("click", (e) => {
          const idx = parseInt(e.currentTarget.dataset.index, 10);
          editFileStore.splice(idx, 1);
          renderEditPreviews();
        });

        const contentContainer = document.createElement("div");
        contentContainer.className = "p-3 flex flex-col gap-3";

        const fileInfoDiv = document.createElement("div");
        fileInfoDiv.className = "w-full pr-6";

        const fileNameLabel = document.createElement("span");
        fileNameLabel.className =
          "block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5";
        fileNameLabel.textContent = "ファイル名";

        const fileNameText = document.createElement("p");
        fileNameText.className = "text-xs font-bold text-slate-700 truncate";
        fileNameText.textContent = file.name;

        fileInfoDiv.appendChild(fileNameLabel);
        fileInfoDiv.appendChild(fileNameText);

        const fileReader = new FileReader();
        const isImage = file.type.startsWith("image/");
        let previewEl;

        if (isImage) {
          previewEl = document.createElement("img");
          fileReader.onload = () => {
            previewEl.src = fileReader.result;
          };
          fileReader.readAsDataURL(file);
        } else {
          previewEl = document.createElement("div");
          const pdfIcon = document.createElement("i");
          pdfIcon.classList.add(
            "fa-solid",
            "fa-file-pdf",
            "text-red-500",
            "text-4xl",
          );
          previewEl.appendChild(pdfIcon);
          previewEl.style.cursor = "default";
        }

        previewEl.className =
          "preview-thumbnail w-full aspect-square object-cover rounded-lg bg-slate-100 border border-slate-100 cursor-pointer hover:opacity-90 transition-opacity flex items-center justify-center";

        const typeDiv = document.createElement("div");
        typeDiv.className = "w-full";

        const typeLabel = document.createElement("label");
        typeLabel.className =
          "block text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-1";
        typeLabel.textContent = "画像の種類";

        const typeSelect = document.createElement("select");
        typeSelect.name = "image_types[]";
        typeSelect.className =
          "w-full text-sm font-bold text-slate-700 bg-blue-50 border border-blue-200 rounded-lg py-2 px-2 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none cursor-pointer transition-all appearance-none";

        const options = [
          { val: "RECEIPT", text: "🧾 レシート" },
          { val: "WARRANTY", text: "🛡️ 保証書" },
          { val: "DEVICE_IMAGE", text: "📷 本体写真" },
          { val: "OTHER", text: "📁 その他" },
        ];

        options.forEach((opt) => {
          const optionEl = document.createElement("option");
          optionEl.value = opt.val;
          optionEl.textContent = opt.text;
          typeSelect.appendChild(optionEl);
        });

        typeDiv.appendChild(typeLabel);
        typeDiv.appendChild(typeSelect);

        contentContainer.appendChild(fileInfoDiv);
        contentContainer.appendChild(previewEl);
        contentContainer.appendChild(typeDiv);

        previewWrapper.appendChild(removeBtn);
        previewWrapper.appendChild(contentContainer);

        editImagePreviewContainer.appendChild(previewWrapper);
      });
    }

    // --- フォーム送信ロジック ---
    const editDeviceForm = userDeviceDialog.querySelector("#edit-device-form");
    if (editDeviceForm) {
      editDeviceForm.addEventListener("submit", function (e) {
        e.preventDefault();

        // 検証: ステータスがactiveでない場合、使用不可日が設定されているか確認
        const statusSelect = userDeviceDialog.querySelector("#edit-status");
        const unusableDateInput = userDeviceDialog.querySelector(
          "#edit-unusable-date",
        );

        if (
          statusSelect &&
          statusSelect.value !== "使用中" &&
          unusableDateInput &&
          !unusableDateInput.value
        ) {
          alert("終了日を入力してください。");
          unusableDateInput.focus();
          return;
        }

        const formData = new FormData(this);
        // 新しいファイルを追加
        editFileStore.forEach((file) => formData.append(`images[]`, file));

        // 左パネル（ユーザーダイアログ）から評価を追加
        const ratingContainer = userDeviceDialog.querySelector(
          "#detail-rating-container",
        );
        if (ratingContainer) {
          const checkedRating = ratingContainer.querySelector(
            'input[name="detail-rating"]:checked',
          );
          if (checkedRating) {
            formData.append("rating", checkedRating.value);
          }
        }

        fetch(BASE_URL + "actions/update_device.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            alert(data.message);
            if (data.status) {
              userDeviceDialog.close();
              window.location.reload();
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("更新中にエラーが発生しました。");
          });
      });
    }
  }
});
