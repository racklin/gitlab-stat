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

## Usage
Running `gitlab-stat` or `php gitlab-stat`.

Running with first parameter can be used to specify a processing date, default value is yesterday.
Ex: `gitlab-stat 2021-09-01`

------

## Framework
For full documentation, visit [laravel-zero.com](https://laravel-zero.com/).
