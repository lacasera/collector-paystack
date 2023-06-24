<?php

namespace Collector\PayStack;

use Illuminate\Database\Eloquent\Model;

trait ManagesCustomer
{
    public function createOrGetPayStackCustomer(array $options)
    {
      dd("here");

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
//
//        $customer = $this->request
//            ->post('/customer', [
//                'first_name' => $collectable->first_name,
//                'last_name' => $collectable->last_name,
//                'phone' => $collectable->phone_number
//            ])
//            ->json('data');
//
//        if ($customer) {
//            $collectable->fill([
//                'paystack_id' => $customer['customer_code']
//            ])->save();
//        }

        return '';
    }

    public function hasPayStackId()
    {
        return ! is_null($this->paystack_id);
    }

    protected function paystackFirstName()
    {

    }
}
