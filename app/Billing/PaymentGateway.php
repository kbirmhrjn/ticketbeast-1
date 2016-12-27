<?php


namespace App\Billing;


interface PaymentGateway {

	public function charge($charge, $token);

}