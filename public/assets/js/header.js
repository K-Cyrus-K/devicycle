document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('main-header');
    const toggleOpen = document.getElementById('toggleOpen');
    const toggleClose = document.getElementById('toggleClose');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

    // --- Scroll Effect ---
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            header.classList.add('header-scrolled');
        } else {
            header.classList.remove('header-scrolled');
        }
    });

    // --- Mobile Menu Logic ---
    const openMenu = () => {
        if (!mobileMenu || !mobileMenuOverlay) return;
        
        // 1. まず hidden を外す
        mobileMenuOverlay.classList.remove('hidden');
        
        // 2. ブラウザの再描画を待ってからアニメーション用クラスを付与
        setTimeout(() => {
            mobileMenuOverlay.classList.add('opacity-100');
            mobileMenu.classList.add('menu-open');
        }, 10);
        
        document.body.style.overflow = 'hidden';
    };

    const closeMenu = () => {
        if (!mobileMenu || !mobileMenuOverlay) return;

        // アニメーションクラスを外す
        mobileMenuOverlay.classList.remove('opacity-100');
        mobileMenu.classList.remove('menu-open');
        
        // アニメーション完了後に非表示にする
        setTimeout(() => {
            mobileMenuOverlay.classList.add('hidden');
        }, 400);
        
        document.body.style.overflow = 'auto';
    };

    if (toggleOpen) toggleOpen.addEventListener('click', openMenu);
    if (toggleClose) toggleClose.addEventListener('click', closeMenu);
    if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMenu);

    // --- Notification Logic ---
    const bellBtn = document.getElementById('notification-bell');
    const dropdown = document.getElementById('notification-dropdown');
    const stopBtn = document.getElementById('stop-session-alert');
    const badge = document.getElementById('notification-badge');

    // 初期チェック: セッションで通知がオフにされているか
    if (sessionStorage.getItem('devicycle_disable_alerts') === 'true') {
        if (badge) badge.classList.add('hidden');
    }

    if (bellBtn && dropdown) {
        bellBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        // ドロップダウンの外をクリックしたら閉じる
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && e.target !== bellBtn) {
                dropdown.classList.add('hidden');
            }
        });
    }

    if (stopBtn) {
        stopBtn.addEventListener('click', () => {
            if (confirm('ブラウザを閉じるまで、通知バッジを非表示にしますか？')) {
                sessionStorage.setItem('devicycle_disable_alerts', 'true');
                if (badge) badge.classList.add('hidden');
                if (dropdown) dropdown.classList.add('hidden');
                // マイページ側のアラートセクションも隠すためのイベント
                window.dispatchEvent(new CustomEvent('devicycle_alerts_changed'));
            }
        });
    }

    // 外部からの通知設定変更を監視
    window.addEventListener('devicycle_alerts_changed', () => {
        const isDisabled = sessionStorage.getItem('devicycle_disable_alerts') === 'true';
        if (badge) {
            if (isDisabled) {
                badge.classList.add('hidden');
            } else {
                badge.classList.remove('hidden');
            }
        }
    });

    // Close menu on link click
    const mobileLinks = mobileMenu ? mobileMenu.querySelectorAll('a') : [];
    mobileLinks.forEach(link => {
        link.addEventListener('click', closeMenu);
    });
});
