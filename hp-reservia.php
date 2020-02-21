<?php
/*
Plugin Name: Reservia ReViews for HairsPress
Plugin URI: https://mi-rai.co.jp/
Description: Reservia API on HairsPress. Reservia reviews page.
Author: MIRAI
Version: 1.1.0
Author URI: https://mi-rai.co.jp/
*/
require __DIR__.'/plugin-update-checker/plugin-update-checker.php';

$hpReserviaChecker = Puc_V4_Factory::buildUpdateChecker(
	'https://mi-rai.co.jp/wp-update-server/?action=get_metadata&slug=hp-reservia',
	__FILE__,
	'hp-reservia'
);

class HP_Reservia
{
	private static $_instance = null;

	private $application_token = 'df5e905af266c273a07659d8ed5da4e2';
	private $application_seed  = 'wm179pu6731w';

	public $page_id = null;

	private $setting;

	private $options;

	private $page_title = 'ReserviaAPI';
	private $menu_title = 'Reservia連携';
	private $menu_slug  = 'hp-reservia-setting';
	private $capability = 'manage_options';

	private $option_group = 'hp_reservia_group';
	private $option_name  = 'hp_reservia_option';

	private $section_id   = 'hp_reservia_section_id';

	private $shop_id_flag = true;
	private $hairspress_reserve = '';

	private $api_base_url = 'https://api.reservia.jp';
	private $api_datetime = 'YmdHis';

	private $api_query = array();

	public function __construct()
	{
		$this->setting['attr'] = array(
			'type',
			'id',
			'name',
			'value',
		);

		define("HPR_DIR_PATH", plugin_dir_path(__FILE__));

		define("HPR_DIR_URL", plugin_dir_url(__FILE__));

		// HairsPressの場合にshop_idがある場合は、HairsPress側を使用するようにする
		if (function_exists('get_field'))
		{
			if (
				$reserve_system = get_field('hp_webreserve_system', 'option')
				and ! empty($reserve_system)
				and $reserve_system === 'reservia'
			)
			{
				if (
					$hp_reservia_shop_id = get_field('hp_salon_reservia', 'option')
					and ! empty($hp_reservia_shop_id)
				)
				{
					$this->shop_id_flag = false;
					$this->hairspress_reserve = $hp_reservia_shop_id;
				}
			}
		}

		// 実行
		$this->init();
	}

	/**
	 * 設定ページの作成
	 */
	public function add_option_page()
	{
		add_options_page(
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			array($this, 'create_options_page')
		);
	}

	/**
	 * 管理画面側の表示を作成
	 */
	public function create_options_page()
	{
		$this->options = get_option($this->option_name);

		$param = array();
		echo $this->template('hp-admin', $param);
	}

	/**
	 * 管理画面に表示するフォームを設定
	 */
	public function page_init()
	{
		register_setting(
			$this->option_group,
			$this->option_name,
			array($this, 'sanitize')
		);

		add_settings_section(
			$this->section_id,
			'API設定',
			array($this, 'print_section_info'),
			$this->menu_slug
		);

		add_settings_field(
			'reservia_shop_id',
			'店舗番号',
			array($this, 'application_shop_id_cb'),
			$this->menu_slug,
			$this->section_id
		);

		add_settings_field(
			'reservia_review_page_number',
			'口コミの表示件数',
			array($this, 'review_page_number_cb'),
			$this->menu_slug,
			$this->section_id
		);
	}

	/**
	 * Sectionのテキスト
	 */
	public function print_section_info()
	{
		print 'Reserviaと連動するために、以下の情報を設定してください。';
		print 'クチコミを表示するための機能です。';
	}

	/**
	 * Sanitize
	 */
	public function sanitize($input)
	{
		$new_input = array();

		if ($this->shop_id_flag)
		{
			if (isset($input['reservia_shop_id']))
				$new_input['reservia_shop_id'] = absint($input['reservia_shop_id']);
		}
		else
		{
			if ( ! empty($this->hairspress_reserve))
				$new_input['reservia_shop_id'] = absint($this->hairspress_reserve);
		}

		if (isset($input['reservia_review_page_number']))
			$new_input['reservia_review_page_number'] = absint($input['reservia_review_page_number']);

		return $new_input;
	}

	public function application_shop_id_cb()
	{
		$field_id = 'reservia_shop_id';
		$shop_id = '';
		if ($this->shop_id_flag)
		{
			$shop_id = isset($this->options[$field_id]) ? esc_attr($this->options[$field_id]) : '';
		}
		else
		{
			if ( ! empty($this->hairspress_reserve))
				$shop_id = esc_attr($this->hairspress_reserve);
		}
		echo $this->input(
			'number',
			$field_id,
			$shop_id,
			array('step' => '1', 'min' => '1', 'max' => '9999999')
		);
	}

	public function review_page_number_cb()
	{
		$field_id = 'reservia_review_page_number';
		echo $this->input(
			'number',
			$field_id,
			isset($this->options[$field_id]) ? esc_attr($this->options[$field_id]) : '10',
			array('max' => '100', 'min' => '1', 'step' => '1')
		);
		echo '<p>1ページ辺りに表示する件数です。</p>';
	}



	/**
	 * Shop IDを取得する
	 *
	 * @return String Number
	 */
	public function get_shop_id()
	{
		$field_id = 'reservia_shop_id';
		if ($this->shop_id_flag)
		{
			return $this->options[$field_id];
		}
		return $this->hairspress_reserve;
	}

	public function h($str)
	{
		return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Remote Post
	 */
	public function reservia_remote_post($path, $param = array())
	{
		$this->options = get_option($this->option_name);

		$datetime = date_i18n($this->api_datetime);

		$request = array(
			'application_token' => $this->application_token,
			'access_token' => md5($datetime.$this->application_seed),
			'datetime' => $datetime,
			'page_number' => 1,
		);

		if ( ! empty($this->get_shop_id()))
			$request['shop_id'] = $this->get_shop_id();

		if ( ! empty($this->options['reservia_review_page_number']))
			$request['page_size'] = $this->options['reservia_review_page_number'];

		if (isset($_GET['page_number']) and ! empty($_GET['page_number']) and ctype_digit($_GET['page_number']))
		{
			$page_num = (int) $this->h($_GET['page_number']);
			$request['page_number'] = ($page_num <= 0) ? 1 : $page_num;
		}
		else
		{
			if (isset($_GET['page_number']) and ! ctype_digit($_GET['page_number']))
				$request['page_number'] = 'error';
		}

		if ( ! empty($param))
		{
			foreach ($param as $k => $v)
			{
				$request[$k] = $v;
			}
		}


		$header = array(
			'Content-Type: application/x-www-form-urlencoded',
		);

		$response = wp_remote_post($this->api_base_url.$path, array(
			'header' => $header,
			'body' => $request
		));

		if ($response['response']['code'] !== 200)
			return json_decode($response['body']);

		return json_decode($response['body']);
	}

	/**
	 * 口コミのショートコード
	 */
	public function do_review()
	{

		$reservia = $this->reservia_remote_post('/review/list');

		// 接続できなかった場合
		if ($reservia === false)
			return '<p class="alert alert-danger" role="alert">Reserviaへの接続が出来ませんでした。<br>エラーコード：0101</p>';

		if (isset($reservia->status) and $reservia->status === 2)
			return '<p class="alert alert-danger" role="alert">'.$reservia->error_message.'<br>エラーコード：'.$reservia->error_code.'</p>';

		// 総合評価の平均値
		if ($review_evaluation = $reservia->evaluation_count)
		{
			$rating = $this->evaluation($review_evaluation);
		}



		// Viewへ変数を渡すための配列
		$params = array();

		$params['reservia'] = $reservia;

		if (isset($rating))
			$params['rating'] = $rating;

		if (isset($reservia->reviews))
			$params['reviews'] = $reservia->reviews;

		$params['pagination'] = $this->pagination($reservia->number_of_pages, $reservia->page_number);

		// Viewへ出力
		return $this->template('view', $params);
	}

	public function pagination($limit, $page, $disp = 5)
	{
		if ($limit <= 1)
			return false;

		$url = get_permalink($this->page_id);

		$next = $page + 1;
		$prev = $page - 1;

		$start = ($page - floor($disp/2) > 0) ? ($page - floor($disp/2)) : 1;
		$end   = ($start > 1) ? ($page + floor($disp/2)) : $disp;
		$start = ($limit < $end) ? $start - ($end - $limit) : $start;

		$pagination = array();

		if ($page != 1)
		{
			$url_prev = ($prev === 1) ? $url : $url.'?page_number='.$prev;
			$pagination[] = '<a href="'.esc_url($url_prev).'" class="prev"><span>前へ</span></a>';
		}

		if ($start >= floor($disp / 2))
		{
			$pagination[] = '<a href="'.esc_url($url).'" class="number">1</a>';
			if ($start > floor($disp/2))
				$pagination[] = '<span class="dot">...</span>';
		}

		for ($i = $start; $i <= $end; $i++)
		{
			$class = ($page == $i) ? ' active current' : '';

			if ($i <= $limit and $i > 0)
			{
				if ($page == $i)
				{
					$pagination[] = '<span class="number'.$class.'">'.$i.'</span>';
				}
				else
				{
					if ($i == 1)
					{
						$pagination[] = '<a href="'.esc_url($url).'" class="number'.$class.'">'.$i.'</a>';
					}
					else
					{
						$pagination[] = '<a href="'.esc_url($url).'?page_number='.$i.'" class="number'.$class.'">'.$i.'</a>';
					}
				}
			}
		}

		if ($limit > $end)
		{
			if ($limit - 1 > $end)
				$pagination[] = '...';

			$pagination[] = '<a href="'.esc_url($url).'?page_number='.$limit.'">'.$limit.'</a>';
		}

		if ($page < $limit)
		{
			$pagination[] = '<a href="'.esc_url($url).'?page_number='.$next.'" class="next"><span>次へ</span></a>';
		}

		return $pagination;
	}

	/**
	 * View用のInclude
	 */
	public function template($filename, $params)
	{
		extract($params);
		ob_start();
		include HPR_DIR_PATH.'/'.$filename.'.php';
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function asset_path()
	{
		return HPR_DIR_URL.'assets/';
	}

	public function css($file)
	{
		return $this->asset_path().'css/'.$file;
	}

	public function js($file)
	{
		return $this->asset_path().'js/'.$file;
	}

	public function img($file, $attr = array())
	{
		return sprintf(
			'<img src="%1$s" %2$s>',
			$this->asset_path().'img/'.$file,
			empty($attr) ? '' : $this->attr($attr)
		);
	}


	public function enqueue_style()
	{
		wp_enqueue_style('hpr-view', $this->css('view.css'), array(), '0.1' );
	}

	public function current_pagehook()
	{
		global $hook_suffix;

		if ( ! current_user_can('manage_options')) return;

		echo '<div class="updated"><p>hook_suffix : '.$hook_suffix.'</p></div>';
	}

	public function admin_enqueue($hook_suffix)
	{
		if ('settings_page_hp-reservia-setting' == $hook_suffix)
		{
			wp_enqueue_style('hpr-admin', $this->css('admin.css'), array(), '0.1');
		}
	}

	public function view_asset_load()
	{
		if ($queried = get_queried_object())
		{
			if (isset($queried->post_content) and has_shortcode( $queried->post_content, 'reservia_review' ))
			{
				// var_dump($queried);
				$this->page_id = $queried->ID;
				add_action('wp_enqueue_scripts', array($this, 'enqueue_style'));
			}
		}
	}

	/**
	 * レーティングの平均を返す
	 *
	 * レーティング：5段階
	 * 計算：☆5☓人数 ＋ ☆4☓人数 ＋ ☆3☓人数 ＋ ☆2☓人数 ＋ ☆1☓人数 ＝ 各人数を足した数の配列を渡すと、平均を割り出すようにしている
	 *
	 * @return Integer
	 */
	public function rating_average($rating, $count)
	{
		$total = array_sum($rating);

		$count = array_sum($count);

		$average = round($total / $count, 1);

		return $average;
	}

	/**
	 *
	 */
	public function evaluation($evaluation = array())
	{
		$rating = array();
		$count = array();

		foreach ($evaluation as $k => $v)
		{
			if (isset($v->evaluation) and isset($v->count))
				$rating[] = floor($v->evaluation * $v->count);

			if (isset($v->count))
				$count[] = $v->count;
		}

		$result = array(
			'average' => 0,
			'count' => 0,
		);

		if ( ! empty($rating))
			$result['average'] = $this->rating_average($rating, $count);

		if ( ! empty($count))
			$result['count'] = array_sum($count);

		return $result;
	}


	/**
	 * Inputタグの出力をする
	 *
	 * @param $type     String
	 * @param $field_id String
	 * @param $value    String
	 * @param $attr     Array
	 * @return html input
	 */
	public function input($type = 'text', $field_id, $value = '', $attr = array())
	{
		return sprintf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" %5$s />',
			$type,
			$field_id,
			$this->option_name.'['.$field_id.']',
			$value,
			empty($this->attr($attr)) ? '' : $this->attr($attr)
		);
	}

	/**
	 * 配列から指定した文字列があるか調べる
	 */
	public function wpel_strpos($str, $arr = array())
	{
		if (empty($arr))
			return false;

		foreach ($arr as $v)
		{
			if (strpos($v, $str) !== false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Attributeを追加するための配列処理
	 *
	 * @param $args | array
	 *
	 * @return ' $key="$value"' が繰り返し処理
	 */
	public function attr($args = array())
	{
		if ( ! empty($args))
		{
			foreach ($args as $k => $v)
			{
				if ( ! (is_numeric($k) or $this->wpel_strpos($k, $this->setting['attr'])))
				{
					$attr[] = $k.'="'.$v.'"';
				}
			}
		}

		if (isset($attr))
		{
			return implode(' ', $attr);
		}
	}

	/**
	 * Initialize option page
	 *
	 *
	 */
	public function init()
	{
		// add_action('admin_notices', array($this, 'current_pagehook'));
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
		add_action('admin_menu', array($this, 'add_option_page'));
		add_action('admin_init', array($this, 'page_init'));

		// var_dump();
		add_action('wp', array($this, 'view_asset_load'));

		add_shortcode('reservia_review', array($this, 'do_review'));
	}

	/**
	 * Return the instance of the hairspress reservia class
	 *
	 * @static
	 * @since 0.0.1
	 * @return HP_Reservia
	 */
	public static function instance()
	{
		if (self::$_instance === null)
		{
			self::$_instance = new HP_Reservia();
		}

		return self::$_instance;
	}
}

HP_Reservia::instance();
