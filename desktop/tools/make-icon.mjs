import fs from 'node:fs';
import pngToIco from 'png-to-ico';

const source = new URL('../build/icon.png', import.meta.url);
const target = new URL('../build/icon.ico', import.meta.url);
const buffer = await pngToIco(source.pathname.replace(/^\/([A-Za-z]:)/, '$1'));
fs.writeFileSync(target, buffer);
console.log('icon.ico created');
