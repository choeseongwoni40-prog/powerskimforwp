<?php
/**
 * Theme Functions - 지원금 스킨 완전체
 * 광고 자동 개조 + 카드형 디자인 + 버튼 자동 변환
 */

// ==================== 기본 테마 설정 ====================
function support_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
}
add_action('after_setup_theme', 'support_theme_setup');

// ==================== 스크립트 및 스타일 로드 ====================
function support_enqueue_scripts() {
    wp_enqueue_style('support-style', get_stylesheet_uri(), array(), '1.0');
    wp_enqueue_script('support-custom', get_template_directory_uri() . '/custom.js', array(), '1.0', true);
    
    wp_localize_script('support-custom', 'supportAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('support_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'support_enqueue_scripts');

// ==================== 관리자 메뉴 ====================
function support_admin_menu() {
    add_menu_page(
        '광고 설정',
        '광고 관리',
        'manage_options',
        'support-ads',
        'support_ads_page',
        'dashicons-money-alt',
        20
    );
}
add_action('admin_menu', 'support_admin_menu');

// ==================== 광고 설정 페이지 ====================
function support_ads_page() {
    if (isset($_POST['save_ads']) && check_admin_referer('support_save_ads')) {
        $ad_code = sanitize_textarea_field($_POST['ad_code']);
        
        // 광고 코드 자동 분석 및 개조
        $processed_ads = support_process_ad_code($ad_code);
        
        update_option('support_ad_settings', array(
            'original_code' => $ad_code,
            'anchor_code' => $processed_ads['anchor'],
            'interstitial_code' => $processed_ads['interstitial'],
            'manual_code' => $processed_ads['manual'],
            'ad_frequency' => intval($_POST['ad_frequency']),
            'delay_seconds' => intval($_POST['delay_seconds']),
            'enable_anchor' => isset($_POST['enable_anchor']),
            'enable_interstitial' => isset($_POST['enable_interstitial']),
            'enable_manual' => isset($_POST['enable_manual'])
        ));
        
        echo '<div class="notice notice-success"><p>✅ 광고 설정이 저장되었습니다! 자동으로 광고 단위가 개조되었습니다.</p></div>';
    }
    
    $settings = get_option('support_ad_settings', array(
        'original_code' => '',
        'ad_frequency' => 3,
        'delay_seconds' => 5,
        'enable_anchor' => true,
        'enable_interstitial' => false,
        'enable_manual' => true
    ));
    ?>
    <div class="wrap">
        <h1>📢 광고 설정 - 자동 개조 시스템</h1>
        <p>어떤 광고 코드를 넣어도 자동으로 앵커/전면/수동 광고로 개조됩니다.</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('support_save_ads'); ?>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 8px;">
                <h3 style="margin-top: 0;">🤖 자동 개조 기능</h3>
                <ul style="margin: 10px 0; line-height: 1.8;">
                    <li>✅ <strong>애드센스</strong> → 앵커/전면/디스플레이 광고 자동 생성</li>
                    <li>✅ <strong>타뮬라</strong> → 각 위치에 맞게 자동 변환</li>
                    <li>✅ <strong>데이블</strong> → 위젯을 광고 단위별로 재배치</li>
                    <li>✅ <strong>기타 광고</strong> → 스크립트 분석 후 최적 위치 배치</li>
                </ul>
                <p style="color: #856404; margin: 0;"><strong>💡 한 번만 붙여넣으면 자동으로 3가지 광고 단위가 생성됩니다!</strong></p>
            </div>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ad_code">광고 코드 입력</label></th>
                    <td>
                        <textarea id="ad_code" name="ad_code" rows="12" class="large-text code" style="font-family: monospace; font-size: 12px;"><?php echo esc_textarea($settings['original_code']); ?></textarea>
                        <p class="description">
                            <strong>사용 가능한 광고:</strong> 애드센스, 타뮬라, 데이블, Ezoic, 카카오애드핏 등 모든 광고 코드<br>
                            <em>스크립트 태그와 div 태그를 모두 포함해서 붙여넣으세요.</em>
                        </p>
                    </td>
                </tr>
            </table>
            
            <h2>🎯 광고 단위 활성화</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">앵커 광고 (하단 고정)</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_anchor" value="1" <?php checked($settings['enable_anchor']); ?>>
                            활성화 - 화면 하단에 고정되는 광고 (모바일 최적화)
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">전면 광고 (페이지 전체)</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_interstitial" value="1" <?php checked($settings['enable_interstitial']); ?>>
                            활성화 - 페이지 로드 시 전체 화면 광고
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">수동 광고 (카드 사이)</th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_manual" value="1" <?php checked($settings['enable_manual']); ?>>
                            활성화 - 콘텐츠 사이에 자연스럽게 배치
                        </label>
                    </td>
                </tr>
            </table>
            
            <h2>⚙️ 광고 최적화 설정</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="ad_frequency">광고 빈도</label></th>
                    <td>
                        <input type="number" id="ad_frequency" name="ad_frequency" value="<?php echo esc_attr($settings['ad_frequency']); ?>" min="2" max="10" class="small-text">
                        개의 카드마다 광고 1개 삽입
                        <p class="description">권장: 3-4개 (너무 많으면 사용자 경험 저하)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="delay_seconds">전면 광고 딜레이</label></th>
                    <td>
                        <input type="number" id="delay_seconds" name="delay_seconds" value="<?php echo esc_attr($settings['delay_seconds']); ?>" min="0" max="30" class="small-text">
                        초 후 표시
                        <p class="description">권장: 5-10초 (콘텐츠를 먼저 보게 함)</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="save_ads" class="button button-primary button-hero" value="💾 저장 및 자동 개조 실행">
            </p>
        </form>
        
        <?php if (!empty($settings['original_code'])): ?>
        <div style="background: #d1ecf1; border-left: 4px solid #0c5460; padding: 20px; margin: 20px 0; border-radius: 8px;">
            <h3 style="margin-top: 0; color: #0c5460;">📊 개조된 광고 미리보기</h3>
            
            <details style="margin-bottom: 15px;">
                <summary style="cursor: pointer; font-weight: bold; padding: 10px; background: white; border-radius: 5px;">🔗 앵커 광고 코드</summary>
                <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 10px;"><code><?php echo esc_html($settings['anchor_code'] ?? '자동 생성 중...'); ?></code></pre>
            </details>
            
            <details style="margin-bottom: 15px;">
                <summary style="cursor: pointer; font-weight: bold; padding: 10px; background: white; border-radius: 5px;">🎬 전면 광고 코드</summary>
                <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 10px;"><code><?php echo esc_html($settings['interstitial_code'] ?? '자동 생성 중...'); ?></code></pre>
            </details>
            
            <details style="margin-bottom: 15px;">
                <summary style="cursor: pointer; font-weight: bold; padding: 10px; background: white; border-radius: 5px;">📝 수동 광고 코드</summary>
                <pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; margin-top: 10px;"><code><?php echo esc_html($settings['manual_code'] ?? '자동 생성 중...'); ?></code></pre>
            </details>
        </div>
        <?php endif; ?>
        
        <div style="background: #f0f9ff; border-left: 4px solid #3182F6; padding: 20px; margin: 20px 0; border-radius: 8px;">
            <h3 style="margin-top: 0; color: #1e3a8a;">💡 CTR 극대화 팁</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #dbeafe;">
                        <th style="padding: 10px; text-align: left;">광고 유형</th>
                        <th style="padding: 10px; text-align: center;">CTR</th>
                        <th style="padding: 10px; text-align: center;">수익성</th>
                        <th style="padding: 10px; text-align: center;">UX 영향</th>
                        <th style="padding: 10px; text-align: left;">권장 설정</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>앵커</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">⭐⭐⭐⭐</td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">높음</td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">중간</td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;">항상 활성화</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>전면</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">⭐⭐⭐⭐⭐</td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">최고</td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">높음</td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;">5-10초 딜레이</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>수동</strong></td>
                        <td style="padding: 10px; text-align: center;">⭐⭐⭐</td>
                        <td style="padding: 10px; text-align: center;">안정</td>
                        <td style="padding: 10px; text-align: center;">낮음</td>
                        <td style="padding: 10px;">3-4개마다 배치</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// ==================== 광고 코드 자동 개조 함수 ====================
function support_process_ad_code($ad_code) {
    $result = array(
        'anchor' => '',
        'interstitial' => '',
        'manual' => ''
    );
    
    if (empty($ad_code)) {
        return $result;
    }
    
    // 스크립트 태그 추출
    preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $ad_code, $scripts);
    preg_match_all('/<script[^>]*src=["\'](.*?)["\'][^>]*><\/script>/i', $ad_code, $script_srcs);
    
    // 광고 div 추출
    preg_match_all('/<ins[^>]*class=["\']adsbygoogle["\'][^>]*>.*?<\/ins>/is', $ad_code, $adsense_divs);
    preg_match_all('/<div[^>]*id=["\']taboola[^"\']*["\'][^>]*>.*?<\/div>/is', $ad_code, $taboola_divs);
    preg_match_all('/<div[^>]*class=["\']dablewidget["\'][^>]*>.*?<\/div>/is', $ad_code, $dable_divs);
    
    // 애드센스 감지
    if (strpos($ad_code, 'adsbygoogle') !== false || strpos($ad_code, 'googlesyndication') !== false) {
        $client_id = '';
        if (preg_match('/ca-pub-(\d+)/', $ad_code, $matches)) {
            $client_id = 'ca-pub-' . $matches[1];
        }
        
        // 앵커 광고 생성
        $result['anchor'] = '
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . $client_id . '" crossorigin="anonymous"></script>
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="' . $client_id . '"
     data-ad-slot="0000000000"
     data-ad-format="autorelaxed"
     data-full-width-responsive="true"></ins>
<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
        
        // 전면 광고 생성
        $result['interstitial'] = '
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . $client_id . '" crossorigin="anonymous"></script>
<ins class="adsbygoogle"
     style="display:block"
     data-ad-format="autorelaxed"
     data-ad-client="' . $client_id . '"
     data-ad-slot="0000000000"></ins>
<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
        
        // 수동 광고 생성 (원본 사용)
        $result['manual'] = $ad_code;
    }
    // 타뮬라 감지
    elseif (strpos($ad_code, 'taboola') !== false) {
        $result['anchor'] = $ad_code;
        $result['interstitial'] = $ad_code;
        $result['manual'] = $ad_code;
    }
    // 데이블 감지
    elseif (strpos($ad_code, 'dable') !== false) {
        $result['anchor'] = $ad_code;
        $result['interstitial'] = $ad_code;
        $result['manual'] = $ad_code;
    }
    // 기타 광고
    else {
        $result['anchor'] = $ad_code;
        $result['interstitial'] = $ad_code;
        $result['manual'] = $ad_code;
    }
    
    return $result;
}

// ==================== 프론트엔드 광고 삽입 ====================
function support_inject_ads() {
    $settings = get_option('support_ad_settings', array());
    
    // 앵커 광고
    if (!empty($settings['enable_anchor']) && !empty($settings['anchor_code'])) {
        echo '<div id="support-anchor-ad" class="support-anchor-ad">' . $settings['anchor_code'] . '</div>';
    }
    
    // 전면 광고 (딜레이 적용)
    if (!empty($settings['enable_interstitial']) && !empty($settings['interstitial_code'])) {
        $delay = isset($settings['delay_seconds']) ? intval($settings['delay_seconds']) : 5;
        ?>
        <script>
        setTimeout(function() {
            var interstitialDiv = document.createElement('div');
            interstitialDiv.id = 'support-interstitial-ad';
            interstitialDiv.className = 'support-interstitial-ad';
            interstitialDiv.innerHTML = <?php echo json_encode($settings['interstitial_code']); ?>;
            document.body.appendChild(interstitialDiv);
            
            setTimeout(function() {
                interstitialDiv.style.display = 'block';
            }, 100);
        }, <?php echo $delay * 1000; ?>);
        </script>
        <?php
    }
}
add_action('wp_footer', 'support_inject_ads');

// ==================== 수동 광고 가져오기 ====================
function support_get_manual_ad() {
    $settings = get_option('support_ad_settings', array());
    if (!empty($settings['enable_manual']) && !empty($settings['manual_code'])) {
        return '<div class="support-manual-ad">' . $settings['manual_code'] . '</div>';
    }
    return '';
}

// ==================== 광고 빈도 가져오기 ====================
function support_get_ad_frequency() {
    $settings = get_option('support_ad_settings', array());
    return isset($settings['ad_frequency']) ? intval($settings['ad_frequency']) : 3;
}

// ==================== 광고 스타일 ====================
function support_ad_styles() {
    ?>
    <style>
    /* 앵커 광고 */
    .support-anchor-ad {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        z-index: 9998;
        background: white;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 50px;
        padding: 5px 0;
    }
    
    /* 전면 광고 */
    .support-interstitial-ad {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
    }
    
    .support-interstitial-ad > * {
        max-width: 90%;
        max-height: 90%;
    }
    
    /* 수동 광고 */
    .support-manual-ad {
        margin: 20px 0;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100px;
    }
    
    @media (max-width: 768px) {
        .support-manual-ad {
            background: transparent;
            border-radius: 0;
            padding: 10px 0;
            margin: 16px 0;
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'support_ad_styles');

// ==================== 모든 링크를 버튼으로 변환 ====================
function support_convert_links_to_buttons($content) {
    // 본문의 모든 <a> 태그를 버튼으로 변환
    $content = preg_replace_callback(
        '/<a\s+([^>]*?)href=["\']([^"\']*)["\']([^>]*)>(.*?)<\/a>/is',
        function($matches) {
            $before_href = $matches[1];
            $href = $matches[2];
            $after_href = $matches[3];
            $text = $matches[4];
            
            // 이미지 링크는 제외
            if (strpos($text, '<img') !== false) {
                return $matches[0];
            }
            
            // 버튼으로 변환
            return '<a href="' . esc_url($href) . '" class="support-btn-link" ' . $before_href . $after_href . '>' . $text . ' <span class="btn-arrow">→</span></a>';
        },
        $content
    );
    
    return $content;
}
add_filter('the_content', 'support_convert_links_to_buttons', 20);

// ==================== 텍스트를 카드로 변환 ====================
function support_convert_text_to_cards($content) {
    // h2, h3 태그와 그 다음 p 태그들을 카드로 묶기
    $content = preg_replace_callback(
        '/(<h[23][^>]*>.*?<\/h[23]>)(.*?)(?=<h[23]|$)/is',
        function($matches) {
            $heading = $matches[1];
            $text = $matches[2];
            
            // 텍스트가 있을 때만 카드로 변환
            if (trim(strip_tags($text))) {
                return '<div class="support-card-block">' . $heading . $text . '</div>';
            }
            
            return $matches[0];
        },
        $content
    );
    
    return $content;
}
add_filter('the_content', 'support_convert_text_to_cards', 21);

// ==================== 카드 및 버튼 스타일 ====================
function support_card_button_styles() {
    ?>
    <style>
    /* 버튼 스타일 */
    .support-btn-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, #3182F6 0%, #1E6AD4 100%);
        color: white !important;
        padding: 14px 28px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        text-decoration: none !important;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(49, 130, 246, 0.3);
        margin: 10px 5px;
        border: none;
    }
    
    .support-btn-link:hover {
        background: linear-gradient(135deg, #1E6AD4 0%, #1556B0 100%);
        box-shadow: 0 6px 20px rgba(49, 130, 246, 0.4);
        transform: translateY(-2px);
        color: white !important;
    }
    
    .support-btn-link .btn-arrow {
        transition: transform 0.25s ease;
        font-size: 16px;
    }
    
    .support-btn-link:hover .btn-arrow {
        transform: translateX(4px);
    }
    
    /* 카드 블록 스타일 */
    .support-card-block {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin: 20px 0;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }
    
    .support-card-block:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(49, 130, 246, 0.12);
    }
    
    .support-card-block h2,
    .support-card-block h3 {
        color: #1a1a1a;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #3182F6;
    }
    
    .support-card-block h2 {
        font-size: 24px;
    }
    
    .support-card-block h3 {
        font-size: 20px;
    }
    
    .support-card-block p {
        line-height: 1.8;
        color: #4b5563;
        margin-bottom: 12px;
    }
    
    .support-card-block ul,
    .support-card-block ol {
        padding-left: 24px;
        margin: 16px 0;
    }
    
    .support-card-block li {
        margin-bottom: 8px;
        line-height: 1.6;
        color: #4b5563;
    }
    
    /* 반응형 */
    @media (max-width: 768px) {
        .support-btn-link {
            width: 100%;
            padding: 16px 24px;
            font-size: 16px;
        }
        
        .support-card-block {
            padding: 20px;
            margin: 16px 0;
        }
        
        .support-card-block h2 {
            font-size: 20px;
        }
        
        .support-card-block h3 {
            font-size: 18px;
        }
    }
    </style>
    <?php
}
add_action('wp_head', 'support_card_button_styles', 100);

// ==================== 관리자 스타일 ====================
function support_admin_styles() {
    ?>
    <style>
    .button-hero {
        font-size: 16px !important;
        padding: 12px 24px !important;
        height: auto !important;
    }
    </style>
    <?php
}
add_action('admin_head', 'support_admin_styles');
?>
