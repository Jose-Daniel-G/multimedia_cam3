<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles; //añadida no especificada en el curso
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles; //añadida no especificada en el curso
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable, AuthenticationLoggable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];
    public function isActive()
    {
        return $this->status == 1; // O usa 'is_active' si ese es el nombre del campo
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];
    public function adminlte_image()
    {
        return url($this->profile_photo_url);
        // return 'https://picsum.photos/300/300';
    }
    public function adminlte_desc()
    {
        return 'Administradr';
    }
    public function adminlte_profile_url()
    {
        return url('user/profile');
    }
    // Relacion Uno a Muchos
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
