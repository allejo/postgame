# Postgame

A Symfony 4 website for summarizing BZFlag replay files.

## Local Development

This project follows the standard Symfony 4 way of doing things, meaning Yarn + Webpack Encore for front-end assets and Symfony + Twig for back-end.

Additionally, this project is using the experimental [BZFlag style-guide](https://github.com/BZFlag-Dev/style-guide) for it's front-end design.

```
# We need to download a dev build of the BZ style-guide
git clone https://github.com/BZFlag-Dev/style-guide assets/vendor/style-guide

# Install dependencies
composer install
yarn install

# Run the CSS + JS build script in one terminal
yarn encore dev --watch

# Run the PHP web server in another terminal
bin/console server:run
```

## License

[MIT](./LICENSE.md)
