const allEmotButton = document.getElementsByClassName('poll-btn');
const confirmPollButton = document.getElementById('confirm-poll');
const clearPollButton = document.getElementById('clear-poll');

let pollSelected = null;

function start() {
    confirmPollButton.disabled = true;
    clearPollButton.disabled = true;
}

clearPollButton.addEventListener('click', function (event) {
    event.preventDefault();

    confirmPollButton.value = "";

    if (pollSelected !== null) {
        allEmotButton[pollSelected].classList.remove('poll-selected');
    }

    start();
})

for (let btn = 0; btn < allEmotButton.length; btn++) {
    allEmotButton[btn].addEventListener('click', function () {
        if (pollSelected !== null) {
            allEmotButton[pollSelected].classList.remove('poll-selected');
        }
        confirmPollButton.disabled = false;
        clearPollButton.disabled = false;

        if (btn === 0) {
            // confirmPollButton.innerHTML = "Kurang Puas " + '&nbsp;<i class="fa-solid fa-thumbs-down"></i>';
            confirmPollButton.value = 'kurang puas'
        } else if (btn === 1) {
            // confirmPollButton.innerHTML = "Puas " + '&nbsp;<i class="fa-solid fa-thumbs-up"></i>';
            confirmPollButton.value = 'puas'
        } else if (btn === 2) {
            // confirmPollButton.innerHTML = "Sangat Puas " + '&nbsp;<i class="fa-solid fa-thumbs-up"></i>&nbsp;<i class="fa-solid fa-thumbs-up"></i>';
            confirmPollButton.value = 'sangat puas'
        }

        pollSelected = btn;
        allEmotButton[btn].classList.add('poll-selected');

    })
}

start();
