/**
 * 지원금 수익화 테마 - Custom JavaScript
 * 타뷸라 클릭률 최적화 시스템
 */

(function($) {
    'use strict';

    // ============================================================
    // 전면 광고 관리 (1분 쿨다운)
    // ============================================================
    function showInterstitialAd() {
        const overlay = document.getElementById('interstitial-ad-overlay');
        if (!overlay) return;

        const lastTime = localStorage.getItem('lastInterstitialTime');
        const now = Date.now();
        const oneMinute = 60000; // 1분 = 60,000ms

        // 1분이 지났는지 확인
        if (!lastTime || (now - lastTime) > oneMinute) {
            overlay.style.display = 'flex';
            
            // 5초 카운트다운
            let countdown = 5;
            const countdownEl = document.getElementById('interstitial-countdown');
            const closeBtn = document.querySelector('.interstitial-close');
            
            if (closeBtn) closeBtn.style.pointerEvents = 'none';
            
            const interval = setInterval(() => {
                countdown--;
                if (countdownEl) countdownEl.textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(interval);
                    if (closeBtn) closeBtn.style.pointerEvents = 'auto';
                }
            }, 1000);
            
            localStorage.setItem('lastInterstitialTime', now);
        }
    }

    // 페이지 로드 완료 후 전면 광고 표시
    $(window).on('load', function() {
        setTimeout(showInterstitialAd, 1000);
    });

    // 페이지 전환 시 전면 광고 표시 (WordPress 환경)
    let isFirstLoad = true;
    $(document).on('click', 'a:not([target="_blank"])', function(e) {
        if (isFirstLoad) {
            isFirstLoad = false;
            return;
        }
        
        const href = $(this).attr('href');
        if (href && href.indexOf(window.location.hostname) !== -1) {
            e.preventDefault();
            showInterstitialAd();
            setTimeout(() => {
                window.location.href = href;
            }, 6000); // 광고 표시 후 페이지 이동
        }
    });

    // ============================================================
    // 네이티브 광고 클릭률 극대화 (타뷸라 스타일)
    // ============================================================
    
    // 네이티브 광고에 마우스 호버 효과 강화
    $('.native-ad-wrapper').hover(
        function() {
            $(this).css({
                'transform': 'scale(1.03)',
                'box-shadow': '0 12px 40px rgba(255, 215, 0, 0.5)',
                'border-color': '#FFA500'
            });
        },
        function() {
            $(this).css({
                'transform': 'scale(1)',
                'box-shadow': '0 4px 20px rgba(0, 0, 0, 0.06)',
                'border-color': '#FFD700'
            });
        }
    );

    // 네이티브 광고 클릭 시 시각적 피드백
    $('.native-ad-wrapper').on('click', function() {
        $(this).css({
            'transform': 'scale(0.98)',
            'transition': 'transform 0.1s ease'
        });
        
        setTimeout(() => {
            $(this).css({
                'transform': 'scale(1)',
                'transition': 'transform 0.3s ease'
            });
        }, 100);
    });

    // 네이티브 광고 내 모든 링크 클릭률 최적화
    $('.native-ad-wrapper a, .native-ad-wrapper img').css({
        'cursor': 'pointer',
        'user-select': 'none'
    });

    // ============================================================
    // 앵커 광고 클릭률 최적화
    // ============================================================
    
    // 앵커 광고 펄스 효과
    function pulseAnchorAd() {
        const anchorAd = $('.anchor-ad-wrapper');
        if (anchorAd.length) {
            setInterval(() => {
                anchorAd.animate({
                    'box-shadow': '0 -6px 30px rgba(49, 130, 246, 0.4)'
                }, 500).animate({
                    'box-shadow': '0 -4px 20px rgba(0, 0, 0, 0.15)'
                }, 500);
            }, 3000);
        }
    }

    pulseAnchorAd();

    // 앵커 광고 호버 시 확대
    $('.anchor-ad-wrapper').hover(
        function() {
            $(this).find('.anchor-ad-content').css({
                'transform': 'scale(1.05)',
                'transition': 'transform 0.3s ease'
            });
        },
        function() {
            $(this).find('.anchor-ad-content').css({
                'transform': 'scale(1)'
            });
        }
    );

    // ============================================================
    // 카드 호버 효과 강화 (클릭 유도)
    // ============================================================
    
    $('.info-card').hover(
        function() {
            $(this).find('.info-card-btn').css({
                'animation': 'pulse 0.5s infinite',
                'transform': 'scale(1.05)'
            });
        },
        function() {
            $(this).find('.info-card-btn').css({
                'animation': 'pulse 2s infinite',
                'transform': 'scale(1)'
            });
        }
    );

    // ============================================================
    // 스크롤 기반 광고 가시성 추적
    // ============================================================
    
    function trackAdVisibility() {
        $('.native-ad-wrapper, .anchor-ad-wrapper').each(function() {
            const adElement = $(this);
            const elementTop = adElement.offset().top;
            const elementBottom = elementTop + adElement.outerHeight();
            const viewportTop = $(window).scrollTop();
            const viewportBottom = viewportTop + $(window).height();

            // 광고가 화면에 50% 이상 보이면 강조
            if (elementBottom > viewportTop && elementTop < viewportBottom) {
                const visibleHeight = Math.min(elementBottom, viewportBottom) - Math.max(elementTop, viewportTop);
                const visibilityRatio = visibleHeight / adElement.outerHeight();
                
                if (visibilityRatio > 0.5) {
                    adElement.addClass('ad-visible');
                } else {
                    adElement.removeClass('ad-visible');
                }
            }
        });
    }

    $(window).on('scroll', _.throttle(trackAdVisibility, 100));
    trackAdVisibility(); // 초기 실행

    // ============================================================
    // 모바일 터치 최적화
    // ============================================================
    
    if ('ontouchstart' in window) {
        // 모바일에서 광고 터치 시 즉각 반응
        $('.native-ad-wrapper, .anchor-ad-wrapper, .info-card-btn').on('touchstart', function() {
            $(this).css({
                'transform': 'scale(0.98)',
                'transition': 'transform 0.1s'
            });
        }).on('touchend', function() {
            $(this).css({
                'transform': 'scale(1)',
                'transition': 'transform 0.3s'
            });
        });
    }

    // ============================================================
    // 광고 노출 시간 추적 (Analytics용)
    // ============================================================
    
    let adViewTimes = {};
    
    function startAdViewTracking(adId) {
        if (!adViewTimes[adId]) {
            adViewTimes[adId] = {
                startTime: Date.now(),
                totalTime: 0
            };
        }
    }
    
    function stopAdViewTracking(adId) {
        if (adViewTimes[adId] && adViewTimes[adId].startTime) {
            const viewDuration = Date.now() - adViewTimes[adId].startTime;
            adViewTimes[adId].totalTime += viewDuration;
            adViewTimes[adId].startTime = null;
            
            // 5초 이상 보면 콘솔에 로그 (추후 Analytics 연동 가능)
            if (adViewTimes[adId].totalTime > 5000) {
                console.log(`광고 ${adId} 노출 시간: ${adViewTimes[adId].totalTime}ms`);
            }
        }
    }

    // Intersection Observer로 광고 노출 추적
    if ('IntersectionObserver' in window) {
        const adObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const adId = entry.target.getAttribute('data-ad-id') || 'unknown';
                
                if (entry.isIntersecting) {
                    startAdViewTracking(adId);
                } else {
                    stopAdViewTracking(adId);
                }
            });
        }, {
            threshold: 0.5
        });

        // 모든 광고 요소 관찰
        $('.native-ad-wrapper, .anchor-ad-wrapper').each(function(index) {
            $(this).attr('data-ad-id', 'ad-' + index);
            adObserver.observe(this);
        });
    }

    // ============================================================
    // 히어로 CTA 버튼 클릭률 최적화
    // ============================================================
    
    $('.hero-cta').on('mouseenter', function() {
        $(this).css({
            'animation': 'bounce 0.5s ease-in-out 3',
            'transform': 'scale(1.05)'
        });
    }).on('mouseleave', function() {
        $(this).css({
            'animation': 'bounce 1s ease-in-out infinite',
            'transform': 'scale(1)'
        });
    });

    // ============================================================
    // 이탈 방지 팝업 강화
    // ============================================================
    
    // 사용자가 페이지를 떠나려 할 때 추가 유도
    let exitIntentShown = false;
    
    $(document).on('mouseleave', function(e) {
        if (e.clientY < 10 && !exitIntentShown) {
            const exitPopup = $('#exitPopup');
            if (exitPopup.length) {
                exitPopup.fadeIn(300);
                exitIntentShown = true;
            }
        }
    });

    // ============================================================
    // 페이지 로드 성능 최적화
    // ============================================================
    
    // 광고 스크립트 지연 로드
    function lazyLoadAds() {
        $('iframe[data-src]').each(function() {
            const iframe = $(this);
            const src = iframe.attr('data-src');
            
            if (src && !iframe.attr('src')) {
                iframe.attr('src', src);
                iframe.removeAttr('data-src');
            }
        });
    }

    // 페이지 로드 후 1초 뒤 광고 로드
    setTimeout(lazyLoadAds, 1000);

    // ============================================================
    // 클릭률 향상을 위한 시각적 힌트
    // ============================================================
    
    // 광고 주변에 시각적 표시 추가
    function addVisualCues() {
        $('.native-ad-wrapper').each(function() {
            if (!$(this).find('.visual-cue').length) {
                $(this).prepend('<div class="visual-cue" style="position: absolute; top: 10px; right: 10px; background: #FFD700; color: #000; padding: 4px 8px; border-radius: 8px; font-size: 10px; font-weight: 700; z-index: 1;">인기</div>');
            }
        });
    }

    addVisualCues();

    // ============================================================
    // 전역 클릭 이벤트 최적화
    // ============================================================
    
    // 모든 광고 관련 클릭 이벤트에 시각적 피드백
    $('body').on('click', '.native-ad-wrapper, .anchor-ad-wrapper, .info-card-btn, .hero-cta', function(e) {
        const clickedElement = $(this);
        
        // 클릭 리플 효과
        const ripple = $('<div class="click-ripple"></div>');
        ripple.css({
            position: 'absolute',
            top: e.pageY - clickedElement.offset().top + 'px',
            left: e.pageX - clickedElement.offset().left + 'px',
            width: '10px',
            height: '10px',
            background: 'rgba(255, 255, 255, 0.6)',
            borderRadius: '50%',
            transform: 'scale(0)',
            animation: 'ripple 0.6s ease-out',
            pointerEvents: 'none',
            zIndex: 9999
        });
        
        clickedElement.css('position', 'relative').append(ripple);
        
        setTimeout(() => ripple.remove(), 600);
    });

    // 리플 애니메이션 CSS 추가
    if (!$('#ripple-animation').length) {
        $('<style id="ripple-animation">@keyframes ripple { to { transform: scale(20); opacity: 0; }}</style>').appendTo('head');
    }

    // ============================================================
    // 디버그 모드 (개발용)
    // ============================================================
    
    if (window.location.search.includes('debug=1')) {
        console.log('=== 광고 시스템 디버그 모드 ===');
        console.log('네이티브 광고 수:', $('.native-ad-wrapper').length);
        console.log('앵커 광고:', $('.anchor-ad-wrapper').length > 0 ? '활성' : '비활성');
        console.log('전면 광고:', $('#interstitial-ad-overlay').length > 0 ? '활성' : '비활성');
        console.log('카드 수:', $('.info-card').length);
        
        // 광고 영역 표시
        $('.native-ad-wrapper, .anchor-ad-wrapper').css('border', '3px solid red');
    }

    console.log('✅ 지원금 수익화 테마 JS 로드 완료');

})(jQuery);
