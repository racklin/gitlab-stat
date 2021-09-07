# gitlab-stat
gitlab-stat gets commits for all users on gitlab.

## Settings
1. Edit `config/gitlabstat.php` for your url,token and timezone.
```php
<?php

return [
    'gitlab_url' => '', // your self-hosting gitlab or empty for gitlab.com
    'gitlab_token' => '', // your gitlab api token
    'timezone' => 'Asia/Shanghai'
];
```

## From source code 
Execute `gitlab-stat fetch` or `php gitlab-stat fetch`.

With first parameter can be used to specify a processing date, default value is yesterday.
Ex: `gitlab-stat fetch 2021-09-01`

## From PHAR
You can using `--config` option to specify config file.
For example:
 `./gitlab-stat fetch --config=/home/rack/gitlabstat.php`.

------

## Framework
For full documentation, visit [laravel-zero.com](https://laravel-zero.com/).
