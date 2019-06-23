import dayjs from 'dayjs';

/**
 * Get the timezone abbreviation from a date.
 *
 * @param {Date} date
 */
function timezoneAbbr(date) {
    return date.toLocaleTimeString('en-us', { timeZoneName: 'short' }).split(' ')[2];
}

/** @type {NodeListOf<HTMLTimeElement>} */
const timeElements = document.querySelectorAll('time[data-format]');

for (let i = 0; i < timeElements.length; i++) {
    const element = timeElements[i];
    const datetime = element.getAttribute('datetime');
    const format = element.getAttribute('data-format');

    const date = new Date(datetime);
    const tzAbbr = timezoneAbbr(date);

    element.innerText = dayjs(date).format(format) + ' ' + tzAbbr;
}
