import c3 from 'c3';

const graphs = document.querySelectorAll('[data-role="c3-chart"]');

function typeToC3(type) {
    const mapping = {
        'line': 'spline',
    };

    return mapping[type];
}

for (let i = 0; i < graphs.length; i++) {
    const graph = graphs[i];

    const id = graph.getAttribute('id');
    const type = graph.getAttribute('data-graph');
    const data = JSON.parse(graph.getAttribute('data-data'));

    c3.generate({
        bindto: `#${id}`,
        data: {
            columns: data,
            type: typeToC3(type),
        },
    });
}
