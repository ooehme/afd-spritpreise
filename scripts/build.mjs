import { copyFile, mkdir, readFile, readdir } from 'node:fs/promises';
import { spawnSync } from 'node:child_process';

const files = ['area-picker.js', 'frontend.js', 'block.js', 'block-variations.js', 'block-compact.js', 'block-icon.js', 'block-variation-icon.js', 'admin-shortcodes.js'];
await mkdir('assets/js', { recursive: true });
for (const file of files) {
    const check = spawnSync(process.execPath, ['--check', `src/${file}`], { stdio: 'inherit' });
    if (check.status !== 0) process.exit(check.status || 1);
    await copyFile(`src/${file}`, `assets/js/${file}`);
}
async function blockMetadata(directory) {
    const entries = await readdir(directory, { withFileTypes: true });
    const found = [];
    for (const entry of entries) {
        const path = `${directory}/${entry.name}`;
        if (entry.isDirectory()) found.push(...await blockMetadata(path));
        if (entry.isFile() && entry.name === 'block.json') found.push(path);
    }
    return found;
}

const metadata = await blockMetadata('block');
for (const file of metadata) JSON.parse(await readFile(file, 'utf8'));
console.log(`Built ${files.length} JavaScript assets and validated ${metadata.length} blocks.`);
