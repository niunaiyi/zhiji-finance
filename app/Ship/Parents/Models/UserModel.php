<?php

namespace App\Ship\Parents\Models;

use Apiato\Core\Models\UserModel as AbstractUserModel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

abstract class UserModel extends AbstractUserModel implements MustVerifyEmail
{
    use Notifiable;
    use HasRoles;
}
