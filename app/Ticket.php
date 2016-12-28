<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{

	protected $guarded = [];

	public function scopeAvailable($query)
	{
		return $query->whereNull('order_id');
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
}
