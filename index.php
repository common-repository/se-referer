<?php
/*
Plugin Name: SE Referer
Plugin URI: http://s-one.ru/
Description: This plugin creates a search history, and will publish its on the widget 
Author: S1eng
Author URI: http://s-one.ru/
Version: 0.01
*/

class SEReferer extends WP_Widget{
 
	public function SEReferer() {
		$widget_ops = array( 'classname' => 'sereferer', 'description' => 'Displays Search History block at sidebar.' );
		$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'sereferer' );
		parent::__construct( 'sereferer', 'SEReferer', $widget_ops, $control_ops );
	}
	
	public function se_activate()
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . "sereferer";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
			dbDelta("CREATE TABLE IF NOT EXISTS `". $table_name ."` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `se` text NOT NULL,
					  `query` text NOT NULL,
					  `url` text NOT NULL,
					  `date` int(11) NOT NULL,
					  `repeat` int(11) NOT NULL,
					  UNIQUE KEY `id_2` (`id`),
					  KEY `id` (`id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=5 ;");
	}
 
	public function form($instance) {?>
		<p><label for="<?php echo $this->get_field_id('hello'); 
		?>"><?php _e("Введите заголовок"); ?></label>:
		<input id="<?php echo $this->get_field_id('hello'); 
		?>" name="<?php echo $this->get_field_name('hello'); 
		?>" value="<?php echo $instance['hello']; 
		?>" /></p><?php 
	}
 
	public function update($new_instance, $old_instance) {
		$this->se_activate();
		return $new_instance;
	}
 
	public function widget($args, $instance) {
		global $wpdb;
		$table_name = $wpdb->prefix . "sereferer";
		$this -> save_query();
		
		echo $args['before_widget'],$args['before_title'] . $instance['hello'] . $args['after_title'];
		

		$query_rows = $wpdb -> get_results("SELECT * FROM `". $table_name ."` ORDER BY `date` DESC LIMIT 20");
		foreach($query_rows as $query_row)
		{
			echo '<img src="http://'.$_SERVER['SERVER_NAME'].'/wp-content/plugins/se-referer/'. $query_row->se .'.ico" alt="'. $query_row->se .'">';
			echo ' <a href="http://'. $query_row->url .'" title="'. $query_row->query .'">'. $query_row->query .'</a><br />';
		}
		echo $args['after_widget'];
	}
	
	public function save_query()
	{
		$referer = $_SERVER['HTTP_REFERER'];
		//$referer = 'http://www.google.ru/search?client=opera&rls=ru&q=php+referrer&sourceid=opera&ie=utf-8&oe=utf-8&channel=suggest';
		//$referer = 'http://yandex.ru/yandsearch?clid=9582&text=%D0%B2%D1%81%D1%8F%D0%BA%D0%B8%D0%B5+%D1%80%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B5+%D1%81%D0%B8%D0%BC%D0%B2%D0%BE%D0%BB%D1%8B&lr=236';
		
		if ((strpos($referer, 'http://wap.google.ru') === 0) || 
			(strpos($referer, 'http://www.google.ru') === 0) || 
			(strpos($referer, 'http://google.ru') === 0) || 
			(strpos($referer, 'http://www.google.com') === 0) ||
			(strpos($referer, 'http://google.com') === 0) || 
			(strpos($referer, 'http://wap.google.com') === 0))
		{
			preg_match('/q=(.*?)(&|$)/i', $referer, $queryArr);
			$query = mysql_real_escape_string(urldecode($queryArr[1]));
			$se = 'google';
		}
		if ((strpos($referer, 'http://www.yandex.ru') === 0) || 
			(strpos($referer, 'http://yandex.ru') === 0) || 
			(strpos($referer, 'http://wap.yandex.ru') === 0) || 
			(strpos($referer, 'http://yandex.com') === 0) || 
			(strpos($referer, 'http://www.yandex.com') === 0) || 
			(strpos($referer, 'http://wap.yandex.com') === 0))
		{
			preg_match('/text=(.*?)(\&|$)/i', $referer, $queryArr);
			$query = mysql_real_escape_string(urldecode($queryArr[1]));
			$se = 'yandex';
		}
		//echo $query;
		if(!empty($query))
		{
			global $wpdb;
			$table_name = $wpdb->prefix . "sereferer";
			
			$rows = $wpdb->get_var("SELECT COUNT(*) FROM ". $table_name ." WHERE `query` = '". $query ."'");
			//echo $rows;
			if($rows > 0)
			{
				$wpdb->query("UPDATE `". $table_name ."` SET `repeat` = `repeat`+1, `date` = '". time() ."' WHERE `query` = '". $query ."'");
			}
			else
			{
				$page = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
				$wpdb->query("INSERT INTO `". $table_name ."` (`se`, `query`, `url`, `date` ) " . 
				"VALUES ('". $se ."','". $query ."', '". $page ."', '". time() ."') ");
			}
		}
	}
}
 
// register SEReferer widget
add_action('activate_se-referer/index.php', 'activate');
add_action('widgets_init', create_function('', 'return register_widget("SEReferer");'));
?>