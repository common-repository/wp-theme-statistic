<div class="plugin-info">
	<div class="container">
		<h1><?php esc_html_e( 'WP Theme Statistic', 'wp-theme-statistic');?></h1>
		<p><?php esc_html_e( 'This plugin is based on WordPress API to get theme info from wordpress.org by using shortcode.', 'wp-theme-statistic');?></p>
		
		<h2><?php esc_html_e( 'Documentation', 'wp-theme-statistic' );?></h2>
		
		<p><?php esc_html_e('1. Just add shortcode [theme-info slug=\'theme-slug\'] into the page / post / text widget where you need to display theme info such as Active Installations, total downloaded, last updated, version no, required WordPress version and PHP version etc. ', 'wp-theme-statistic');?></p>
		<p><?php esc_html_e('2. These data are live referred from wordpress.org theme repository.', 'wp-theme-statistic');?></p>
		<p><?php esc_html_e('3. Shortcode example is as follows: " [theme-info slug=\'new-blog\'] ". In the place of new-blog you have to use your required theme-slug. ', 'wp-theme-statistic');?></p>

		<br/>
		<img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . '/assets/img/theme-slug.png'; ?>" >
		<br/>
		<p><?php esc_html_e('4. Output of plugin is as shown below', 'wp-theme-statistic');?></p>
		<img src="<?php echo plugin_dir_url( dirname( __FILE__ ) ) . '/assets/img/output.png'; ?>" >

	</div>
</div>