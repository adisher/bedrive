/* eslint-disable no-console */
const path = require('path');
const fse = require('fs-extra');
const glob = require('fast-glob');

const DIR = path.resolve(__dirname, '../src/common/icons/material');

function normalizeFileName(file) {
  return path.parse(file).name;
}

function createIndexTyping(files) {
  const contents = `${files
    .map(
      file =>
        `export {${normalizeFileName(file)}Icon} from "./${normalizeFileName(
          file
        )}";`
    )
    .join('\n')}`;

  return fse.writeFile(path.resolve(DIR, 'index.ts'), contents, 'utf8');
}

async function run() {
  await fse.ensureDir(DIR);
  const files = await glob('!(index)*.tsx', {cwd: DIR});
  await createIndexTyping(files);
}

run();
