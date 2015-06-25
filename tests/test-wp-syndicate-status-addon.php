<?php

class WSSA_Test extends WP_UnitTestCase {
	private $action;
	private $feed = array(
						'create-100' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/status/create-100.xml',
						'update-100' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/status/update-100.xml',
						'delete-100' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/status/delete-100.xml',
						'no-status-100' => 'https://raw.githubusercontent.com/horike37/wp-syndicate-test-data/master/status/no-status-100.xml',
	);
	
	public function setUp() {
		parent::setUp();
		$this->action = new WP_SYND_Action();
	}

	/**
	 * @記事の新規投入処理
	 */	
	public function testInsert() {
		
		//createステータスでの新規投稿：そのまま記事をインサートして公開
		$key = 'create';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['create-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title('create_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'create-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );

		//updateステータスでの新規投稿：そのまま記事をインサートして公開
		$key = 'update';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['update-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title('update_100'), OBJECT, 'post' );

		$this->assertEquals( 2, count($posts) );
		$this->assertEquals( 'update-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//deleteステータスでの新規投稿：無視
		$key = 'delete';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['delete-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title('delete_100'), OBJECT, 'post' );

		$this->assertEquals( 2, count($posts) );
		$this->assertEquals( null, $post );
		
		//ステータスタグが存在していない場合：updateとしてデータを取り込む
		$key = 'no-status';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['no-status-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title('no-status_100'), OBJECT, 'post' );

		$this->assertEquals( 3, count($posts) );
		$this->assertEquals( 'no-status-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
	}

	/**
	 * @記事の更新処理（当該記事のステータスが「公開済み」の場合）
	 */	
	public function testUpdateForPublish() {
		//記事を新規投入
		$key = 'testUpdateForPublish';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['create-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'create-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//statusが'update'の場合 ⇒ そのまま記事を更新。
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['update-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'update-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );

		//statusが'create'の場合 ⇒ 何もしない
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['create-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );

		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'update-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );		
		
		//statusが'delete'の場合 ⇒ 記事のステータスを「非公開」に変更
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['delete-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'private') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );

		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'update-100', $post->post_title );
		$this->assertEquals( 'private', $post->post_status );
		$this->assertEquals( 'delete', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//記事を新規投入
		$key = 'testUpdateForPublish2';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['create-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'create-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//statusが存在しない場合 ⇒ statusが'update'の場合と同様の処理
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['no-status-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'no-status-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
	}

	/**
	 * @記事の更新処理（当該記事のステータスが「下書き(draft)」の場合）
	 */	
	public function testUpdateForDraft() {
		//記事を新規投入
		$key = 'testUpdateForDraft';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['create-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'create-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//記事を下書き(draft)へ変更
		wp_update_post( array( 'ID' => $post->ID, 'post_title' => 'draft-100', 'post_status' => 'draft' ) );
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'draft') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );

		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'draft-100', $post->post_title );
		$this->assertEquals( 'draft', $post->post_status );
		
		//statusが'create'の場合 ⇒ 何もしない
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['create-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'draft') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );

		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'draft-100', $post->post_title );
		$this->assertEquals( 'draft', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );

		//statusが'update'の場合 ⇒ そのまま記事を更新。ただし記事ステータスは「下書き(draft)」のまま。
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['update-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'draft') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'update-100', $post->post_title );
		$this->assertEquals( 'draft', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );

		//statusが'delete'の場合 ⇒ 記事のステータスを「非公開」に変更
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['delete-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'private') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );

		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'update-100', $post->post_title );
		$this->assertEquals( 'private', $post->post_status );
		$this->assertEquals( 'delete', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//記事を新規投入
		$key = 'testUpdateForDraft2';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['create-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'create-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//記事を下書き(draft)へ変更
		wp_update_post( array( 'ID' => $post->ID, 'post_title' => 'draft-100', 'post_status' => 'draft' ) );
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'draft') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );

		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'draft-100', $post->post_title );
		$this->assertEquals( 'draft', $post->post_status );
		
		//statusが存在しない場合 ⇒ statusが'update'の場合と同様の処理
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['no-status-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'draft') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'no-status-100', $post->post_title );
		$this->assertEquals( 'draft', $post->post_status );
		$this->assertEquals( 'update', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
	}

	/**
	 * @記事の更新処理（当該記事のステータスが「非公開(private)」の場合）
	 */	
	public function testUpdateForPrivate() {
		//記事を新規投入
		$key = 'testUpdateForPrivate';
		$post_id = $this->factory->post->create(array('post_type' => 'wp-syndicate', 'post_name' => $key));
		$this->add_post_meta($post_id, $this->feed['create-100']);
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50) );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'create-100', $post->post_title );
		$this->assertEquals( 'publish', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//記事を非公開(private)へ変更
		wp_update_post( array( 'ID' => $post->ID, 'post_title' => 'private-100', 'post_status' => 'private' ) );
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'private') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );

		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'private-100', $post->post_title );
		$this->assertEquals( 'private', $post->post_status );
		
		//statusが'update'の場合 ⇒ 何もしない
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['update-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'private') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'private-100', $post->post_title );
		$this->assertEquals( 'private', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );

		//statusが'create'の場合 ⇒ 何もしない
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['create-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'private') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );

		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'private-100', $post->post_title );
		$this->assertEquals( 'private', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
		
		//statusが存在しない場合 ⇒ statusが'update'の場合と同様の処理
		update_post_meta( $post_id, 'wp_syndicate-feed-url', $this->feed['no-status-100'] );
		$this->action->import($post_id);
		$posts = get_posts( array('posts_per_page' => 50, 'post_status' => 'private') );
		$post = get_page_by_path( sanitize_title($key.'_100'), OBJECT, 'post' );
		
		$this->assertEquals( 1, count($posts) );
		$this->assertEquals( 'private-100', $post->post_title );
		$this->assertEquals( 'private', $post->post_status );
		$this->assertEquals( 'create', get_post_meta( $post->ID, 'wp_syndicate_status', true ) );
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

