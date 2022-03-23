<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoreModel extends Model
{
    use HasFactory;

    protected $table = 'tb_score';
    protected $primaryKey = 'id';

    public $timestamps = false;
    public $incrementing = false;

    public static function setToUserId(int $userId, float $score): void
    {
        $object = self::whereUserId($userId)->first();

        if(!$object) {
            self::insert([
                'user_id' => $userId,
                'score' => $score,
            ]);
        } else {
            self::whereUserId($userId)
            ->update([
                'score' => $score,
            ]);
        }
    }
}
