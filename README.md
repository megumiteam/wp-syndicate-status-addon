# wp-syndicate-status-addon
##
[WP Syndicate](https://github.com/horike37/WP-Syndicate)プラグインのアドオンです。
以下のstatusタグを仕様に基づいて処理します。
* <status>create</status>:新規
* <status>update</status>:更新
* <status>delete</status>:削除

## 仕様
### 記事の新規投入処理
* statusが'create'の場合 ⇒ そのまま記事をインサートして公開。
* statusが'update'の場合 ⇒ そのまま記事をインサートして公開。
* statusが'delete'の場合 ⇒ 無視
* statusが存在しない場合 ⇒ statusが'update'の場合と同様の処理

### 記事の更新処理
* ポータルサイト側の当該記事のステータスが「公開済み」の場合
 - statusが'create'の場合 ⇒ 何もしない（記事内容が初回取り込み時から変更がないので）
 - statusが'update'の場合 ⇒ そのまま記事を更新。
 - statusが'delete'の場合 ⇒ 記事のステータスを「非公開」に変更
 - statusが存在しない場合 ⇒ statusが'update'の場合と同様の処理
* ポータルサイト側の当該記事のステータスが「下書き or レビュー待ち」の場合
 - statusが'create'の場合 ⇒ 何もしない（記事内容が初回取り込み時から変更がないので）
 - statusが'update'の場合 ⇒ そのまま記事を更新。ただし記事ステータスは「下書き or レビュー待ち」のまま。
 - statusが'delete'の場合 ⇒ 記事のステータスを「非公開」に変更
 - statusが存在しない場合 ⇒ statusが'update'の場合と同様の処理
* ポータルサイト側の当該記事のステータスが「非公開」の場合
 - statusの値が何であれ、いっさい更新を受け付けない。
