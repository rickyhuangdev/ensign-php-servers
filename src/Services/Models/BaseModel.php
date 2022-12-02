<?php
declare(strict_types=1);
namespace Rickytech\Library\Services\Models;

use Hyperf\Database\Model\Model;

class BaseModel extends Model
{
    public function getUpdatedAtAttribute($value): string
    {
        return $value ? date("Y-m-d H:i:s", strtotime($value)) : '';
    }

    public function getCreatedAtAttribute($value): string
    {
        return $value ? date("Y-m-d H:i:s", strtotime($value)) : '';
    }
}
