<?php
/**
	Plugin Name: ACF Page Grandchildren
	Plugin URI: http://www.navz.me
	Description: A great way to show group of fields only on grandchildren pages
	Version: 1.0.1
	Author: Navneil Naicer
	Author URI: http://www.navz.me
	License: GPLv2 or later
	
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
	
	Copyright 2016 Navneil Naicker

*/

//Preventing from direct access
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class acf_page_grandchildren{
	
	public function __construct(){
		add_filter('acf/location/rule_types', array($this, 'acf_location_rules_types'));
		add_filter('acf/location/rule_values/grandchildren', array($this, 'acf_location_rules_values_page'));
		add_filter('acf/location/rule_match/grandchildren', array($this, 'acf_rule_match_grandchild'), 10, 3);
	}
	
	public function acf_location_rules_types( $choices ){
		$choices['Page']['grandchildren'] = 'Page Grandchildren';
		return $choices;
	}
	
	public function acf_location_rules_values_page( $choices ){
		$posts = get_posts(array(
			'posts_per_page' => -1,
			'post_type' => 'page',
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'post_status' => 'any',
			'suppress_filters' => false,
			'update_post_meta_cache' => false
		));
		
		if( $posts ){
			// sort into hierachial order!
			if( is_post_type_hierarchical( 'page' )){ $posts = get_page_children( 0, $posts ); }
			foreach( $posts as $page ){
				$title = '';
				$ancestors = get_ancestors($page->ID, 'page');
				if($ancestors){
					foreach($ancestors as $a){
						$title .= '- ';
					}
				}
				$title .= apply_filters( 'the_title', $page->post_title, $page->ID );
				// status
				if($page->post_status != "publish"){
					$title .= " ($page->post_status)";
				}	
				$choices[ $page->ID ] = $title;
			}
		}
		return $choices;
	}

	public function acf_rule_match_grandchild( $match, $rule, $options ){
		if( empty($options['post_id']) || 'page' !== get_post_type( $options['post_id']) ) return false;
		$parent = get_ancestors( $options['post_id'], 'page' );
		$grandparent = !empty($parent[1])? $parent[1]: null;
		if( $grandparent ){
			$is_grandparent = ($rule['value'] == $grandparent)? $grandparent: null;
			if ( '==' == $rule['operator'] ) { 
				$match = $is_grandparent;
			} elseif ( '!=' == $rule['operator'] ) {
				$match = ! $is_grandparent;
			}
		}
		return $match;
	}

}

new acf_page_grandchildren;
