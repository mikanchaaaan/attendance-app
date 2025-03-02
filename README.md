# 勤怠管理アプリ

## 環境構築
### Dockerビルド
1. ```git clone git@github.com:mikanchaaaan/attendance-app.git```
2. ```docker-compose up -d --build```
※ MySQLは、OSによって起動しない場合があるためそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。

### Laravel環境構築
1. ```docker-compose exec php bash```
2. ```composer install```
3. ```cp -p .env.example .env```
4. envの環境変数を変更（[環境変数](#環境変数env)参照）
5. ```php artisan key:generate```
6. ```php artisan migrate```
7. ```php artisan db:seed```

### 環境変数（.env）
| 変数名              | 値                                         | 備考                                    |
| ------------------- | ------------------------------------------ | --------------------------------------- |
| DB_CONNECTION       | mysql                                      | 接続するデータベース接続名                    |
| DB_HOST       　　　 | mysql                                      | 接続するデータベース                    |
| DB_PORT             | 3306                                      | データベース接続時に利用するポート                  |
| DB_DATABASE         | docker-compose.ymlの「MYSQL_DATABASE」参照 | 接続するデータベース名                  |
| DB_USERNAME         | docker-compose.ymlの「MYSQL_USER」参照     | データベースに接続時のユーザー名        |
| DB_PASSWORD         | docker-compose.ymlの「MYSQL_PASSWORD」参照 | データベースに接続時のパスワード        |
| SESSION_ADMIN_DRIVER| database                                  | 管理者ユーザで使用するセッションの保管場所     |
| SESSION_TABLE       | sessions                                  | 一般ユーザで使用するセッションテーブル名        |
| SESSION_TABLE_ADMIN | admin_sessions                            | 管理者ユーザで使用するセッションテーブル名        |
| SESSION_COOKIE_USER | user_session                              | 一般ユーザで使用するクッキー名        |
| SESSION_COOKIE_ADMIN | admin_session                            | 管理者ユーザで使用するクッキー名      |
| MAIL_MAILER       | smtp                                        | メール認証で使用するサービス      |
| MAIL_HOST         | mailhog                                     | メール認証時に使用するホスト名      |
| MAIL_PORT         | 1025                                        | メール認証時に使用するポート      |
| MAIL_FROM_ADDRESS | ```hello@example.com```                     | メール認証時の送信元メールアドレス      |

### 使用技術
* PHP 8.3.17
* Laravel Framework 11.38.2
* nginx 1.21.1
* MySQL 8.0.26
* mailhog latest

### URL
* 開発環境：
  * 一般ユーザ：```http://localhost/login```
  * 管理者ユーザ：```http://localhost/admin/login```
* phpMyAdmin：```http://localhost:8080/```
* Mailhog：```http://localhost:8025/```

### 接続ユーザ情報
#### 管理者ログイン情報
* メールアドレス：```admin@example.com```
* パスワード：```p@ssw0rd!1234```

#### 勤怠データ確認用一般ユーザログイン情報（過去180日分の勤怠データが参照可能）
* メールアドレス：```test@example.com```
* パスワード：```coachtech1106```　<br>
※メール認証画面が表示された場合は、メールを再送して認証してください。

## テスト
#### テスト用DB構築
1. ```docker-compose exec mysql bash```
2. ```mysql -u root -p```（rootのパスワードを入力する）
3. ```CREATE DATABASE demo_test;```
4. ```SHOW DATABASES;```
5. ```demo_test```のデータベースが作成されていることを確認し、```exit```を2回実行してMySQLから抜ける。

### PHP Unitテスト
#### 環境構築
1. ```docker-compose exec php bash```
2. ```cp -p .env .env.testing```
3. .env.exampleの環境変数を変更（[テスト用環境変数](#テスト用環境変数)参照）
4. ```php artisan key:generate --env=testing```
5. ```php artisan migrate --env=testing```

#### テスト実行
※ 各テスト内容は案件シートの[テストケース一覧]シート参照。
1. ```vendor/bin/phpunit tests/Feature/UserRegister.php```
2. ```vendor/bin/phpunit tests/Feature/UserLogin.php```
3. ```vendor/bin/phpunit tests/Feature/AdminLogin.php```
4. ```vendor/bin/phpunit tests/Feature/GetDate.php```
5. ```vendor/bin/phpunit tests/Feature/GetAttendanceStatus.php```
6. ```vendor/bin/phpunit tests/Feature/SetClockIn.php```
7. ```vendor/bin/phpunit tests/Feature/SetRest.php```
8. ```vendor/bin/phpunit tests/Feature/SetClockOut.php```
9. ```vendor/bin/phpunit tests/Feature/GetUserAttendanceList.php```
10. ```vendor/bin/phpunit tests/Feature/GetUserAttendanceDetail.php```
11. ```vendor/bin/phpunit tests/Feature/SetUserAttendanceRequest.php```
12. ```vendor/bin/phpunit tests/Feature/GetAdminAttendanceList.php```
13. ```vendor/bin/phpunit tests/Feature/GetAdminAttendanceDetail.php```
14. ```vendor/bin/phpunit tests/Feature/SetAdminAttendanceRequest.php```


### テスト用環境変数（.env.testing)（コメントアウトされている値については解除してください）
| 変数名              | 値                                              | 備考                                                                |
| ------------------- | ----------------------------------------------- | ------------------------------------------------------------------- |
| APP_ENV             | testing(.env.testingの場合)                     | PHP Unitテスト時に接続する環境名                                    |
| APP_KEY             | 既存の値を削除する                              | Laravelアプリケーションの暗号化に使用されるキー。再作成するため削除 |
| DB_CONNECTION       | mysql                                      | 接続するデータベース接続名                    |
| DB_HOST             | mysql                                           | 接続するデータベース                                                |
| DB_PORT             | 3306                                      | データベース接続時に利用するポート                  |
| DB_DATABASE         | demo_test                                    | 接続するデータベース名                                              |
| DB_USERNAME         | root                                            | データベースに接続時のユーザー名                                    |
| DB_PASSWORD         | docker-compose.ymlの「MYSQL_ROOT_PASSWORD」参照 | データベースに接続時のパスワード                                    |
| SESSION_ADMIN_DRIVER| database                                  | 管理者ユーザで使用するセッションの保管場所     |
| SESSION_TABLE       | sessions                                  | 一般ユーザで使用するセッションテーブル名        |
| SESSION_TABLE_ADMIN | admin_sessions                            | 管理者ユーザで使用するセッションテーブル名        |
| SESSION_COOKIE_USER | user_session                              | 一般ユーザで使用するクッキー名        |
| SESSION_COOKIE_ADMIN | admin_session                            | 管理者ユーザで使用するクッキー名      |
| MAIL_MAILER       | smtp                                        | メール認証で使用するサービス      |
| MAIL_HOST         | mailhog                                     | メール認証時に使用するホスト名      |
| MAIL_PORT         | 1025                                        | メール認証時に使用するポート      |
| MAIL_FROM_ADDRESS | ```hello@example.com```                     | メール認証時の送信元メールアドレス      |
