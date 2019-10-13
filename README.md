# Work Sample for 040

## Get it running

The project is made as a React app talking to an api.

### The api

1. In `./api` folder, run `composer install`.
2. Create a MySQL database.
3. Copy `config-sample.php` to a new file `config.php`, and set your own credentials.
4. Serve the `./api` folder with Apache
5. To setup the database schema, visit `[http://your/api/url/]db-schema.php`. This script also resets the DB to initial state.

### The App

1. In `./webapp` folder, run `npm install`
2. In `./webapp/src`, copy `config-sample.js` to a new file `config.js`, and set your own credentials.
3. Run `npm run build` and serve with `serve -s build`

## Enjoy!
