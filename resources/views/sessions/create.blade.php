<x-layout bodyClass="bg-gray-200">

    {{-- <div class="container position-sticky z-index-sticky top-0">
            <div class="row">
                <div class="col-12">
                    <!-- Navbar -->
                    <x-navbars.navs.guest signin='login' signup='register'></x-navbars.navs.guest>
                    <!-- End Navbar -->
                </div>
            </div>
        </div> --}}
    <main class="main-content mt-0">
        <div class="page-header align-items-start min-vh-100 rz-bg-green-grad">
            <span class="mask opacity-6"></span>
            <div class="container mt-5">
                <div class="row signin-margin">
                    <div class="col-lg-4 col-md-8 col-12 mx-auto">
                        <div class="card z-index-0 fadeIn3 fadeInBottom">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="rz-bg-green-grad shadow-success border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Masuk</h4>
                                    <div class="row mt-3">
                                        <h6 class='text-white text-center'>
                                            <span class="font-weight-normal">LOGIN ADMIN
                                            <br>
                                            <!--<span class="font-weight-normal">Kata Sandi:</span> pmbunusida-->
                                        </h6>
                                        <!--<h6 class='text-white text-center'>-->
                                        <!--    <span class="font-weight-normal">Email:</span> admin@admin.net-->
                                        <!--    <br>-->
                                        <!--    <span class="font-weight-normal">Kata Sandi:</span> pmbunusida-->
                                        <!--</h6>-->
                                        {{-- <div class="col-2 text-center ms-auto">
                                            <a class="btn btn-link px-3" href="javascript:;">
                                                <i class="fa fa-facebook text-white text-lg"></i>
                                            </a>
                                        </div>
                                        <div class="col-2 text-center px-1">
                                            <a class="btn btn-link px-3" href="javascript:;">
                                                <i class="fa fa-github text-white text-lg"></i>
                                            </a>
                                        </div>
                                        <div class="col-2 text-center me-auto">
                                            <a class="btn btn-link px-3" href="javascript:;">
                                                <i class="fa fa-google text-white text-lg"></i>
                                            </a>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <form role="form" method="POST" action="{{ route('login') }}" class="text-start">
                                    @csrf
                                    @if (Session::has('status'))
                                        <div class="alert alert-success alert-dismissible text-white" role="alert">
                                            <span class="text-sm">{{ Session::get('status') }}</span>
                                            <button type="button" class="btn-close text-lg py-3 opacity-10"
                                                data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    <div class="input-group input-group-outline mt-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control input-green-border" name="email"
                                            />
                                    </div>
                                    @error('email')
                                        <p class='text-danger inputerror'>Info yang anda masukan salah! </p>
                                    @enderror
                                    <div class="input-group input-group-outline mt-3">
                                        <label class="form-label">Kata Sandi</label>
                                        <input type="password" class="form-control" name="password"/>
                                    </div>
                                    @error('password')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                    {{-- <div class="form-check form-switch d-flex align-items-center my-3">
                                        <input class="form-check-input" type="checkbox" id="rememberMe">
                                        <label class="form-check-label mb-0 ms-2" for="rememberMe">Remember
                                            me</label>
                                    </div> --}}
                                    <div class="text-center">
                                        <button type="submit"
                                            class="btn rz-bg-green-grad w-100 my-4 mb-2 text-white">Sign
                                            in</button>
                                    </div>
                                    {{-- <p class="mt-4 text-sm text-center">
                                            Belum punya akun?
                                            <a href="{{ route('register') }}"
                                                class="text-success text-gradient font-weight-bold">Daftar</a>
                                        </p> --}}
                                    {{-- <p class="text-sm text-center">
                                            ? Reset your password
                                            <a href="{{ route('verify') }}"
                                                class="text-success text-gradient font-weight-bold">here</a>
                                        </p> --}}
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <x-footers.guest></x-footers.guest> --}}
        </div>
    </main>
    @push('js')
        <script src="{{ asset('assets') }}/js/jquery.min.js"></script>
        <script>
            $(function() {

                var text_val = $(".input-group input").val();
                if (text_val === "") {
                    $(".input-group").removeClass('is-filled');
                } else {
                    $(".input-group").addClass('is-filled');
                }
            });
        </script>
    @endpush
</x-layout>
