<?php
/**
 * @package  WP Theme Statistic
 */

class WPStatisticActivate
{
	public static function activate() {
		flush_rewrite_rules();
	}
}