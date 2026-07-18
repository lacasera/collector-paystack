<?php

namespace Collector\PayStack;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait ManagesCustomer
{
    /**
     * The PayStack customer payload, cached for the life of this instance.
     *
     * Rendering the billing portal asks for the customer three times over
     * (subscription sync, transaction history, payment methods); without this
     * each one is a separate blocking round-trip for an identical response.
     */
    protected ?array $payStackCustomer = null;

    /** The paystack_id $payStackCustomer was fetched for. */
    protected ?string $payStackCustomerFor = null;

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

        return $this->createAsPayStackCustomer($options);
    }

    /**
     * Create a PayStack customer for the given model.
     *
     * @return $this
     */
    public function createAsPayStackCustomer(array $options = [])
    {
        if (! array_key_exists('email', $options) && $email = $this->payStackEmail()) {
            $options['email'] = $email;
        }

        if (! array_key_exists('phone', $options) && $phone = $this->payStackPhone()) {
            $options['phone'] = $phone;
        }

        $customer = $this->request->post('/customer', $options)->json('data');

        if ($customer) {
            $this->fillUserPaymentDetails($customer);
        }

        return $this;
    }

    /**
     * Update the underlying PayStack customer information for the model.
     *
     * @return array|null
     */
    public function updatePayStackCustomer(array $options = [])
    {
        if (! $this->hasPayStackId()) {
            throw new Exception('user is not a paystack customer');
        }

        $response = $this->request->put("customer/$this->paystack_id", $options);

        if (! $response->ok()) {
            return null;
        }

        $customer = $response->json('data');

        if ($customer) {
            $this->fillUserPaymentDetails($customer);
        }

        return $customer;
    }

    /**
     * @return array|null
     */
    public function getAsPaystackCustomer()
    {
        if (! $this->hasPayStackId()) {
            throw new Exception('user is not a paystack customer');
        }

        // Keyed on the id it was fetched for, so becoming a different PayStack
        // customer mid-request cannot serve the previous one's data.
        if ($this->payStackCustomerFor === $this->paystack_id && ! is_null($this->payStackCustomer)) {
            return $this->payStackCustomer;
        }

        $response = $this->request->get("customer/$this->paystack_id");

        if (! $response->ok()) {
            return null;
        }

        $this->payStackCustomerFor = $this->paystack_id;

        return $this->payStackCustomer = $response->json('data');
    }

    /**
     * Drop the cached customer payload so the next read re-fetches it.
     *
     * Call after anything that changes the customer on PayStack's side.
     */
    public function forgetPayStackCustomer(): void
    {
        $this->payStackCustomer = null;
        $this->payStackCustomerFor = null;
    }

    /**
     * @return bool
     */
    public function hasPayStackId()
    {
        return ! is_null($this->paystack_id);
    }

    /**
     * Get the model's saved payment methods (PayStack authorizations).
     *
     * @return Collection
     */
    public function paymentMethods(?string $type = null)
    {
        if (! $this->hasPayStackId()) {
            return collect();
        }

        $authorizations = collect(data_get($this->getAsPaystackCustomer(), 'authorizations', []));

        if ($type) {
            $authorizations = $authorizations->where('channel', $type);
        }

        return $authorizations->values();
    }

    /**
     * Get the model's default payment method (as stored locally).
     *
     * @return object|null
     */
    public function defaultPaymentMethod()
    {
        if ($this->hasPaymentMethod()) {
            return (object) [
                'type' => $this->pm_type,
                'last4' => $this->pm_last_four,
                'expiration' => $this->pm_expiration,
            ];
        }

        return $this->paymentMethods()->first();
    }

    /**
     * Determine if the model has a default payment method.
     */
    public function hasPaymentMethod(): bool
    {
        return ! is_null($this->pm_type) || ! is_null($this->pm_last_four);
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

    /**
     * @return $this
     */
    protected function fillUserPaymentDetails(array $paystackCustomer)
    {
        $authorization = Arr::first(data_get($paystackCustomer, 'authorizations'));

        // forceFill, not fill: these are the package's own columns, written from
        // a trusted PayStack response rather than user input. The host's User
        // model owns $fillable, and a restrictive one (the Laravel default)
        // would otherwise discard every key here silently.
        $this->forceFill([
            'paystack_id' => $paystackCustomer['customer_code'],
            'pm_type' => data_get($authorization, 'card_type'),
            'pm_last_four' => data_get($authorization, 'last4'),
            'pm_expiration' => data_get($authorization, 'exp_month') . '/' . data_get($authorization, 'exp_year'),
        ])->save();

        // This payload is fresher than anything cached, and the customer may
        // have changed identity entirely.
        $this->payStackCustomer = $paystackCustomer;
        $this->payStackCustomerFor = $this->paystack_id;
    }
}
