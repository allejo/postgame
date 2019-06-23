import dayjs from 'dayjs';

/** @type {NodeListOf<HTMLTimeElement>} */
const timeElements = document.querySelectorAll('time[data-format]');

for (let i = 0; i < timeElements.length; i++) {
    const element = timeElements[i];
    const datetime = element.getAttribute('datetime');
    const format = element.getAttribute('data-format');

    element.innerText = dayjs(datetime).format(format);
}
