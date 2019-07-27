import c3 from 'c3';

const graphs = document.querySelectorAll('[data-role="c3-chart"]');

const graphDefs = {
    'timeseries': (id, definition) => {
        const c3_def = {
            bindto: `#${id}`,
            data: {
                json: definition['data'],
                keys: {
                    x: definition['x-axis'],
                    value: definition['lines']
                },
                type: 'spline',
            },
            axis: {
                x: {
                    type: 'timeseries',
                    tick: {
                        format: '%Y-%m-%d',
                    },
                }
            }
        };

        c3.generate(c3_def);
    },
};

for (let i = 0; i < graphs.length; i++) {
    const graph = graphs[i];

    const id = graph.getAttribute('id');
    const url = graph.getAttribute('data-url');

    fetch(url)
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            graphDefs[data['type']](id, data);
        })
    ;
}
