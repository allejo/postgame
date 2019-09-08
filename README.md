# Postgame

[![GitHub release](https://img.shields.io/github/v/release/allejo/postgame?include_prereleases)](https://github.com/allejo/postgame/releases/latest)
[![GitHub license](https://img.shields.io/github/license/allejo/postgame)](https://github.com/allejo/postgame/blob/master/LICENSE.md)

A Symfony 4 website for summarizing BZFlag replay files.

<table>
<tr>
<td><img src="./github/assets/postgame-homepage.jpg" alt="Homepage of this application"></td>
<td><img src="./github/assets/postgame-replay-list.jpg" alt="List view of available replays"></td>
<td><img src="./github/assets/postgame-replay-show.jpg" alt="Summary view of single replay"></td>
</tr>
</table>

## Project Setup

This project follows the standard Symfony 4 way of doing things, meaning Yarn + Webpack Encore for front-end assets and Symfony + Twig for back-end.

Additionally, this project is using the experimental [BZFlag style-guide](https://github.com/BZFlag-Dev/style-guide) for it's front-end design.

### Setup

1. Pull down the necessary submodules with `git submodule update --init`
2. Duplicate `.env` to an `.env.local` file in the root of the project
3. Set the `DATABASE_URL` environment variable

### Production Deployment

To deploy to production, you don't need to install development dependencies.

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
yarn install --production

# Build our front-end assets
yarn build

# Setup Symfony cache
APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear

# Migrate database schemas
php bin/console doctrine:migrations:migrate
```

Once you've built everything, point your web server to the `public/` folder.

### Local Development

For local development, you'll need to install development dependencies and run local processes.

```bash
# Install dependencies
composer install
yarn install

# Migrate database schemas
php bin/console doctrine:migrations:migrate

# Run the CSS + JS build script in one terminal
yarn encore dev --watch

# (Optional) Run the PHP web server in another terminal. Otherwise, you'll
# need a local web server pointing to `public/`.
bin/console server:run
```

## License

[MIT](./LICENSE.md)
