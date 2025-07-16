<x-homepoll.header></x-homepoll.header>

<body class="rz-body">
    <header class="rz-header">
        <div class="logo-group">
            <img src="{{ asset('assets/img/logos/Logo-Unusida-Putih-min.png') }}" alt="Logo Unusids Putih">
            <img src="{{ asset('assets/img/logos/kampus_merdeka_putih.png') }}" alt="Logo Unusids Putih">
        </div>
        <div class="header-title" style="margin-left: -30px">PMB UNUSIDA</div>
        <div class="logo-group">
            <img src="{{ asset('assets/img/logos/Logo-Pmb-Putih.png') }}" alt="Logo Unusids Putih">
            <img src="{{ asset('assets/img/logos/Logo-1-Dekade-Ref-Putih-min.png') }}" alt="Logo Unusids Putih">
        </div>
    </header>
    <main class="rz-main">
        <div class="title-area">
            <h1>
                Penerimaan Mahasiswa Baru <br>
                Universitas Nahdlatul Ulama Sidoarjo
            </h1>
            <h2>
                Bagaimana pelayanan kami?
            </h2>
        </div>
        <form action="/spmb/dopoll" method="POST" class="emot-group">
            @csrf
            <div class="emot-center">
                <div class="poll-btn">
                    <div class="emot-icon is-red">
                        {{-- <i class="fa-solid fa-face-meh"></i> --}}
                        <img src="{{ asset('assets/img/icons/e-1.png') }}" alt="Emot 1">
                    </div>
                    <div class="emot-text">
                        <p>
                            Kurang Puas
                        </p>
                    </div>
                </div>
                <div class="poll-btn">
                    <div class="emot-icon is-yellow">
                        {{-- <i class="fa-solid fa-face-grin-wide"></i> --}}
                        <img src="{{ asset('assets/img/icons/e-2.png') }}" alt="Emot 1">
                    </div>
                    <div class="emot-text">
                        <p>Puas</p>
                    </div>
                </div>
                <div class="poll-btn">
                    <div class="emot-icon is-green">
                        {{-- <i class="fa-solid fa-face-laugh-beam"></i> --}}
                        <img src="{{ asset('assets/img/icons/e-3.png') }}" alt="Emot 1">
                    </div>
                    <div class="emot-text">
                        <p>Sangat Puas</p>
                    </div>
                </div>
            </div>
            <div class="emot-button">
                <button class="confirm-poll-btn" id="clear-poll"><i class="fa-solid fa-xmark"></i></button>
                <button class="confirm-poll-btn" id="confirm-poll" value="0" name="confirm-poll">Simpan</button>
            </div>
        </form>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
        setInterval(function() {
            // Refresh CSRF token
            var token = $('meta[name="csrf-token"]').attr('content');
            $.get('/refresh-csrf-token', {
                _token: token
            });
        }, 600000); // Refresh every 10 minutes (adjust as needed)
    </script>
    <x-homepoll.footer nowDate="{{ $nowDate }}" totalPoll="{{ $totalPoll }}"></x-homepoll.footer>
    <script src="{{ asset('assets/js/rz/dashpoll.js') }}"></script>
    {{-- <script src="https://kit.fontawesome.com/c036512f7c.js" crossorigin="anonymous"></script> --}}
</body>

</html>
