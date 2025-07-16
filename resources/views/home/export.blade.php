<x-layout bodyClass="g-sidenav-show  bg-gray-200">
    <script src="../../assets/js/plugins/chartjs.min.js"></script>

    <x-navbars.sidebar activePage="export"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth titlePage="Export"></x-navbars.navs.auth>

        <div class="container">
            <div class="card mt-3 p-2">
                <h1 class="p-3">Export</h1>
                <form action="/export" method="POST" class="row p-3">
                    @csrf
                    <div class="col-sm">
                        <div class="input-group input-group-static mb-4">
                            <label for="exampleFormControlSelect1" class="ms-0">Tanggal</label>
                            <select name="hari-value" class="form-control" id="exampleFormControlSelect1">
                                @for ($i = 1; $i <= $dateNow[0]; $i++)
                                    <option value="{{ $i }}" {{ $i === $dateNow[1] ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="input-group input-group-static mb-4">
                            <label for="exampleFormControlSelect1" class="ms-0">Bulan</label>
                            <select name="bulan-value" class="form-control" id="exampleFormControlSelect1">
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
                            <select name="tahun-value" class="form-control" id="exampleFormControlSelect1">
                                @for ($i = 2023; $i <= 2030; $i++)
                                    <option value={{ $i }} {{ $i === $dateNow[3] ? 'selected' : '' }}>
                                        {{ $i }}
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-2 text-center">
                        <button class="btn bg-gradient-success mt-3">Filter</button>
                    </div>
                </form>
            </div>

            <div class="card mt-3 p-4">
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
            </div>
        </div>


    </main>
    </x-rlayout>
