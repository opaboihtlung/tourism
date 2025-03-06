<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operator extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    /** GUARDED EMPTY MEANS THAT ALL ATTRIBUTES ARE MASS ASSIIGNABLE */
    protected $guarded=[]; 
    protected $with=['operatorRating'];
    public function districtMetaData()
    {
      return $this->belongsTo(DistrictMetaData::class);
    }
    public function operatorRating(){
      return $this->hasMany(OperatorRating::class,'operators_id');
  }
}
