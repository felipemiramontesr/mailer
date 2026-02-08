# Security & 2FA Implementation

## 🛡️ Threat Model & Protection

This system is designed to prevent unauthorized email relaying (spam) and protect sensitive API keys.

### 1. Two-Factor Authentication (2FA)

- **Phase 1**: Initial request requires a `MAILER_PASSWORD`. If correct, the system generates a random 6-digit PIN.
- **Phase 2**: The PIN is sent only to the `MASTER_AUTH_EMAIL` configured in the backend.
- **Phase 3**: The user must input the PIN in the UI within 5 minutes to authorize the actual transmission.

### 2. Secret Management

- **No Client Secrets**: Gemini API keys and SMTP passwords are **never** bundled into the JavaScript.
- **Injection Script**: A professional `inject-secrets.cjs` utility maps environment variables to a non-public `config.php`.
- **.gitignore**: Critical files like `config.php`, `access_hash.php`, and auth logs are strictly ignored by Git.

### 3. Backend Hardening

- **HTTP Status Codes**: Proper 401 (Unauthorized) and 403 (Forbidden) responses for auth failures.
- **Technical Logs**: Every transmission attempt is logged in `public/logs/` for security auditing.
- **Type Safety**: The backend enforces strict JSON payloads and validates operation codes before execution.

---

> [!IMPORTANT]
> Never commit `public/config.php` or any file containing real passwords to the repository. Use the environment variables provided by the deployment platform.
