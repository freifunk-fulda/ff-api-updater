Freifunk Fulda - API Updater
============================

Update our information in the freifunk.net directory API.

You have to copy `config.example.php` to `config.php`.

Change `nodes.json` location and update the communityname.

The new script generates an human readable and a minimized version of the 
`api.json` file.

To upgrade the api file run `./ff-api-update.php newapi.json`

Syntax:
`ff-api-update.php [api.json]` 