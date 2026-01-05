<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php wp_head(); ?>
    
    <!-- 애드센스/광고 헤드 코드 -->
    <?php 
    $interstitial_ad = get_option('revenue_interstitial_ad', '');
    $anchor_ad = get_option('revenue_anchor_ad', '');
    
    // 광고 스크립트 헤더 삽입
    if (!empty($interstitial_ad)) {
        preg_match('/src=["\']([^"\']+)["\']/', $interstitial_ad, $matches);
        if (!empty($matches[1])) {
            echo '<script async src="' . esc_url($matches[1]) . '"></script>';
        }
    }
    ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="main-wrapper">
    <!-- 헤더 섹션 (상단 고정) -->
    <header id="header" class="site-header">
        <div class="container">
            <div class="header-content">
                <?php 
                $logo_url = get_option('revenue_logo_url', '');
                $site_title = get_option('revenue_site_title', get_bloginfo('name'));
                ?>
                
                <?php if ($logo_url): ?>
                <div class="logo">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_title); ?>">
                    </a>
                </div>
                <?php endif; ?>
                
                <h1 class="logo-text">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <?php echo esc_html($site_title); ?>
                    </a>
                </h1>
            </div>
        </div>
    </header>

    <!-- 탭 메뉴 (헤더 아래 고정) -->
    <div class="tab-wrapper">
        <div class="container">
            <nav class="tab-container">
                <ul class="tabs">
                    <?php 
                    $tabs = get_option('revenue_tabs', array());
                    if (!empty($tabs)) {
                        foreach ($tabs as $tab) {
                            if (!empty($tab['name'])) {
                                $active_class = $tab['active'] === '1' ? ' active' : '';
                                $tab_url = !empty($tab['url']) ? $tab['url'] : home_url('/');
                                printf(
                                    '<li class="tab-item"><a class="tab-link%s" href="%s">%s</a></li>',
                                    $active_class,
                                    esc_url($tab_url),
                                    esc_html($tab['name'])
                                );
                            }
                        }
                    } else {
                        // 기본 탭
                        echo '<li class="tab-item"><a class="tab-link active" href="' . esc_url(home_url('/')) . '">홈</a></li>';
                    }
                    ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- 메인 콘텐츠 시작 -->
    <div class="site-content">
