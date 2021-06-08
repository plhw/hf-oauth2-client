#!/usr/bin/env node

const updateJsonFile = require('update-json-file')

const composerPath = 'composer.json';
const currentVersion = require('../package.json').version;

updateJsonFile(composerPath, (data) => {
  data.version = `${currentVersion}`;

  return data;
}, {indent: '  '});

