/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

require('@fortawesome/fontawesome-free/css/all.min.css');
require('c3/c3.css');

// any CSS you require will output into a single css file (app.css in this case)
require('../sass/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// const $ = require('jquery');

require('../vendor/style-guide/src/js/bootstrap');
require('../vendor/style-guide/src/js/navigation');
require('../vendor/style-guide/src/js/table');
require('../vendor/style-guide/src/js/tablist');

require('./lib/graphs');
require('./lib/timestamps');
