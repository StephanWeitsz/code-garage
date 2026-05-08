<?php

namespace CodeGarage\Payments\Presentation\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use CodeGarage\Shared\Authorization\BasePolicy;

class PaymentPolicy extends BasePolicy
{
    public function viewAny(User $user): Response
    {
        return $this->allowIf($user, 'payments.view');
    }

    public function view(User $user): Response
    {
        return $this->allowIf($user, 'payments.view');
    }

    public function viewOwn(User $user): Response
    {
        return $this->allowIf($user, 'payments.view-own');
    }

    public function create(User $user): Response
    {
        return $this->allowIf($user, 'payments.create');
    }

    public function collect(User $user): Response
    {
        return $this->allowIf($user, 'payments.collect');
    }

    public function refund(User $user): Response
    {
        return $this->allowIf($user, 'payments.refund');
    }

    public function markPaid(User $user): Response
    {
        return $this->allowIf($user, 'payments.mark-paid');
    }
}
