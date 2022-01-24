<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use function vprintf;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function favorite_recipes()
    {
        return $this->belongsToMany(Recipe::class)->withPivot(['ingredient_id']);
    }

    public function factories()
    {
        return $this->hasMany(ProductionLine::class);
    }

    public function addFavoriteByName($name)
    {
        if ( ! $recipe = Recipe::ofName($name) )
            return;

        $this->addFavorite($recipe);
    }

    public function addFavorite(Recipe $recipe)
    {
        $this->favorite_recipes()->wherePivot('ingredient_id','=',$recipe->product_id)->detach();
        $this->favorite_recipes()->attach($recipe->id,['ingredient_id' => $recipe->product_id]);
    }

    public function removeFavorite(Recipe $recipe)
    {
        $this->favorite_recipes()->detach($recipe->id);
    }

    public function viewFavorites()
    {
        return $this->favorite_recipes
            ->map(function($recipe) {
                $description = $recipe->description ?? "default";
                $ingredients = $recipe->ingredients
                    ->map(function($ingredient){
                        return "{$ingredient->name} ({$ingredient->pivot->base_qty} ppm)";
                    })->implode(", ");
                return "{$recipe->product->name} [$description] ($recipe->base_per_min ppm) {$ingredients}";
            })
            ->all();
    }
}
