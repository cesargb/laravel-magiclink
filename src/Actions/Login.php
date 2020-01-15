<?php

namespace Cesargb\MagicLink\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Login extends Action implements ActionInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    protected $redirectTo;

    protected $guard;

    /**
     * Constructor to action.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param string $redirectTo
     */
    public function __construct($user, $redirectTo = '/', $guard = 'web')
    {
        $this->user = $user;

        $this->redirectTo = $redirectTo;

        $this->guard = $guard;
    }

    /**
     * Execute Action.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function run()
    {
        Auth::guard($this->guard)->login($this->user);

        return redirect($this->redirectTo);
    }
}
