import dayjs from 'dayjs';

/** @type {NodeListOf<HTMLDivElement>} */
const replaySections = document.querySelectorAll('section[data-day-section] > div');

for (let i = 0; i < replaySections.length; i++) {
    const replaySection = replaySections[i];
    const replayDate = replaySection.getAttribute('data-day-section');

    /** @type {NodeListOf<HTMLDivElement>} */
    const replaysToMove = replaySection.querySelectorAll(`article:not([data-replay-date="${replayDate}"])`);

    for (let j = replaysToMove.length - 1; j >= 0; j--) {
        const replay = replaysToMove[j];
        const utcDate = dayjs(replay.getAttribute('data-replay-date'));
        const date = utcDate.format('YYYY-MM-DD');

        let targetSection = document.querySelector(`[data-day-section="${date}"] > div`);

        if (!targetSection) {
            const newSection = (
                <section data-day-section={date}>
                    <h2 class="border-b h4 mb-3 py-2">
                        {utcDate.format('MMMM DD')}
                    </h2>

                    <div>
                        {replay}
                    </div>
                </section>
            );

            const replayContainer = document.getElementById('replay-container');
            const containers = replayContainer.children;

            for (let k = 0; k < containers.length; k++) {
                const container = containers[k];
                const containerDate = container.getAttribute('data-day-section');

                if (containerDate < date) {
                    replayContainer.insertBefore(newSection, container);
                    break;
                }
            }
        } else {
            targetSection.prepend(replay);
        }
    }

    if (replaySection.children.length === 0) {
        replaySection.parentNode.setAttribute('style', 'display: none;');
    }
}
