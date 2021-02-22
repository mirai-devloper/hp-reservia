<div id="reserviaReview">
  <div v-if="review" class="review-wrapper" itemprop="review" itemscope itemtype="http://schema.org/Review">
    <div class="review-header">
      <div class="rating-area">
        <span class="head">総合評価：</span>
        <div class="rating" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
          <div class="star-box star-back">
            <i v-for="n in 5" class="star" v-html="star.back"></i>
          </div>
          <div class="star-box star-top" data-star="average" :style="sizeEvaluation(rating.average)">
            <i v-for="n in 5" class="star" v-html="star.top"></i>
          </div>
        </div>
        <span class="sum" itemprop="ratingValue">{{ rating.average }}</span>
        <span class="count" itemprop="ratingCount">{{ rating.count }}件</span>
      </div>

      <div class="review-salon-comment">
        <p>クチコミの投稿ありがとうございます！いただいたクチコミはサービス向上の参考とさせていただきます。</p>
      </div>
    </div>

    <div class="review-contents">
      <ul  class="review-list">
        <li v-for="(item, $index) in reviews" :id="'rev'+item.id" :key="$index">
          <div class="review-item" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
            <div class="review-item-header">
              <div class="avater"><img :src="sexAvatar(item.user_sex)" alt=""></div>
              <div class="rating">
                <div class="star-box star-back">
                    <i v-for="n in 5" v-html="star.back" class="star"></i>
                </div>
                <div class="star-box star-top" :data-star="item.evaluation" :style="sizeEvaluation(item.evaluation)">
                    <i v-for="n in 5" v-html="star.top" class="star"></i>
                </div>
              </div>
              <div class="rating-sum" itemprop="ratingValue">{{ item.evaluation }}</div>

              <a :href="item.reserve_url" class="btn btn-default btn-reserve" target="_blank">Web予約</a>
            </div>

            <div class="review-item-body" itemprop="reviewBody">
              <p style="white-space: pre-wrap;word-wrap:break-word;">{{ item.comment }}</p>
            </div>

            <div class="review-item-footer">
              <div class="reviewer" itemprop="author" itemscope itemtype="http://schema.org/Person">
                <span class="tag">投稿者</span>
                <span class="name" itemprop="name">{{ item.user_nickname }}さん</span>
                <span class="sex">{{ sexText(item) }}</span>

              </div>
              <div class="review-posted">
                <span class="tag">投稿日時</span>
                <time class="date" itemprop="datePublished">{{ dateText(item.review_datetime) }}</time>
              </div>
              <div class="review-menu">
                <span class="tag">メニュー</span>
                <span class="menu-name" data-menuid="menu_id">{{ item.menu_name }}</span>
              </div>
            </div>

            <div v-if="item.response_comment" class="review-reply">
              <div class="review-staff-header">
                <div class="review-staff-avater">
                  <img :src="item.response_staff_profile_image_url" alt="">
                </div>

                <div class="review-staff-info" data-staffid="response_staff_id">
                  <span class="manage">担当者</span>
                  <span class="name">{{ item.response_staff_name }}</span>
                </div>
              </div>

              <div class="review-staff-body">
                <p style="white-space: pre-wrap;word-wrap:break-word;">{{ item.response_comment }}</p>
              </div>
            </div>
          </div>
        </li>
      </ul>
      <!-- <div v-show="loading" class="loader">Loading...</div> -->

      <!-- <nav class="review-pagination">
        <pagination v-model="page" :records="total" :per-page="10" :options="pager_options" @paginate="pager" @next="pager"></pagination>
      </nav> -->
      <infinite-loading @infinite="infiniteHandler"></infinite-loading>
    </div>
  </div>
  <div v-else>
    <div class="text-center">
      <p>口コミの投稿が見つかりませんでした。</p>
    </div>
  </div>
</div>

<style>.VuePagination__count {display:none;}</style>


<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.12/vue.min.js" integrity="sha512-BKbSR+cfyxLdMAsE0naLReFSLg8/pjbgfxHh/k/kUC82Hy7r6HtR5hLhobaln2gcTvzkyyehrdREdjpsQwy2Jw==" crossorigin="anonymous"></script>
<script src="https://unpkg.com/vue-infinite-loading@^2/dist/vue-infinite-loading.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
const api_url = '<?= home_url('wp-json/api/reservia/reviews'); ?>';
Vue.use(window.VueInfiniteLoading);
const reserviaReview = new Vue({
  el: '#reserviaReview',
  data() {
    return {
      review: [],
      reviews: [],
      user_sex_text: {
        1: '男性',
        2: '女性',
      },
      user_sex: {
        1: '<?= $this->img_url('people_male.png'); ?>',
        2: '<?= $this->img_url('people_famale.png'); ?>',
      },
      star: {
        back: '<svg aria-hidden="true" focusable="false" data-prefix="far" data-icon="star" class="svg-inline--fa fa-star fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M528.1 171.5L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6zM388.6 312.3l23.7 138.4L288 385.4l-124.3 65.3 23.7-138.4-100.6-98 139-20.2 62.2-126 62.2 126 139 20.2-100.6 98z"></path></svg>',
        top: '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="star" class="svg-inline--fa fa-star fa-w-18" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path></svg>',
      },
      rating: {
        average: 0,
        count: 0
      },

      page_size: <?= $page_size; ?>,
      total: 0,
      max_page: 1,
      page: 1,
      pager_options: {
        chunk: 5,
        chunksNavigation: 'scroll'
      },

      loading: true,
    }
  },

  created() {
    axios
      .get(api_url, {
        params: {
          page_size: this.page_size,
          page_number: this.page
        }
      })
      .then(response => (
        this.review = response.data,
        this.reviews = response.data.reviews,
        this.evaluation(response.data.evaluation_count),
        this.total = Number(response.data.total_count),
        this.page = Number(response.data.page_number),
        this.max_page = Number(response.data.number_of_pages),
        this.loading = false
      ));
  },

  mounted() {
    // this.evaluation(this.review.evaluation_count);
  },

  computed: {
  },

  methods: {
    sizeEvaluation(evaluation) {
      let w = Math.round(100 * evaluation / 5);
      return 'width: ' + w + '%';
    },

    isStrNumber(str) {
      if (typeof str === 'string' || str instanceof String) {
        return Number.isFinite(Number(str));
      }
      return false;
    },

    sexAvatar(str) {
      if (str === '1' || str === '男性') {
        return this.user_sex[1];
      } else {
        return this.user_sex[2];
      }
    },

    sexText(item) {
      let text = '';
      let num = item.user_generation;

      if (this.isStrNumber(item.user_sex)) {
        text = this.user_sex_text[item.user_sex];
      } else {
        text = item.user_sex;
      }
      if (num) {
        let hex_first = String(num).substr(-1, 1);
        let hex_second = String(num).substr(0, 1);
        text += '／' + hex_second + '0代';
        text += (5 <= hex_first) ? '後半' : '前半';
      }

      return text;
    },

    dateText(str) {
      return moment(str).format('YYYY年MM月DD日');
    },

    rating_average(rating, count) {
      var total = array_sum(rating);
      var $count = array_sum(count);
      var average = sumAverage(total / $count, 3);

      return average;
    },

    evaluation(arr) {
      var $rating = [];
      var $count = [];

      arr.forEach(function(v) {
        if (v.evaluation && v.count) {
          $rating.push(Math.floor(v.evaluation * v.count));
        }
        if (v.count) {
          $count.push(v.count);
        }
      });

      if ($rating) {
        this.rating.average = this.rating_average($rating, $count);
      }
      if ($count) {
        this.rating.count = array_sum($count);
      }

      return this.rating;
    },

    pager(page) {
      this.loading = true;
      axios.get(api_url, {
        params: {
          page_size: this.page_size,
          page_number: this.page
        }
      })
      .then(({ data }) => {
        this.review = data;
        this.reviews = data.reviews;
        this.evaluation(data.evaluation_count);
        this.total = data.total_count;
        this.page = data.page_number;
        this.max_page = data.number_of_pages;

        const elem = document.getElementById('reserviaReview');
        elem.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
        this.loading = false;
      });
    },

    infiniteHandler($state) {
      var self = this;
      axios.get(api_url, {
        params: {
          page_size: this.page_size,
          page_number: this.page
        }
      })
      .then(({ data }) => {
        if (this.page < this.max_page) {
          this.page += 1;
          this.reviews.push(...data.reviews);
          $state.loaded();
        } else {
          $state.complete();
        }
      })
    }
  },
});

function sumAverage(x, prec) {
  let a = Number(x.toPrecision(prec));
  let vabs = Math.abs(a);
  if (vabs >= 1.0e5 || (vabs < 1.0e-4 && vabs > 0)) {
    return a.toExponention();
  }

  return a.toString();
}

function array_sum(arr) {
  let key;
  let sum = 0;

  if (typeof arr !== 'object') {
    return null;
  }
  for (key in arr) {
    if (!isNaN(parseFloat(arr[key]))) {
      sum += parseFloat(arr[key]);
    }
  }
  return sum;
}
</script>

<style>
.loader {
  margin: 50px auto;
  font-size: 10px;
  width: 1em;
  height: 1em;
  border-radius: 50%;
  position: relative;
  text-indent: -9999em;
  -webkit-animation: load5 1.1s infinite ease;
  animation: load5 1.1s infinite ease;
  -webkit-transform: translateZ(0);
  -ms-transform: translateZ(0);
  transform: translateZ(0);
}
@-webkit-keyframes load5 {
  0%,
  100% {
    box-shadow: 0em -2.6em 0em 0em #ccc, 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.5), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.7);
  }
  12.5% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.7), 1.8em -1.8em 0 0em #ccc, 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.5);
  }
  25% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.5), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.7), 2.5em 0em 0 0em #ccc, 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  37.5% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.5), 2.5em 0em 0 0em rgba(220, 220, 220, 0.7), 1.75em 1.75em 0 0em #ccc, 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  50% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.5), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.7), 0em 2.5em 0 0em #ccc, -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  62.5% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.5), 0em 2.5em 0 0em rgba(220, 220, 220, 0.7), -1.8em 1.8em 0 0em #ccc, -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  75% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.5), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.7), -2.6em 0em 0 0em #ccc, -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  87.5% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.5), -2.6em 0em 0 0em rgba(220, 220, 220, 0.7), -1.8em -1.8em 0 0em #ccc;
  }
}
@keyframes load5 {
  0%,
  100% {
    box-shadow: 0em -2.6em 0em 0em #ccc, 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.5), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.7);
  }
  12.5% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.7), 1.8em -1.8em 0 0em #ccc, 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.5);
  }
  25% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.5), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.7), 2.5em 0em 0 0em #ccc, 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  37.5% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.5), 2.5em 0em 0 0em rgba(220, 220, 220, 0.7), 1.75em 1.75em 0 0em #ccc, 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  50% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.5), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.7), 0em 2.5em 0 0em #ccc, -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.2), -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  62.5% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.5), 0em 2.5em 0 0em rgba(220, 220, 220, 0.7), -1.8em 1.8em 0 0em #ccc, -2.6em 0em 0 0em rgba(220, 220, 220, 0.2), -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  75% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.5), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.7), -2.6em 0em 0 0em #ccc, -1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2);
  }
  87.5% {
    box-shadow: 0em -2.6em 0em 0em rgba(220, 220, 220, 0.2), 1.8em -1.8em 0 0em rgba(220, 220, 220, 0.2), 2.5em 0em 0 0em rgba(220, 220, 220, 0.2), 1.75em 1.75em 0 0em rgba(220, 220, 220, 0.2), 0em 2.5em 0 0em rgba(220, 220, 220, 0.2), -1.8em 1.8em 0 0em rgba(220, 220, 220, 0.5), -2.6em 0em 0 0em rgba(220, 220, 220, 0.7), -1.8em -1.8em 0 0em #ccc;
  }
}
</style>