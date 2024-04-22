#!/usr/bin/env node
import chokidar from "chokidar";
import {exec} from "child_process";
import chalk from "chalk";

// Define the directory you want to watch
const watcher = chokidar.watch('./lang/**/*.php', {
    ignored: /(^|[\/\\])\../, // ignore dotfiles
    persistent: true
});

function runArtisanCommand() {
    exec('php artisan dictionary --pretty', (err, stdout, stderr) => {
        if (err) {
            console.error(chalk.redBright(`exec error: ${err}`));
            return;
        }
        
        console.log(chalk.redBright(`${stdout}`));
    });
}

watcher
    .on('change', path => {
        console.clear();
        console.log(chalk.greenBright(`File ${path} has been changed\n`));
        
        runArtisanCommand();
    });

console.clear();
runArtisanCommand();

console.log(chalk.greenBright('Watching for changes in lang directory...'));