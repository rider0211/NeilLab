{{-- Extends layout --}}
@extends('layout.fullwidth')



{{-- Content --}}
@section('content')
    <div class="col-md-6">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
						<div class="text-center mb-3">
							<img src="images/logo-full-black.png" alt="">
						</div>
                        @if(session()->has('error'))
                        <div class="alert alert-danger"><div class="alert-body">{{ session()->get('error') }}</div></div>
                        @endif

                        @if(session()->has('success'))
                        <div class="alert alert-success"><div class="alert-body">{{ session()->get('success') }}</div></div>
                        @endif

                        <h4 class="text-center mb-4">{{__('locale.signup_page_title')}}</h4>
                        <form method="POST" action="{!! url('/register_new_user'); !!}">
                            @csrf
                            <div class="row">
                            <div class="col-xl-6">
                                <div class="form-group">
                                    <label class="mb-1"><strong>First Name</strong></label>
                                    <input type="text" class="form-control" name="firstname" placeholder="{{ __('locale.firstname_paceholder') }}">
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="form-group">
                                    <label class="mb-1"><strong>Last Name</strong></label>
                                    <input type="text" class="form-control" name="lastname" placeholder="{{ __('locale.lastname_paceholder') }}">
                                </div>
                            </div>
                            </div>
                            <div class="form-group">
                                <label class="mb-1"><strong>Username</strong></label>
                                <input type="text" class="form-control" name="username" placeholder="{{ __('locale.username_paceholder') }}">
                            </div>
                            <div class="form-group">
                                <label class="mb-1"><strong>Email</strong></label>
                                <input type="email" class="form-control" name="email" placeholder="{{__('locale.email_paceholder')}}">
                            </div>
                            <div class="form-group">
                                <label class="mb-1"><strong>Password</strong></label>
                                <input type="password" class="form-control" name="password" placeholder="{{__('locale.password_paceholder')}}" value="">
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-block">Sign me up</button>
                            </div>
                        </form>
                        <div class="new-account mt-3">
                            <p>Already have an account? <a class="text-primary" href="{!! url('/login'); !!}">Sign in</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 