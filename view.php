<div itemscope itemtype="http://schema.org/LocalBusiness">
  <div class="review-wrapper" itemprop="review" itemscope itemtype="http://schema.org/Review">
    <?php if (isset($reviews) and ! empty($reviews)) : ?>
    <meta itemprop="itemReviewed" content="<?php echo esc_attr(get_bloginfo('name')); ?>" />
    <div class="review-header">
      <?php if (isset($rating)) : ?>
      <div class="rating-area">
        <span class="head">総合評価：</span>
        <div class="rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
          <div class="star-box star-back">
            <?php for ($r = 0; $r < 5; $r++) : ?>
              <i class="star"><svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="star" class="svg-inline--fa fa-star fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M528.1 171.5L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6zM388.6 312.3l23.7 138.4L288 385.4l-124.3 65.3 23.7-138.4-100.6-98 139-20.2 62.2-126 62.2 126 139 20.2-100.6 98z"></path></svg></i>
            <?php endfor; ?>

          </div><?php
            $rat_size = round(100 * $rating['average'] / 5, 4);
            $style = " style=\"width: {$rat_size}%\"";
          ?>
          <div class="star-box star-top" data-star="<?= (isset($rating['average'])) ? $rating['average'] : '0'; ?>" <?= $style; ?>>
            <?php for ($i = 0; $i < 5; $i++) : ?>
              <i class="star"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="star" class="svg-inline--fa fa-star fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path></svg></i>
            <?php endfor; ?>

          </div>
        </div>
        <?php if (isset($rating['average'])) : ?>
        <span class="sum" itemprop="ratingValue"><?php echo $rating['average']; ?></span>
        <?php endif; ?>
        <?php if (isset($rating['count'])) : ?>
        <span class="count" itemprop="ratingCount"><?php echo $rating['count']; ?>件</span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <div class="review-salon-comment">
        <p>クチコミの投稿ありがとうございます！いただいたクチコミはサービス向上の参考とさせていただきます。</p>
      </div>

      <!-- <div class="review-salon-staff-avater">
        <span class="avater"></span>
        <span class="avater"></span>
        <span class="avater"></span>
        <span class="avater"></span>
        <span class="avater"></span>
        <span class="avater"></span>
        <span class="avater"></span>
      </div> -->
    </div>

    <div class="review-contents">
      <ul class="review-list">
      <?php foreach ($reviews as $k => $v) : ?>
        <li id="rev<?php echo esc_attr($v->id); ?>" >
          <div class="review-item" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
            <div class="review-item-header">
              <div class="avater">
              <?php
                if (strpos($v->user_sex, '女性') !== false)
                {
                  echo $this->img('people_famale.png');
                }
                else
                {
                  echo $this->img('people_male.png');
                }
              ?>
              </div>
              <div class="rating">
                <div class="star-box star-back">
                  <?php for ($r = 0; $r < 5; $r++) : ?>
                    <i class="star"><svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="star" class="svg-inline--fa fa-star fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M528.1 171.5L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6zM388.6 312.3l23.7 138.4L288 385.4l-124.3 65.3 23.7-138.4-100.6-98 139-20.2 62.2-126 62.2 126 139 20.2-100.6 98z"></path></svg></i>
                  <?php endfor; ?>
                </div><?php
                  $rat_size = round(100 * $v->evaluation / 5);
                  $style = " style=\"width: {$rat_size}%\"";
                ?>
                <div class="star-box star-top" data-star="<?php echo esc_attr($v->evaluation); ?>" <?php echo $style; ?>>
                  <?php for ($i = 0; $i < 5; $i++) : ?>
                    <i class="star"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="star" class="svg-inline--fa fa-star fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path></svg></i>
                  <?php endfor; ?>
                </div>
              </div>
              <div class="rating-sum" itemprop="ratingValue"><?php echo esc_html($v->evaluation); ?></div>

              <?php if (isset($v->reserve_url)) : ?>
              <a href="<?php echo esc_url($v->reserve_url); ?>" class="btn btn-default btn-reserve" target="_blank">Web予約</a>
              <?php endif; ?>
            </div>

            <div class="review-item-body" itemprop="reviewBody">
              <p><?php echo nl2br(esc_html(wp_strip_all_tags($v->comment)), true); ?></p>
            </div>

            <div class="review-item-footer">
              <div class="reviewer" itemprop="author" itemscope itemtype="http://schema.org/Person">
                <span class="tag">投稿者</span>
                <span class="name" itemprop="name"><?php echo ( ! empty($v->user_nickname)) ? esc_html(wp_strip_all_tags($v->user_nickname)).'さん' : '匿名'; ?></span>
                <span class="sex"><?php
                  echo wp_strip_all_tags($v->user_sex);
                  if ($num = $v->user_generation and ! empty($num))
                  {
                    $hex_first = substr($num, 1, 1);
                    $hex_second = substr($num, 0, 1);
                    echo '／'.$hex_second.'0代';
                    echo (5 <= $hex_first) ? '後半' : '前半';
                  }
                ?></span>
              </div>
              <div class="review-posted">
                <span class="tag">投稿日時</span>
                <time class="date" itemprop="datePublished"><?php echo date('Y年m月d日', strtotime($v->review_datetime)); ?></time>
              </div>
              <div class="review-menu">
                <span class="tag">メニュー</span>
                <span class="menu-name" data-menuid="<?php echo esc_attr($v->menu_id); ?>"><?php echo esc_html($v->menu_name); ?></span>
              </div>
            </div>

            <?php if ( ! empty($v->response_comment)) : ?>
            <div class="review-reply">
              <div class="review-staff-header">
                <div class="review-staff-avater">
                  <img src="<?php echo esc_url($v->response_staff_profile_image_url); ?>" alt="">
                </div>

                <div class="review-staff-info" data-staffid="<?php echo esc_attr($v->response_staff_id); ?>">
                  <span class="manage">担当者</span>
                  <span class="name"><?php echo esc_html($v->response_staff_name); ?></span>
                </div>
              </div>

              <div class="review-staff-body">
                <p><?php echo nl2br(esc_html(wp_strip_all_tags($v->response_comment)), true); ?></p>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
      </ul>
    <?php else : ?>
      <div class="text-center">
        <p>口コミの投稿が見つかりませんでした。</p>
      </div>
    <?php endif; ?>

    <?php if (isset($pagination) and $pagination !== false) : ?>
      <nav class="review-pagination">
        <ul class="pagination">
          <li><?php echo implode('</li><li>', $pagination); ?></li>
        </ul>
      </nav>
    <?php endif; ?>
    </div>
  </div>
</div>
