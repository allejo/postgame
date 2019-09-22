import dayjs from 'dayjs';

/** @type {NodeListOf<HTMLDivElement>} */
const replaySections = document.querySelectorAll('div[data-day-section]');

for (let i = 0; i < replaySections.length; i++) {
    const replaySection = replaySections[i];
    const replayDate = replaySection.getAttribute('data-day-section');

    /** @type {NodeListOf<HTMLDivElement>} */
    const replays = replaySection.querySelectorAll(`article:not([data-replay-date="${replayDate}"])`);

    for (let j = replays.length - 1; j >= 0; j--) {
        const replay = replays[j];
        const utcDate = replay.getAttribute('data-replay-date');
        const date = dayjs(utcDate).format('YYYY-MM-DD');
        const targetSection = document.querySelector(`[data-day-section="${date}"]`);

        targetSection.prepend(replay);
    }
}
