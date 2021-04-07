# API server thực tập tốt nghiệp
## 1. run cmd 
```
npm install
composer install
```
## 2. copy folder <b>.env.example</b> -> <b>.env</b>
## 3. run cmd
```
php artisan key:generate
```
## 4. setting <b>database</b> file <b>.env</b>
## 5. run cmd
```
php artisan passport:install
```
## 6. run cmd
```
php artisan passport:client --paswword
```
### - Enter -> chọn 1, lặp lại -> chọn 2
## 7. open <b>vendor\\laravel\\framework\\src\\Illuminate\\Foundation\\Auth</b>
## 8. Create file <b>Lecturer.php</b> + <b>Student.php</b> (edit class name)
```php
<?php

namespace Illuminate\Foundation\Auth;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class Lecturer extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
}

```
