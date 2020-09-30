#!/usr/bin/env node

/** Simple script to create a scaffold for your custom theme.
 * You could do this manually by simply copying the scaffold folder in the base theme & putting it in the theme folder + renaming it.
 * But why go through all that trouble if we've done the lifting for you? ;-)
 * Use: node create-theme <themename> (replace <themename> by the name of the custom theme you're creating)
 **/
const theme = `${__dirname}/../../theme`;

try {
    let themeName = process.argv[2];
    if (!themeName) {
        requestThemeName();
    } else {
        createScaffold(themeName);
    }

} catch (e) {
    console.log(e);
}

function requestThemeName() {
    const readline = require('readline').createInterface({
        input: process.stdin,
        output: process.stdout,
    });

    readline.question(`What's the name for your new theme?\t`, name => {
        console.log(`Hi ${name}!`);
        createScaffold(name);
        readline.close();
    });
}

function createScaffold(themeName) {
    console.log(`Creating scaffold for new theme ${themeName}.\n`);
    executeShellCommand(`cp -R ${theme}/base/scaffold ${theme}/${themeName}`);

    console.log(`Scaffold created.\nYou now have a directory ${themeName} as a subdirectory of the theme folder, with a few files & folder to get you started.\n`);
}

function executeShellCommand(command) {
    const { exec } = require('child_process');
    exec(command, (error, stdOut, stdError) => {
        if (error) {
            console.log(`exec error: ${error}`);
            return;
        }

        if (!stdOut && stdError) {
            console.log(`error info: ${stdError}`);
        }

        console.log(stdOut);
        return process.exit(0);
    });
}
