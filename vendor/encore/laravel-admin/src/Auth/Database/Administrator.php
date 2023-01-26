<?php

namespace Encore\Admin\Auth\Database;

use App\Models\ActivityReport;
use App\Models\CaseModel;
use App\Models\Enterprise;
use App\Models\Location;
use App\Models\StudentHasClass;
use App\Models\Utils;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;
    use HasPermissions;
    use DefaultDatetimeFormat;
    use Notifiable;

    protected $fillable = ['username', 'password', 'name', 'avatar'];

    public function reports()
    {
        return $this->hasMany(ActivityReport::class, 'reported_by');
    }

    public function cases()
    {
        return $this->hasMany(CaseModel::class);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {
            $m->username = $m->email;
            $m->name = $m->first_name . " " . $m->middle_name . " " . $m->last_name;

            $m->phone_number_1 = Utils::prepare_phone_number($m->phone_number_1);
            $m->phone_number_2 = Utils::prepare_phone_number($m->phone_number_2);

            $m->district_id = 1;
            if ($m->sub_county_id != null) {
                $sub = Location::find($m->sub_county_id);
                if ($sub != null) {
                    $m->district_id = $sub->parent;
                }
            }
        });

        self::created(function ($model) {
            //created
        });

        self::updating(function ($m) {

            $m->district_id = 1;
            if ($m->sub_county_id != null) {
                $sub = Location::find($m->sub_county_id);
                if ($sub != null) {
                    $m->district_id = $sub->parent;
                }
            }

            $m->username = $m->email;
            $m->phone_number_1 = Utils::prepare_phone_number($m->phone_number_1);
            $m->phone_number_2 = Utils::prepare_phone_number($m->phone_number_2);
            //$m->name = $m->first_name . " " . $m->middle_name . " " . $m->last_name;

            return $m;
        });

        self::updated(function ($model) {
            // ... code here
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }


    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    /**
     * Get avatar attribute.
     *
     * @param string $avatar
     *
     * @return string
     */
    public function getAvatarAttribute($avatar)
    {
        if ($avatar == null || strlen($avatar) < 3) {
            $default = url('assets/logo.png');

            //return $default;
        }
        $avatar = str_replace('images/', '', $avatar);
        $link = 'storage/images/' . $avatar;

        if (!file_exists(public_path($link))) {
            //dd($avatar);
            //$link = 'assets/logo.png';
        }
        return url($link);
    }


    /**
     * A user has and belongs
     * to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    public function enterprise()
    {
        $e = Enterprise::find($this->enterprise_id);
        if ($e == null) {
            $this->enterprise_id = 1;
            $this->save();
        }
        return $this->belongsTo(Enterprise::class);
    }

    public function classes()
    {
        return $this->hasMany(StudentHasClass::class);
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function sendPasswordResetCode()
    {

        Mail::send('email_view', [], function ($m) {

            $m->to("muhindo@8technologies.net", $this->name)
            ->subject('Email Subject!');
        });

        die($this->email);
        return $this->getKey();
    }



    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
