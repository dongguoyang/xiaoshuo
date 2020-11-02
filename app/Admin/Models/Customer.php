<?php


namespace App\Admin\Models;

use Illuminate\Support\Facades\Hash;
use Encore\Admin\Auth\Database\HasPermissions;
use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Logics\Repositories\src\StatisticRepository;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Customer extends Model implements AuthenticatableContract {
    use Authenticatable, AdminBuilder, HasPermissions;
    protected $table = 'customers';
    // protected $fillable = ['username', 'password', 'name', 'avatar'];
    protected $fillable = [
        'pid', 'username', 'password', 'name', 'avatar', 'remember_token', 'tel', 'qq', 'wexin', 'truename', 'company', 'addr', 'email', 'status', 'updated_at', 'created_at', 'web_info', 'web_tpl',
        'balance', 'recharge_money', 'cash_money', 'cashing_num', 'cashed_num', 'bank_name', 'bank_info', 'bank_no','group_id'
    ];
    public $fields = [
        'id', 'pid', 'username', 'password', 'name', 'avatar', 'remember_token', 'tel', 'qq', 'wexin', 'truename', 'company', 'addr', 'email', 'status', 'updated_at', 'created_at', 'web_info', 'web_tpl',
        'balance', 'recharge_money', 'cash_money', 'cashing_num', 'cashed_num', 'bank_name', 'bank_info', 'bank_no','group_id'
    ];
    protected $hidden = ['password'];

    public function pCustomer() {
        return $this->belongsTo(Customer::class, 'pid', 'id');
    }

    public function gCustomer() {
        return $this->belongsTo(Customer::class, 'group_id', 'id');
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
        if (url()->isValidUrl($avatar)) {
            return $avatar;
        }

        $disk = config('admin.upload.disk');

        if ($avatar && array_key_exists($disk, config('filesystems.disks'))) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        $default = config('admin.default_avatar') ?: '/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg';

        return admin_asset($default);
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles() : BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions() : BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }

    public function setCreatedAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {
            $value = strtotime($value);
        }
        $this->attributes['created_at'] = $value;
    }
    public function setUpdatedAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {
            $value = strtotime($value);
        }
        $this->attributes['updated_at'] = $value;
    }
    /*
    public function users() {
        return $this->hasMany(User::class, 'customer_id', 'id');
    }
    public function getPasswdAttribute($password) {
        return '';
    }
    */
    public function setPasswordAttribute($password) {
        if(!empty($password) && preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*?[#?!@$%^&*\.\|\~\(\)\-\_\+\=\{\}\[\]:;\'\"\/\\\,])[A-Za-z\d#?!@$%^&*\.\|\~\(\)\-\_\+\=\{\}\[\]:;\'\"\/\\\,]{6,18}$/', $password)) {
            $this->attributes['password'] = Hash::make($password);
        } else {
            unset($this->attributes['password']);
        }
    }

    public function getWebInfoAttribute($web_info) {
        if(IsJson($web_info)) {
            return json_decode($web_info, true);
        } else {
            return ["title" => "", "tel" => "", "weixin" => "", "service" => ""];
        }
    }

    public function setWebInfoAttribute($web_info) {
        if(IsJson($web_info)) {
            $this->attributes['web_info'] = $web_info;
        } else {
            $this->attributes['web_info'] = !empty($web_info) && is_array($web_info) ? json_encode($web_info, JSON_UNESCAPED_UNICODE) : ["title" => "", "tel" => "", "weixin" => "", "service" => ""];
        }
    }

    public function setAvatarAttribute($value) {
        $disk = config('admin.upload.disk');
        if($disk != 'local' && !Str::startsWith($value, ['http://', 'https://', 'HTTP://', 'HTTPS://'])) {
            $temp = Storage::disk($disk)->url($value);
        } elseif($disk == 'local' && !Str::startsWith($value, ['/storage/'])) {
            $temp = Storage::disk($disk)->url($value);
        } else {
            $temp = $value;
        }
        $this->attributes['avatar'] = $temp ?: $value;
    }
    
    //每日收益
    public function recharged_sum($group_id = 0,$customer_id){
        $statistics = new StatisticRepository;
        if($customer_id == 1){
            $customer_id = 0;
            $group_id = 1;
        }
        $all = $statistics->getStatisticsAll($group_id, $customer_id);
        if($all){
            return $all['recharged_sum']; 
        }
    }
    
    //今日收益
        public function recharge_money($group_id = 0,$customer_id){
        $statistics = new StatisticRepository;
        if($customer_id == 1){
            $customer_id = 0;
            $group_id = 1;
        }
        $all = $statistics->getStatisticsToday($group_id, $customer_id);
        if($all){
            return $all['recharge_money']; 
        }
    }
}