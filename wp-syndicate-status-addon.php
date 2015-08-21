<?php 
/*
Plugin Name: WP Syndicate Status Addon
Plugin URI: http://digitalcube.jp
Description: WP Syndicateプラグインにてstatusタグの取り扱いを可能にします。
Author: Digitalcube
Version: 1.0
Author URI: http://digitalcube.jp


Copyright 2015 Digitalcube (email : info@digitalcube.jp)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_filter( 'wp_syndicate_is_skip', function( $is_skip, $item, $updated, $set_post_id ){
	
	//更新対象の記事が非公開であれば一切の更新を受け付けない
	if ( $updated ) {
		$post = get_post($set_post_id);
		if ( !empty($post) && is_object($post) && $post->post_status == 'private' ) {
			return true;
		}
	}

	$lastpubdate = $item->get_item_tags('', 'lastpubdate');
	if ( $updated ) {
		if ( is_array( $lastpubdate ) && count( $lastpubdate ) > 0 && !empty($lastpubdate[0]['data']) ) {
			$lastpuddate_single = get_post_meta( $set_post_id, 'wp_syndicate_lastpubdate', true );
			
			//lastpubdateが更新対象記事のlastpubdate日時以下の場合は、処理をスキップ
			if ( strtotime( $lastpubdate[0]['data'] ) <= strtotime( $lastpuddate_single ) ) {
				return true;
			}
		} else {
			//lastpubdateタグが存在しておらず、更新の場合は処理をスキップ
			return true;
		}
	}
	$status = $item->get_item_tags('', 'status');	
	//deleteの時は更新であればprivateへ変更してスキップ
	if ( is_array( $status ) && count( $status ) > 0 && $status[0]['data'] == 'delete' ) {
		if ( $updated ) {
			update_post_meta( $set_post_id, 'wp_syndicate_status', 'delete' );
			wp_update_post( array( 'ID' => $set_post_id, 'post_status' => 'private' ) );
			update_post_meta( $set_post_id, 'wp_syndicate_lastpubdate', $lastpubdate[0]['data'] );
		}
		return true;
	}
	
	//createの時は更新処理があると何もしない
	if ( is_array( $status ) && count( $status ) > 0 && $status[0]['data'] == 'create' && $updated ) {
			return true;
	}
	return $is_skip;
}, 10, 4 );

//statusタグのDBへの保存
add_action( 'wp_syndicate_save_post', function( $update_post_id, $item ){
	$status = $item->get_item_tags('', 'status');
	if ( is_array( $status ) && count( $status ) > 0 ) {
		$old_status = get_post_meta( $update_post_id,  'wp_syndicate_status', true );
		if ( empty($old_status) ) {
			update_post_meta( $update_post_id, 'wp_syndicate_status', $status[0]['data'] );
		} elseif ( $status[0]['data'] == 'update' ) {
			update_post_meta( $update_post_id, 'wp_syndicate_status', 'update' );
		}
	} else {
		update_post_meta( $update_post_id, 'wp_syndicate_status', 'update' );
	}
}, 10, 2 );

//lastpubdaeタグのDBへの保存
add_action( 'wp_syndicate_save_post', function( $update_post_id, $item ){
	$lastpubdate = $item->get_item_tags('', 'lastpubdate');
	if ( !is_array( $lastpubdate ) || count( $lastpubdate ) === 0 ) {
		$lastpubdate = $item->get_item_tags('', 'lastpubDate');	
	}

	if ( !is_array( $lastpubdate ) || count( $lastpubdate ) === 0 ) {
		$lastpubdate = $item->get_item_tags('', 'lastPubDate');	
	}
	
	if ( is_array( $lastpubdate ) && count( $lastpubdate ) > 0 ) {
		update_post_meta( $update_post_id, 'wp_syndicate_lastpubdate', $lastpubdate[0]['data'] );
	} else {
		update_post_meta( $update_post_id, 'wp_syndicate_lastpubdate', mysql2date('D, d M Y H:i:s +0900', date_i18n('Y-m-d H:i:s'), false) );
	}
}, 10, 2 );