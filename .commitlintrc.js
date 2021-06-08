const config = require('@commitlint/config-conventional');

config.rules['type-enum'][2].push('content');

module.exports = {
extends: ['@commitlint/config-conventional'],
};
