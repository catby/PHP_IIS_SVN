・SVN/
	SubversionをIIS経由で参照、編集する為のファイルを格納
	IISの404エラーを"SVN/svn_mgr.php"にリダイレクトする様に設定すると、ApacheでSubversionを使用する場合と同じ様な振る舞いができる。

・cat1/
	Subversionに格納されているドキュメントを視覚的に視認しやすい形で表示する為のファイルを格納
	"index.php"と"toppage.css"を別フォルダにコピーし、パスの情報だけ書き換える新たなページを作成可能

・PHP/
	上記2フォルダで共通で使用するPHPファイルを格納

・img/
	ホームページで表示する画像ファイルを格納

・userAuth/
	SubversionにCommitする際のユーザー認証に使用するファイルを格納

・userInfo/
	SubversionにCommitする際のユーザー認証情報を格納


・ScreenShot1.png
	"cat1/"のページを開いた状態のScreenShot

・ScreenShot2.png
	"cat1/"ページの「機能仕様」サブカテゴリの「編集」ページを開いた状態のScreenShot

・ScreenShot3.png
	"cat1/"ページの「ABC要求仕様書」の「変更履歴」ページを開いた状態のScreenShot
