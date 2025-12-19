let mix = require("laravel-mix");
let path = require("path");

mix.extend("nova", new require("laravel-nova-devtool"));

mix.setPublicPath("dist")
    .js("resources/js/field.js", "dist/js/nova-fontawesome.js")
    .vue({ version: 3 })
    .nova("marshmallow/nova-fontawesome");
