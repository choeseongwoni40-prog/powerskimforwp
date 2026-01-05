<?php get_header(); ?>

<div class="container main-content">
    
    <!-- 상단 인트로 -->
    <div class="intro-section">
        <span class="intro-badge">신청마감 D-3일</span>
        <p class="intro-sub">숨은 보험금 1분만에 찾기!</p>
        <h2 class="intro-title">숨은 지원금 찾기</h2>
    </div>

    <!-- 정보 박스 -->
    <div class="info-box">
        <div class="info-box-header">
            <span class="info-box-icon">🏷️</span>
            <span class="info-box-title">신청 안하면 절대 못 받아요</span>
        </div>
        <div class="info-box-amount">1인 평균 127만원 환급</div>
        <p class="info-box-desc">대한민국 92%가 놓치고 있는 정부 지원금! 지금 확인하고 혜택 놓치지 마세요.</p>
    </div>

    <!-- 카드 그리드 (자동으로 광고 삽입됨) -->
    <div class="info-card-grid">
        <?php 
        // 피소니 법칙이 적용된 카드 + 광고 출력
        echo get_revenue_cards_with_ads(); 
        ?>
    </div>

    <!-- 히어로 섹션 (CTA) -->
    <div class="hero-section">
        <div class="hero-content">
            <span class="hero-urgent">🔥 신청마감 D-3일</span>
            
            <p class="hero-sub">숨은 지원금 1분만에 찾기!</p>
            <h2 class="hero-title">
                나의 <span class="hero-highlight">숨은 지원금</span> 찾기
            </h2>
            <p class="hero-amount">신청자 <strong>1인 평균 127만원</strong> 수령</p>
            
            <a class="hero-cta" href="<?php echo esc_url(home_url('/')); ?>">
                30초만에 내 지원금 확인 <span class="cta-arrow">→</span>
            </a>
            
            <div class="hero-trust">
                <span class="trust-item">✓ 무료 조회</span>
                <span class="trust-item">✓ 30초 완료</span>
                <span class="trust-item">✓ 개인정보 보호</span>
            </div>
            
            <div class="hero-notice">
                <div class="notice-content">
                    <div class="notice-title">💡신청 안하면 못 받아요</div>
                    <p class="notice-desc">대한민국 92%가 놓치고 있는 정부 지원금, 지금 확인하고 혜택 놓치지 마세요!</p>
                </div>
            </div>
        </div>
    </div>

    <?php
    // 단일 카드 페이지인 경우 내용 표시
    if (is_singular('revenue_card')) {
        while (have_posts()) {
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('card-single'); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <div class="card-meta">
                    <?php
                    $amount = get_post_meta(get_the_ID(), '_card_amount', true);
                    $amount_sub = get_post_meta(get_the_ID(), '_card_amount_sub', true);
                    $description = get_post_meta(get_the_ID(), '_card_description', true);
                    $target = get_post_meta(get_the_ID(), '_card_target', true);
                    $period = get_post_meta(get_the_ID(), '_card_period', true);
                    $link_url = get_post_meta(get_the_ID(), '_card_link_url', true);
                    ?>
                    
                    <?php if ($amount): ?>
                    <div class="meta-item">
                        <strong>혜택:</strong> <?php echo esc_html($amount); ?>
                        <?php if ($amount_sub): ?>
                            <span class="meta-sub">(<?php echo esc_html($amount_sub); ?>)</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($description): ?>
                    <div class="meta-item">
                        <strong>설명:</strong> <?php echo esc_html($description); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($target): ?>
                    <div class="meta-item">
                        <strong>지원대상:</strong> <?php echo esc_html($target); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($period): ?>
                    <div class="meta-item">
                        <strong>신청시기:</strong> <?php echo esc_html($period); ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($link_url): ?>
                    <div class="meta-item">
                        <a href="<?php echo esc_url($link_url); ?>" class="btn-apply" target="_blank">
                            지금 바로 신청하기 →
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php
        }
    }
    ?>

</div>

<?php get_footer(); ?>
