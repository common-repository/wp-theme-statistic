<?php
/**
 * @package WP Theme Statistic
 */
/*
Plugin Name: WP Theme Statistic
Plugin URI: https://www.postmagthemes.com/wp-theme-statistic/
Description: This plugin reflect theme statistic data from wordpress.org repository using shortcode. It shows version no, last theme update date, released date, total download, within days, average download per day, active installation, yesterday download, required wordpress version, required php version and ratings.
Version: 1.0.6
Author: postmagthemes
Author URI:  http://postmagthemes.com
License: GPLv2 or later
Text Domain: wp-theme-statistic
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/
defined( 'ABSPATH' ) or die( 'Hey, what are you doing here? You silly human!' );

if ( !class_exists( 'WP_Theme_Statistic' ) ) {

	class WP_Theme_Statistic
	{

		public $plugin;

		function __construct() {
			$this->plugin = plugin_basename( __FILE__ );
		}

		function register() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'front_enqueue' ) );
			add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );

			add_filter( "plugin_action_links_$this->plugin", array( $this, 'settings_link' ) );
		}

		public function settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=wp_theme_statistic">Settings</a>';
			array_push( $links, $settings_link );
			return $links;
		}

		public function add_admin_pages() {
			add_menu_page( 'WP Theme Statistic', 'WP Theme Statistic', 'manage_options', 'wp_theme_statistic', array( $this, 'admin_index' ), 'dashicons-store', 110 );
		}

		public function admin_index() {
			require_once plugin_dir_path( __FILE__ ) . 'templates/admin.php';
		}

		function enqueue() {
			// enqueue all our scripts
			wp_enqueue_style( 'admin-style', plugins_url( '/assets/admin-style.css', __FILE__ ) );
		}

		function front_enqueue() {
			
			wp_enqueue_style( 'frontpage', plugins_url( '/assets/frontpage.css', __FILE__ ) );
		}
		function activate() {
			require_once plugin_dir_path( __FILE__ ) . 'inc/wp-statistic-activate.php';
			WPStatisticActivate::activate();
		}
	}

	$WPStatistic = new WP_Theme_Statistic();
	$WPStatistic->register();

	// activation
	register_activation_hook( __FILE__, array( $WPStatistic, 'activate' ) );

	// deactivation
	require_once plugin_dir_path( __FILE__ ) . 'inc/wp-statistic-deactivate.php';
	register_deactivation_hook( __FILE__, array( 'WPStatisticDeactivate', 'deactivate' ) );

	// add shortcode for wordPress org
	add_shortcode( 'theme-info', 'wp_org_api_theme_info' );

	function wp_org_api_theme_info($atts){
		
		$atts = shortcode_atts( array(
				'slug' => '',
			), $atts, 'theme-info' 
		);
		$resultThemeInfo = WP_ThemeInfo_API($atts);

		$resultDownloadInfo = json_decode( WP_Download_API($atts), true );
		$totalresultDownloadInfo = count($resultDownloadInfo);
		
		$downloadSum = 0;
		$html = '';
		$activeInsTitle = esc_html__( 'Active Installations:', 'wp-theme-statistic' );
		$totalDownload = esc_html__( 'Total Download:', 'wp-theme-statistic' );
		$lastUpdated = esc_html__( 'Last Updated:', 'woion:', 'wp-theme-statistic' );
		$phpVersion = esc_html__( 'Required PHP Version:', 'wp-theme-statistic' );
		$yesterDayDownloadedNumber = esc_html__( 'Yesterday Download: ', 'wp-theme-statistic' );
		$releaseDate = esc_html__( 'Released Date:', 'wp-theme-statistic' );
		$AverateDownload = esc_html__( 'Average Download per day: ' );
		$version = esc_html__( 'Version:', 'wp-theme-statistic' );
		$wordPressVersion = esc_html__( 'Required WordPress Version:', 'wp-theme-statistic' );
		if(isset($resultThemeInfo) > 0){
			$html .='<div class="theme-info" style="float:left">';
			
			$html .='<p class="version">'.$version.'<strong>'.$resultThemeInfo->version.'</strong></p>';

			$html .='<p class="last_updated">'.$lastUpdated.'<strong>'.$resultThemeInfo->last_updated.'</strong></p>';

			$releasedDate = key($resultDownloadInfo);

			$html .='<p class="release-date">'.$releaseDate.'<strong>'.$releasedDate.'</strong></p>';

			
			$html .='<p class="total_download">'.$totalDownload.'<strong>'.absint($resultThemeInfo->downloaded).'</strong></p>';

			$html .='<p class="within-day">'.esc_html__( 'within: ', 'wp-theme-statistic' ).$totalresultDownloadInfo.' '.esc_html__( 'Days', 'wp-theme-statistic' ).'</p>';
			
			$html .='<p class="active_installs">'.$activeInsTitle.'<strong>'.absint($resultThemeInfo->active_installs).' + </strong></p>';

			foreach ($resultDownloadInfo as $key => $value) {
				//echo $key.'<br>';
				$downloadSum = $downloadSum + $value;

				// Yesterday Downloading
				if($key == date('Y-m-d',strtotime("-1 days")) ){
					$html .='<p class="yesterday-downloaded">'.$yesterDayDownloadedNumber.'<strong>'.$value.'</strong></p>';
				}
			}

			$html .='<p class="average-downloaded">'.$AverateDownload.'<strong>'.round($downloadSum/$totalresultDownloadInfo, 2).'</strong></p>';

			$html .='<p class="wordpress_version">'.$wordPressVersion.'<strong>'.$resultThemeInfo->requires.' '.esc_html__('or higher','wp-theme-statistic').'</strong></p>';

			$html .='<p class="requires_php">'.$phpVersion.'<strong>'.$resultThemeInfo->requires_php.' '.esc_html__('or higher','wp-theme-statistic').'</strong></p>';
	
			$html .='</div>';

			if(isset($resultThemeInfo->rating)){
				$raing_point = $resultThemeInfo->rating/20;
				$html .='<div class="rating-section" style="float:right">';
				$html .='<div class="average-rating">';
         		$html .='<h4>Ratings</h4>';
        		$html .='<div class="star-ratings-sprite"><span style="width:'.$resultThemeInfo->rating.'%" class="star-ratings-sprite-rating"></span></div>';
        		$html .='<div class="rating-text">'.$raing_point.' <small>'.esc_html__('out of 5 stars','wp-theme-statistic').'</small></div>';
      			$html .='</div>';	
			}
			
			if(isset($resultThemeInfo->ratings)){
				$total_star = $resultThemeInfo->ratings[1] + $resultThemeInfo->ratings[2] + $resultThemeInfo->ratings[3] + $resultThemeInfo->ratings[4] + $resultThemeInfo->ratings[5];
				$start1per = divisibleByZero($resultThemeInfo->ratings[1],$total_star);
				$start2per = divisibleByZero($resultThemeInfo->ratings[2],$total_star);
				$start3per = divisibleByZero($resultThemeInfo->ratings[3],$total_star);
				$start4per = divisibleByZero($resultThemeInfo->ratings[4],$total_star);
				$start5per = divisibleByZero($resultThemeInfo->ratings[5],$total_star);
      			$html .='<div class="ratings-star">';
			    $html .= '<div class="star">5 <span class="glyphicon glyphicon-star"></span></div>';
			    $html .=  '<div class="star wp-statistic">';
			    $html .=  '<div class="wp-statistic-bar wp-statistic-bar-success" role="wp-statistic-bar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="5" style="width:'.$start5per.'%">';		
			    $html .=  '<span class="sr-only">'.$start5per.esc_html__('% Complete (danger)','wp-theme-statistic').'</span>';
			     $html .=  '</div>';
			     $html .=   '</div>';
			     $html .=  '<div class="star ratings">'.$resultThemeInfo->ratings[5].'</div>';
			     $html .=   '</div>';

			    $html .='<div class="ratings-star">';
			    $html .= '<div class="star">4 <span class="glyphicon glyphicon-star"></span></div>';
			    $html .=  '<div class="star wp-statistic">';
			    $html .=  '<div class="wp-statistic-bar wp-statistic-bar-success" role="wp-statistic-bar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="5" style="width:'.$start4per.'%">';		
			    $html .=  '<span class="sr-only">'.$start4per.esc_html__('% Complete (danger)','wp-theme-statistic').'</span>';
			    $html .=  '</div>';
			    $html .=   '</div>';
			    $html .=  '<div class="star ratings">'.$resultThemeInfo->ratings[4].'</div>';
			    $html .=   '</div>';

			    $html .='<div class="ratings-star">';
			    $html .= '<div class="star">3 <span class="glyphicon glyphicon-star"></span></div>';
			    $html .=  '<div class="star wp-statistic">';
			    $html .=  '<div class="wp-statistic-bar wp-statistic-bar-success" role="wp-statistic-bar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="5" style="width:'.$start3per.'%">';		
			    $html .=  '<span class="sr-only">'.$start3per.esc_html__('% Complete (danger)','wp-theme-statistic').'</span>';
			    $html .=  '</div>';
			    $html .=   '</div>';
			    $html .=  '<div class="star ratings">'.$resultThemeInfo->ratings[3].'</div>';
			    $html .=   '</div>';

			    $html .='<div class="ratings-star">';
			    $html .= '<div class="star">2 <span class="glyphicon glyphicon-star"></span></div>';
			    $html .=  '<div class="star wp-statistic">';
			    $html .=  '<div class="wp-statistic-bar wp-statistic-bar-success" role="wp-statistic-bar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="5" style="width:'.$start2per.'%">';		
			    $html .=  '<span class="sr-only">'.$start2per.esc_html__('% Complete (danger)','wp-theme-statistic').'</span>';
			    $html .=  '</div>';
			    $html .=   '</div>';
			    $html .=  '<div class="star ratings">'.$resultThemeInfo->ratings[2].'</div>';
			    $html .=   '</div>';

			    $html .='<div class="ratings-star">';
			    $html .= '<div class="star">1 <span class="glyphicon glyphicon-star"></span></div>';
			    $html .=  '<div class="star wp-statistic">';
			    $html .=  '<div class="wp-statistic-bar wp-statistic-bar-success" role="wp-statistic-bar" aria-valuenow="5" aria-valuemin="0" aria-valuemax="5" style="width:'.$start1per.'%">';		
			    $html .=  '<span class="sr-only">'.$start1per.esc_html__('% Complete (danger)','wp-theme-statistic').'</span>';
			    $html .=  '</div>';
			    $html .=   '</div>';
			    $html .=  '<div class="star ratings">'.$resultThemeInfo->ratings[1].'</div>';
			    $html .=   '</div>';
			    
			}	
			$html .='</div>';
			$html .='<div class="clearFix"></div>';

		}
		return $html;
	}	

	//Theme info wordPress API
	function WP_ThemeInfo_API($attributes){

		$api_params = [
			'slug' => $attributes['slug'], /// <<== searched keyword
			'fields' => [
				'name' => false,
				'author' => false,
				'slug' => true,
				'downloadlink' => false,
				'rating' => true,
				'ratings' => true,
				'downloaded' => true,
				'description' => false,
				'active_installs' => true,
				'short_description' => true,
				'donate_link' => true,
				'tags' => false,
				'sections' => false,
				'homepage' => false,
				'last_updated' => true,
				'compatibility' => true,
				'tested' => true,
				'requires' => true,
				'versions' => false,
				'support_threads' => true,
				'support_threads_resolved' => false,
				'requires_php' => true
			],	
		];
		
		$themes_object = wp_theme_statistic_api( 'theme_information', $api_params );
 
	
		return $themes_object;
	}

	// WOrdPress downloads API
	function WP_Download_API($slug){		

		$url = 'http://api.wordpress.org/stats/themes/1.0/downloads.php?slug='.$slug['slug'].'&limit=600';
	    $http_args = array(
	        'body' => array(
	        'timeout' => 15,
	        )
	    ); 
	     
	    $request = wp_remote_get( $url, $http_args ); 
	 
	    if ( is_wp_error( $request ) ) {
	        
	        return false;
	    }
	
	    return maybe_unserialize( wp_remote_retrieve_body( $request ) );
	}

	function divisibleByZero($resultThemeInfo, $total_star){
		if($total_star != 0){
			$result =($resultThemeInfo/$total_star)*100;
		}
		else{
			$result = 0;
		}
		return $result;
	}

	/**
	 * Makes a call to the WordPress.org Themes API, v1.0
	 * @param string $action Either query_themes (a list of themes), theme_information (Information about a specific theme), hot_tags (List of the most popular theme tags), feature_list (List of valid theme tags)
	 * @param array $api_params
	 * @return object Only the body of the raw response as a PHP object.
	 */
	function wp_theme_statistic_api( $action, $api_params = array() ) {
	    $url = 'https://api.wordpress.org/themes/info/1.0/';
	    $args = (object) $api_params;
	    $http_args = array(
	        'body' => array(
	        'action' => $action,
	        'timeout' => 15,
	        'request' => serialize( $args )
	        )
	    ); 
	     
	    $request = wp_remote_post( $url, $http_args ); 
	 
	    if ( is_wp_error( $request ) ) {
	        
	        return false;
	    }
	 
	    return maybe_unserialize( wp_remote_retrieve_body( $request ) );
	}
}
