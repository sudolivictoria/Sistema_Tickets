<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;



new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirect('/dashboard', navigate: true);
    }
}; ?>

<style>
    /*-------fuente para el login-------*/
    @import url('https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap');

    .login-container,
    .login-container * {
        font-family: 'Bree Serif', serif !important;
    }
</style>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div style="background: linear-gradient(to bottom, #001764 0%, #004e23 50%, #5b8f00 100%); min-height: 100vh; width: 100%; display: flex; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; z-index: 999; font-family: 'Bree Serif', serif;">

        <div style="position: relative; width: 90%; max-width: 400px;">

            <div style="position: absolute; inset: 0; background-color: #0c003f; border-radius: 2rem; transform: rotate(6deg) scale(1.05); z-index: 1;"></div>
            <div style="position: absolute; inset: 0; background-color: #120064; border-radius: 2rem; transform: rotate(-6deg) scale(1.05); z-index: 1;"></div>


            <div style="position: relative; background: white; border-radius: 2rem; padding-left:40px; padding-right:40px; padding-bottom:40px; padding-top:10px; z-index: 10;">

                <div style="text-align: center; margin: 0; padding: 0; margin-bottom: 0;">
                    <a href="/" style="display: flex; justify-content: center;">
                        <img src="{{ asset('images/logo_istu.png') }}" alt="Logo" style="height: 150px; width: 150px; object-fit: contain; margin: 0; padding: 0; display: block;">
                    </a>
                </div>

                <h1 style="text-align: center; font-size: 24px; font-weight: bolder; color: #005797; margin-bottom: 30px; font-family: 'Bree Serif', serif;">Help Desk Istu</h1>

                <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

                <form wire:submit.prevent="login" style="display: flex; flex-direction: column; gap: 15px;">
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: bold; color: #005c0c; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Correo</label>
                        <input wire:model="form.email" type="email" required autofocus autocomplete="username"
                            style="width: 100%; padding: 12px; border: 1px solid #f3f4f6; border-radius: 12px; background: #f9fafb; outline: none; transition: 0.3s;"
                            onfocus="this.style.border='1px solid #bef264'">
                        <x-input-error :messages="$errors->get('form.email')" class="mt-1" />
                    </div>

                    <div>
                        <label style="display: block; font-size: 11px; font-weight: bold; color: #005c0c; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Contraseña</label>
                        <div style="position: relative; width: 100%;">
                            <input id="password-input" wire:model="form.password" type="password" required autocomplete="current-password"
                                style="width: 100%; padding: 12px 40px 12px 12px; border: 1px solid #f3f4f6; border-radius: 12px; background: #f9fafb; outline: none; transition: 0.3s;"
                                onfocus="this.style.border='1px solid #bef264'">
                            <button type="button" id="toggle-password"
                                style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; padding: 0;">
                                <!-- simple eye icon -->
                                <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#005c0c" viewBox="0 0 24 24">
                                    <path d="M12 5c-7.633 0-11 6.52-11 7s3.367 7 11 7 11-6.52 11-7-3.367-7-11-7zm0 12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5zm0-8.5c-1.931 0-3.5 1.569-3.5 3.5s1.569 3.5 3.5 3.5 3.5-1.569 3.5-3.5-1.569-3.5-3.5-3.5z" />
                                </svg>
                                <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#005c0c" viewBox="0 0 24 24" style="display:none;">
                                    <path d="M12 5c-7.633 0-11 6.52-11 7s3.367 7 11 7c1.645 0 3.223-.314 4.692-.884l3.186 3.186 1.414-1.414-18-18-1.414 1.414 3.705 3.705c-2.216 1.381-3.947 3.268-4.972 4.778 1.112 1.618 3.385 4.096 6.667 5.238l-1.38-1.38c-2.142-.702-3.715-2.43-4.268-3.506.982-1.336 3.124-3.932 7.481-3.932 1.763 0 3.34.408 4.683 1.09l1.937-1.937c-1.787-.926-3.847-1.561-6.62-1.561zm3.931 10.931l-1.655-1.655c.435-.484.724-1.121.724-1.776 0-1.378-1.122-2.5-2.5-2.5-.655 0-1.292.289-1.776.724l-1.655-1.655c.888-.66 1.957-1.064 3.431-1.064 2.761 0 5 2.239 5 5 0 1.474-.404 2.543-1.069 3.431z" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('form.password')" class="mt-1" />
                    </div>

                    <button type="submit" style="width: 100%; background-color: #006625; color: white; font-weight: bold; padding: 14px; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; margin-top: 10px; transition: 0.3s;"
                        onmouseout="this.style.backgroundColor='#006625'"
                        onmouseover="this.style.backgroundColor='#004e1d'">
                        Iniciar Sesión
                    </button>
                </form>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const pwdInput = document.getElementById('password-input');
                        const toggleBtn = document.getElementById('toggle-password');
                        const eyeOpen = document.getElementById('eye-open');
                        const eyeClosed = document.getElementById('eye-closed');

                        toggleBtn.addEventListener('click', function() {
                            if (pwdInput.type === 'password') {
                                pwdInput.type = 'text';
                                eyeOpen.style.display = 'none';
                                eyeClosed.style.display = 'block';
                            } else {
                                pwdInput.type = 'password';
                                eyeOpen.style.display = 'block';
                                eyeClosed.style.display = 'none';
                            }
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</div>