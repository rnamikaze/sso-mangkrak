<x-homepoll.header></x-homepoll.header>

<body class="rz-body">
    <header class="rz-header">
        <div class="logo-group">
            <img src="{{ asset('assets') }}/img/logos/Logo-Unusida-Putih-min.png" alt="Logo Unusids Putih">
            <img src="{{ asset('assets/img/logos/kampus_merdeka_putih.png') }}" alt="Logo Unusids Putih">
        </div>
        <div class="header-title" style="margin-left: -30px">PMB UNUSIDA</div>
        <div class="logo-group">
            <img src="{{ asset('assets') }}/img/logos/Logo-Pmb-Putih.png" alt="Logo Unusids Putih">
            <img src="{{ asset('assets') }}/img/logos/Logo-1-Dekade-Ref-Putih-min.png" alt="Logo Unusids Putih">
        </div>
    </header>
    <main class="rz-main">
        {{-- <div class="title-area">
            <h1>
                Terima Kasih
            </h1>

            <h2>Mengalihkan dalam <span id="counter">5</span> detik...</h2>
        </div> --}}
        <div class="survey-source" id="survey_source">
            <div class="survey-source_title" style="display: flex; justify-content: center; width: 410px;">
                Sumber Informasi
            </div>
            <div class="survey_flex">
                <div id="dosen" class="survey-source_item">
                    Dosen
                </div>
                <div id="tendik" class="survey-source_item">
                    Karyawan/Tendik
                </div>
            </div>
            <div class="survey_flex">
                <div id="mahasiswa" class="survey-source_item">
                    Mahasiswa
                </div>
                <div id="sekolah" class="survey-source_item">
                    Sekolah
                </div>
            </div>
            <div class="survey_flex">
                <div id="instagram" class="survey-source_item">
                    Instagram
                </div>
                <div id="website" class="survey-source_item">
                    Website
                </div>
            </div>
            <div class="survey_flex">
                <div id="survey_lainnya" class="survey-source_item">
                    Lainnya
                    <textarea id="textarea_lainnya" maxlength="200" disabled style="margin-top: 10px;" rows="5"
                        placeholder="Isi disini...">Tidak di isi</textarea>
                </div>
            </div>
            <form action="/spmb/update-poll-info" method="POST">
                @csrf
                <input type="hidden" name="selected_id" id="survey-id" value={{ $selectedId }}>
                <input type="hidden" name="survey_info" id="survey-info" value="">
                <button id="submit-button" disabled class="submit-button" type="submit">SELESAI</button>
            </form>
        </div>

    </main>
    <x-homepoll.footer nowDate="{{ $nowDate }}"></x-homepoll.footer>
    <script src="{{ asset('assets') }}/js/rz/dashpoll.js"></script>
    <script>
        let selected = null;
        let textAreaValue = "";

        const surveyInfo = document.getElementById('survey-info');
        const textAreaForm = document.getElementById('textarea_lainnya');
        const surveySource = document.getElementById('survey_source');

        const surveySourceChild = surveySource.getElementsByClassName('survey-source_item');

        for (let i = 0; i < surveySourceChild.length; i++) {
            surveySourceChild[i].addEventListener('click', function() {
                for (let i = 0; i < surveySourceChild.length; i++) {
                    surveySourceChild[i].classList.remove('active');
                }

                if (surveySourceChild[i].getAttribute('id') === "survey_lainnya") {
                    surveySourceChild[i].getElementsByTagName('textarea')[0].disabled = false;
                    selected = "Tidak di Isi";
                } else {
                    selected = surveySourceChild[i].getAttribute('id');
                    textAreaForm.disabled = true;
                }
                surveySourceChild[i].classList.add('active');
                surveyInfo.value = selected;

                if (selected !== null) document.getElementById('submit-button').disabled = false;
                // // console.log(selected);
                // // console.log(surveyInfo.value);
                // // console.log(surveySourceChild[i].getAttribute('id'));
            });

        }

        textAreaForm.addEventListener('input', function() {
            textAreaValue = textAreaForm.value;
            if (textAreaValue.length < 1) textAreaValue = "Tidak di Isi";
            selected = textAreaValue;
            surveyInfo.value = selected;
            // // console.log(surveyInfo.value);
        })

        document.getElementById('submit-button').disabled = true;
        textAreaForm.disabled = true;

        // // console.log(surveySourceChild);
        // let counter = 5;

        // const redirect = setInterval(() => {
        //     counter--;

        //     document.getElementById('counter').innerText = counter;


        //     if (counter <= 0) {
        //         clearInterval(redirect);
        //         window.location.href = "{{ route('spmb.homepoll') }}";
        //     }
        // }, 1000);

        // setTimeout(function() {

        // }, 5000);
    </script>
</body>
