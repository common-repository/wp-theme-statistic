<?php
/**
 * @package  WP Theme Statistic
 */

class WPStatisticDeactivate
{
	public static function deactivate() {
		flush_rewrite_rules();
	}
}