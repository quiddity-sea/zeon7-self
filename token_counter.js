// token_counter.js
// ------------------------------------------------------------
// Node.js script that counts Gemini tokens for a piece of text.
// Works with a file path argument or reads from STDIN.
// ------------------------------------------------------------

// 1️⃣ Install the required package first:
//    npm install @google/generative-ai
//    (requires Node 14+)

// 2️⃣ Set up Google credentials (service‑account JSON) as an env var:
//    set GOOGLE_APPLICATION_CREDENTIALS=C:\path\to\your\key.json

const fs = require('fs');
const path = require('path');
const { tokenizer } = require('@google/generative-ai');

/**
 * Returns the number of Gemini tokens for the supplied string.
 * @param {string} text
 * @returns {number}
 */
function tokenCount(text) {
    // The tokenizer.encode method returns an array of token IDs.
    return tokenizer.encode(text).length;
}

/**
 * Reads a file synchronously (UTF‑8) and returns its content.
 * @param {string} filePath
 * @returns {string}
 */
function readFileContent(filePath) {
    return fs.readFileSync(filePath, { encoding: 'utf8' });
}

/**
 * Main entry point.
 */
async function main() {
    const args = process.argv.slice(2);
    let content = '';

    if (args.length > 0) {
        // Assume first argument is a path to a markdown / txt file.
        const filePath = path.resolve(args[0]);
        if (!fs.existsSync(filePath)) {
            console.error(`❌ File not found: ${filePath}`);
            process.exit(1);
        }
        content = readFileContent(filePath);
    } else {
        // No file supplied – read from STDIN.
        console.log('Paste/enter your text, then press Ctrl+Z (Windows) or Ctrl+D (Unix) and Enter:');
        // Collect all data from stdin.
        content = '';
        for await (const chunk of process.stdin) {
            content += chunk;
        }
    }

    const tokens = tokenCount(content);
    const words = content.trim().split(/\s+/).filter(Boolean).length;
    const chars = content.length;

    console.log('\n=== Token count ===');
    console.log(`Characters : ${chars}`);
    console.log(`Words      : ${words}`);
    console.log(`Tokens     : ${tokens}`);
}

main().catch(err => {
    console.error('Error:', err);
    process.exit(1);
});
