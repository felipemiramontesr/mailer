const fs = require('fs');
const path = require('path');

/**
 * Professional Secret Injector
 * Handles literal string replacement to avoid shell/sed character corruption.
 */

const paths = {
    configTemplate: 'public/config.php.template',
    hashTemplate: 'public/access_hash.php.template',
    distConfig: 'dist/config.php',
    distHash: 'dist/access_hash.php'
};

function inject() {
    console.log('--- CI/CD Secret Injection Sequence Start ---');

    // 1. Inject config.php
    if (fs.existsSync(paths.configTemplate)) {
        let config = fs.readFileSync(paths.configTemplate, 'utf8');
        config = config.replace('__GEMINI_API_KEY__', process.env.VITE_GEMINI_API_KEY || '');
        config = config.replace('__SMTP_HOST__', process.env.SMTP_HOST || '');
        config = config.replace('__SMTP_PORT__', process.env.SMTP_PORT || '');
        config = config.replace('__SMTP_USER__', process.env.SMTP_USER || '');
        config = config.replace('__SMTP_PASS__', process.env.SMTP_PASSWORD || '');

        fs.writeFileSync(paths.distConfig, config);
        console.log('✓ Production config.php generated successfully.');
    }

    // 2. Inject access_hash.php
    if (fs.existsSync(paths.hashTemplate)) {
        let hashFile = fs.readFileSync(paths.hashTemplate, 'utf8');
        const secretHash = process.env.MAILER_PASSWORD_HASH;

        if (!secretHash) {
            console.error('✖ CRITICAL ERROR: MAILER_PASSWORD_HASH secret is missing in environment.');
            process.exit(1);
        }

        // Use string replacement which is literal and safe for characters like $
        hashFile = hashFile.replace('__MAILER_PASSWORD_HASH__', secretHash);

        fs.writeFileSync(paths.distHash, hashFile);
        console.log('✓ Secure access_hash.php generated successfully (Atomic Injection).');
    }

    // 3. Ensure auth_codes protection
    const codesDir = 'dist/auth_codes';
    if (!fs.existsSync(codesDir)) {
        fs.mkdirSync(codesDir, { recursive: true });
    }
    const htaccessSource = 'public/auth_codes/.htaccess';
    if (fs.existsSync(htaccessSource)) {
        fs.copyFileSync(htaccessSource, path.join(codesDir, '.htaccess'));
        console.log('✓ Security .htaccess synchronized for auth_codes.');
    }

    console.log('--- Injection Sequence Complete ---');
}

inject();
