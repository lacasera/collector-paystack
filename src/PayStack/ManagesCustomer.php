<?php

namespace Collector\PayStack;

use Illuminate\Database\Eloquent\Model;

trait ManagesCustomer
{
    public function createOrGetPayStackCustomer()
    {
     // dd($options);

 //        if (! array_key_exists('name', $options) && $name = $this->paystackFirstName()) {
//            $options['name'] = $name;
//        }
//
//        if (! array_key_exists('email', $options) && $email = $this->paystackEmail()) {
//            $options['email'] = $email;
//        }
//
//        if (! array_key_exists('phone', $options) && $phone = $this->paystackPhone()) {
//            $options['phone'] = $phone;
//        }

        if ($this->hasPayStackId()) {
            return $this;
        }

        $customer = $this->request
            ->post('/customer', [
                'email' => $this->email
            ])
            ->json('data');

        if ($customer) {
            $this->fill(['paystack_id' => $customer['customer_code']])->save();
        }

        return $this;
    }

    public function hasPayStackId()
    {
        return ! is_null($this->paystack_id);
    }

    protected function paystackFirstName()
    {

    }
}
