</div><!-- .site-content -->

</div><!-- .main-wrapper -->

<!-- 푸터 -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-left">
            <div class="footer-brand"><?php echo esc_html(get_option('revenue_site_title', get_bloginfo('name'))); ?></div>
            <ul class="footer-info">
                <li><i>📍</i> 사업자 주소: <?php echo esc_html(get_option('revenue_business_address', '')); ?></li>
                <li><i>🏢</i> 사업자 번호: <?php echo esc_html(get_option('revenue_business_number', '')); ?></li>
            </ul>
        </div>
        <div class="footer-right">
            <p>제작자 : 아로스</p>
            <p>홈페이지 : <a href="https://aros100.com" target="_blank">바로가기</a></p>
            <p class="footer-copyright">Copyrights © 2020 All Rights Reserved by (주)아백</p>
        </div>
    </div>
</footer>

<!-- 앵커 광고 (하단 고정) -->
<?php 
$anchor_enabled = get_option('revenue_anchor_enabled', '1');
$anchor_ad = get_option('revenue_anchor_ad', '');
if ($anchor_enabled === '1' && !empty($anchor_ad)): 
?>
<div id="anchor-ad-container" class="anchor-ad-wrapper">
    <button class="anchor-ad-close" onclick="closeAnchorAd()">×</button>
    <div class="anchor-ad-content">
        <?php echo $anchor_ad; ?>
    </div>
</div>
<?php endif; ?>

<!-- 전면 광고 -->
<?php 
$interstitial_enabled = get_option('revenue_interstitial_enabled', '1');
$interstitial_ad = get_option('revenue_interstitial_ad', '');
if ($interstitial_enabled === '1' && !empty($interstitial_ad)): 
?>
<div id="interstitial-ad-overlay" class="interstitial-overlay" style="display: none;">
    <div class="interstitial-content">
        <button class="interstitial-close" onclick="closeInterstitialAd()">
            <span>광고 건너뛰기</span>
            <span id="interstitial-countdown">5</span>
        </button>
        <div class="interstitial-ad">
            <?php echo $interstitial_ad; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 이탈 방지 팝업 -->
<div class="exit-popup-overlay" id="exitPopup">
    <div class="exit-popup">
        <div class="exit-popup-title">🎁 잠깐! 놓치신 혜택이 있어요</div>
        <div class="exit-popup-desc">
            지금 확인 안 하면<br>
            <strong>최대 300만원</strong> 지원금을 못 받을 수 있어요!
        </div>
        <button class="exit-popup-btn" onclick="closePopupAndScroll()">
            내 지원금 확인하기 →
        </button>
        <button class="exit-popup-close" onclick="closePopupNotNow()">
            다음에 할게요
        </button>
    </div>
</div>

<?php wp_footer(); ?>

<script>
// 앵커 광고 닫기
function closeAnchorAd() {
    document.getElementById('anchor-ad-container').style.display = 'none';
    sessionStorage.setItem('anchorAdClosed', 'true');
}

// 페이지 로드 시 앵커 광고 상태 확인
if (sessionStorage.getItem('anchorAdClosed') === 'true') {
    const anchorAd = document.getElementById('anchor-ad-container');
    if (anchorAd) anchorAd.style.display = 'none';
}

// 전면 광고 닫기
function closeInterstitialAd() {
    document.getElementById('interstitial-ad-overlay').style.display = 'none';
    localStorage.setItem('lastInterstitialTime', Date.now());
}

// 이탈 방지 팝업
var popupShown = sessionStorage.getItem('exitPopupShown');
var closeCount = parseInt(sessionStorage.getItem('exitPopupCloseCount')) || 0;
var scrollTriggered = false;

window.addEventListener('load', function() {
    // PC: 마우스 이탈
    document.addEventListener('mouseout', function(e) {
        if (e.clientY < 0 && !popupShown && closeCount < 2) {
            showPopup();
        }
    });
    
    // 뒤로가기 감지
    history.pushState(null, '', location.href);
    window.addEventListener('popstate', function() {
        if (closeCount < 2) {
            showPopup();
        }
        history.pushState(null, '', location.href);
    });
    
    // 모바일: 60% 스크롤
    window.addEventListener('scroll', function() {
        var h = document.body.scrollHeight - window.innerHeight;
        var percent = (window.scrollY / h) * 100;
        
        if (percent > 60 && !popupShown && !scrollTriggered && closeCount < 2) {
            showPopup();
            scrollTriggered = true;
        }
    });
});

function showPopup() {
    document.getElementById('exitPopup').style.display = 'flex';
}

function closePopupAndScroll() {
    document.getElementById('exitPopup').style.display = 'none';
    var hero = document.querySelector('.hero-section');
    if (hero) {
        hero.scrollIntoView({ behavior: 'smooth' });
    }
}

function closePopupNotNow() {
    document.getElementById('exitPopup').style.display = 'none';
    popupShown = true;
    closeCount++;
    sessionStorage.setItem('exitPopupShown', 'true');
    sessionStorage.setItem('exitPopupCloseCount', closeCount);
}
</script>

</body>
</html>
