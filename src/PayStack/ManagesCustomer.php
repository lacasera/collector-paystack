<?php

namespace Collector\PayStack;

use Exception;

trait ManagesCustomer
{
    public function createOrGetPayStackCustomer(array $options = [])
    {

        if (! array_key_exists('email', $options) && $email = $this->payStackEmail()) {
            $options['email'] = $email;
        }

        if (! array_key_exists('phone', $options) && $phone = $this->payStackPhone()) {
            $options['phone'] = $phone;
        }

        if ($this->hasPayStackId()) {
            return $this;
        }

        $customer = $this->request->post('/customer', $options)->json('data');

        if ($customer) {
            $this->fill(['paystack_id' => $customer['customer_code']])->save();
        }

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAsPaystackCustomer()
    {
        if (! $this->hasPayStackId()) {
            throw new Exception('user is not a paystack customer');
        }

        $response = $this->request->get("customer/$this->paystack_id");

        if (! $response->ok()) {
            return null;
        }

        return $response->json('data');
    }

    /**
     * @return bool
     */
    public function hasPayStackId()
    {
        return ! is_null($this->paystack_id);
    }

    /**
     * @return mixed|null
     */
    protected function payStackEmail()
    {
        return $this->email ?? null;
    }

    /**
     * @return mixed|null
     */
    protected function payStackPhone()
    {
        return $this->phone ?? null;
    }
}
