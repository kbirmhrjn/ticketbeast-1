<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{

	protected $guarded = [];

	public function scopeAvailable($query)
	{
		return $query->whereNull('order_id');
  }

  public function reserve()
  {
  	$this->update([
  		'reserved_at' => Carbon::now()
		]);
  }

	/**
	 * Releases tickets from the order it is associated with.
	 * Simply sets its order_id to null.
	 */
	public function release()
	{
		$this->update([
			'order_id' => null
		]);
  }

	public function concert()
	{
		return $this->belongsTo(Concert::class);
  }

	public function getPriceAttribute()
	{
		return $this->concert->ticket_price;
  }
}
