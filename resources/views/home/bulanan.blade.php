<x-layout bodyClass="g-sidenav-show  bg-gray-200">
    <script src="../../assets/js/plugins/chartjs.min.js"></script>

    <x-navbars.sidebar activePage="dashboard"></x-navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-navbars.navs.auth titlePage="Dashboard"></x-navbars.navs.auth>

        <div class="container">
            <div class="card mt-3 mb-2 p-2">
                <h1 class="p-3">Bulanan</h1>
                <form action="/spmb/admin-bulanan" method="POST" class="row p-3">
                    @csrf
                    <div class="col-sm">
                        <div class="input-group input-group-static mb-4">
                            <label for="exampleFormControlSelect1" class="ms-0">Bulan</label>
                            <select name="bulan-value" class="form-control" id="exampleFormControlSelect1">
                                @for ($i = 0; $i < sizeof($bulanIndonesia); $i++)
                                    <option value="{{ $i + 1 }}" {{ $i === $dateNow[0] - 1 ? 'selected' : '' }}>
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
                                    <option value={{ $i }} {{ $i === $dateNow[1] ? 'selected' : '' }}>
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

            <div class="container" style="padding: 0px; max-height: 570px">
                <div class="row">
                    <div class="col-sm">
                        <div class="card p-1" style="max-height: 550px">
                            <div class="p-2">
                                @if (array_sum($dataPoll) > 0)
                                    {!! $chart->container() !!}
                                @else
                                    <div style="height: 200px;"">
                                        Kosong
                                    </div>
                                @endif

                            </div>

                            <div class="row p-5">
                                <div class="col-sm p-2">
                                    <h3 class="h3-text">Kurang Puas</h3>
                                    <span>{{ $dataPoll[0] }}</span>
                                </div>
                                <div class="col-sm p-2">
                                    <h3 class="h3-text">Puas</h3>
                                    <span>{{ $dataPoll[1] }}</span>
                                </div>
                                <div class="col-sm p-2">
                                    <h3 class="h3-text">Sangat Puas</h3>
                                    <span>{{ $dataPoll[2] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="card h-100 p-1 overflow-auto custom-margin-card" style="max-height: 550px;">
                            <form action="/spmb/admin-bulanan" method="POST" class="row p-3 mw-100">
                                @csrf

                                <div class="col-sm">
                                    <h5 style="font-size: 17px; opacity: 0.7;">Periode {{ $periode->periode_text }}</h5>
                                </div>
                            </form>
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0" style="position: relative;">
                                    <thead style="position: sticky; top: 0px; background: white;">
                                        <tr>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Tanggal Input</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                Sumber</th>
                                            <th
                                                class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Respon</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($periodePoll as $hData)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-3 py-1">

                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $hData->created_at }}</h6>

                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ ucwords($hData->sumber_info) }}
                                                    </p>
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

                                        @if (sizeof($periodePoll) < 1)
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






        </div>
        <x-footers.auth></x-footers.auth>
        </div>


    </main>
    <script src="{{ $chart->cdn() }}"></script>
    {{ $chart->script() }}
    </x-rlayout>
