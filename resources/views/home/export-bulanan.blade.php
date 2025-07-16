<x-layout bodyClass="g-sidenav-show  bg-gray-200">
    <script src="../../assets/js/plugins/chartjs.min.js"></script>

    <x-navbars.sidebar activePage="export"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth titlePage="Export"></x-navbars.navs.auth>

        <div class="container">
            <div class="card mt-3 p-2">
                <h1 class="p-3">Export Bulanan</h1>
                <form action="/spmb/export" method="POST" class="row p-3">
                    @csrf
                    <div class="col-sm">
                        <div class="input-group input-group-static mb-4">
                            <label for="exampleFormControlSelect1" class="ms-0">Bulan</label>
                            <select name="bulan-value" class="form-control" id="export-bulan-select">
                                @for ($i = 0; $i < sizeof($bulanIndonesia); $i++)
                                    <option value="{{ $i + 1 }}" {{ $i === $dateNow[2] - 1 ? 'selected' : '' }}>
                                        {{ $bulanIndonesia[$i] }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="input-group input-group-static mb-4">
                            <label for="exampleFormControlSelect1" class="ms-0">Tahun</label>
                            <select name="tahun-value" class="form-control" id="export-tahun-select">
                                @for ($i = 2023; $i <= 2030; $i++)
                                    <option value={{ $i }} {{ $i === $dateNow[3] ? 'selected' : '' }}>
                                        {{ $i }}
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-2 text-center">
                        <button class="btn bg-gradient-secondary mt-3">Filter</button>
                    </div>
                </form>
            </div>

            <div class="card mt-3 p-4" id="export-format-card">
                <h4 class="h4-text mb-3">Pilih Format</h4>
                <form action="/spmb/do-export-bulanan" method="POST" class="row">
                    @csrf
                    <input type="hidden" name="month-filter" value="{{ $dateNow[2] }}">
                    <input type="hidden" name="year-filter" value="{{ $dateNow[3] }}">

                    <div class="col-sm">
                        <button name="export-btn" value="{{ "$dateNow[3]-$dateNow[2]" }}"
                            class="btn bg-gradient-secondary btn-lg w-100" {{ $totalDataCount < 1 ? 'disabled' : '' }}>
                            {{ $totalDataCount < 1 ? 'Data Kosong' : "$dateNow[3]-$dateNow[2].csv" }}
                            &nbsp;[CSV]</button>
                    </div>
                    <div class="col-sm">
                        <button name="export-btn" value="{{ "$dateNow[3]-$dateNow[2]" }}"
                            class="btn bg-gradient-secondary btn-lg w-100 disabled" disabled>
                            {{ $totalDataCount < 1 ? 'Data Kosong' : "$dateNow[3]-$dateNow[2].csv" }}
                            &nbsp;[PDF]</button>
                    </div>

                </form>
            </div>

            <div class="card mt-3 p-4" id="export-format-card">
                <h4 class="h4-text mb-3">Export Per/Periode</h4>
                <form action="/spmb/do-export-periode" method="POST" class="row">
                    @csrf
                    <div class="col-sm">
                        <div class="input-group input-group-static mb-4">
                            <label for="exampleFormControlSelect1" class="ms-0">Tahun</label>
                            <select name="id_periode" class="form-control" id="export-tahun-select">
                                @foreach ($listPeriode as $periode)
                                    <option value={{ $periode->id }}>{{ $periode->periode_text }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm" style=" display: flex; align-items: center;">
                        <button name="export-btn" value="" class="btn bg-gradient-success btn-lg w-100">
                            Download [CSV]</button>
                    </div>

                </form>
            </div>
        </div>


    </main>
    <script src="{{ asset('assets') }}/js/rz/rz-dashboard.js"></script>
    </x-rlayout>
