<x-layout bodyClass="g-sidenav-show  bg-gray-200">
    <script src="../../assets/js/plugins/chartjs.min.js"></script>

    <x-navbars.sidebar activePage="pengaturan"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth titlePage="Export"></x-navbars.navs.auth>

        <div class="container">
            <div class="card mt-3 p-2">
                <h1 class="p-3">Pengaturan</h1>
                <form action="/spmb/tambah-periode" method="POST" class="row p-3">
                    @csrf

                    <h5 class="p-3" style="text-transform: uppercase">Tambah Periode</h5>
                    <div class="col-sm">
                        <div class="container" style="padding: 0px;">
                            <div class="row" style="padding: 0px;">
                                <div class="col-sm">
                                    <div class="input-group input-group-static mb-4">
                                        <label>Tanggal Awal</label>
                                        <input type="date" name="periode_start" id="periode-start"
                                            class="form-control">

                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="input-group input-group-static mb-4">
                                        <label>Tanggal Akhir</label>
                                        <input type="date" name="periode_end" id="periode-end" class="form-control">

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="coler">
                        <button class="btn bg-gradient-success mt-3">Tambah</button>
                    </div>
                </form>
            </div>

            <div class="card my-4" style="padding-top: 20px; margin-bottom: 10px !important;">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-secondary shadow-primary border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Atur Periode</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Tanggal Awal</th>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Tanggal Akhir</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allPeriode as $all)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">

                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-0 text-sm">{{ $all->periode_start_fm }}</h6>

                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex  py-1">

                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-0 text-sm">{{ $all->periode_end_fm }}</h6>

                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle justify-content-center text-sm">
                                            <div class="d-flex  py-1">
                                                @if ($all->selected === 0)
                                                    <form action="/spmb/delete-periode" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="periode_id"
                                                            value={{ $all->id }} />

                                                        <button class="btn bg-gradient-danger mt-3 btn-sm"
                                                            style="display: inline-block">
                                                            Hapus
                                                        </button>&nbsp;&nbsp;
                                                    </form>
                                                @endif

                                                <form action="/spmb/set-periode-active" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="active" value={{ $all->selected }} />
                                                    <input type="hidden" name="periode_id" value={{ $all->id }} />
                                                    @if ($all->selected === 1)
                                                        <button class="btn bg-gradient-secondary mt-3 btn-sm"
                                                            style="display: inline-block;  cursor: not-allowed !important; opacity: 0.5;"
                                                            disabled>
                                                            Aktif
                                                        </button>
                                                    @else
                                                        <button class="btn bg-gradient-success mt-3 btn-sm"
                                                            style="display: inline-block;">
                                                            Aktifkan
                                                        </button>
                                                    @endif
                                                </form>
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach

                                @if (sizeof($allPeriode) < 1)
                                    <tr>
                                        <td>Kosong</td>
                                        <td>Kosong</td>
                                        <td>Kosong</td>
                                    </tr>
                                @endif


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- <div class="card mt-3 p-4">
                <h4 class="h4-text mb-3">Pilih Format</h4>
                <form action="/do-export" method="POST" class="row">
                    @csrf
                    <div class="col-sm">
                        <button name="export-btn" value="{{ "$dateNow[3]-$dateNow[2]-$dateNow[1]" }}"
                            class="btn bg-gradient-secondary btn-lg w-100" {{ $totalDataCount < 1 ? 'disabled' : '' }}>
                            {{ $totalDataCount < 1 ? 'Data Kosong' : "$dateNow[3]-$dateNow[2]-$dateNow[1].csv" }}
                            &nbsp;[CSV]</button>
                    </div>
                    <div class="col-sm">
                        <button name="export-btn" value="{{ "$dateNow[3]-$dateNow[2]-$dateNow[1]" }}"
                            class="btn bg-gradient-secondary btn-lg w-100 disabled" disabled>
                            {{ $totalDataCount < 1 ? 'Data Kosong' : "$dateNow[3]-$dateNow[2]-$dateNow[1].csv" }}
                            &nbsp;[PDF]</button>
                    </div>

                </form>
            </div> --}}
            <x-footers.auth></x-footers.auth>
        </div>

        <script>
            // Get today's date
            var today = new Date();
            var dd = String(today.getDate()).padStart(2, '0');
            var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
            var yyyy = today.getFullYear();

            today = yyyy + '-' + mm + '-' + dd;

            // Set the max attribute of the date input field
            // document.getElementById("periode-end").setAttribute("max", today);
            document.getElementById("periode-end").setAttribute("value", today);
            document.getElementById("periode-start").setAttribute("value", today);
        </script>

    </main>
    </x-rlayout>
