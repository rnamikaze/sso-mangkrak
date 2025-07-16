<x-layout bodyClass="g-sidenav-show  bg-gray-200">
    <x-navbars.sidebar activePage="harian"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-navbars.navs.auth titlePage="Tables"></x-navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-3 mb-5 p-2">
                        <h1 class="p-3">Harian</h1>
                        <form action="/spmb/admin-harian" method="POST" class="row p-3">
                            @csrf
                            <div class="col-sm">
                                <div class="input-group input-group-static mb-4">
                                    <label for="exampleFormControlSelect1" class="ms-0">Tanggal</label>
                                    <select name="hari-value" class="form-control" id="exampleFormControlSelect1">
                                        @for ($i = 1; $i <= $dateNow[0]; $i++)
                                            <option value="{{ $i }}"
                                                {{ $i === $dateNow[1] ? 'selected' : '' }}>
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
                                            <option value="{{ $i + 1 }}"
                                                {{ $i === $dateNow[2] - 1 ? 'selected' : '' }}>
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
                                            <option value={{ $i }}
                                                {{ $i === $dateNow[3] ? 'selected' : '' }}>
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
                    <div class="card my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-secondary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-capitalize ps-3">Data Harian</h6>
                            </div>
                        </div>
                        <div class="card-body px-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Tanggal Input</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Nama</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Respon</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($harianData as $hData)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-3 py-1">

                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $hData->created_at }}</h6>

                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">Tanpa Nama</p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    @if (intval($hData->poll_code) === 1)
                                                        <span
                                                            class="badge badge-sm bg-gradient-danger">{{ $hData->poll }}</span>
                                                    @elseif (intval($hData->poll_code) === 2)
                                                        <span
                                                            class="badge badge-sm bg-gradient-warning">{{ $hData->poll }}</span>
                                                    @elseif (intval($hData->poll_code) === 3)
                                                        <span
                                                            class="badge badge-sm bg-gradient-success">{{ $hData->poll }}</span>
                                                    @endif

                                                </td>

                                            </tr>
                                        @endforeach

                                        @if (sizeof($harianData) < 1)
                                            <tr>
                                                <td class="text-center">Kosong</td>
                                                <td class="text-center">Kosong</td>
                                                <td class="text-center">Kosong</td>
                                            </tr>
                                        @endif


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-footers.auth></x-footers.auth>
        </div>
    </main>

</x-layout>
