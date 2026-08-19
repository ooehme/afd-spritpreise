import { copyFile, mkdir, readFile } from 'node:fs/promises';
import { spawnSync } from 'node:child_process';

const files = ['area-picker.js', 'frontend.js', 'block.js'];
await mkdir('assets/js', { recursive: true });
for (const file of files) {
    const check = spawnSync(process.execPath, ['--check', `src/${file}`], { stdio: 'inherit' });
    if (check.status !== 0) process.exit(check.status || 1);
    await copyFile(`src/${file}`, `assets/js/${file}`);
}
JSON.parse(await readFile('block/block.json', 'utf8'));
console.log(`Built ${files.length} JavaScript assets.`);
