<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'tmt_cpns',
        'tmt_pns',
        'tmt_golongan',
        'tmt_jabatan',
        'kd_gol',
        'kd_jab',
        'status_pegawai',
        'mk_tahun',
        'mk_bulan',
        'agama',
        'no_ijazah',
        'tgl_ijazah',
    ];

    protected $casts = [
        'tmt_cpns' => 'date',
        'tmt_pns' => 'date',
        'tmt_golongan' => 'date',
        'tmt_jabatan' => 'date',
        'tgl_ijazah' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
