<?php
/*
Plugin Name: csv importer
Description: imports csv
Author: Isikcan Yilmaz
*/
//This plugin was originally written by Daniel Huesken (http://danielhuesken.de). It was repurposed/edited for Expressions' needs.
/*
	Copyright (C) 2012 Inpsyde GmbH  (email: info@inpsyde.com)

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
*/
add_action('admin_menu', 'csv_import_register_plugin_page');
add_action('admin_enqueue_scripts', 'csv_import_scripts');
add_action('wp_ajax_csv', 'csv_handler');
function csv_import_register_plugin_page(){
	add_menu_page('CSV_IMPORT_PLUGIN', 'Import Plugin', 'manage_options', 'csv_import_plugin', 'csv_import_form_page');

}

function csv_import_scripts(){
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-ui-mouse');
	wp_enqueue_script('jquery-ui-droppable');
	wp_register_script('csv_import', plugin_dir_url(__FILE__) . 'csv_import.js', false, '1.0');
	wp_enqueue_script('csv_import');
}

function csv_import_form_page(){
	?>
	<input id='upload_input' type='file' accept=".csv" required='required' multiple>
	<input class='btn' type='submit' value='Go'>


	<?php
	//reader_handler();
	/*$file_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'asdasd.csv';
	$rawfile = file_get_contents($file_path);
	$file = str_getcsv($rawfile);
	for ($i = 0; $i <= count($file); $i++){
		echo '<p>' . $file[$i] .'  '. $i . '</p>';
	}*/

}
//      0           1              2             3              4                   5                          6                             7                                 8                    9
// ['article', 'article_name', 'sections', academics', 'classifications', 'geo-spatial coverages', 'historical coverages', 'research sample characteristics', 'types, methods, approaches', 'contributors']
function csv_handler(){ 
	$csv_file = $_REQUEST['file'];
	//$decoded_csv = csv_decoder($csv_file);
	$decoded_csv = str_getcsv($csv_file, '	');
	$ldiv = 11;
	$articles = array(); // articles[0] == index

	//arrange the posts in array
	for ($i = 0; $i < count($decoded_csv) / $ldiv; $i++){
		$temp_array = array();
		for ($o = 0; $o < $ldiv; $o++){
			$selected_cell = str_replace("\\\"", "", $decoded_csv[$o + ($i * $ldiv)]);
			if ($o != 0 && substr_count($selected_cell, ';') > 0){
				$selected_cell = explode(';', $selected_cell);
			}
			if ($i > 0 && $o > 1 && !is_array($selected_cell)){
				$selected_cell = array($selected_cell);
			}
			$temp_array[] = $selected_cell;
		}
		array_push($articles, $temp_array);
	}

	//insert the posts and terms if the terms were not used before.
	for ($i = 1; $i < count($articles); $i++){
		$selected_article = $articles[$i];
		$post = array(
			'post_content' => $selected_article[0],
			'post_title' => $selected_article[1],
			//'post_status' => 'inherit',
			'post_date' => date_time(),
			'post_date_gmt' => date_time(),
			'post_type' => 'journal_article',
			'tax_input' => array(
				'journal_sections' => term_inserter($selected_article[2], 'journal_sections'),//$selected_article[2],  //todo butun termleri id lerine cevir;
				'journal_academics' => term_inserter($selected_article[3], 'journal_academics'),
				'journal_classification' => term_inserter($selected_article[4], 'journal_classification'),
				'journal_geospatial_coverage' => term_inserter($selected_article[5], 'journal_geospatial_coverage'),
				'journal_historical_coverage' => term_inserter($selected_article[6], 'journal_historical_coverage'),
				'journal_research_sample' => term_inserter($selected_article[7], 'journal_research_sample'),
				'journal_type_method_approach' => term_inserter($selected_article[8], 'journal_type_method_approach'),
				'journal_contributors' => term_inserter($selected_article[9], 'journal_contributors')
				)

			);

		print_r(wp_insert_post($post));
		//print_r(term_inserter(array('aaca4', 'aaca5', 'aaca6'), 'journal_academics'));
		
	}
	print_r($articles);
	
	exit();
}

function date_time(){
	$date_time = getdate();
	$date_arr = array($date_time['year'], $date_time['mon'], $date_time['mday'], $date_time['hours'], $date_time['minutes'], $date_time['seconds']);
	for($i = 0; $i < count($date_arr); $i++){
		if($date_arr[$i] < 10){
			$date_arr[$i] = '0' . $date_arr[$i];
		}
	}
	return $date_arr[0] . '-' . $date_arr[1] . '-' . $date_arr[2] . ' ' . $date_arr[3] . ':' . $date_arr[4] . ':' . $date_arr[5];
}

//
function term_inserter($term, $taxonomy){
	/*$termid = get_term_by( 'slug', $term, $taxonomy );
	if ($termid == ''){
		$termid = wp_insert_term($term, $taxonomy);
		$termid = $termid['term_id'];
		return $termid;
	}else{
		return $termid->term_id;
	}*/
	if (count($term) == 0){
		return '';
	}else{
		$term_array = array();
		for ($i = 0; $i < count($term); $i++){
			$selected_term = get_term_by( 'slug', $term[$i], $taxonomy );
			if ($selected_term == ''){
				$selected_term = wp_insert_term($term[$i], $taxonomy);
				$selected_term = $selected_term['term_id'];
			}else{
				$selected_term = $selected_term->term_id;
			}
			$term_array[] = $selected_term;			
			
		}
		return $term_array;
	}
	
}