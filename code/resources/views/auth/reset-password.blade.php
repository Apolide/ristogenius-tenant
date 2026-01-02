@extends('layouts.auth')
@section('content')
<div class="container">
    <div class="grid grid-cols-12 gap-x-6 authentication authentication-basic items-center h-full text-defaultsize text-defaulttextcolor">
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
                    <p class="h5 font-semibold mb-2 text-center">Reset Password</p>
                    <p class="mb-4 text-[#8c9097] opacity-[0.7] font-normal text-center">Hello Jhon !</p>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf   
                        
                        <div class="grid grid-cols-12 gap-y-4">
                            <div class="xl:col-span-12 col-span-12">
                                <label for="signin-username" class="form-label text-default">Email</label>
                                <input type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="email" class="form-control form-control-lg w-full !rounded-md" id="email" placeholder="e-mail">
                            </div>
                            <div class="xl:col-span-12 col-span-12">
                                <label for="reset-newpassword" class="form-label text-default">Nuova Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" required autocomplete="new-password" class="form-control form-control-lg !rounded-tl-sm !rounded-bl-sm rtl:!rounded-tl-none rtl:!rounded-bl-none  rtl:!rounded-tr-sm rtl:!rounded-br-sm" id="reset-newpassword" placeholder="nuova password">
                                    <button class="ti-btn ti-btn-light !mb-0 !rounded-tl-none !rounded-bl-none !rounded-tr-sm !rounded-br-sm rtl:!rounded-tr-none rtl:!rounded-br-none  rtl:!rounded-tl-sm rtl:!rounded-bl-sm" type="button" onclick="createpassword('reset-newpassword',this)" id="button-addon21"><i class="ri-eye-off-line align-middle"></i></button>
                                </div>
                            </div>
                            <div class="xl:col-span-12 col-span-12 mb-2">
                                <label for="reset-confirmpassword" class="form-label text-default ">Conferma Password</label>
                                <div class="input-group">
                                    <input type="password" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control form-control-lg !rounded-tl-sm !rounded-bl-sm rtl:!rounded-tl-none rtl:!rounded-bl-none  rtl:!rounded-tr-sm rtl:!rounded-br-sm" id="reset-confirmpassword" placeholder="conferma password">
                                    <button class="ti-btn ti-btn-light !mb-0 !rounded-tl-none !rounded-bl-none !rounded-tr-sm !rounded-br-sm rtl:!rounded-tr-none rtl:!rounded-br-none  rtl:!rounded-tl-sm rtl:!rounded-bl-sm" type="button" onclick="createpassword('reset-confirmpassword',this)" id="button-addon22"><i class="ri-eye-off-line align-middle"></i></button>
                                </div>
                            </div>
                            <div class="xl:col-span-12 col-span-12 grid mt-2">
                                <button type="submit" class="ti-btn ti-btn-lg bg-primary !border-0 text-white !font-medium">Salva</button>
                            </div>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
        <div class="xxl:col-span-4 xl:col-span-4 lg:col-span-4 md:col-span-3 sm:col-span-2"></div>
    </div>
</div>
@endsection