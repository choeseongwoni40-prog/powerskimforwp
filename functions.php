<?php
/**
 * 지원금 수익화 테마 - Functions
 * 워드프레스 최적화 수익화 시스템
 */

// 보안 체크
if (!defined('ABSPATH')) exit;

// 테마 기본 설정
function revenue_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    register_nav_menus(array(
        'primary' => '메인 메뉴',
        'tabs' => '탭 메뉴'
    ));
}
add_action('after_setup_theme', 'revenue_theme_setup');

// CSS & JS 로드
function revenue_theme_scripts() {
    wp_enqueue_style('revenue-style', get_stylesheet_uri(), array(), '1.0.0');
    wp_enqueue_script('revenue-custom', get_template_directory_uri() . '/custom.js', array('jquery'), '1.0.0', true);
    
    // AJAX 설정
    wp_localize_script('revenue-custom', 'revenueAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('revenue_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'revenue_theme_scripts');

// ============================================================
// 카드 커스텀 포스트 타입
// ============================================================
function create_card_post_type() {
    register_post_type('revenue_card', array(
        'labels' => array(
            'name' => '카드',
            'singular_name' => '카드',
            'add_new' => '새 카드 추가',
            'add_new_item' => '새 카드 추가',
            'edit_item' => '카드 수정',
            'view_item' => '카드 보기'
        ),
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-id-alt',
        'supports' => array('title', 'editor', 'custom-fields'),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'card')
    ));
}
add_action('init', 'create_card_post_type');

// 카드 메타박스
function add_card_meta_boxes() {
    add_meta_box(
        'card_details',
        '카드 상세 정보',
        'card_meta_box_callback',
        'revenue_card',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_card_meta_boxes');

function card_meta_box_callback($post) {
    wp_nonce_field('save_card_meta', 'card_meta_nonce');
    
    $amount = get_post_meta($post->ID, '_card_amount', true);
    $amount_sub = get_post_meta($post->ID, '_card_amount_sub', true);
    $description = get_post_meta($post->ID, '_card_description', true);
    $target = get_post_meta($post->ID, '_card_target', true);
    $period = get_post_meta($post->ID, '_card_period', true);
    $link_url = get_post_meta($post->ID, '_card_link_url', true);
    $is_featured = get_post_meta($post->ID, '_card_featured', true);
    $order = get_post_meta($post->ID, '_card_order', true) ?: 0;
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="card_amount">금액/혜택 강조</label></th>
            <td><input type="text" id="card_amount" name="card_amount" value="<?php echo esc_attr($amount); ?>" class="regular-text" placeholder="예: 최대 4.5% 금리"></td>
        </tr>
        <tr>
            <th><label for="card_amount_sub">부가 설명</label></th>
            <td><input type="text" id="card_amount_sub" name="card_amount_sub" value="<?php echo esc_attr($amount_sub); ?>" class="regular-text" placeholder="예: 비과세 + 대출 우대"></td>
        </tr>
        <tr>
            <th><label for="card_description">한 줄 설명</label></th>
            <td><textarea id="card_description" name="card_description" rows="2" class="large-text"><?php echo esc_textarea($description); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="card_target">지원대상 (20자 이내)</label></th>
            <td><input type="text" id="card_target" name="card_target" value="<?php echo esc_attr($target); ?>" class="regular-text" maxlength="20" placeholder="예: 만 19~34세 청년"></td>
        </tr>
        <tr>
            <th><label for="card_period">신청시기</label></th>
            <td><input type="text" id="card_period" name="card_period" value="<?php echo esc_attr($period); ?>" class="regular-text" placeholder="예: 상시, 매년 5월"></td>
        </tr>
        <tr>
            <th><label for="card_link_url">연결 URL</label></th>
            <td><input type="url" id="card_link_url" name="card_link_url" value="<?php echo esc_url($link_url); ?>" class="regular-text" placeholder="https://"></td>
        </tr>
        <tr>
            <th><label for="card_order">정렬 순서</label></th>
            <td><input type="number" id="card_order" name="card_order" value="<?php echo esc_attr($order); ?>" class="small-text" min="0"></td>
        </tr>
        <tr>
            <th><label for="card_featured">인기 카드</label></th>
            <td><input type="checkbox" id="card_featured" name="card_featured" value="1" <?php checked($is_featured, '1'); ?>></td>
        </tr>
    </table>
    <?php
}

// 카드 메타 저장
function save_card_meta($post_id) {
    if (!isset($_POST['card_meta_nonce']) || !wp_verify_nonce($_POST['card_meta_nonce'], 'save_card_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    $fields = array('card_amount', 'card_amount_sub', 'card_description', 'card_target', 'card_period', 'card_link_url', 'card_order');
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    update_post_meta($post_id, '_card_featured', isset($_POST['card_featured']) ? '1' : '0');
}
add_action('save_post_revenue_card', 'save_card_meta');

// 카드 순서 컬럼 추가
function add_card_order_column($columns) {
    $columns['card_order'] = '순서';
    return $columns;
}
add_filter('manage_revenue_card_posts_columns', 'add_card_order_column');

function show_card_order_column($column, $post_id) {
    if ($column == 'card_order') {
        echo get_post_meta($post_id, '_card_order', true) ?: '0';
    }
}
add_action('manage_revenue_card_posts_custom_column', 'show_card_order_column', 10, 2);

// ============================================================
// 광고 관리 시스템
// ============================================================
function revenue_ads_settings_page() {
    add_menu_page(
        '광고 관리',
        '광고 관리',
        'manage_options',
        'revenue-ads',
        'revenue_ads_page_html',
        'dashicons-megaphone',
        30
    );
}
add_action('admin_menu', 'revenue_ads_settings_page');

function revenue_ads_page_html() {
    if (!current_user_can('manage_options')) return;
    
    // 설정 저장
    if (isset($_POST['revenue_ads_submit'])) {
        check_admin_referer('revenue_ads_save');
        
        update_option('revenue_interstitial_ad', wp_kses_post($_POST['interstitial_ad']));
        update_option('revenue_anchor_ad', wp_kses_post($_POST['anchor_ad']));
        update_option('revenue_native_ad', wp_kses_post($_POST['native_ad']));
        update_option('revenue_interstitial_enabled', isset($_POST['interstitial_enabled']) ? '1' : '0');
        update_option('revenue_anchor_enabled', isset($_POST['anchor_enabled']) ? '1' : '0');
        
        echo '<div class="notice notice-success"><p>광고 설정이 저장되었습니다.</p></div>';
    }
    
    $interstitial_ad = get_option('revenue_interstitial_ad', '');
    $anchor_ad = get_option('revenue_anchor_ad', '');
    $native_ad = get_option('revenue_native_ad', '');
    $interstitial_enabled = get_option('revenue_interstitial_enabled', '1');
    $anchor_enabled = get_option('revenue_anchor_enabled', '1');
    
    ?>
    <div class="wrap">
        <h1>💰 광고 관리 시스템</h1>
        <p>피소니의 법칙을 적용한 최적 광고 배치 시스템</p>
        
        <form method="post" action="">
            <?php wp_nonce_field('revenue_ads_save'); ?>
            
            <h2>🎯 전면 광고 (Interstitial)</h2>
            <p>페이지 전환 시 표시 | 1분 간격 | 애드센스 형태 + 타뷸라 클릭률</p>
            <label>
                <input type="checkbox" name="interstitial_enabled" value="1" <?php checked($interstitial_enabled, '1'); ?>>
                전면 광고 활성화
            </label>
            <textarea name="interstitial_ad" rows="8" class="large-text code"><?php echo esc_textarea($interstitial_ad); ?></textarea>
            
            <h2>⚓ 앵커 광고 (Anchor)</h2>
            <p>하단 고정 광고 | 애드센스 형태 + 타뷸라 클릭률</p>
            <label>
                <input type="checkbox" name="anchor_enabled" value="1" <?php checked($anchor_enabled, '1'); ?>>
                앵커 광고 활성화
            </label>
            <textarea name="anchor_ad" rows="8" class="large-text code"><?php echo esc_textarea($anchor_ad); ?></textarea>
            
            <h2>📰 네이티브 광고 (Native - 수동 배치)</h2>
            <p>카드 사이 자동 삽입 | 타뷸라 스타일 + 극대화 클릭률</p>
            <textarea name="native_ad" rows="8" class="large-text code"><?php echo esc_textarea($native_ad); ?></textarea>
            
            <p class="submit">
                <input type="submit" name="revenue_ads_submit" class="button button-primary" value="광고 설정 저장">
            </p>
        </form>
        
        <div class="card" style="margin-top: 20px; max-width: 800px;">
            <h3>📊 피소니 법칙 광고 배치</h3>
            <ul>
                <li><strong>1번째 카드 전</strong>: 네이티브 광고 (즉시 시선 집중)</li>
                <li><strong>4번째 카드 전</strong>: 네이티브 광고 (스크롤 중간점)</li>
                <li><strong>7번째 카드 전</strong>: 네이티브 광고 (이탈 직전 포착)</li>
                <li><strong>전면 광고</strong>: 페이지 전환 + 1분 쿨다운</li>
                <li><strong>앵커 광고</strong>: 하단 고정 (항시 노출)</li>
            </ul>
            <p><em>이 배치는 수익을 극대화하도록 설계되었습니다.</em></p>
        </div>
    </div>
    <?php
}

// ============================================================
// 탭 메뉴 관리
// ============================================================
function revenue_tabs_settings_page() {
    add_submenu_page(
        'themes.php',
        '탭 메뉴 관리',
        '탭 메뉴 관리',
        'manage_options',
        'revenue-tabs',
        'revenue_tabs_page_html'
    );
}
add_action('admin_menu', 'revenue_tabs_settings_page');

function revenue_tabs_page_html() {
    if (!current_user_can('manage_options')) return;
    
    if (isset($_POST['revenue_tabs_submit'])) {
        check_admin_referer('revenue_tabs_save');
        
        $tabs = array();
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($_POST["tab_{$i}_name"])) {
                $tabs[] = array(
                    'name' => sanitize_text_field($_POST["tab_{$i}_name"]),
                    'url' => esc_url_raw($_POST["tab_{$i}_url"]),
                    'active' => isset($_POST["tab_{$i}_active"]) ? '1' : '0'
                );
            }
        }
        
        update_option('revenue_tabs', $tabs);
        echo '<div class="notice notice-success"><p>탭 메뉴가 저장되었습니다.</p></div>';
    }
    
    $tabs = get_option('revenue_tabs', array());
    
    ?>
    <div class="wrap">
        <h1>📑 탭 메뉴 관리</h1>
        
        <form method="post">
            <?php wp_nonce_field('revenue_tabs_save'); ?>
            
            <table class="form-table">
                <?php for ($i = 1; $i <= 5; $i++): 
                    $tab = isset($tabs[$i-1]) ? $tabs[$i-1] : array('name' => '', 'url' => '', 'active' => '0');
                ?>
                <tr>
                    <th>탭 <?php echo $i; ?></th>
                    <td>
                        <input type="text" name="tab_<?php echo $i; ?>_name" value="<?php echo esc_attr($tab['name']); ?>" placeholder="탭 이름" class="regular-text">
                        <input type="url" name="tab_<?php echo $i; ?>_url" value="<?php echo esc_url($tab['url']); ?>" placeholder="https://" class="regular-text">
                        <label>
                            <input type="checkbox" name="tab_<?php echo $i; ?>_active" value="1" <?php checked($tab['active'], '1'); ?>>
                            Active
                        </label>
                    </td>
                </tr>
                <?php endfor; ?>
            </table>
            
            <p class="submit">
                <input type="submit" name="revenue_tabs_submit" class="button button-primary" value="탭 메뉴 저장">
            </p>
        </form>
    </div>
    <?php
}

// ============================================================
// 헤더 설정
// ============================================================
function revenue_header_settings() {
    add_submenu_page(
        'themes.php',
        '헤더 설정',
        '헤더 설정',
        'manage_options',
        'revenue-header',
        'revenue_header_page_html'
    );
}
add_action('admin_menu', 'revenue_header_settings');

function revenue_header_page_html() {
    if (!current_user_can('manage_options')) return;
    
    if (isset($_POST['revenue_header_submit'])) {
        check_admin_referer('revenue_header_save');
        
        update_option('revenue_logo_url', esc_url_raw($_POST['logo_url']));
        update_option('revenue_site_title', sanitize_text_field($_POST['site_title']));
        
        echo '<div class="notice notice-success"><p>헤더 설정이 저장되었습니다.</p></div>';
    }
    
    $logo_url = get_option('revenue_logo_url', '');
    $site_title = get_option('revenue_site_title', get_bloginfo('name'));
    
    ?>
    <div class="wrap">
        <h1>🎨 헤더 설정</h1>
        
        <form method="post">
            <?php wp_nonce_field('revenue_header_save'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label>로고 URL</label></th>
                    <td><input type="url" name="logo_url" value="<?php echo esc_url($logo_url); ?>" class="large-text"></td>
                </tr>
                <tr>
                    <th><label>사이트 제목</label></th>
                    <td><input type="text" name="site_title" value="<?php echo esc_attr($site_title); ?>" class="regular-text"></td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="revenue_header_submit" class="button button-primary" value="헤더 저장">
            </p>
        </form>
    </div>
    <?php
}

// ============================================================
// 카드 가져오기 함수 (피소니 법칙 적용)
// ============================================================
function get_revenue_cards_with_ads() {
    $cards = new WP_Query(array(
        'post_type' => 'revenue_card',
        'posts_per_page' => -1,
        'meta_key' => '_card_order',
        'orderby' => 'meta_value_num',
        'order' => 'ASC'
    ));
    
    $native_ad = get_option('revenue_native_ad', '');
    $output = '';
    $count = 0;
    
    if ($cards->have_posts()) {
        while ($cards->have_posts()) {
            $cards->the_post();
            
            // 피소니 법칙: 1, 4, 7번째 카드 앞에 광고
            if ($native_ad && in_array($count, array(0, 3, 6))) {
                $output .= '<div class="native-ad-wrapper">' . $native_ad . '</div>';
            }
            
            $post_id = get_the_ID();
            $amount = get_post_meta($post_id, '_card_amount', true);
            $amount_sub = get_post_meta($post_id, '_card_amount_sub', true);
            $description = get_post_meta($post_id, '_card_description', true);
            $target = get_post_meta($post_id, '_card_target', true);
            $period = get_post_meta($post_id, '_card_period', true);
            $link_url = get_post_meta($post_id, '_card_link_url', true) ?: get_permalink();
            $is_featured = get_post_meta($post_id, '_card_featured', true);
            
            $featured_class = $is_featured ? ' featured' : '';
            $badge = $is_featured ? '<span class="info-card-badge">🔥 인기</span>' : '';
            
            $output .= sprintf(
                '<a class="info-card%s" href="%s">
                    <div class="info-card-highlight">
                        %s
                        <div class="info-card-amount">%s</div>
                        <div class="info-card-amount-sub">%s</div>
                    </div>
                    <div class="info-card-content">
                        <h3 class="info-card-title">%s</h3>
                        <p class="info-card-desc">%s</p>
                        <div class="info-card-details">
                            <div class="info-card-row">
                                <span class="info-card-label">지원대상</span>
                                <span class="info-card-value">%s</span>
                            </div>
                            <div class="info-card-row">
                                <span class="info-card-label">신청시기</span>
                                <span class="info-card-value">%s</span>
                            </div>
                        </div>
                        <div class="info-card-btn">
                            지금 바로 신청하기 <span class="btn-arrow">→</span>
                        </div>
                    </div>
                </a>',
                $featured_class,
                esc_url($link_url),
                $badge,
                esc_html($amount),
                esc_html($amount_sub),
                get_the_title(),
                esc_html($description),
                esc_html($target),
                esc_html($period)
            );
            
            $count++;
        }
        wp_reset_postdata();
    }
    
    return $output;
}

// 현재 날짜 가져오기
function get_current_korean_date() {
    return date('Y.m.d');
}
