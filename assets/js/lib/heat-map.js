const LOCAL_STORAGE_KEY = 'postgame/options/heatmapEnabled';

const heatmapEnabled = window.localStorage.getItem(LOCAL_STORAGE_KEY) === '1';
const heatmapToggles = document.querySelectorAll('[data-role="show-map-overlay"]');
const heatmapOverlays = document.getElementsByClassName('map-overlay');

function toggleOverlays(enable) {
    for (const overlay of heatmapOverlays) {
        const fxnCall = enable ? 'remove' : 'add';
        overlay.classList[fxnCall]('d-none');
    }
}

toggleOverlays(heatmapEnabled);

heatmapToggles.forEach((checkbox) => {
    checkbox.checked = heatmapEnabled;
    checkbox.addEventListener('change', (event) => {
        const checked = event.currentTarget.checked;
        window.localStorage.setItem(LOCAL_STORAGE_KEY, String(+checked));

        toggleOverlays(checked);
    });
});
