<?php

namespace App\Traits;

use Laravel\Cashier\Cashier;
use Stripe\Account;

trait StripeConnectAccount
{
    public function stripeAccountId(): ?string
    {
        return $this->stripe_account_id;
    }

    public function stripeAccountEmail(): string
    {
        return $this->email;
    }

    public function hasStripeAccountId(): bool
    {
        return !is_null($this->stripeAccountId());
    }

    public function hasSubmittedAccountDetails(): bool
    {
        $this->assertAccountExists();
        return $this->asStripeAccount()->details_submitted;
    }

    public function hasCompletedOnboarding(): bool
    {
        return $this->hasSubmittedAccountDetails();
    }

    public function asStripeAccount(): Account
    {
        $this->assertAccountExists();
        return $this->stripe()->accounts->retrieve($this->stripeAccountId(), $this->stripeAccountOptions());
    }

    public function deleteAndCreateStripeAccount(string $type = 'standard', array $options = []): Account
    {
        // Delete account if it already exists.
        if ($this->hasStripeAccountId()) {
            $this->deleteStripeAccount();
        }
        // Create account and return it.
        return $this->createAsStripeAccount($type, $options);
    }

    public function deleteStripeAccount(): Account
    {
        $this->assertAccountExists();

        // Process account delete.
        $account = $this->asStripeAccount();
        $account->delete();

        // Wipe account id reference from model.
        $this->stripe_account_id = null;
        $this->save();

        return $account;
    }

    public function createAsStripeAccount(string $type = 'standard', array $options = []): Account
    {
        // Check if model already has a connected Stripe account.
        if ($this->hasStripeAccountId()) {
            abort(500, 'Stripe account already exists.');
        }
        // Create payload.
        $options = array_merge([
            'type' => $type,
            'email' => $this->stripeAccountEmail(),
        ], $options);
        // Create account.
        $account = $this->stripe()->accounts->create($options, $this->stripeAccountOptions());
        // Save the id.
        $this->stripe_account_id = $account->id;
        $this->save();

        return $account;
    }


    public function redirectToAccountDashboard(): string
    {
        return $this->accountDashboardUrl();
    }

    public function accountDashboardUrl(array $options = []): ?string
    {
        $this->assertAccountExists();

        // Can only create login link if details has been submitted.
        return $this->hasSubmittedAccountDetails()
            ? $this->stripe()->accounts->createLoginLink($this->stripeAccountId(), $options, $this->stripeAccountOptions())->url
            : null;
    }

    public function redirectToAccountOnboarding(string $return_url, string $refresh_url, array $options = []): string
    {
        return $this->accountOnboardingUrl($return_url, $refresh_url, $options);
    }

    public function accountOnboardingUrl(string $return_url, string $refresh_url, array $options = []): string
    {
        $options = array_merge([
            'return_url' => $return_url,
            'refresh_url' => $refresh_url,
        ], $options);

        return $this->accountLinkUrl('account_onboarding', $options);
    }

    public function accountLinkUrl(string $type, array $options = []): string
    {
        $this->assertAccountExists();

        $options = array_merge([
            'type' => $type,
            'account' => $this->stripeAccountId(),
        ], $options);

        return $this->stripe()->accountLinks->create($options, $this->stripeAccountOptions())->url;
    }

    public function stripeAccountOptions(array $options = []): array
    {
//        if ($this->hasStripeAccountId()) {
//            $options['stripe_account'] = $this->stripeAccountId();
//        }
        return $options;
    }

    protected function assertAccountExists(): void
    {
        if (!$this->hasStripeAccountId()) {
            abort(500, 'Stripe account does not exist.');
        }
    }

    public function stripe(array $options = [])
    {
        return Cashier::stripe($options);
    }
}
