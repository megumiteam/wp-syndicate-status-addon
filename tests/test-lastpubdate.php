<?php

class WSLP_Test extends WP_UnitTestCase {
	private $action;
	private $feed = array(
					'lastpubdate-1' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/lastpubdate/lastpubdate-1.xml',
					'lastpubdate-2' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/lastpubdate/lastpubdate-2.xml',
					'lastpubdate-3' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/lastpubdate/lastpubdate-3.xml',
					'lastpubdate-4' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/lastpubdate/lastpubdate-4.xml',
					'lastpubdate-5' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/lastpubdate/lastpubdate-5.xml',
					'no-lastpubdate' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/lastpubdate/no-lastpubdate.xml',
					'typo' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/lastpubdate/typo.xml'
	);
	
	public function setUp() {
		parent::setUp();
		$this->action = new WP_SYND_Action();
	}

	/**
	 * @lastpubdateの境界値テスト
	 */	
	public function testlastpubdate() {
		//記事を新規投入 lastpubdate Fri, 26 Aug 2014 12:00:00 +0900
		$key = 'lastpubdate';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['lastpubdate-1']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'lastpubdate-1', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		$this->assertEquals( 'Fri, 26 Aug 2014 12:00:00 +0900', get_post_meta( $post->ID, 'wp_syndicate_lastpubdate', true ) );
		
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['lastpubdate-2'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'lastpubdate-2', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		$this->assertEquals( 'Fri, 26 Aug 2014 12:00:01 +0900', get_post_meta( $post->ID, 'wp_syndicate_lastpubdate', true ) );
		
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['lastpubdate-3'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'lastpubdate-2', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		$this->assertEquals( 'Fri, 26 Aug 2014 12:00:01 +0900', get_post_meta( $post->ID, 'wp_syndicate_lastpubdate', true ) );

		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['lastpubdate-4'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'lastpubdate-2', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		$this->assertEquals( 'Fri, 26 Aug 2014 12:00:01 +0900', get_post_meta( $post->ID, 'wp_syndicate_lastpubdate', true ) );
		
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['lastpubdate-5'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'lastpubdate-5', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		$this->assertEquals( 'Fri, 26 Aug 2014 12:00:02 +0900', get_post_meta( $post->ID, 'wp_syndicate_lastpubdate', true ) );
		
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['no-lastpubdate'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'lastpubdate-5', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		$this->assertEquals( 'Fri, 26 Aug 2014 12:00:02 +0900', get_post_meta( $post->ID, 'wp_syndicate_lastpubdate', true ) );
	}
	
	/**
	 * @lastPubDateでもlastpubDateでも取り込まれるテスト
	 */
	public function test_D_or_d() {
		$key = 'lastpubdate';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['typo'] );
		$this->action->import($post_id);
		
		$post = get_page_by_path( sanitize_title($key.'_typo-1'), OBJECT, 'post' );
		$this->assertEquals( 'lastpubdate-typo-1', $post->post_title );
		$this->assertEquals( 'Fri, 26 Aug 2014 12:30:02 +0900', get_post_meta( $post->ID, 'wp_syndicate_lastpubdate', true ) );
		
		$post = get_page_by_path( sanitize_title($key.'_typo-2'), OBJECT, 'post' );
		$this->assertEquals( 'lastpubdate-typo-2', $post->post_title );
		$this->assertEquals( 'Fri, 26 Aug 2014 15:00:02 +0900', get_post_meta( $post->ID, 'wp_syndicate_lastpubdate', true ) );
	}

	function add_post_meta( $post_id, $feed_url ) {
		add_post_meta( $post_id, 'wp_syndicate-feed-url', $feed_url );
		add_post_meta( $post_id, 'wp_syndicate-feed-retrieve-term', 5 );
		add_post_meta( $post_id, 'wp_syndicate-author-id', 1 );
		add_post_meta( $post_id, 'wp_syndicate-default-post-status', 'publish' );
		add_post_meta( $post_id, 'wp_syndicate-default-post-type', 'post' );
		add_post_meta( $post_id, 'wp_syndicate-registration-method', 'insert-or-update' );
	}
}

