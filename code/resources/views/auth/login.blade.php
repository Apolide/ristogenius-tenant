@extends('layouts.auth')
@section('content')
<div class="container">
    <div class="grid grid-cols-12 authentication authentication-basic items-center h-full text-defaultsize text-defaulttextcolor">
        <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-3 sm:col-span-2"></div>
        <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-6 sm:col-span-8 col-span-12">
            <div class="my-[2.5rem] flex justify-center">
                <a href="/">
                    <img src="/assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                    <img src="/assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
                </a>
            </div>
            <div class="box">
                <div class="box-body !p-[3rem]">
                    <p class="h5 font-semibold mb-2 text-center !text-defaulttextcolor dark:!text-defaulttextcolor/85">Login</p>
                    <p class="mb-4 text-[#8c9097] opacity-[0.7] font-normal text-center">Accedi al sistema</p>
                    @session('status')
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ $value }}
                        </div>
                    @endsession
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="grid grid-cols-12 gap-y-4">
                            <div class="xl:col-span-12 col-span-12">
                                <label for="signin-username" class="form-label text-default">Email</label>
                                <input type="email" name="email" :value="old('email')" required autofocus autocomplete="email" class="form-control form-control-lg w-full !rounded-md" id="signin-username" placeholder="e-mail">
                            </div>
                            <div class="xl:col-span-12 col-span-12">
                                <label for="signin-password" class="form-label text-default block">Password @if (Route::has('password.request'))<a href="{{ route('password.request') }}"" class="float-right text-danger">Password dimenticata ?</a>@endif</label>
                                <div class="input-group">
                                    <input name="password" required autocomplete="current-password" type="password" class="form-control form-control-lg !rounded-tl-md !rounded-bl-md" id="signin-password" placeholder="password">
                                    <button class="ti-btn ti-btn-light !rounded-tl-none !rounded-bl-none !mb-0" type="button" onclick="createpassword('signin-password',this)" id="button-addon2"><i class="ri-eye-off-line align-middle"></i></button>
                                </div>
                                <div class="mt-2">
                                    <div class="form-check flex items-center gap-2">
                                        <input class="form-check-input" type="checkbox" value="true" name="remember" id="defaultCheck1" checked>
                                        <label class="form-check-label text-[#8c9097] font-normal" for="defaultCheck1">
                                            Resta connesso
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-12 col-span-12 grid">
                                <button type="submit" class="ti-btn ti-btn-lg bg-primary !border-0 text-white !font-medium">Accedi</button>
                            </div>
                        </div>
                    </form>
                    <?php
                    /*
                    <div class="text-center">
                        <p class="text-[0.75rem] text-[#8c9097] mt-4">Dont have an account? <a href="sign-up-basic.html" class="text-primary">Sign Up</a></p>
                    </div>
                    */ 
                    ?>
                   
                    
                </div>
            </div>
        </div>
        <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-3 sm:col-span-2"></div>
    </div>
</div>
@endsection


